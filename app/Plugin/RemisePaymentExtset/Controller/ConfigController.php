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
use Plugin\RemisePaymentExtset\Form\Type\ConfigType;

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

        $extsetConfigService = $this->app['eccube.plugin.service.remise_extset_config'];

        // config取得
        $extsetPluginConfig = $extsetConfigService->getPluginConfig();

        // プラグインの稼働確認
        if (!$extsetConfigService->getEnablePlugin(true))
        {
            return $this->app->redirect($this->app->url('admin_store_plugin'));
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

        // 入力フォーム
        $type = new ConfigType($this->app, $extsetInfo);
        $builder = $this->app['form.factory']->createBuilder($type);

        if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
            // イベント生成
            $event = new EventArgs(
                array(
                    'builder' => $builder,
                    'RemiseConfig' => $RemiseConfig,
                    'RemiseExtsetConfig' => $RemiseExtsetConfig,
                ),
                $request
            );
            $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::ADMIN_PLUGIN_REMISE_EXTSET_CONFIG_EDIT_INITIALIZE, $event);
        }

        $form = $builder->getForm();

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
                        'RemiseExtsetConfig' => $RemiseExtsetConfig,
                    ),
                    $request
                );
                $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::ADMIN_PLUGIN_REMISE_EXTSET_CONFIG_EDIT_PROGRESS, $event);
            }

            // 入力チェックＯＫ時
            if ($form->isValid())
            {
                // フォームデータ取得
                $formData = $form->getData();

                $logService = $this->app['eccube.plugin.service.remise_log'];

                $em = $this->app['orm.em'];
                $em->getConnection()->beginTransaction();
                try
                {
                    // プラグイン設定情報登録
                    $extsetConfigService->regist($formData);

                    $em->flush();
                    $em->getConnection()->commit();
                    $em->close();

                    // ログメッセージ出力
                    $RemiseLog = $this->createLogForConfig();
                    $RemiseLog->addMessage($formData);
                    $logService->outputRemiseLog($RemiseLog);

                    if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
                        // イベント生成
                        $event = new EventArgs(
                            array(
                                'form' => $form,
                                'RemiseConfig' => $RemiseConfig,
                                'RemiseExtsetConfig' => $RemiseExtsetConfig,
                            ),
                            $request
                        );
                        $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::ADMIN_PLUGIN_REMISE_EXTSET_CONFIG_EDIT_COMPLETE, $event);
                    }

                    // 登録完了メッセージ
                    $this->app->addSuccess('登録が完了しました。', 'admin');
                }
                catch (\Exception $e)
                {
                    $em->getConnection()->rollback();
                    $em->close();

                    // エラーログ出力
                    $RemiseLog = $this->createLogForConfig(3);
                    $RemiseLog->addMessage('ErrCode:' . $e->getCode());
                    $RemiseLog->addMessage('ErrMessage:' . $e->getMessage());
                    $RemiseLog->addMessage($e);
                    $logService->outputRemiseLog($RemiseLog);
                }
            }
        }

        // 画面返却
        return $this->app['view']->render('RemisePaymentExtset/Resource/template/admin/config.twig', array(
            'form'      => $form->createView(),
            'title'     => $RemiseExtsetConfig->getName(),
            'version'   => $extsetPluginConfig['version'],
            'code'      => isset($info['code']) ? $info['code'] : "",
            'recv_url'  => $this->app->url('remise_extset_recv'),
        ));
    }

    /**
     * ログ情報生成
     *
     * @param  integer  $level  エラーレベル
     *
     * @return  RemiseLog
     */
    private function createLogForConfig($level = 0)
    {
        $RemiseLog = new \Plugin\RemisePayment\Entity\RemiseLog();
        $RemiseLog->setFilename('remise_extset_config.log');
        $RemiseLog->setAction('extset_config');
        $RemiseLog->setLevel($level);
        return $RemiseLog;
    }
}
