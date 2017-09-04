<?php
/*
 * Copyright(c) 2015 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\Yaml\Yaml;

/**
 * マイグレーション
 */
class Version20150901090000 extends AbstractMigration
{
    /**
     * データベースの変更前の処理
     */
    public function preUp(Schema $schema)
    {
    }

    /**
     * データベースの変更処理
     */
    public function up(Schema $schema)
    {
        // テーブル作成
        $this->createRemiseConfig($schema);
        $this->createRemiseOrderResult($schema);
        $this->createRemiseOrderStatus($schema);
        $this->createRemisePaymentMethod($schema);
    }

    /**
     * データベースの変更後の処理
     */
    public function postUp(Schema $schema)
    {
        // ルミーズプラグイン設定情報レコード追加
        $this->insertRemiseConfig();
    }

    /**
     * データベースの取り消し前の処理
     */
    public function preDown(Schema $schema)
    {
    }

    /**
     * データベースの取り消し処理
     */
    public function down(Schema $schema)
    {
        // 支払方法更新
        $this->updateRemisePaymentMethods();
        // 受注状態更新
        $this->updateRemiseOrderStatus();
        // プラグイン設定情報更新
        $this->updateRemiseConfig();
    }

    /**
     * データベースの取り消し後の処理
     */
    public function postDown(Schema $schema)
    {
    }


    /**
     * ルミーズプラグイン設定情報テーブル作成
     *
     * @param  Schema  $schema  
     */
    protected function createRemiseConfig(Schema $schema)
    {
        $tableName = 'plg_remise_config';

        // テーブル生成/取得
        $table = null;
        $isSetPKey = 0;
        if (!$schema->hasTable($tableName)) {
            $table = $schema->createTable($tableName);
        } else {
            $table = $schema->getTable($tableName);
            $isSetPKey = 1;
        }

        // プラグインID
        if (!$table->hasColumn('plugin_id'))
        {
            $table->addColumn('plugin_id', 'integer', array('autoincrement' => true));
        }
        // プラグインコード
        if (!$table->hasColumn('plugin_code'))
        {
            $table->addColumn('plugin_code', 'text', array('notnull' => true));
        }
        // プラグイン名
        if (!$table->hasColumn('plugin_name'))
        {
            $table->addColumn('plugin_name', 'text', array('notnull' => true));
        }
        // 設定情報
        if (!$table->hasColumn('info'))
        {
            $table->addColumn('info', 'text', array('notnull' => false));
        }
        // 削除フラグ
        if (!$table->hasColumn('del_flg'))
        {
            $table->addColumn('del_flg', 'smallint', array('notnull' => true, 'unsigned' => false, 'default' => 0));
        }
        // 作成日時
        if (!$table->hasColumn('create_date'))
        {
            $table->addColumn('create_date', 'datetime', array('notnull' => true));
        }
        // 更新日時
        if (!$table->hasColumn('update_date'))
        {
            $table->addColumn('update_date', 'datetime', array('notnull' => true));
        }

        // 主キー設定
        if ($isSetPKey == 0)
        {
            $table->setPrimaryKey(array('plugin_id'));
        }
    }

