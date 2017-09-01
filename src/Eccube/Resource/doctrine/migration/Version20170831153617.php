<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170831153617 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('dtb_questionnaire');

        if ($table->hasColumn('question7')) {
            $table->getColumn('question7')->setType(Type::getType('smallint'));
        } else {
            $table->addColumn('question7', 'smallint', array('NotNull' => true));
        }
        $table->addForeignKeyConstraint($schema->getTable('mtb_questionnaire_question7'), array('question7'), array('id'));

        if (!$table->hasColumn('question8')) {
            $table->addColumn('question8', 'text', array('NotNull' => false));
        }

        if (!$table->hasColumn('question8_note_admin')) {
            $table->addColumn('question8_note_admin', 'text', array('NotNull' => false));
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
