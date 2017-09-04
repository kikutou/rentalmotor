<?php
/*
 * Copyright(c) 2016 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Event\EventArgs;
use Eccube\Util\EntityUtil;

use Plugin\RemisePayment\Event\RemiseEventBase;

/**
 * 収納情報通知制御
 */
class PaymentAcptController
{
    /**
     * Application
     */
    public $app;

    /**
     * config情報
     */
    public $pluginConfig;

    /**
     * 収納情報通知
     */
    public function index(Application $app, Request $request)
    {
        $this->app = $app;
        $requestData = $request->request->all();

        $configService = $this->app['eccube.plugin.service.remise_config'];
        $logService = $this->app['eccube.plugin.service.remise_log'];

        // config取得
        $this->pluginConfig = $configService->getPluginConfig();

        // リクエストログ作成
        $RemiseLog = $logService->createLogForMultiResult();
        $RemiseLog->addMessage($_REQUEST);
        $logService->outputRemiseLog($RemiseLog);

        if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
            // イベント生成
            $event = new EventArgs(
                array(),
                $request
            );
            $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::FRONT_PLUGIN_REMISE_ACPT_INDEX_INITIALIZE, $event);
        }

        $returnContents = "";
        switch ($this->lfGetMode($requestData))
        {
            // マルチ・収納情報通知処理
            case 'conveni_check':
                $returnContents = $this->lfRemiseConveniCheck($requestData);
                break;

            default:
                $error_code = 901;
                $returnContents = 'RESULT_FORMAT_ERROR:' . $error_code;
                break;
        }

        $response = new Response($returnContents, Response::HTTP_OK,
            array('Content-Type' => 'text/html; charset=Shift_JIS')
        );

        if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
            // イベント生成
            $event = new EventArgs(
                array(
                    'response' => $response,
                ),
                $request
            );
            $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::FRONT_PLUGIN_REMISE_ACPT_INDEX_COMPLETE, $event);
        }

        return $response;
    }

    /**
     * 処理モード設定
     *
     * @param  array  $requestData  リクエスト情報
     *
     * @return  string  $mode  処理モード名
     */
    protected function lfGetMode($requestData)
    {
        $mode = '';
        // マルチ決済・収納情報通知処理
        if (isset($requestData["JOB_ID"]) && isset($requestData["REC_FLG"])) {
            $mode = 'conveni_check';
        }
        return $mode;
    }

    /**
     * マルチ決済・収納情報通知処理
     *
     * @param  array  $requestData  リクエスト情報
     */
    function lfRemiseConveniCheck($requestData)
    {
        $logService = $this->app['eccube.plugin.service.remise_log'];

        $error_code = 999;
        if (!isset($requestData["JOB_ID"]) || !isset($requestData["REC_FLG"]))
        {
            return 'RESULT_FORMAT_ERROR:' . $error_code;
        }

        // 収納結果ログ作成
        $RemiseLog = $logService->createLogForMultiResult();
        $RemiseLog->addMessage('job_id : ' . $requestData["JOB_ID"]);
        $logService->outputRemiseLog($RemiseLog);

        // 収納済みでない場合
        if ($requestData["REC_FLG"] != $this->app["config"]["remise_convenience_charge"])
        {
            $RemiseLog = $logService->createLogForMultiResult();
            $RemiseLog->addMessage('rec_flg : ' . $requestData["REC_FLG"]);
            $logService->outputRemiseLog($RemiseLog);
            return 'ERROR:' . $error_code;
        }

        // 請求番号と金額の取得
        $orderId = 0;
        $paymentTotal = 0;
        if (isset($requestData["S_TORIHIKI_NO"])) {
            $orderId = $requestData["S_TORIHIKI_NO"];
        }
        if (isset($requestData["TOTAL"])) {
            $paymentTotal = $requestData["TOTAL"];
        }

        // 受注情報取得
        $Order = $this->app['eccube.repository.order']->findOneById(array('id' => $orderId));

        // 受注情報なし
        if (EntityUtil::isEmpty($Order)) {
            $error_code = 101;
            $RemiseLog = $logService->createLogForMultiResult();
            $RemiseLog->addMessage('error_code : ' . $error_code);
            $logService->outputRemiseLog($RemiseLog);
            return 'NOTFOUND_ORDER:' . $error_code;
        }

        // 支払方法取得
        $RemisePaymentMethod = $this->app['eccube.plugin.remise.repository.remise_payment_method']
            ->find($Order->getPayment()->getId());

        // 支払方法対象外
        if (EntityUtil::isEmpty($RemisePaymentMethod)) {
            $error_code = 105;
            $RemiseLog = $logService->createLogForMultiResult();
            $RemiseLog->addMessage('error_code : ' . $error_code);
            $logService->outputRemiseLog($RemiseLog);
            return 'ERROR_METHOD:' . $error_code;
        }

        // 金額の相違
        if ($Order->getPaymentTotal() != $paymentTotal) {
            $error_code = 102;
            $RemiseLog = $logService->createLogForMultiResult();
            $RemiseLog->addMessage('error_code : ' . $error_code);
            $logService->outputRemiseLog($RemiseLog);
            return 'TOTAL_MISMATCH:' . $error_code;
        }

        // 受注情報更新
        if (!$this->lfOrderUpdate($Order, $requestData)) {
            return 'UPDATE_ERROR:' . $error_code;
        }

        // 収納情報通知の応答
        return $this->app["config"]["remise_convenience_charge_ok"];
    }

    /**
     * 受注情報更新
     *
     * @param  Order  $Order  受注情報
     * @param  array  $requestData  リクエスト情報
     * @return  bool
     */
    protected function lfOrderUpdate($Order, $requestData)
    {
        $configService = $this->app['eccube.plugin.service.remise_config'];
        $remiseOrderService = $this->app['eccube.plugin.service.remise_order'];

        // config取得
        $pluginConfig = $configService->getPluginConfig();

        // ルミーズプラグイン設定情報の取得
        $RemiseConfig = $this->app['eccube.plugin.remise.repository.remise_config']
            ->findOneBy(array('code' => $pluginConfig['code']));

        // 設定情報取得
        $info = $RemiseConfig->getUnserializeInfo();
        if (empty($info)) $info = array();

        // ルミーズ受注結果情報の更新
        $remiseOrderService->updateRemiseResult($Order, $requestData);

        // 入金済みへの更新処理
        $remiseOrderService->updatePreEnd($Order, true);

        if (isset($info['receiptmail_flg']) && $info['receiptmail_flg'] == "1")
        {
            // 入金確認メールを送信
            $remiseOrderService->sendReceiptMail($Order);
        }

        return true;
    }
}
