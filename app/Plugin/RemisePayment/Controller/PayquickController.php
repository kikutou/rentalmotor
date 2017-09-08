<?php
/*
 * Copyright(c) 2016 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Controller;

use Symfony\Component\HttpFoundation\Request;
use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Event\EventArgs;

use Plugin\RemisePayment\Event\RemiseEventBase;

/**
 * ペイクイック制御
 */
class PayquickController
{
    /**
     * Application
     */
    public $app;

    /**
     * 管理画面からのペイクイック削除
     */
    public function admin_delete(Application $app, Request $request)
    {
        $this->app = $app;

        $logService = $this->app['eccube.plugin.service.remise_log'];

        // 会員ID
        $customerId = $request->attributes->get('id');
        // ペイクイック番号
        $payquickNo = $request->attributes->get('payquick_no');

        if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
            // イベント生成
            $event = new EventArgs(
                array(),
                $request
            );
            $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::ADMIN_PLUGIN_REMISE_PAYQUICK_DELETE_INITIALIZE, $event);
        }

        try
        {
            // ペイクイック情報削除
            $payquickId = $this->app['eccube.plugin.remise.repository.remise_customer_payquick']
                ->deleteByPayquickNo($customerId, $payquickNo);

            // ログメッセージ出力
            $RemiseLog = $logService->createLogForDeletePayquick();
            $RemiseLog->addMessage("\t" . 'Payquick Admin Delete Success');
            $RemiseLog->addMessage("\t" . 'customerId: ' . $customerId);
            $RemiseLog->addMessage("\t" . 'payquickNo: ' . $payquickNo);
            $RemiseLog->addMessage("\t" . 'payquickId: ' . $payquickId);
            $logService->outputRemiseLog($RemiseLog);

            if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
                // イベント生成
                $event = new EventArgs(
                    array(),
                    $request
                );
                $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::ADMIN_PLUGIN_REMISE_PAYQUICK_DELETE_COMPLETE, $event);
            }

            // 完了メッセージ
            $this->app->addSuccess('ペイクイック情報を削除しました。', 'admin');
        }
        catch (\Exception $e)
        {
            // エラーログ出力
            $RemiseLog = $logService->createLogForDeletePayquick(3);
            $RemiseLog->addMessage('ErrCode:' . $e->getCode());
            $RemiseLog->addMessage('ErrMessage:' . $e->getMessage());
            $RemiseLog->addMessage($e);
            $logService->outputRemiseLog($RemiseLog);

            // エラーメッセージ
            $this->app->addError('ペイクイック情報の削除に失敗しました。', 'admin');
        }

        return $this->app->redirect($this->app->url('admin_customer_edit', array('id' => $customerId)));
    }

    /**
     * マイページからのペイクイック削除
     */
    public function delete(Application $app, Request $request)
    {
        $this->app = $app;

        $logService = $this->app['eccube.plugin.service.remise_log'];

        // 会員情報
        $Customer = $this->app->user();
        // 会員ID
        $customerId = $Customer->getId();
        // ペイクイック番号
        $payquickNo = $request->attributes->get('payquick_no');

        if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
            // イベント生成
            $event = new EventArgs(
                array(),
                $request
            );
            $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::FRONT_PLUGIN_REMISE_PAYQUICK_DELETE_INITIALIZE, $event);
        }

        try
        {
            // ペイクイック情報削除
            $payquickId = $this->app['eccube.plugin.remise.repository.remise_customer_payquick']
                ->deleteByPayquickNo($customerId, $payquickNo);

            // ログメッセージ出力
            $RemiseLog = $logService->createLogForDeletePayquick();
            $RemiseLog->addMessage("\t" . 'Payquick Delete Success');
            $RemiseLog->addMessage("\t" . 'customerId: ' . $customerId);
            $RemiseLog->addMessage("\t" . 'payquickNo: ' . $payquickNo);
            $RemiseLog->addMessage("\t" . 'payquickId: ' . $payquickId);
            $logService->outputRemiseLog($RemiseLog);

            if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
                // イベント生成
                $event = new EventArgs(
                    array(),
                    $request
                );
                $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::FRONT_PLUGIN_REMISE_PAYQUICK_DELETE_COMPLETE, $event);
            }

            // 完了フラグ
            $this->app['session']->set('eccube.plugin.remise.payquick.delete', '1');
        }
        catch (\Exception $e)
        {
            // エラーログ出力
            $RemiseLog = $logService->createLogForDeletePayquick(3);
            $RemiseLog->addMessage('ErrCode:' . $e->getCode());
            $RemiseLog->addMessage('ErrMessage:' . $e->getMessage());
            $RemiseLog->addMessage($e);
            $logService->outputRemiseLog($RemiseLog);

            // エラーフラグ
            $this->app['session']->set('eccube.plugin.remise.payquick.delete', '2');
        }

        return $this->app->redirect($this->app->url('mypage_change'));
    }
}