    /**
     * ルミーズ受注結果情報テーブル作成
     *
     * @param  Schema  $schema  
     */
    protected function createRemiseOrderResult(Schema $schema)
    {
        $tableName = 'plg_remise_order_result';

        // テーブル生成/取得
        $table = null;
        $isSetPKey = 0;
        if (!$schema->hasTable($tableName)) {
            $table = $schema->createTable($tableName);
        } else {
            $table = $schema->getTable($tableName);
            $isSetPKey = 1;
        }

        // 注文番号ID
        if (!$table->hasColumn('order_id'))
        {
            $table->addColumn('order_id', 'integer', array('notnull' => true));
        }
        // 決済結果
        if (!$table->hasColumn('credit_result'))
        {
            $table->addColumn('credit_result', 'text', array('notnull' => false));
        }
        // 備考1
        if (!$table->hasColumn('memo01'))
        {
            $table->addColumn('memo01', 'text', array('notnull' => false));
        }
        // 備考2
        if (!$table->hasColumn('memo02'))
        {
            $table->addColumn('memo02', 'text', array('notnull' => false));
        }
        // 備考3
        if (!$table->hasColumn('memo03'))
        {
            $table->addColumn('memo03', 'text', array('notnull' => false));
        }
        // 備考4
        if (!$table->hasColumn('memo04'))
        {
            $table->addColumn('memo04', 'text', array('notnull' => false));
        }
        // 備考5
        if (!$table->hasColumn('memo05'))
        {
            $table->addColumn('memo05', 'text', array('notnull' => false));
        }
        // 備考6
        if (!$table->hasColumn('memo06'))
        {
            $table->addColumn('memo06', 'text', array('notnull' => false));
        }
        // 備考7
        if (!$table->hasColumn('memo07'))
        {
            $table->addColumn('memo07', 'text', array('notnull' => false));
        }
        // 備考8
        if (!$table->hasColumn('memo08'))
        {
            $table->addColumn('memo08', 'text', array('notnull' => false));
        }
        // 備考9
        if (!$table->hasColumn('memo09'))
        {
            $table->addColumn('memo09', 'text', array('notnull' => false));
        }
        // 備考10
        if (!$table->hasColumn('memo10'))
        {
            $table->addColumn('memo10', 'text', array('notnull' => false));
        }
        // 前回ペイクイックID
        if (!$table->hasColumn('old_payquick_id'))
        {
            $table->addColumn('old_payquick_id', 'text', array('notnull' => false));
        }
        // 前回カード番号
        if (!$table->hasColumn('old_card'))
        {
            $table->addColumn('old_card', 'text', array('notnull' => false));
        }
        // 前回カード有効期限
        if (!$table->hasColumn('old_expire'))
        {
            $table->addColumn('old_expire', 'text', array('notnull' => false));
        }
        // 前回ペイクイック実行日
        if (!$table->hasColumn('old_payquick_date'))
        {
            $table->addColumn('old_payquick_date', 'text', array('notnull' => false));
        }
        // ペイクイックID
        if (!$table->hasColumn('payquick_id'))
        {
            $table->addColumn('payquick_id', 'text', array('notnull' => false));
        }
        // カード番号
        if (!$table->hasColumn('card'))
        {
            $table->addColumn('card', 'text', array('notnull' => false));
        }
        // カード有効期限
        if (!$table->hasColumn('expire'))
        {
            $table->addColumn('expire', 'text', array('notnull' => false));
        }
        // ペイクイック実行日
        if (!$table->hasColumn('payquick_date'))
        {
            $table->addColumn('payquick_date', 'text', array('notnull' => false));
        }
        // ペイクイック実行フラグ
        if (!$table->hasColumn('payquick_flg'))
        {
            $table->addColumn('payquick_flg', 'text', array('notnull' => false));
        }
        // 作成日時
        if (!$table->hasColumn('create_date'))
        {
            $table->addColumn('create_date', 'datetime', array('notnull' => true));
        }
        // 更新日時
        if (!$table->hasColumn('update_date'))
        {
            $table->addColumn('update_date', 'datetime', array('notnull' => true));
        }

        // 主キー設定
        if ($isSetPKey == 0)
        {
            $table->setPrimaryKey(array('order_id'));
        }
    }

    /**
     * ルミーズ受注状態テーブル作成
     *
     * @param  Schema  $schema  
     */
    protected function createRemiseOrderStatus(Schema $schema)
    {
        $tableName = 'plg_remise_order_status';

        // テーブル生成/取得
        $table = null;
        $isSetPKey = 0;
        if (!$schema->hasTable($tableName)) {
            $table = $schema->createTable($tableName);
        } else {
            $table = $schema->getTable($tableName);
            $isSetPKey = 1;
        }

        // 受注状態ID
        if (!$table->hasColumn('status_id'))
        {
            $table->addColumn('status_id', 'integer', array('notnull' => true));
        }
        // 状態種別
        if (!$table->hasColumn('status_type'))
        {
            $table->addColumn('status_type', 'smallint', array('notnull' => true, 'unsigned' => false));
        }
        // 状態名
        if (!$table->hasColumn('status_name'))
        {
            $table->addColumn('status_name', 'text', array('notnull' => true));
        }
        // 状態色
        if (!$table->hasColumn('status_color'))
        {
            $table->addColumn('status_color', 'text', array('notnull' => false));
        }
        // 会員向け状態名
        if (!$table->hasColumn('customer_status_name'))
        {
            $table->addColumn('customer_status_name', 'text', array('notnull' => false));
        }
        // 削除フラグ
        if (!$table->hasColumn('del_flg'))
        {
            $table->addColumn('del_flg', 'smallint', array('notnull' => true, 'unsigned' => false, 'default' => 0));
        }
        // 作成日時
        if (!$table->hasColumn('create_date'))
        {
            $table->addColumn('create_date', 'datetime', array('notnull' => true));
        }
        // 更新日時
        if (!$table->hasColumn('update_date'))
        {
            $table->addColumn('update_date', 'datetime', array('notnull' => true));
        }

        // 主キー設定
        if ($isSetPKey == 0)
        {
            $table->setPrimaryKey(array('status_id'));
        }
    }

