<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170828162113 extends AbstractMigration
{

    const NAME = 'dtb_customer_category';

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
        $table->addColumn('customer_id', 'integer', array('NotNull' => true));
        $table->addColumn('category_id', 'integer', array('NotNull' => true));
        $table->addColumn('rank', 'integer', array('NotNull' => true));
        $table->setPrimaryKey(array('customer_id', 'category_id'));
        $table->addForeignKeyConstraint($schema->getTable('dtb_customer'), array('customer_id'), array('customer_id'));
        $table->addForeignKeyConstraint($schema->getTable('dtb_category'), array('category_id'), array('category_id'));
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
