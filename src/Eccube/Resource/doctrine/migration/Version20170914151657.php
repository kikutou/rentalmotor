<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170914151657 extends AbstractMigration
{
    const NAME = 'dtb_customer';
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        if (!$schema->hasTable(self::NAME)) {
            return true;
        }

        $table = $schema->getTable(self::NAME);

        $table->addColumn('bike1_brand', 'smallint', array('NotNull' => false));
        $table->addForeignKeyConstraint($schema->getTable('mtb_customer_bike_brand'), array('bike1_brand'), array('id'));
        $table->addColumn('bike1_model', 'text', array('NotNull' => false));
        $table->addColumn('bike1_year', 'text', array('NotNull' => false));

        $table->addColumn('bike2_brand', 'smallint', array('NotNull' => false));
        $table->addForeignKeyConstraint($schema->getTable('mtb_customer_bike_brand'), array('bike2_brand'), array('id'));
        $table->addColumn('bike2_model', 'text', array('NotNull' => false));
        $table->addColumn('bike2_year', 'text', array('NotNull' => false));

        $table->addColumn('bike3_brand', 'smallint', array('NotNull' => false));
        $table->addForeignKeyConstraint($schema->getTable('mtb_customer_bike_brand'), array('bike3_brand'), array('id'));
        $table->addColumn('bike3_model', 'text', array('NotNull' => false));
        $table->addColumn('bike3_year', 'text', array('NotNull' => false));

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
