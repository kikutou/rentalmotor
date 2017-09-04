<?php
/*
 * Copyright(c) 2016 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\Yaml\Yaml;

/**
 * マイグレーション
 */
class Version20160602090000 extends AbstractMigration
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
                    . " VALUES ('" . $pluginConfig['code'] . "', 'ルミーズカード決済拡張セットプラグイン', '" . $datetime . "', '" . $datetime . "')";
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
}
