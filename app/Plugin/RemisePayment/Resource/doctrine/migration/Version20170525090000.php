<?php
/*
 * Copyright(c) 2017 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\Yaml\Yaml;

/**
 * マイグレーション
 */
class Version20170525090000 extends AbstractMigration
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
    }

    /**
     * データベースの変更後の処理
     */
    public function postUp(Schema $schema)
    {
        // ルミーズマルチ決済支払方法レコード追加
        $this->insertRemiseMultiPayWay();
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
     * ルミーズマルチ決済支払方法レコード追加
     */
    public function insertRemiseMultiPayWay()
    {
        // マルチ決済支払方法
        $multiWays = array(
            '26' => array('CVS_CODE' => 'M601', 'CVS_WAY' => '600', 'CVS_NAME' => 'ドコモ ケータイ払い（都度決済）', 'PAYINFO_ID' => '9'),
            '27' => array('CVS_CODE' => 'M602', 'CVS_WAY' => '600', 'CVS_NAME' => 'ドコモ ケータイ払い（都度決済）', 'PAYINFO_ID' => '9'),
        );

        $datetime = date('Y-m-d H:i:s');

        // 新規追加
        foreach ($multiWays as $id => $record)
        {
            // ルミーズマルチ決済支払方法の取得
            $select = "SELECT count(*) FROM plg_remise_multi_payway where payway_id = " . $id;
            $count = $this->connection->executeQuery($select)->fetchColumn(0);

            // 存在する場合、処理しない
            if ($count != 0) continue;

            $insert = "INSERT INTO plg_remise_multi_payway (payway_id,"
                    . " cvs_code, cvs_way,"
                    . " cvs_name, payinfo_id,"
                    . " del_flg, create_date, update_date)"
                    . " VALUES (" . $id . ","
                    . " " . $this->getVal($record, 'CVS_CODE') . ", " . $this->getVal($record, 'CVS_WAY') . ","
                    . " " . $this->getVal($record, 'CVS_NAME') . ", " . $this->getVal($record, 'PAYINFO_ID') . ","
                    . " 0, '" . $datetime . "', '" . $datetime . "')";
            $this->connection->executeUpdate($insert);
        }
    }

    private function getVal($record, $keyword)
    {
        $value = "null";
        if (empty($record)) return $value;
        if (!isset($record[$keyword])) return $value;
        if (empty($record[$keyword])) return $value;
        $value = "'" . $record[$keyword] . "'";
        return $value;
    }
}
