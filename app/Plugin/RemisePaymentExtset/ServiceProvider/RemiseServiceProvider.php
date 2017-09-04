<?php
/*
 * Copyright(c) 2015 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePaymentExtset\ServiceProvider;

use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * ルーティングの拡張
 */
class RemiseServiceProvider implements ServiceProviderInterface
{
    /**
     * 登録
     */
    public function register(BaseApplication $app)
    {
        //=============================================================
        // ルーティング
        //=============================================================
        // プラグイン設定画面
        $app->match(
            '/' . $app["config"]["admin_route"] . '/plugin/RemisePaymentExtset/config',
            '\\Plugin\\RemisePaymentExtset\\Controller\\ConfigController::edit'
        )->bind('plugin_RemisePaymentExtset_config');

        // 受注情報一覧一括売上処理
        $app->match(
            '/' . $app["config"]["admin_route"] . '/order/sales',
            '\\Plugin\\RemisePaymentExtset\\Controller\\ExtsetController::sales'
        )->bind('remise_admin_order_sales');
        // 受注情報一覧一括売上結果表示処理
        $app->match(
            '/' . $app["config"]["admin_route"] . '/order/sales_result',
            '\\Plugin\\RemisePaymentExtset\\Controller\\ExtsetController::salesResult'
        )->bind('remise_admin_order_sales_result');

        // 結果通知
        $app->match(
            '/extsetcard/remise_recv',
            '\\Plugin\\RemisePaymentExtset\\Controller\\ExtsetRecvController::index'
        )->bind('remise_extset_recv');

        //=============================================================
        // サービスの登録
        //=============================================================
        // プラグイン設定処理
        $app['eccube.plugin.service.remise_extset_config'] = $app->share(function () use ($app) {
            return new \Plugin\RemisePaymentExtset\Service\RemiseConfigService($app);
        });
        // 受注処理
        $app['eccube.plugin.service.remise_extset_order'] = $app->share(function () use ($app) {
            return new \Plugin\RemisePaymentExtset\Service\RemiseOrderService($app);
        });
        // 拡張セット処理
        $app['eccube.plugin.service.remise_extset'] = $app->share(function () use ($app) {
            return new \Plugin\RemisePaymentExtset\Service\RemiseExtsetService($app);
        });

        //=============================================================
        // configの登録
        //=============================================================
        $config = $app['config'];
        $app['config'] = $app->share(function () use ($config) {
            $mergeConfig = array();

            $file = __DIR__ . '/../Resource/config/constant.yml';
            if (file_exists($file)) {
                $arrConstant = Yaml::parse(file_get_contents($file));
                if (isset($arrConstant)) {
                    $mergeConfig = array_replace_recursive($mergeConfig, $arrConstant);
                }
            }

            return array_replace_recursive($config, $mergeConfig);
        });
    }

    public function boot(BaseApplication $app) {}
}
