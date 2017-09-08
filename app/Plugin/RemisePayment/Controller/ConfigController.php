<?php
/*
 * Copyright(c) 2015 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Controller;

use Symfony\Component\HttpFoundation\Request;
use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Event\EventArgs;

use Plugin\RemisePayment\Event\RemiseEventBase;
use Plugin\RemisePayment\Form\Type\ConfigType;

/**
 * プラグイン設定画面制御
 */
class ConfigController
{
    /**
     * Application
     */
    public $app;

    /**
     * プラグイン設定
     *
     * @param  Application  $app  
     * @param  Request $request リクエスト情報
     */
    public function edit(Application $app, Request $request)
    {
        $this->app = $app;

        $configService = $this->app['eccube.plugin.service.remise_config'];
        $logService = $this->app['eccube.plugin.service.remise_log'];

        // config取得
        $pluginConfig = $configService->getPluginConfig();

        // カード支払方法取得
        $arrCardMethod = $this->app['eccube.plugin.remise.repository.remise_card_method']
            ->findAll();

        // プラグインの稼働確認
        if (!$configService->getEnablePlugin())
        {
            return $this->app['view']->render('error.twig', array(
                'error_title'   => 'プラグインエラー',
                'error_message' => 'ルミーズ決済プラグインが有効ではありません。',
            ));
        }

        // EC-CUBEバージョン3.0.8未満の場合はflagをtureにする
        $isEccubeVerLower308 = false;
        switch (Constant::VERSION) 
        {
                case "3.0.0":
                case "3.0.1":
                case "3.0.2":
                case "3.0.3":
                case "3.0.4":
                case "3.0.5":
                case "3.0.6":
                case "3.0.7":
                case "3.0.8":
                    //alert("3.0.8以下");
                    $isEccubeVerLower308 = true;
                    break;
                default:
                    //alert("3.0.9以上");
                    break;
        }

        // ルミーズプラグイン設定情報の取得
        $RemiseConfig = $this->app['eccube.plugin.remise.repository.remise_config']
            ->findOneBy(array('code' => $pluginConfig['code']));

        // 設定情報
        $info = $RemiseConfig->getUnserializeInfo();

        // 入力フォーム
        $type = new ConfigType($this->app, $info);
        $builder = $this->app['form.factory']->createBuilder($type);

        if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
            // イベント生成
            $event = new EventArgs(
                array(
                    'builder' => $builder,
                    'RemiseConfig' => $RemiseConfig,
                ),
                $request
            );
            $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::ADMIN_PLUGIN_REMISE_CONFIG_EDIT_INITIALIZE, $event);
        }

        $form = $builder->getForm();

        $configComplete = '';

        // 登録時
        if ('POST' === $this->app['request']->getMethod())
        {
            $form->handleRequest($this->app['request']);

            if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
                // イベント生成
                $event = new EventArgs(
                    array(
                        'builder' => $builder,
                        'RemiseConfig' => $RemiseConfig,
                    ),
                    $request
                );
                $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::ADMIN_PLUGIN_REMISE_CONFIG_EDIT_PROGRESS, $event);
            }

            // 入力チェックＯＫ時
            if ($form->isValid())
            {
                // フォームデータ取得
                $formData = $form->getData();

                $em = $this->app['orm.em'];
                $em->getConnection()->beginTransaction();
                try
                {
                    // プラグイン設定情報登録
                    $configService->regist($formData);

                    $em->flush();
                    $em->getConnection()->commit();
                    $em->close();

                    // ログメッセージ出力
                    $RemiseLog = $logService->createLogForConfig();
                    $RemiseLog->addMessage($formData);
                    $logService->outputRemiseLog($RemiseLog);

                    if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
                        // イベント生成
                        $event = new EventArgs(
                            array(
                                'form' => $form,
                                'RemiseConfig' => $RemiseConfig,
                            ),
                            $request
                        );
                        $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::ADMIN_PLUGIN_REMISE_CONFIG_EDIT_COMPLETE, $event);
                    }

                    $configComplete = '1';

                    // 登録完了メッセージ
                    $this->app->addSuccess('登録が完了しました。', 'admin');
                }
                catch (\Exception $e)
                {
                    $em->getConnection()->rollback();
                    $em->close();

                    // エラーログ出力
                    $RemiseLog = $logService->createLogForConfig(3);
                    $RemiseLog->addMessage('ErrCode:' . $e->getCode());
                    $RemiseLog->addMessage('ErrMessage:' . $e->getMessage());
                    $RemiseLog->addMessage($e);
                    $logService->outputRemiseLog($RemiseLog);
                }
            }
        }

        // 画面返却
        return $this->app['view']->render('RemisePayment/Resource/template/admin/config.twig', array(
            'form'      => $form->createView(),
            'title'     => $RemiseConfig->getName(),
            'is_eccube_ver_lower308' => $isEccubeVerLower308,
            'version'   => $pluginConfig['version'],
            'recv_url'  => $this->app->url('remise_shopping_recv'),
            'acpt_url'  => $this->app->url('remise_shopping_acpt'),
            'complete'  => $configComplete,
            'cardmethodlist' => $arrCardMethod,
            'use_cardmethodlist' => isset($info['use_cardmethod']) ? $info['use_cardmethod'] : array(),
        ));
    }

    /**
     * 支払方法複製
     *
     * @param  Application  $app  
     * @param  Request $request リクエスト情報
     */
    public function copy_payment(Application $app, Request $request)
    {
        $this->app = $app;

        $configService = $this->app['eccube.plugin.service.remise_config'];

        // 支払方法ID
        $paymentId = $request->attributes->get('id');
        if (empty($paymentId)) return $this->app->redirect($this->app->url('admin_setting_shop_payment'));

        try
        {
            // 支払方法複製
            $newPaymentId = $configService->copyPayment($paymentId);

            // 完了メッセージ
            $this->app->addSuccess('支払方法を複製しました。', 'admin');

            return $this->app->redirect($this->app->url('admin_setting_shop_payment_edit', array('id' => $newPaymentId)));
        }
        catch (\Exception $e)
        {
            // エラーメッセージ
            $this->app->addError('支払方法の複製に失敗しました。', 'admin');

            return $this->app->redirect($this->app->url('admin_setting_shop_payment_edit', array('id' => $paymentId)));
        }
    }
}
