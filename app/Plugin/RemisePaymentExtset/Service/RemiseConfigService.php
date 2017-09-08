<?php
/*
 * Copyright(c) 2016 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePaymentExtset\Service;

use Symfony\Component\Yaml\Yaml;
use Eccube\Application;
use Eccube\Common\Constant;

/**
 * プラグイン設定処理
 */
class RemiseConfigService
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
     * コンストラクタ
     *
     * @param  Application  $app  
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        // configファイル読込
        $this->pluginConfig = Yaml::parse(__DIR__ . '/../config.yml');
    }

    /**
     * config取得
     *
     * @return  array  
     */
    public function getPluginConfig()
    {
        return $this->pluginConfig;
    }

    /**
     * プラグインの稼働確認
     *
     * @return integer
     */
    public function getEnablePlugin($isSetMsg = false)
    {
        // プラグイン情報を検索
        $plugin = $this->app['eccube.repository.plugin']
            ->findOneBy(array('code' => $this->pluginConfig['code']));

        // 対象レコードが存在しない場合、未稼働
        if (!isset($plugin) || empty($plugin)) return Constant::DISABLED;

        // プラグインが有効でない場合、未稼働
        if (!$plugin->getEnable()) {
            if ($isSetMsg === true) {
                $message = "プラグインが有効ではありません。";
                $this->app->addWarning($message, 'admin');
            }
            return Constant::DISABLED;
        }

        // メインプラグイン情報を検索
        $pluginMain = $this->app['eccube.repository.plugin']
            ->findOneBy(array('code' => $this->app["config"]["main_plugin_code"]));

        // 対象レコードが存在しない場合、未インストール
        if (!isset($pluginMain) || empty($pluginMain)) {
            if ($isSetMsg === true) {
                $message = "「RemisePayment」(ルミーズ決済プラグイン) がインストールされていません。ルミーズ決済プラグインのインストール、及び設定後にご利用ください。";
                $this->app->addWarning($message, 'admin');
            }
            return Constant::DISABLED;
        }

        // プラグインが有効でない場合、未稼働
        if (!$pluginMain->getEnable()) {
            if ($isSetMsg === true) {
                $message = "「RemisePayment」(ルミーズ決済プラグイン) が有効ではありません。ルミーズ決済プラグインの有効化、及び設定後にご利用ください。";
                $this->app->addWarning($message, 'admin');
            }
            return Constant::DISABLED;
        }

        // ルミーズプラグイン設定情報の取得
        $RemiseConfig = $this->app['eccube.plugin.remise.repository.remise_config']
            ->findOneBy(array('code' => $this->app["config"]["main_plugin_code"]));

        // ルミーズプラグイン設定情報が存在しない場合、未稼働
        if (!isset($RemiseConfig) || empty($RemiseConfig) || $RemiseConfig->getDelFlg() != 0) {
            if ($isSetMsg === true) {
                $message = "「RemisePayment」(ルミーズ決済プラグイン) の設定がされていません。ルミーズ決済プラグインの設定後にご利用ください。";
                $this->app->addWarning($message, 'admin');
            }
            return Constant::DISABLED;
        }

        // 設定情報
        $info = $RemiseConfig->getUnserializeInfo();

        // ルミーズプラグイン設定情報が存在しない場合、未稼働
        if (empty($info)) {
            if ($isSetMsg === true) {
                $message = "「RemisePayment」(ルミーズ決済プラグイン) の設定がされていません。ルミーズ決済プラグインの設定後にご利用ください。";
                $this->app->addWarning($message, 'admin');
            }
            return Constant::DISABLED;
        }

        // ルミーズ決済情報の取得
        $RemisePaymentMethod = $this->app['eccube.plugin.remise.repository.remise_payment_method']
            ->findOneBy(array('type' => $this->app['config']['remise_payment_credit']));

        // カード決済が存在しない場合、未稼働
        if (!isset($RemisePaymentMethod) || empty($RemisePaymentMethod) || $RemisePaymentMethod->getDelFlg() != 0) {
            if ($isSetMsg === true) {
                $message = "「RemisePayment」(ルミーズ決済プラグイン) でカード決済のご利用が設定されていません。カード決済の設定後にご利用ください。";
                $this->app->addWarning($message, 'admin');
            }
            return Constant::DISABLED;
        }

        // 稼働
        return Constant::ENABLED;
    }

    /**
     * ポイントプラグインの稼働確認
     *
     * @return integer
     */
    public function getEnablePointPlugin()
    {
        // プラグイン情報を検索
        $plugin = $this->app['eccube.repository.plugin']
            ->findOneBy(array('code' => $this->app["config"]["check_plugin_code_point"]));

        // 対象レコードが存在しない場合、未稼働
        if (!isset($plugin) || empty($plugin)) return Constant::DISABLED;

        // プラグインが有効でない場合、未稼働
        if (!$plugin->getEnable()) return Constant::DISABLED;

        // 稼働
        return Constant::ENABLED;
    }

    /**
     * プラグイン設定情報登録
     *
     * @param  array  $info
     */
    public function regist(array $info)
    {
        if (empty($info)) return;

        // ルミーズプラグイン設定情報の取得
        $RemiseConfig = $this->app['eccube.plugin.remise.repository.remise_config']
            ->findOneBy(array('code' => $this->pluginConfig['code']));

        if (!isset($RemiseConfig) || empty($RemiseConfig)) return;

        // 設定情報登録
        $RemiseConfig->setSerializeInfo($info);
        $this->app['orm.em']->persist($RemiseConfig);
        $this->app['orm.em']->flush();
    }
}
