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
class Version20160331090000 extends AbstractMigration
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
        $this->createRemiseMultiPayInfo($schema);
        $this->createRemiseMultiPayWay($schema);
        $this->createRemiseMailTemplate($schema);
    }

    /**
     * データベースの変更後の処理
     */
    public function postUp(Schema $schema)
    {
        // ルミーズマルチ決済支払方法案内情報レコード追加
        $this->insertRemiseMultiPayInfo();
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
     * ルミーズマルチ決済支払方法案内情報テーブル作成
     *
     * @param  Schema  $schema  
     */
    protected function createRemiseMultiPayInfo(Schema $schema)
    {
        $tableName = 'plg_remise_multi_payinfo';

        // テーブル生成/取得
        $table = null;
        $isSetPKey = 0;
        if (!$schema->hasTable($tableName)) {
            $table = $schema->createTable($tableName);
        } else {
            $table = $schema->getTable($tableName);
            $isSetPKey = 1;
        }

        // 支払方法案内ID
        if (!$table->hasColumn('payinfo_id'))
        {
            $table->addColumn('payinfo_id', 'integer', array('notnull' => true));
        }
        // 払出番号1ラベル
        if (!$table->hasColumn('pay_no1_label'))
        {
            $table->addColumn('pay_no1_label', 'text', array('notnull' => false));
        }
        // 払出番号2ラベル
        if (!$table->hasColumn('pay_no2_label'))
        {
            $table->addColumn('pay_no2_label', 'text', array('notnull' => false));
        }
        // 登録電話番号ラベル
        if (!$table->hasColumn('telno_label'))
        {
            $table->addColumn('telno_label', 'text', array('notnull' => false));
        }
        // 各支払先窓口でのお支払い方法ラベル
        if (!$table->hasColumn('dsk_label'))
        {
            $table->addColumn('dsk_label', 'text', array('notnull' => false));
        }
        // メッセージ
        if (!$table->hasColumn('message'))
        {
            $table->addColumn('message', 'text', array('notnull' => false));
        }
        // 備考
        if (!$table->hasColumn('note'))
        {
            $table->addColumn('note', 'text', array('notnull' => false));
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
            $table->setPrimaryKey(array('payinfo_id'));
        }
    }

    /**
     * ルミーズマルチ決済支払方法テーブル作成
     *
     * @param  Schema  $schema  
     */
    protected function createRemiseMultiPayWay(Schema $schema)
    {
        $tableName = 'plg_remise_multi_payway';

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
        if (!$table->hasColumn('payway_id'))
        {
            $table->addColumn('payway_id', 'integer', array('notnull' => true));
        }
        // CVS_CODE
        if (!$table->hasColumn('cvs_code'))
        {
            $table->addColumn('cvs_code', 'text', array('notnull' => true));
        }
        // CVS_WAY
        if (!$table->hasColumn('cvs_way'))
        {
            $table->addColumn('cvs_way', 'text', array('notnull' => true));
        }
        // 名称
        if (!$table->hasColumn('cvs_name'))
        {
            $table->addColumn('cvs_name', 'text', array('notnull' => true));
        }
        // 支払方法案内ID
        if (!$table->hasColumn('payinfo_id'))
        {
            $table->addColumn('payinfo_id', 'integer', array('notnull' => true));
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
            $table->setPrimaryKey(array('payway_id'));
        }
    }

    /**
     * ルミーズメールテンプレートテーブル作成
     *
     * @param  Schema  $schema  
     */
    protected function createRemiseMailTemplate(Schema $schema)
    {
        $tableName = 'plg_remise_mail_template';

        // テーブル生成/取得
        $table = null;
        $isSetPKey = 0;
        if (!$schema->hasTable($tableName)) {
            $table = $schema->createTable($tableName);
        } else {
            $table = $schema->getTable($tableName);
            $isSetPKey = 1;
        }

        // ID
        if (!$table->hasColumn('mail_template_id'))
        {
            $table->addColumn('mail_template_id', 'integer', array('notnull' => true));
        }
        // 種別
        if (!$table->hasColumn('template_type'))
        {
            $table->addColumn('template_type', 'smallint', array('notnull' => true, 'unsigned' => false));
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
            $table->setPrimaryKey(array('mail_template_id'));
        }
    }

    /**
     * ルミーズマルチ決済支払方法案内情報レコード追加
     */
    public function insertRemiseMultiPayInfo()
    {
        // マルチ決済支払方法
        $multiInfos = array(
            '1' => array(
                'PAY_NO1'   => '払込票番号',
                'PAY_NO2'   => 'お支払い方法のご案内',
                'TELNO'     => '',
                'DSK'       => '',
                'MESSAGE'   => 
'※上記リンクのページを印刷して店頭へお持ちいただくか、払込票番号(13桁)をメモして頂いて、セブンイレブンのレジでお支払いください。
手書きメモの場合、「インターネット支払い」とレジにてお申し出の上、払込票番号を記したメモをご提示ください。',
                'NOTE'      => 'セブンイレブン',
            ),
            '2' => array(
                'PAY_NO1'   => '受付番号,確認番号',
                'PAY_NO2'   => 'お支払い方法のご案内',
                'TELNO'     => '',
                'DSK'       => '',
                'MESSAGE'   => '',
                'NOTE'      => 'ローソン、ミニストップ',
            ),
            '3' => array(
                'PAY_NO1'   => '受付番号',
                'PAY_NO2'   => 'お支払い方法のご案内',
                'TELNO'     => '登録電話番号',
                'DSK'       => '',
                'MESSAGE'   => '',
                'NOTE'      => 'セイコーマート',
            ),
            '4' => array(
                'PAY_NO1'   => '受付番号',
                'PAY_NO2'   => 'お支払い方法のご案内',
                'TELNO'     => '登録電話番号',
                'DSK'       => '',
                'MESSAGE'   => '',
                'NOTE'      => 'サンクス、サークルＫ',
            ),
            '5' => array(
                'PAY_NO1'   => 'オンライン決済番号',
                'PAY_NO2'   => 'お支払い方法のご案内',
                'TELNO'     => '',
                'DSK'       => '',
                'MESSAGE'   => 
'※上記リンクのページを印刷して店頭へお持ちいただくか、オンライン決済番号(11桁)をメモして頂いて、デイリーヤマザキ・ヤマザキデイリーストアのレジでお支払いください。
手書きメモの場合、「オンライン決済」とレジにてお申し出の上、オンライン決済番号を記したメモをご提示ください。',
                'NOTE'      => 'デイリーヤマザキ、ヤマザキデイリーストア',
            ),
            '6' => array(
                'PAY_NO1'   => '企業コード',
                'PAY_NO2'   => '注文番号',
                'TELNO'     => '',
                'DSK'       => 'お支払い方法のご案内',
                'MESSAGE'   => 
'※上記リンクのページから「お支払い先」の支払方法を選択し、画面の指示に従ってお支払いください。',
                'NOTE'      => 'ファミリーマート',
            ),
            '7' => array(
                'PAY_NO1'   => '確認番号',
                'PAY_NO2'   => 'お支払い方法のご案内',
                'TELNO'     => '登録電話番号',
                'DSK'       => '',
                'MESSAGE'   => 
'※「Pay-easy」マークが貼付されているATM、インターネットバンキング、モバイルバンキングでのお支払いができます。
各収納機関の画面指示に従ってお支払いください。
『収納機関番号』は「58091」、『お客様番号』は「ご登録のお電話番号」となります。
なお、上記リンクからログインされた場合は『収納機関番号』・『お客様番号』・『確認番号』の入力は不要になります。',
                'NOTE'      => 'ペイジー',
            ),
            '8' => array(
                'PAY_NO1'   => '受付番号',
                'PAY_NO2'   => '',
                'TELNO'     => '',
                'DSK'       => 'お支払い方法のご案内',
                'MESSAGE'   => 
'※上記リンクのページから「お支払い先」の支払方法を選択し、画面の指示に従ってお支払いください。',
                'NOTE'      => 'コンビニ払込票、コンビニ払込票（郵便局・ゆうちょ銀行）',
            ),
            '9' => array(
                'PAY_NO1'   => '受付番号',
                'PAY_NO2'   => 'お支払い方法のご案内',
                'TELNO'     => '',
                'DSK'       => '',
                'MESSAGE'   => 
'※上記リンクのページを表示の上、画面の指示に従ってお支払いください。',
                'NOTE' => '楽天Ｅｄｙ、モバイルＥｄｙ、モバイルＳｕｉｃａ、Ｓｕｉｃａインターネットサービス、楽天銀行、ジャパンネット銀行、ウェブマネー、ビットキャッシュ、ＪＣＢプレモカード、ＰａｙＰａｌ、クレジットカード',
            ),
        );

        $datetime = date('Y-m-d H:i:s');

        // ルミーズマルチ決済支払方法案内情報の取得
        $select = "SELECT count(*) FROM plg_remise_multi_payinfo";
        $count = $this->connection->executeQuery($select)->fetchColumn(0);

        // 存在する場合、処理しない
        if ($count != 0) {
            return;
        }

        // 新規追加
        foreach ($multiInfos as $id => $record)
        {
            $insert = "INSERT INTO plg_remise_multi_payinfo (payinfo_id,"
                    . " pay_no1_label, pay_no2_label,"
                    . " telno_label, dsk_label,"
                    . " message, note,"
                    . " create_date, update_date)"
                    . " VALUES (" . $id . ","
                    . " " . $this->getVal($record, 'PAY_NO1') . ", " . $this->getVal($record, 'PAY_NO2') . ","
                    . " " . $this->getVal($record, 'TELNO') . ", " . $this->getVal($record, 'DSK') . ","
                    . " " . $this->getVal($record, 'MESSAGE') . ", " . $this->getVal($record, 'NOTE') . ","
                    . " '" . $datetime . "', '" . $datetime . "')";
            $this->connection->executeUpdate($insert);
        }
    }

    /**
     * ルミーズマルチ決済支払方法レコード追加
     */
    public function insertRemiseMultiPayWay()
    {
        // マルチ決済支払方法
        $multiWays = array(
            '1'  => array('CVS_CODE' => 'D001', 'CVS_WAY' => '002', 'CVS_NAME' => 'セブンイレブン',                         'PAYINFO_ID' => '1'),
            '2'  => array('CVS_CODE' => 'D002', 'CVS_WAY' => '003', 'CVS_NAME' => 'ローソン',                               'PAYINFO_ID' => '2'),
            '3'  => array('CVS_CODE' => 'D005', 'CVS_WAY' => '004', 'CVS_NAME' => 'ミニストップ',                           'PAYINFO_ID' => '2'),
            '4'  => array('CVS_CODE' => 'D015', 'CVS_WAY' => '004', 'CVS_NAME' => 'セイコーマート',                         'PAYINFO_ID' => '3'),
            '5'  => array('CVS_CODE' => 'D003', 'CVS_WAY' => '004', 'CVS_NAME' => 'サンクス',                               'PAYINFO_ID' => '4'),
            '6'  => array('CVS_CODE' => 'D004', 'CVS_WAY' => '004', 'CVS_NAME' => 'サークルＫ',                             'PAYINFO_ID' => '4'),
            '7'  => array('CVS_CODE' => 'D010', 'CVS_WAY' => '004', 'CVS_NAME' => 'デイリーヤマザキ',                       'PAYINFO_ID' => '5'),
            '8'  => array('CVS_CODE' => 'D011', 'CVS_WAY' => '004', 'CVS_NAME' => 'ヤマザキデイリーストア',                 'PAYINFO_ID' => '5'),
            '9'  => array('CVS_CODE' => 'D030', 'CVS_WAY' => '006', 'CVS_NAME' => 'ファミリーマート',                       'PAYINFO_ID' => '6'),
            '10' => array('CVS_CODE' => 'D405', 'CVS_WAY' => '400', 'CVS_NAME' => 'ペイジー',                               'PAYINFO_ID' => '7'),
            '11' => array('CVS_CODE' => 'P901', 'CVS_WAY' => '900', 'CVS_NAME' => 'コンビニ払込票',                         'PAYINFO_ID' => '8'),
            '12' => array('CVS_CODE' => 'P902', 'CVS_WAY' => '900', 'CVS_NAME' => 'コンビニ払込票（郵便局・ゆうちょ銀行）', 'PAYINFO_ID' => '8'),
            '13' => array('CVS_CODE' => 'P903', 'CVS_WAY' => '900', 'CVS_NAME' => 'コンビニ払込票',                         'PAYINFO_ID' => '8'),
            '14' => array('CVS_CODE' => 'D401', 'CVS_WAY' => '400', 'CVS_NAME' => '楽天Ｅｄｙ',                             'PAYINFO_ID' => '9'),
            '15' => array('CVS_CODE' => 'D402', 'CVS_WAY' => '400', 'CVS_NAME' => 'モバイルＥｄｙ',                         'PAYINFO_ID' => '9'),
            '16' => array('CVS_CODE' => 'D403', 'CVS_WAY' => '400', 'CVS_NAME' => 'モバイルＳｕｉｃａ',                     'PAYINFO_ID' => '9'),
            '17' => array('CVS_CODE' => 'D404', 'CVS_WAY' => '400', 'CVS_NAME' => '楽天銀行',                               'PAYINFO_ID' => '9'),
            '18' => array('CVS_CODE' => 'D406', 'CVS_WAY' => '400', 'CVS_NAME' => 'ジャパンネット銀行',                     'PAYINFO_ID' => '9'),
            '19' => array('CVS_CODE' => 'D407', 'CVS_WAY' => '400', 'CVS_NAME' => 'Ｓｕｉｃａインターネットサービス',       'PAYINFO_ID' => '9'),
            '20' => array('CVS_CODE' => 'D451', 'CVS_WAY' => '400', 'CVS_NAME' => 'ウェブマネー',                           'PAYINFO_ID' => '9'),
            '21' => array('CVS_CODE' => 'D452', 'CVS_WAY' => '400', 'CVS_NAME' => 'ビットキャッシュ',                       'PAYINFO_ID' => '9'),
            '22' => array('CVS_CODE' => 'D453', 'CVS_WAY' => '400', 'CVS_NAME' => 'ＪＣＢプレモカード',                     'PAYINFO_ID' => '9'),
            '23' => array('CVS_CODE' => 'C501', 'CVS_WAY' => '500', 'CVS_NAME' => 'クレジットカード',                       'PAYINFO_ID' => '9'),
            '24' => array('CVS_CODE' => 'C502', 'CVS_WAY' => '500', 'CVS_NAME' => 'クレジットカード',                       'PAYINFO_ID' => '9'),
            '25' => array('CVS_CODE' => 'C511', 'CVS_WAY' => '500', 'CVS_NAME' => 'ＰａｙＰａｌ',                           'PAYINFO_ID' => '9'),
        );

        $datetime = date('Y-m-d H:i:s');

        // ルミーズマルチ決済支払方法の取得
        $select = "SELECT count(*) FROM plg_remise_multi_payway";
        $count = $this->connection->executeQuery($select)->fetchColumn(0);

        // 存在する場合、処理しない
        if ($count != 0) {
            return;
        }

        // 新規追加
        foreach ($multiWays as $id => $record)
        {
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
