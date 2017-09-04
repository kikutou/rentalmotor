<?php
/*
 * Copyright(c) 2016 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePaymentExtset\Service;

use Symfony\Component\DomCrawler\Crawler;
use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Event\EventArgs;

use Plugin\RemisePaymentExtset\Event\RemiseEventBase;
use Plugin\RemisePayment\Common\Errinfo;

/**
 * 拡張セット処理
 */
class RemiseExtsetService
{
    /**
     * Application
     */
    public $app;

    /**
     * コンストラクタ
     *
     * @param  Application  $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * 拡張セット処理
     *
     * @param  int  $orderId  受注ＩＤ
     * @param  string  $job  処理区分
     * @param  Request  $request  
     *
     * @return  処理結果
     *              errcode ･･･ 1:成功、2:エラー、3:環境不備、4:処理対象外、5:処理抜け
     */
    public function exec($orderId, $job, $request = null)
    {
        $result = array();
        $result['errcode']      = "";
        $result['message']      = "";
        $result['Order']        = null;
        $result['RemiseResult'] = null;

        $extsetConfigService = $this->app['eccube.plugin.service.remise_extset_config'];

        // config取得
        $extsetPluginConfig = $extsetConfigService->getPluginConfig();

        // プラグインの稼働確認
        if (!$extsetConfigService->getEnablePlugin(true)) {
            $result['errcode'] = "3";
            $result['message'] = "「ルミーズカード決済拡張セットプラグイン」が設定されていません。";
            return $result;
        }

        $remiseOrderService = $this->app['eccube.plugin.service.remise_order'];

        // 受注情報を取得
        $Order = $remiseOrderService->getOrderById($orderId);
        if (!isset($Order) || empty($Order)) {
            $result['errcode'] = "4";
            $result['message'] = "受注情報が見つかりませんでした。";
            return $result;
        }

        $result['Order'] = $Order;

        // ルミーズ支払方法取得
        $RemisePaymentMethod = $this->app['eccube.plugin.remise.repository.remise_payment_method']
            ->find($Order->getPayment()->getId());

        // ルミーズ支払方法でない場合、処理抜け
        if (!isset($RemisePaymentMethod) || empty($RemisePaymentMethod)) {
            $result['errcode'] = "4";
            $result['message'] = "この受注情報はRemiseクレジットカード決済でないため、処理できません。";
            return $result;
        }

        // カード決済のみ
        if ($RemisePaymentMethod->getType() != $this->app['config']['remise_payment_credit']) {
            $result['errcode'] = "4";
            $result['message'] = "この受注情報はRemiseクレジットカード決済でないため、処理できません。";
            return $result;
        }

        // ルミーズ受注結果情報を取得
        $RemiseResult = $this->app['eccube.plugin.remise.repository.remise_order_result']
            ->findOneBy(array('id' => $orderId));
        if (!isset($RemiseResult) || empty($RemiseResult)) {
            $result['errcode'] = "4";
            $result['message'] = "この受注情報はRemiseクレジットカード決済でないため、処理できません。";
            return $result;
        }

        $result['RemiseResult'] = $RemiseResult;

        // 返品の場合
        if ($job == $this->app['config']['job_return']) {
            if ($RemiseResult->getCreateDate()->format('Y-m-d') == date('Y-m-d')) {
                $job = $this->app['config']['job_void'];
            }
        }

        $jobName = "";
        switch ($job) {
            // 売上
            case $this->app['config']['job_sales']:
                $jobName = "売上処理";
                // 仮売上のみ対象
                if ($RemiseResult->getMemo06() != $this->app['config']['state_auth']) {
                    $result['errcode'] = "4";
                    $result['message'] = "状態が「仮売上」でないため、処理できません。";
                    return $result;
                }
                break;

            // 即日取消
            case $this->app['config']['job_void']:
                $jobName = "即日取消処理";
                // 仮売上、売上を対象
                if ($RemiseResult->getMemo06() != $this->app['config']['state_auth']
                 && $RemiseResult->getMemo06() != $this->app['config']['state_sales']
                 && $RemiseResult->getMemo06() != $this->app['config']['state_capture']) {
                    $result['errcode'] = "4";
                    $result['message'] = "状態が「仮売上」または「売上」でないため、処理できません。";
                    return $result;
                }
                break;

            // 返品
            case $this->app['config']['job_return']:
                $jobName = "返品処理";
                // 仮売上、売上を対象
                if ($RemiseResult->getMemo06() != $this->app['config']['state_auth']
                 && $RemiseResult->getMemo06() != $this->app['config']['state_sales']
                 && $RemiseResult->getMemo06() != $this->app['config']['state_capture']) {
                    $result['errcode'] = "4";
                    $result['message'] = "状態が「仮売上」または「売上」でないため、処理できません。";
                    return $result;
                }
                break;

            // 金額変更
            case $this->app['config']['job_change']:
                $jobName = "金額変更処理";
                // 仮売上、売上を対象
                if ($RemiseResult->getMemo06() != $this->app['config']['state_auth']
                 && $RemiseResult->getMemo06() != $this->app['config']['state_sales']
                 && $RemiseResult->getMemo06() != $this->app['config']['state_capture']) {
                    $result['errcode'] = "4";
                    $result['message'] = "状態が「仮売上」または「売上」でないため、処理できません。";
                    return $result;
                }

                if ($request != null) {
                    // 入力チェック
                    $builder = $this->app['form.factory']
                        ->createBuilder('order', $Order);
                    $form = $builder->getForm();
                    $form->handleRequest($request);
                    $inputError = !$form->isValid();
                    if (version_compare(Constant::VERSION, '3.0.6', '>=')) {
                        if ($Order->getTotal() > $this->app['config']['max_total_fee']) $inputError = false;
                    }
                    // エラー時は、既存の処理へ流し、再入力を促す
                    if ($inputError) {
                        $result['errcode'] = "5";
                        $result['message'] = "入力エラー";
                        return $result;
                    }
                }

                break;

            default:
                return $this->redirectAdminOrderEdit($orderId);
        }

        // ルミーズプラグイン設定情報の取得
        $RemiseConfig = $this->app['eccube.plugin.remise.repository.remise_config']
            ->findOneBy(array('code' => $this->app["config"]["main_plugin_code"]));
        // ルミーズ拡張セットプラグイン設定情報の取得
        $RemiseExtsetConfig = $this->app['eccube.plugin.remise.repository.remise_config']
            ->findOneBy(array('code' => $extsetPluginConfig['code']));

        // 設定情報
        $info = $RemiseConfig->getUnserializeInfo();
        $extsetInfo = $RemiseExtsetConfig->getUnserializeInfo();

        if (!isset($extsetInfo['extset_url']) || empty($extsetInfo['extset_url'])) {
            $message = "「ルミーズカード決済拡張セットプラグイン」で決済情報送信先URLが設定されていません。";
            $this->app->addWarning($message, 'admin');

            $result['errcode'] = "3";
            $result['message'] = $message;
            return $result;
        }

        $logService = $this->app['eccube.plugin.service.remise_log'];

        try
        {
            // 送信データ生成
            $url = $extsetInfo['extset_url'];
            $curl = curl_init($url);
            // POST
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            // ヘッダ
            $headers = array(
                "User-Agent:" . strtoupper($extsetPluginConfig['code']) . '_PLG_VER ' . $extsetPluginConfig['version'] . "(" . $job . ")",
            );
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            // パラメータ
            $total = $Order->getPaymentTotal();
            if ($job == $this->app['config']['job_change']) {
                $total = $request->get('remise-payment-total');
            }
            $postData = array(
                'SHOPCO'        => $info['code'],
                'HOSTID'        => $extsetInfo['extset_host_id'],
                'S_TORIHIKI_NO' => $orderId,
                'JOB'           => $job,
                'TRANID'        => $RemiseResult->getMemo04(),
                'TOTAL'         => $total,
                'TAX'           => '0',
                'RETURL'        => 'http://www.remise.jp/', // inputタグを取得するためのダミー
            );

            if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
                // イベント生成
                $event = new EventArgs(
                    array(
                        'Order' => $Order,
                        'RemiseResult' => $RemiseResult,
                        'postData' => $postData,
                    ),
                    $request
                );
                $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::SERVICE_PLUGIN_REMISE_EXTSET_EXEC_POST, $event);
            }

            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postData));

            // ログ出力
            $RemiseLog = $this->createLogForExtset($job);
            $RemiseLog->setLevel(1);
            $RemiseLog->addMessage($url);
            $RemiseLog->addMessage($postData);
            $logService->outputRemiseLog($RemiseLog);

            // 実行
            $response  = curl_exec($curl);
            $curlInfo  = curl_getinfo($curl);
            $curlError = curl_error($curl);

            // 終了
            curl_close($curl);

            // 接続エラー
            if ($response === false) {
                // ログ出力
                $RemiseLog = $this->createLogForExtset($job);
                $RemiseLog->setLevel(2);
                $RemiseLog->addMessage("Connection error.");
                $RemiseLog->addMessage($curlError);
                $RemiseLog->addMessage($curlInfo);
                $logService->outputRemiseLog($RemiseLog);

                $message = "「ルミーズカード決済拡張セットプラグイン」で設定された決済情報送信先URLに接続できません。";
                $this->app->addWarning($message, 'admin');

                $result['errcode'] = "3";
                $result['message'] = $message;
            }
            // 正常
            else {
                // 戻りデータ解析
                $response  = mb_convert_encoding($response, 'UTF-8', 'Shift-JIS');

                $crawler = new Crawler($response);
                $Elements = $crawler->filter('input');

                // 結果判定
                $retData = array();
                foreach ($Elements as $node) {
                    if (!$node->attributes) continue;
                    $nm = "";
                    $val = "";
                    if ($node->attributes->getNamedItem('name')) {
                        $nm = $node->attributes->getNamedItem('name')->nodeValue;
                    }
                    if ($node->attributes->getNamedItem('value')) {
                        $val = $node->attributes->getNamedItem('value')->nodeValue;
                    }
                    if (!empty($nm)) {
                        $retData[$nm] = $val;
                    }
                }

                if (!isset($retData['X-R_CODE'])  ) $retData['X-R_CODE']   = "9:0000";
                if (!isset($retData['X-ERRLEVEL'])) $retData['X-ERRLEVEL'] = "";
                if (!isset($retData['X-ERRCODE']) ) $retData['X-ERRCODE']  = "";

                // エラーメッセージ取得
                $errMsg = Errinfo::getErrCdXRCode($retData['X-R_CODE']);
                if ($retData['X-R_CODE'] == "0:0000" && $retData['X-ERRLEVEL'] != "0") {
                    $errMsg = Errinfo::getErrCdXRCode($retData['X-ERRCODE']);
                }

                // 正常
                if ($retData['X-R_CODE'] == "0:0000" && $retData['X-ERRLEVEL'] == "0") {
                    // ログ出力
                    $RemiseLog = $this->createLogForExtset($job);
                    $RemiseLog->setLevel(1);
                    $RemiseLog->addMessage("Success.");
                    $RemiseLog->addMessage($retData);
                    $logService->outputRemiseLog($RemiseLog);

                    // 受注情報更新
                    $remiseExtsetOrderService = $this->app['eccube.plugin.service.remise_extset_order'];
                    $remiseExtsetOrderService->updateExtsetOrder($Order, $RemiseResult, $job, $retData);

                    $result['errcode'] = "1";
                    $result['message'] = $jobName . "が完了しました。";
                }
                // 異常
                else {
                    // ログ出力
                    $RemiseLog = $this->createLogForExtset($job);
                    $RemiseLog->setLevel(2);
                    $RemiseLog->addMessage($errMsg);
                    $RemiseLog->addMessage($retData);
                    $logService->outputRemiseLog($RemiseLog);

                    $result['errcode'] = "2";
                    $result['message'] = $errMsg;
                    return $result;
                }
            }
            return $result;
        }
        catch (\Exception $e)
        {
            $message = $jobName . "に失敗しました。(" . $e->getCode() . ":" . $e->getMessage() . ")";

            // エラーログ出力
            $RemiseLog = $this->createLogForExtset($job);
            $RemiseLog->setLevel(3);
            $RemiseLog->addMessage('ErrCode:' . $e->getCode());
            $RemiseLog->addMessage('ErrMessage:' . $e->getMessage());
            $RemiseLog->addMessage($e);
            $logService->outputRemiseLog($RemiseLog);

            $result['errcode'] = "2";
            $result['message'] = $message;
            return $result;
        }
    }

    /**
     * ログ情報生成
     *
     * @param  string  $job  処理区分
     *
     * @return  RemiseLog
     */
    private function createLogForExtset($job = "")
    {
        $RemiseLog = new \Plugin\RemisePayment\Entity\RemiseLog();
        $RemiseLog->setFilename('remise_card.log');
        $RemiseLog->setAction('extset ' . $job);
        return $RemiseLog;
    }
}
