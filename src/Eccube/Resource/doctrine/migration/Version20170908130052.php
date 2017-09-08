<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170908130052 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('dtb_questionnaire');
        $table->changeColumn('question1', array('NotNull' => false));
        $table->changeColumn('question2', array('NotNull' => false));
        $table->changeColumn('question3', array('NotNull' => false));
        $table->changeColumn('question4', array('NotNull' => false));
        $table->changeColumn('question5', array('NotNull' => false));
        $table->changeColumn('question6', array('NotNull' => false));
        $table->changeColumn('question7', array('NotNull' => false));
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
