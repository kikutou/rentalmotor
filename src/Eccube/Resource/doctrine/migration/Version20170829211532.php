<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170829211532 extends AbstractMigration
{
    const NAME = 'dtb_questionnaire';
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        if ($schema->hasTable(self::NAME)) {
            return true;
        }
        $table = $schema->createTable(self::NAME);

        $table->addColumn('questionnaire_id', 'integer', array(
            'autoincrement' => true,
        ));

        $table->addColumn('customer_id', 'integer', array('NotNull' => true));
        $table->addForeignKeyConstraint($schema->getTable('dtb_customer'), array('customer_id'), array('customer_id'));

        $table->addColumn('question1', 'smallint', array('NotNull' => true));
        $table->addForeignKeyConstraint($schema->getTable('mtb_questionnaire_question1'), array('question1'), array('id'));
        $table->addColumn('question1_note', 'text', array('NotNull' => false));
        $table->addColumn('question1_note_admin', 'text', array('NotNull' => false));

        $table->addColumn('question2', 'smallint', array('NotNull' => true));
        $table->addForeignKeyConstraint($schema->getTable('mtb_questionnaire_question2'), array('question2'), array('id'));
//        $table->addColumn('question2_note', 'text', array('NotNull' => false));
        $table->addColumn('question2_note_admin', 'text', array('NotNull' => false));


        $table->addColumn('question3', 'datetime', array('NotNull' => true));
//        $table->addColumn('question3_note', 'text', array('NotNull' => false));
        $table->addColumn('question3_note_admin', 'text', array('NotNull' => false));

        $table->addColumn('question4', 'smallint', array('NotNull' => true));
        $table->addForeignKeyConstraint($schema->getTable('mtb_questionnaire_question4'), array('question4'), array('id'));
        $table->addColumn('question4_note', 'text', array('NotNull' => false));
        $table->addColumn('question4_note_admin', 'text', array('NotNull' => false));

        $table->addColumn('question5', 'smallint', array('NotNull' => true));
        $table->addForeignKeyConstraint($schema->getTable('mtb_questionnaire_question5'), array('question5'), array('id'));
        $table->addColumn('question5_note', 'text', array('NotNull' => false));
        $table->addColumn('question5_note_admin', 'text', array('NotNull' => false));

        $table->addColumn('question6', 'smallint', array('NotNull' => true));
        $table->addForeignKeyConstraint($schema->getTable('mtb_questionnaire_question6'), array('question6'), array('id'));
//        $table->addColumn('question6_note', 'text', array('NotNull' => false));
        $table->addColumn('question6_note_admin', 'text', array('NotNull' => false));

        $table->addColumn('question7', 'text', array('NotNull' => true));
//        $table->addColumn('question7_note', 'text', array('NotNull' => false));
        $table->addColumn('question7_note_admin', 'text', array('NotNull' => false));

        $table->addColumn('create_date', 'datetime', array('NotNull' => true));
        $table->addColumn('update_date', 'datetime', array('NotNull' => true));

        $table->addColumn('del_flg', 'smallint', array('NotNull' => true, 'default' => 0));

        $table->setPrimaryKey(array('questionnaire_id'));




    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        if ($schema->hasTable(self::NAME)) {
            $schema->dropTable(self::NAME);
        }

    }
}
