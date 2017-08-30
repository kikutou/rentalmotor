<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170829195652 extends AbstractMigration
{

    const MASTER_TABLE_NAME1 = 'mtb_questionnaire_question1';
    const MASTER_TABLE_NAME2 = 'mtb_questionnaire_question2';
    // †–î}3¤Ï¥Ç©`¥¿ÐÎÊ½¤Ê¤Î¤Ç¡¢»Ø´ð¥Þ¥¹¥¿¥Ç©`¥¿¤Ï¤¤¤é¤Ê¤¤
    // const MASTER_TABLE_NAME3 = 'mtb_questionnaire_question3';
    const MASTER_TABLE_NAME4 = 'mtb_questionnaire_question4';
    const MASTER_TABLE_NAME5 = 'mtb_questionnaire_question5';
    const MASTER_TABLE_NAME6 = 'mtb_questionnaire_question6';
    const MASTER_TABLE_NAME7 = 'mtb_questionnaire_question7';
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        if (!$schema->hasTable(self::MASTER_TABLE_NAME1)) {
            $table = $schema->createTable(self::MASTER_TABLE_NAME1);
            $table->addColumn('id', 'smallint', array('NotNull' => true));
            $table->addColumn('name', 'text', array('NotNull' => false));
            $table->addColumn('rank', 'smallint', array('NotNull' => true));
            $table->setPrimaryKey(array('id'));
        }

        // this up() migration is auto-generated, please modify it to your needs
        if (!$schema->hasTable(self::MASTER_TABLE_NAME2)) {
            $table = $schema->createTable(self::MASTER_TABLE_NAME2);
            $table->addColumn('id', 'smallint', array('NotNull' => true));
            $table->addColumn('name', 'text', array('NotNull' => false));
            $table->addColumn('rank', 'smallint', array('NotNull' => true));
            $table->setPrimaryKey(array('id'));

        }

        // this up() migration is auto-generated, please modify it to your needs
        if (!$schema->hasTable(self::MASTER_TABLE_NAME4)) {
            $table = $schema->createTable(self::MASTER_TABLE_NAME4);
            $table->addColumn('id', 'smallint', array('NotNull' => true));
            $table->addColumn('name', 'text', array('NotNull' => false));
            $table->addColumn('rank', 'smallint', array('NotNull' => true));
            $table->setPrimaryKey(array('id'));

        }

        // this up() migration is auto-generated, please modify it to your needs
        if (!$schema->hasTable(self::MASTER_TABLE_NAME5)) {
            $table = $schema->createTable(self::MASTER_TABLE_NAME5);
            $table->addColumn('id', 'smallint', array('NotNull' => true));
            $table->addColumn('name', 'text', array('NotNull' => false));
            $table->addColumn('rank', 'smallint', array('NotNull' => true));
            $table->setPrimaryKey(array('id'));

        }

        // this up() migration is auto-generated, please modify it to your needs
        if (!$schema->hasTable(self::MASTER_TABLE_NAME6)) {
            $table = $schema->createTable(self::MASTER_TABLE_NAME6);
            $table->addColumn('id', 'smallint', array('NotNull' => true));
            $table->addColumn('name', 'text', array('NotNull' => false));
            $table->addColumn('rank', 'smallint', array('NotNull' => true));
            $table->setPrimaryKey(array('id'));

        }

        // this up() migration is auto-generated, please modify it to your needs
        if (!$schema->hasTable(self::MASTER_TABLE_NAME7)) {
            $table = $schema->createTable(self::MASTER_TABLE_NAME7);
            $table->addColumn('id', 'smallint', array('NotNull' => true));
            $table->addColumn('name', 'text', array('NotNull' => false));
            $table->addColumn('rank', 'smallint', array('NotNull' => true));
            $table->setPrimaryKey(array('id'));

        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        if ($schema->hasTable(self::MASTER_TABLE_NAME1)) {
            $schema->dropTable(self::MASTER_TABLE_NAME1);
        }

        if ($schema->hasTable(self::MASTER_TABLE_NAME2)) {
            $schema->dropTable(self::MASTER_TABLE_NAME2);
        }

        if ($schema->hasTable(self::MASTER_TABLE_NAME4)) {
            $schema->dropTable(self::MASTER_TABLE_NAME4);
        }

        if ($schema->hasTable(self::MASTER_TABLE_NAME5)) {
            $schema->dropTable(self::MASTER_TABLE_NAME5);
        }

        if ($schema->hasTable(self::MASTER_TABLE_NAME6)) {
            $schema->dropTable(self::MASTER_TABLE_NAME6);
        }

        if ($schema->hasTable(self::MASTER_TABLE_NAME7)) {
            $schema->dropTable(self::MASTER_TABLE_NAME7);
        }

    }
}
