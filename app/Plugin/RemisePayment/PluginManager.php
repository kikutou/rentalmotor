<?php
/*
 * Copyright(c) 2015 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment;

use Eccube\Plugin\AbstractPluginManager;

/**
 * プラグインマネージャ
 */
class PluginManager extends AbstractPluginManager
{
    /**
     * 複写元
     */
    protected $srcDir;

    /**
     * 複写先
     */
    protected $dstDir;

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
        $this->updateRecord($app, 1);
    }

    /**
     * 無効化
     */
    public function disable($config, $app)
    {
        // レコードの無効化
        $this->updateRecord($app, 0);
    }

    /**
     * 更新
     */
    public function update($config, $app)
    {
        // テーブル作成
        $this->migrationSchema($app, __DIR__ . '/Resource/doctrine/migration', $config['code']);
        // ファイルの削除
        try
        {
            $this->removeFiles(__DIR__ . '/../../../html/plugin/remise');
        }
        catch (\Exception $e)
        {
        }
    }

    /**
     * ファイル・ディレクトリの削除
     *
     * @param  string  $dir  削除対象
     */
    protected function removeFiles($dir)
    {
        if (!file_exists($dir)) return true;
        if (!is_dir($dir) || is_link($dir)) return unlink($dir);

        foreach (scandir($dir) as $item)
        {
            if ($item == '.' || $item == '..') continue;

            if (!$this->removeFiles($dir . "/" . $item))
            {
                chmod($dir . "/" . $item, 0777);
                if (!$this->removeFiles($dir . "/" . $item)) return false;
            };
        }
        return rmdir($dir);
    }

    /**
     * レコードの有効化/無効化
     *
     * @param  Application  $app  
     * @param  integer  $flag  有効化フラグ（1:有効化、0:無効化）
     */
    protected function updateRecord($app, $flag)
    {
        $delFlg = 0;
        if ($flag == 0) $delFlg = 1;

        // メールテンプレートの更新
        $this->updateRemiseMailTemplate($app, $delFlg);
        // 支払方法の更新
        $this->updateRemisePaymentMethods($app, $delFlg);
        // 受注状態の更新
        $this->updateRemiseOrderStatus($app, $delFlg);
        // プラグイン設定情報の更新
        $this->updateRemiseConfig($app, $delFlg);
    }

    /**
     * メールテンプレートの更新
     *
     * @param  Application  $app  
     * @param  integer  $delFlg  削除フラグ
     */
    protected function updateRemiseMailTemplate($app, $delFlg)
    {
        $updateDate = date('Y-m-d H:i:s');

        // ルミーズメールテンプレートの取得
        $select = "SELECT mail_template_id FROM plg_remise_mail_template";
        $paymentIds = $app['db']->fetchAll($select);

        if (empty($paymentIds)) return;

        // ルミーズメールテンプレートの無効化
        $update = "UPDATE plg_remise_mail_template"
                . " SET del_flg = " . $delFlg . ", update_date = '" . $updateDate . "'";
        $app['db']->executeUpdate($update);

        // ルミーズ専用EC-CUBEメールテンプレートの更新対象取得
        $ids = array();
        foreach ($paymentIds as $item)
        {
            $ids[] = $item['mail_template_id'];
        }

        // ルミーズ専用EC-CUBEメールテンプレートの無効化
        $update = "UPDATE dtb_mail_template "
                . " SET del_flg = " . $delFlg . ", update_date = '" . $updateDate . "'"
                . " WHERE template_id in (" . implode(",", $ids). ")";
        $app['db']->executeUpdate($update);
    }

    /**
     * 支払方法の更新
     *
     * @param  Application  $app  
     * @param  integer  $delFlg  削除フラグ
     */
    protected function updateRemisePaymentMethods($app, $delFlg)
    {
        $updateDate = date('Y-m-d H:i:s');

        // ルミーズ支払方法の取得
        $select = "SELECT payment_id FROM plg_remise_payment_methods";
        $paymentIds = $app['db']->fetchAll($select);

        if (empty($paymentIds)) return;

        // ルミーズ支払方法の無効化
        $update = "UPDATE plg_remise_payment_methods"
                . " SET del_flg = " . $delFlg . ", update_date = '" . $updateDate . "'";
        $app['db']->executeUpdate($update);

        // ルミーズ専用EC-CUBE支払方法の更新対象取得
        $ids = array();
        foreach ($paymentIds as $item)
        {
            $ids[] = $item['payment_id'];
        }

        // ルミーズ専用EC-CUBE支払方法の無効化
        $update = "UPDATE dtb_payment "
                . " SET del_flg = " . $delFlg . ", update_date = '" . $updateDate . "'"
                . " WHERE payment_id in (" . implode(",", $ids). ")";
        $app['db']->executeUpdate($update);
    }

    /**
     * 受注状態の更新
     *
     * @param  Application  $app  
     * @param  integer  $delFlg  削除フラグ
     */
    protected function updateRemiseOrderStatus($app, $delFlg)
    {
        // ルミーズ受注状態の無効化
        $update = "UPDATE plg_remise_order_status"
                . " SET del_flg = " . $delFlg . ", update_date = '" . date('Y-m-d H:i:s') . "'";
        $app['db']->executeUpdate($update);
    }

    /**
     * プラグイン設定情報の更新
     *
     * @param  Application  $app  
     * @param  integer  $delFlg  削除フラグ
     */
    protected function updateRemiseConfig($app, $delFlg)
    {
        // ルミーズ受注状態の無効化
        $update = "UPDATE plg_remise_config"
                . " SET del_flg = " . $delFlg . ", update_date = '" . date('Y-m-d H:i:s') . "'";
        $app['db']->executeUpdate($update);
    }
}
