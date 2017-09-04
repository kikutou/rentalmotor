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
class Version20161027090000 extends AbstractMigration
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
        // カラム追加
        $this->addColumnRemiseCustomerPayquick($schema);
    }

    /**
     * データベースの変更後の処理
     */
    public function postUp(Schema $schema)
    {
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
     * ルミーズペイクイック情報テーブルカラム追加
     *
     * @param  Schema  $schema  
     */
    protected function addColumnRemiseCustomerPayquick(Schema $schema)
    {
        $tableName = 'plg_remise_customer_payquick';

        // テーブル取得
        if (!$schema->hasTable($tableName)) return;
        $table = $schema->getTable($tableName);

        // 前回カードブランド
        if (!$table->hasColumn('old_cardbrand'))
        {
            $table->addColumn('old_cardbrand', 'text', array('notnull' => false));
        }

        // カードブランド
        if (!$table->hasColumn('cardbrand'))
        {
            $table->addColumn('cardbrand', 'text', array('notnull' => false));
        }
    }
}
