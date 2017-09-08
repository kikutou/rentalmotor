<?php
/*
 * Copyright(c) 2016 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePaymentExtset\Controller;

use Symfony\Component\HttpFoundation\Request;
use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Event\EventArgs;

use Plugin\RemisePaymentExtset\Event\RemiseEventBase;

/**
 * 拡張セット処理
 */
class ExtsetController
{
    /**
     * Application
     */
    public $app;

    /**
     * 一括売上処理
     */
    public function sales(Application $app, Request $request)
    {
        $this->app = $app;

        $session = $request->getSession();
        $session->remove('eccube.plugin.remise.extset.result.list');
        $session->remove('eccube.plugin.remise.extset.result.total');
        $session->remove('eccube.plugin.remise.extset.result.error');
        $session->remove('eccube.plugin.remise.extset.result.ng');

        $extsetService = $this->app['eccube.plugin.service.remise_extset'];

        // 処理対象の受注ＩＤ取得
        $orderIds = $request->get('credit-orders');
        if (!isset($orderIds) || empty($orderIds)) {
            return $this->app->redirect($this->app->url('admin_order'));
        }
        $orderIdList = explode(',', $orderIds);

        if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
            // イベント生成
            $event = new EventArgs(
                array(
                    'orderIdList' => $orderIdList,
                ),
                $request
            );
            $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::ADMIN_PLUGIN_REMISE_EXTSET_EXTSET_SALES_INITIALIZE, $event);
        }

        $resultList = array();
        $totalCount = 0;
        $errorCount = 0;
        foreach ($orderIdList as $orderId) {
            // 一括売上処理実行
            $result = $extsetService->exec($orderId, "SALES", $request, false);

            // 環境不備の場合、ここで処理抜けし、受注管理画面を再表示
            if ($result['errcode'] == "3") {
                return $this->app->redirect($this->app->url('admin_order'));
            }

            $resultList[] = $result;
            $totalCount++;
            if ($result['errcode'] != "1") {
                $errorCount++;
            }
        }

        $session->set('eccube.plugin.remise.extset.result.list',  $resultList);
        $session->set('eccube.plugin.remise.extset.result.total', $totalCount);
        $session->set('eccube.plugin.remise.extset.result.error', $errorCount);

        if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
            // イベント生成
            $event = new EventArgs(
                array(
                    'orderIdList' => $orderIdList,
                    'resultList' => $resultList,
                    'totalCount' => $totalCount,
                    'errorCount' => $errorCount,
                ),
                $request
            );
            $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::ADMIN_PLUGIN_REMISE_EXTSET_EXTSET_SALES_COMPLETE, $event);
        }

        return $this->app->redirect($this->app->url('remise_admin_order_sales_result'));
    }

    /**
     * 一括売上結果表示処理
     */
    public function salesResult(Application $app, Request $request)
    {
        $this->app = $app;

        $session = $request->getSession();
        $resultList = $session->get('eccube.plugin.remise.extset.result.list');
        $totalCount = $session->get('eccube.plugin.remise.extset.result.total');
        $errorCount = $session->get('eccube.plugin.remise.extset.result.error');

        if ($errorCount > 0) {
            $this->app->addError("エラーが発生した注文情報は、加盟店バックヤードシステムでの確認をお願いいたします。", 'admin');
        }

        if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
            // イベント生成
            $event = new EventArgs(
                array(
                    'resultList' => $resultList,
                    'totalCount' => $totalCount,
                    'errorCount' => $errorCount,
                ),
                $request
            );
            $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::ADMIN_PLUGIN_REMISE_EXTSET_EXTSET_SALES_RESULT, $event);
        }

        // 画面返却
        $form = $this->app['form.factory']->createBuilder()->getForm();
        return $this->app['view']->render('RemisePaymentExtset/Resource/template/admin/order_index_result.twig', array(
            'form' => $form->createView(),
            'resultList' => $resultList,
            'totalCount' => $totalCount,
            'errorCount' => $errorCount,
        ));
    }
}
