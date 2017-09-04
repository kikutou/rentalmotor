<?php
/*
 * Copyright(c) 2015 REMISE Corporation. All Rights Reserved.
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
 * 結果通知制御
 */
class PaymentRecvController
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
     * 結果通知
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
        $RemiseLog = $logService->createLogForCardResult();
        $RemiseLog->addMessage($_REQUEST);
        $logService->outputRemiseLog($RemiseLog);

        if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
            // イベント生成
            $event = new EventArgs(
                array(),
                $request
            );
            $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::FRONT_PLUGIN_REMISE_RECV_INDEX_INITIALIZE, $event);
        }

        $returnContents = "";
        switch ($this->lfGetMode($requestData))
        {
            // カード決済・結果通知処理
            case 'credit_complete':
                $returnContents = $this->lfRemiseCreditResultCheck($requestData);
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
            $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::FRONT_PLUGIN_REMISE_RECV_INDEX_COMPLETE, $event);
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
        if (isset($requestData["X-TRANID"])) {
            if ($requestData['REC_TYPE'] == "RET") {
                if (isset($requestData['X-PARTOFCARD'])) {
                    // カード決済・結果通知処理
                    $mode = 'credit_complete';
                } else {
                    // カード決済（拡張セット）・結果通知処理
                    $mode = 'extset_complete';
                }
            }
        }
        return $mode;
    }

    /**
     * カード決済・結果通知処理
     *
     * @param  array  $requestData  リクエスト情報
     */
    protected function lfRemiseCreditResultCheck($requestData)
    {
        $logService = $this->app['eccube.plugin.service.remise_log'];

        $error_code = 999;
        if (!isset($requestData["X-TRANID"]) || !isset($requestData["X-PARTOFCARD"]))
        {
            return 'RESULT_FORMAT_ERROR:' . $error_code;
        }

        // カード結果ログ作成
        $RemiseLog = $logService->createLogForCardResult();
        $RemiseLog->addMessage('transaction_id : ' . $requestData["X-TRANID"]);
        $logService->outputRemiseLog($RemiseLog);

        // 請求番号と金額の取得
        $orderId = 0;
        $paymentTotal = 0;
        if (isset($requestData["X-S_TORIHIKI_NO"])) {
            $orderId = $requestData["X-S_TORIHIKI_NO"];
        }
        if (isset($requestData["X-TOTAL"])) {
            $paymentTotal = $requestData["X-TOTAL"];
        }

        // 受注情報取得
        $Order = $this->app['eccube.repository.order']->findOneById(array('id' => $orderId));

        // 受注情報なし
        if (EntityUtil::isEmpty($Order)) {
            $error_code = 101;
            $RemiseLog = $logService->createLogForCardResult();
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
            $RemiseLog = $logService->createLogForCardResult();
            $RemiseLog->addMessage('error_code : ' . $error_code);
            $logService->outputRemiseLog($RemiseLog);
            return 'ERROR_METHOD:' . $error_code;
        }

        // 金額の相違
        if ($Order->getPaymentTotal() != $paymentTotal) {
            $error_code = 102;
            $RemiseLog = $logService->createLogForCardResult();
            $RemiseLog->addMessage('error_code : ' . $error_code);
            $logService->outputRemiseLog($RemiseLog);
            return 'TOTAL_MISMATCH:' . $error_code;
        }

        // 在庫数チェック
        if (!$this->isOrderProduct($Order)) {
            $this->app->addError('front.shopping.stock.error');
            $error_code = 107;
            $RemiseLog = $logService->createLogForCardResult();
            $RemiseLog->addMessage('error_code : ' . $error_code);
            $logService->outputRemiseLog($RemiseLog);
            return 'LACK_STOCK:' . $error_code;
        }

        if (!empty($requestData['X-PARTOFCARD'])) {
            // 受注情報更新
            if (!$this->lfOrderUpdate($Order, $requestData)) {
                $error_code = 103;
                $RemiseLog = $logService->createLogForCardResult();
                $RemiseLog->addMessage('error_code : ' . $error_code);
                $logService->outputRemiseLog($RemiseLog);
                return 'UPDATE_ERROR:' . $error_code;
            }
        }

        // ペイクイックの場合は、顧客テーブルの更新処理を行う
        if (!empty($requestData["X-PAYQUICKID"])) {
            if (!$this->lfPayquickUpdate($Order, $requestData)) {
                $error_code = 106;
                $RemiseLog = $logService->createLogForCardResult();
                $RemiseLog->addMessage('error_code : ' . $error_code);
                $logService->outputRemiseLog($RemiseLog);
                return 'PAYQUICK_ERROR:' . $error_code;
            }
        }

        // 結果通知の応答
        return $this->app["config"]["remise_payment_charge_ok"];
    }

    /**
     * 在庫数チェック
     *
     * @param  Order  $Order  受注情報
     * @return  bool
     */
    protected function isOrderProduct($Order)
    {
        $orderDetails = $Order->getOrderDetails();
        if (EntityUtil::isEmpty($orderDetails)) return false;

        foreach ($orderDetails as $orderDetail) {
            // 在庫が制限あり
            if ($orderDetail->getProductClass()->getStockUnlimited() == Constant::DISABLED) {
                $productStock = $this->app['eccube.repository.product_stock']->find(
                    $orderDetail->getProductClass()->getProductStock()->getId()
                );
                // 購入数量と在庫数をチェックして在庫がなければエラー
                if ($orderDetail->getQuantity() > $productStock->getStock()) {
                    return false;
                }
            }
        }

        return true;
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
        $remiseOrderService = $this->app['eccube.plugin.service.remise_order'];

        $orderStatusId = $Order->getOrderStatus()->getId();

        // ルミーズ受注結果情報の登録
        $remiseOrderService->registRemiseResult($Order, $requestData);

        if ($requestData["X-R_CODE"] != $this->app["config"]["remise_r_code_ok"]
         || $requestData["X-ERRLEVEL"] != $this->app["config"]["remise_errlevel_normal"]){
            return false;
        }

        // 決済処理中から受注未確定への更新処理
        if ($orderStatusId == $this->app['config']['order_pending']) {
            // 受注未確定への更新処理
            $remiseOrderService->updateRemiseOrderPending($Order);
        }

        return true;
    }

    /**
     * ペイクイック情報更新処理
     *
     * @param  Order  $Order  受注情報
     * @param  array  $requestData  リクエスト情報
     * @return  bool
     */
    function lfPayquickUpdate($Order, $requestData)
    {
        $remiseOrderService = $this->app['eccube.plugin.service.remise_order'];
        $logService = $this->app['eccube.plugin.service.remise_log'];

        // 受注情報の取得
        $orderId = $requestData["X-S_TORIHIKI_NO"];
        if (EntityUtil::isEmpty($Order)) {
            $RemiseLog = $logService->createLogForUpdatePayquick(3);
            $RemiseLog->addMessage("\t" . 'Order Not Found: '. $orderId);
            $logService->outputRemiseLog($RemiseLog);
            return false;
        }

        // 過去の受注記録を取得
        $customerId = "";
        if ($Order->getCustomer() != null) {
            $customerId = $Order->getCustomer()->getId();
        }
        if (!$customerId) {
            $RemiseLog = $logService->createLogForUpdatePayquick();
            $RemiseLog->addMessage("\t" . 'Customer_id Not Found');
            $logService->outputRemiseLog($RemiseLog);
            return true;
        }

        // ペイクイック情報更新処理
        if (!$remiseOrderService->updatePayquick($customerId, $requestData)) {
            return false;
        }

        // カード結果ログ作成
        $RemiseLog = $logService->createLogForUpdatePayquick();
        $RemiseLog->addMessage("\t" . 'Payquick Update Success');
        $logService->outputRemiseLog($RemiseLog);

        return true;
    }
}
