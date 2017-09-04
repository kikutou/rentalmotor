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
class Version20160204090000 extends AbstractMigration
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
        $this->createRemiseCardMethod($schema);
        $this->createRemiseCustomerPayquick($schema);
        // 不要カラム削除
        $this->dropColumnRemiseOrderResult($schema);
    }

    /**
     * データベースの変更後の処理
     */
    public function postUp(Schema $schema)
    {
        // ルミーズプラグイン設定情報レコード追加
        $this->insertCardMethod();
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
     * ルミーズ支払方法テーブル作成
     *
     * @param  Schema  $schema  
     */
    protected function createRemiseCardMethod(Schema $schema)
    {
        $tableName = 'plg_remise_card_method';

        // テーブル生成/取得
        $table = null;
        $isSetPKey = 0;
        if (!$schema->hasTable($tableName)) {
            $table = $schema->createTable($tableName);
        } else {
            $table = $schema->getTable($tableName);
            $isSetPKey = 1;
        }

        // id
        if (!$table->hasColumn('card_method_id'))
        {
            $table->addColumn('card_method_id', 'integer', array('autoincrement' => true));
        }
        // 支払区分
        if (!$table->hasColumn('card_method_code'))
        {
            $table->addColumn('card_method_code', 'integer', array('notnull' => true));
        }
        // 支払区分名称
        if (!$table->hasColumn('card_method_name'))
        {
            $table->addColumn('card_method_name', 'text', array('notnull' => true));
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
            $table->setPrimaryKey(array('card_method_id'));
        }
    }

    /**
     * ルミーズペイクイック情報テーブル作成
     *
     * @param  Schema  $schema  
     */
    protected function createRemiseCustomerPayquick(Schema $schema)
    {
        $tableName = 'plg_remise_customer_payquick';

        // テーブル生成/取得
        $table = null;
        $isSetPKey = 0;
        if (!$schema->hasTable($tableName)) {
            $table = $schema->createTable($tableName);
        } else {
            $table = $schema->getTable($tableName);
            $isSetPKey = 1;
        }

        // 顧客ID
        if (!$table->hasColumn('customer_id'))
        {
            $table->addColumn('customer_id', 'integer', array('notnull' => true, 'unsigned' => false));
        }
        // 決済結果
        if (!$table->hasColumn('payquick_no'))
        {
            $table->addColumn('payquick_no', 'smallint', array('notnull' => true, 'unsigned' => false));
        }
        // 旧ペイクイックID
        if (!$table->hasColumn('old_payquick_id'))
        {
            $table->addColumn('old_payquick_id', 'text', array('notnull' => false));
        }
        // 旧カード
        if (!$table->hasColumn('old_card'))
        {
            $table->addColumn('old_card', 'text', array('notnull' => false));
        }
        // 旧有効期限
        if (!$table->hasColumn('old_expire'))
        {
            $table->addColumn('old_expire', 'text', array('notnull' => false));
        }
        // 旧利用日
        if (!$table->hasColumn('old_payquick_date'))
        {
            $table->addColumn('old_payquick_date', 'datetime', array('notnull' => false));
        }
        // ペイクイックID
        if (!$table->hasColumn('payquick_id'))
        {
            $table->addColumn('payquick_id', 'text', array('notnull' => false));
        }
        // カード
        if (!$table->hasColumn('card'))
        {
            $table->addColumn('card', 'text', array('notnull' => false));
        }
        // 有効期限
        if (!$table->hasColumn('expire'))
        {
            $table->addColumn('expire', 'text', array('notnull' => false));
        }
        // 利用日
        if (!$table->hasColumn('payquick_date'))
        {
            $table->addColumn('payquick_date', 'datetime', array('notnull' => false));
        }
        // ペイクイックフラグ
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
            $table->setPrimaryKey(array('customer_id', 'payquick_no'));
        }
    }

    /**
     * ルミーズ受注結果情報テーブル不要カラム削除
     *
     * @param  Schema  $schema  
     */
    protected function dropColumnRemiseOrderResult(Schema $schema)
    {
        $tableName = 'plg_remise_order_result';

        // テーブル取得
        if (!$schema->hasTable($tableName)) return;
        $table = $schema->getTable($tableName);

        // 前回ペイクイックID
        if ($table->hasColumn('old_payquick_id'))
        {
            $table->dropColumn('old_payquick_id');
        }
        // 前回カード番号
        if ($table->hasColumn('old_card'))
        {
            $table->dropColumn('old_card');
        }
        // 前回カード有効期限
        if ($table->hasColumn('old_expire'))
        {
            $table->dropColumn('old_expire');
        }
        // 前回ペイクイック実行日
        if ($table->hasColumn('old_payquick_date'))
        {
            $table->dropColumn('old_payquick_date');
        }
        // ペイクイックID
        if ($table->hasColumn('payquick_id'))
        {
            $table->dropColumn('payquick_id');
        }
        // カード番号
        if ($table->hasColumn('card'))
        {
            $table->dropColumn('card');
        }
        // カード有効期限
        if ($table->hasColumn('expire'))
        {
            $table->dropColumn('expire');
        }
        // ペイクイック実行日
        if ($table->hasColumn('payquick_date'))
        {
            $table->dropColumn('payquick_date');
        }
        // ペイクイック実行フラグ
        if ($table->hasColumn('payquick_flg'))
        {
            $table->dropColumn('payquick_flg');
        }
    }

    /**
    * ルミーズ利用可能支払方法情報レコード追加
    */
    public function insertCardMethod()
    {
        // 支払方法
        $cardMethod = array('10'=>'一括払い', '61'=>'分割払い（２回のみ）', '80'=>'リボルビング');

        $datetime = date('Y-m-d H:i:s');

        // ルミーズプラグイン設定情報の取得
        $select = "SELECT count(*) FROM plg_remise_card_method";
        $count = $this->connection->executeQuery($select)->fetchColumn(0);

        // 存在しない場合
        if ($count == 0)
        {
            foreach ($cardMethod as $methodCode => $methodName)
            {
                // 新規追加
                $insert = "INSERT INTO plg_remise_card_method (card_method_code, card_method_name, create_date, update_date)". " VALUES ('" . $methodCode ."','" . $methodName ."', '" . $datetime . "', '" . $datetime . "')";
                $this->connection->executeUpdate($insert);
            }
        }
    }
}