    /**
     * ルミーズ支払方法情報テーブル作成
     *
     * @param  Schema  $schema  
     */
    protected function createRemisePaymentMethod(Schema $schema)
    {
        $tableName = 'plg_remise_payment_methods';

        // テーブル生成/取得
        $table = null;
        $isSetPKey = 0;
        if (!$schema->hasTable($tableName)) {
            $table = $schema->createTable($tableName);
        } else {
            $table = $schema->getTable($tableName);
            $isSetPKey = 1;
        }

        // 支払方法ID
        if (!$table->hasColumn('payment_id'))
        {
            $table->addColumn('payment_id', 'integer', array('notnull' => true));
        }
        // 支払種別
        if (!$table->hasColumn('pay_type'))
        {
            $table->addColumn('pay_type', 'smallint', array('notnull' => true, 'unsigned' => false));
        }
        // 支払方法
        if (!$table->hasColumn('pay_name'))
        {
            $table->addColumn('pay_name', 'text', array('notnull' => true));
        }
        // 削除フラグ
        if (!$table->hasColumn('del_flg'))
        {
            $table->addColumn('del_flg', 'smallint', array('notnull' => true, 'unsigned' => false, 'default' => 0));
        }
        // 作成日時
        if (!$table->hasColumn('create_date'))
        {
            $table->addColumn('create_date', 'datetime', array('notnull' => true));
        }
        // 更新日時
        if (!$table->hasColumn('update_date'))
        {
            $table->addColumn('update_date', 'datetime', array('notnull' => true));
        }

        // 主キー設定
        if ($isSetPKey == 0)
        {
            $table->setPrimaryKey(array('payment_id'));
        }
    }

    /**
     * ルミーズプラグイン設定情報レコード追加
     */
    public function insertRemiseConfig()
    {
        // configファイル読込
        $pluginConfig = Yaml::parse(__DIR__ . '/../../../config.yml');

        $datetime = date('Y-m-d H:i:s');

        // ルミーズプラグイン設定情報の取得
        $select = "SELECT count(*) FROM plg_remise_config"
                . " WHERE plugin_code = '" . $pluginConfig['code'] . "'";
        $count = $this->connection->executeQuery($select)->fetchColumn(0);

        // 存在しない場合
        if ($count == 0)
        {
            // 新規追加
            $insert = "INSERT INTO plg_remise_config (plugin_code, plugin_name, create_date, update_date)"
                    . " VALUES ('" . $pluginConfig['code'] . "', 'ルミーズ決済プラグイン', '" . $datetime . "', '" . $datetime . "')";
            $this->connection->executeUpdate($insert);
        }
        else
        {
            // 更新
            $update = "UPDATE plg_remise_config"
                    . " SET del_flg = 0, update_date = '" . $datetime . "'"
                    . " WHERE plugin_code = '" . $pluginConfig['code'] . "'";
            $this->connection->executeUpdate($update);
        }
    }

    /**
     * 支払方法更新
     */
    protected function updateRemisePaymentMethods()
    {
        $datetime = date('Y-m-d H:i:s');

        // ルミーズ支払方法情報と紐づくEC-CUBE支払方法レコード検索
        $select = "SELECT p.payment_id FROM plg_remise_payment_methods as m"
                . " JOIN dtb_payment as p ON m.payment_id = p.payment_id";
        $paymentIds = $this->connection->executeQuery($select)->fetchAll();

        if (!empty($paymentIds))
        {
            $ids = array();
            foreach ($paymentIds as $item)
            {
                $ids[] = $item['payment_id'];
            }

            // EC-CUBE支払方法レコードの更新
            $update = "UPDATE dtb_payment"
                    . " SET del_flg = 1, update_date = '" . $datetime . "'"
                    . " WHERE payment_id in (" . implode(",", $ids). ")";
            $this->connection->executeUpdate($update);
        }

        // ルミーズ支払方法情報レコードの更新
        $update = "UPDATE plg_remise_payment_methods"
                . " SET del_flg = 1, update_date = '" . $datetime . "'";
        $this->connection->executeUpdate($update);
    }

    /**
     * 受注状態更新
     */
    protected function updateRemiseOrderStatus()
    {
        // ルミーズ受注状態情報レコードの更新
        $update = "UPDATE plg_remise_order_status"
                . " SET del_flg = 1, update_date = '" . date('Y-m-d H:i:s') . "'";
        $this->connection->executeUpdate($update);
    }

    /**
     * プラグイン設定情報更新
     */
    protected function updateRemiseConfig()
    {
        // ルミーズ受注状態情報レコードの更新
        $update = "UPDATE plg_remise_config"
                . " SET del_flg = 1, update_date = '" . date('Y-m-d H:i:s') . "'";
        $this->connection->executeUpdate($update);
    }
}
