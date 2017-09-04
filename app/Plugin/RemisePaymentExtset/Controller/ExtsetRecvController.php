<?php
/*
 * Copyright(c) 2016 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePaymentExtset\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Event\EventArgs;

use Plugin\RemisePaymentExtset\Event\RemiseEventBase;

/**
 * 結果通知制御
 */
class ExtsetRecvController
{
    /**
     * Application
     */
    public $app;

    /**
     * 結果通知
     */
    public function index(Application $app, Request $request)
    {
        $this->app = $app;
        $requestData = $request->request->all();

        $logService = $this->app['eccube.plugin.service.remise_log'];

        // リクエストログ作成
        $RemiseLog = $this->createLogForExtsetResult();
        $RemiseLog->addMessage($_REQUEST);
        $logService->outputRemiseLog($RemiseLog);

        if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
            // イベント生成
            $event = new EventArgs(
                array(),
                $request
            );
            $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::ADMIN_PLUGIN_REMISE_EXTSET_RECV_INDEX_INITIALIZE, $event);
        }

        $returnContents = "";
        switch ($this->lfGetMode($requestData))
        {
            // カード決済（拡張セット）・結果通知処理
            case 'extset_complete':
                $returnContents = $this->lfRemiseExtsetResultCheck($requestData);
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
            $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::ADMIN_PLUGIN_REMISE_EXTSET_RECV_INDEX_COMPLETE, $event);
        }

        return $response;
    }

    /**
     * 結果通知時のログ情報生成
     *
     * @param  integer  $level  エラーレベル
     *
     * @return  RemiseLog
     */
    private function createLogForExtsetResult($level = 1)
    {
        $RemiseLog = new \Plugin\RemisePayment\Entity\RemiseLog();
        $RemiseLog->setFilename('remise_card.log');
        $RemiseLog->setAction('extset result');
        $RemiseLog->setLevel($level);
        return $RemiseLog;
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
     * カード決済（拡張セット）・結果通知処理
     *
     * @param  array  $requestData  リクエスト情報
     */
    protected function lfRemiseExtsetResultCheck($requestData)
    {
        // 結果通知の応答
        return $this->app["config"]["remise_payment_charge_ok"];
    }
}
