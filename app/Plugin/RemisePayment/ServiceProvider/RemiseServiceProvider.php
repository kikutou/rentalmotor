<?php
/*
 * Copyright(c) 2015 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\ServiceProvider;

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
            '/' . $app["config"]["admin_route"] . '/plugin/RemisePayment/config',
            '\\Plugin\\RemisePayment\\Controller\\ConfigController::edit'
        )->bind('plugin_RemisePayment_config');

        // RemisePayment ログ表示
        $app->match(
            '/' . $app["config"]["admin_route"] . '/setting/system/log/plg_remise_log',
            '\\Plugin\\RemisePayment\\Controller\\LogController::index'
        )->bind('admin_setting_system_plg_remise_log');

        // 決済リダイレクト、完了通知
        $app->match(
            '/shopping/remise_payment',
            '\\Plugin\\RemisePayment\\Controller\\PaymentController::index'
        )->bind('remise_shopping_payment');
        // 決済画面からの加盟店サイトへ戻る
        $app->match(
            '/shopping/remise_payment/back',
            '\\Plugin\\RemisePayment\\Controller\\PaymentController::back'
        )->bind('remise_shopping_payment_back');
        // 結果通知
        $app->match(
            '/shopping/remise_recv',
            '\\Plugin\\RemisePayment\\Controller\\PaymentRecvController::index'
        )->bind('remise_shopping_recv');
        // 収納情報通知
        $app->match(
            '/shopping/remise_acpt',
            '\\Plugin\\RemisePayment\\Controller\\PaymentAcptController::index'
        )->bind('remise_shopping_acpt');
        // ペイクイック削除（管理画面）
        $app->match(
            '/' . $app["config"]["admin_route"] . '/customer/payquickdel/{id}/{payquick_no}',
            '\\Plugin\\RemisePayment\\Controller\\PayquickController::admin_delete'
        )
        ->value('id', null)->assert('id', '\d+|')
        ->value('payquick_no', null)->assert('payquick_no', '\d+|')
        ->bind('remise_admin_payquickdel');
        // ペイクイック削除（マイページ）
        $app->match(
            '/mypage/change/payquickdel/{payquick_no}',
            '\\Plugin\\RemisePayment\\Controller\\PayquickController::delete'
        )
        ->value('payquick_no', null)->assert('payquick_no', '\d+|')
        ->bind('remise_payquickdel');
        // 支払方法複製
        $app->match(
            '/' . $app["config"]["admin_route"] . '/setting/shop/payment/{id}/copy',
            '\\Plugin\\RemisePayment\\Controller\\ConfigController::copy_payment'
        )
        ->value('id', null)->assert('id', '\d+|')
        ->bind('remise_admin_paymentcopy');

        //=============================================================
        // サービスの登録
        //=============================================================
        // プラグイン設定処理
        $app['eccube.plugin.service.remise_config'] = $app->share(function () use ($app) {
            return new \Plugin\RemisePayment\Service\RemiseConfigService($app);
        });
        // 受注処理
        $app['eccube.plugin.service.remise_order'] = $app->share(function () use ($app) {
            return new \Plugin\RemisePayment\Service\RemiseOrderService($app);
        });
        // ログ出力処理
        $app['eccube.plugin.service.remise_log'] = $app->share(function () use ($app) {
            return new \Plugin\RemisePayment\Service\RemiseLogService($app);
        });

        //=============================================================
        // リポジトリの登録
        //=============================================================
        // ルミーズプラグイン設定情報
        $app['eccube.plugin.remise.repository.remise_config'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\RemisePayment\Entity\RemiseConfig');
        });
        // ルミーズ受注結果情報
        $app['eccube.plugin.remise.repository.remise_order_result'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\RemisePayment\Entity\RemiseOrderResult');
        });
        // ルミーズ受注状態
        $app['eccube.plugin.remise.repository.remise_order_status'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\RemisePayment\Entity\RemiseOrderStatus');
        });
        // ルミーズ支払方法情報
        $app['eccube.plugin.remise.repository.remise_payment_method'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\RemisePayment\Entity\RemisePaymentMethod');
        });
        // ルミーズペイクイック情報
        $app['eccube.plugin.remise.repository.remise_customer_payquick'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\RemisePayment\Entity\RemiseCustomerPayquick');
        });
        // ルミーズカード支払区分情報
        $app['eccube.plugin.remise.repository.remise_card_method'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\RemisePayment\Entity\RemiseCardMethod');
        });
        // ルミーズマルチ決済支払方法案内情報
        $app['eccube.plugin.remise.repository.remise_multi_payinfo'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\RemisePayment\Entity\RemiseMultiPayinfo');
        });
        // ルミーズマルチ決済支払方法情報
        $app['eccube.plugin.remise.repository.remise_multi_payway'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\RemisePayment\Entity\RemiseMultiPayway');
        });
        // ルミーズメールテンプレート情報
        $app['eccube.plugin.remise.repository.remise_mail_template'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\RemisePayment\Entity\RemiseMailTemplate');
        });

        //=============================================================
        // フォームの登録
        //=============================================================
        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            // プラグイン設定画面
            $types[] = new \Plugin\RemisePayment\Form\Type\ConfigType($app);
            // 決済リダイレクト画面
            $types[] = new \Plugin\RemisePayment\Form\Type\PaymentType($app);
            // ペイクイック画面
            $types[] = new \Plugin\RemisePayment\Form\Type\PayquickType($app);
            return $types;
        }));

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

        //=============================================================
        // メニューの登録
        //=============================================================
        $app['config'] = $app->share($app->extend('config', function ($config) {
            // $config['nav']
            if (!array_key_exists('nav', $config)) return $config;
            // $config['nav'][4] ※設定メニュー
            $topIdx = -1;
            foreach ($config['nav'] as $key => $value)
            {
                if (!array_key_exists('id', $value)) continue;
                // 設定メニューID特定
                if ($value['id'] != 'setting') continue;
                $topIdx = $key;
                break;
            }
            if (!array_key_exists($topIdx, $config['nav'])) return $config;
            // $config['nav'][4]['child']
            if (!array_key_exists('child', $config['nav'][$topIdx])) return $config;
            // $config['nav'][4]['child'][1] ※システム情報設定メニュー
            $mdlIdx = -1;
            foreach ($config['nav'][$topIdx]['child'] as $key => $value)
            {
                if (!array_key_exists('id', $value)) continue;
                // システム情報設定メニューID特定
                if ($value['id'] != 'system') continue;
                $mdlIdx = $key;
                break;
            }
            if (!array_key_exists($mdlIdx, $config['nav'][$topIdx]['child'])) return $config;
            // $config['nav'][4]['child'][1]['child']
            if (!array_key_exists('child', $config['nav'][$topIdx]['child'][$mdlIdx])) return $config;
            // メニュー追加
            $menu = array();
            foreach ($config['nav'][$topIdx]['child'][$mdlIdx]['child'] as $key => $value)
            {
                // 既存メニューの追加
                $menu[] = $value;
                if (!array_key_exists('id', $value)) continue;
                // EC-CUBEログ表示メニュー特定
                if ($value['id'] != 'log') continue;
                // メニュー追加
                $menu[] = array(
                    'id' => 'plg_remise_log',
                    'name' => 'Remise ログ表示',
                    'url' => 'admin_setting_system_plg_remise_log',
                );
            }
            $config['nav'][$topIdx]['child'][$mdlIdx]['child'] = $menu;
            return $config;
        }));
    }

    public function boot(BaseApplication $app) {}
}
