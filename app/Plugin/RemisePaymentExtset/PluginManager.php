<?php
/*
 * Copyright(c) 2016 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePaymentExtset;

use Eccube\Plugin\AbstractPluginManager;

/**
 * プラグインマネージャ
 */
class PluginManager extends AbstractPluginManager
{
    /**
     * コンストラクタ
     */
    public function __construct()
    {
    }

    /**
     * インストール
     */
    public function install($config, $app)
    {
        // テーブル作成
        $this->migrationSchema($app, __DIR__ . '/Resource/doctrine/migration', $config['code']);
    }

    /**
     * アンインストール
     */
    public function uninstall($config, $app)
    {
        // テーブル削除
        $this->migrationSchema($app, __DIR__ . '/Resource/doctrine/migration', $config['code'], 0);
    }

    /**
     * 有効化
     */
    public function enable($config, $app)
    {
        // レコードの有効化
        $this->updateRecord($config, $app, 1);
    }

    /**
     * 無効化
     */
    public function disable($config, $app)
    {
        // レコードの無効化
        $this->updateRecord($config, $app, 0);
    }

    /**
     * 更新
     */
    public function update($config, $app)
    {
        // テーブル作成
        $this->migrationSchema($app, __DIR__ . '/Resource/doctrine/migration', $config['code']);
    }


    /**
     * レコードの有効化/無効化
     *
     * @param  array  $config  
     * @param  Application  $app  
     * @param  integer  $flag  有効化フラグ（1:有効化、0:無効化）
     */
    protected function updateRecord($config, $app, $flag)
    {
        $delFlg = 0;
        if ($flag == 0) $delFlg = 1;

        // プラグイン設定情報の更新
        $this->updateRemiseConfig($config, $app, $delFlg);
    }

    /**
     * プラグイン設定情報の更新
     *
     * @param  array  $config  
     * @param  Application  $app  
     * @param  integer  $delFlg  削除フラグ
     */
    protected function updateRemiseConfig($config, $app, $delFlg)
    {
        $update = "UPDATE plg_remise_config"
                . " SET del_flg = " . $delFlg . ", update_date = '" . date('Y-m-d H:i:s') . "'"
                . " WHERE plugin_code = '" . $config['code'] . "'";
        $app['db']->executeUpdate($update);
    }
}
