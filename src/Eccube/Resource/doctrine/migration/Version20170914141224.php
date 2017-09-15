<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Eccube\Entity\Master\CustomerBikeBrand;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170914141224 extends AbstractMigration
{
    const NAME = 'mtb_customer_bike_brand';
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        if (!$schema->hasTable(self::NAME)) {
            return true;
        }

        $app = \Eccube\Application::getInstance();
        $em = $app["orm.em"];

        $brand_list = [
            "HONDA",
            "YAMAHA",
            "SUZUKI",
            "Kawasaki",
            "HARLEY-DAVIDSON",
            "BMW",
            "DUCATI",
            "BUELL",
            "KTM",
            "HUSQVARNA",
            "Vespa",
            "APRILIA",
            "MV AGUSTA",
            "PIAGGIO",
            "MOTO GUZZI",
            "TRIUMPH",
            "MEGELLI",
            "SYM",
            "KYMCO",
            "ROYAL ENFIELD",
            "DERBI",
            "CK FACTORY",
            "ACCESS",
            "APA'X POWER",
            "OVERCREATIVE",
            "SUN MOTORCYCLES",
            "SNAKE MOTORS",
            "DAIHATSU",
            "Terra Motors",
            "DEN-RIN",
            "NOMEL",
            "BRIDGESTONE",
            "Prozza",
            "MEGURO",
            "MORIWAKI",
            "YOSHIMURA",
            "ROADHOPPER",
            "TOKYOMARUI",
            "富士重工",
            "国内メーカーその他",
            "INDIAN MOTORCYCLE",
            "VICTORY",
            "CLEVELAND",
            "PHOENIX",
            "BOSSHOSSボ",
            "RODEO MOTORCYCLE",
            "WestCoastChoppers",
            "TM",
            "VOR",
            "AERMACCHI",
            "ATALA",
            "ADIVA",
            "ITALJET",
            "CAGIVA",
            "GILERA",
            "SEGALE",
            "bimota",
            "FANTIC",
            "BETA",
            "BENELLI",
            "VERTEMATI",
            "MAGNI",
            "MALAGUTI",
            "MOTO MORINI",
            "Mondial",
            "LAVERDA",
            "LAMBRETTA",
            "SWM",
            "MZ",
            "SACHS",
            "BSA",
            "Norton",
            "GASGAS",
            "Bultaco",
            "MONTESA",
            "RIEJU",
            "SHERCO",
            "HUSABERG",
            "MBK",
            "SCORPA",
            "SOLEX",
            "PEUGEOT",
            "MOTO BECANE",
            "AJP",
            "TOMOS",
            "ural",
            "BRP",
            "LML",
            "BAJAJ",
            "aeon motor",
            "CPI",
            "PGO",
            "TGB",
            "Hartford",
            "DAELIM",
            "HYOSUNG",
            "FYM",
            "JONWAY",
            "XINGYU",
            "BSE",
            "Yadea",
            "海外メーカーその他",
            "その他"
        ];

        for($i = 0;$i < count($brand_list) ; $i++) {
            $CustomerBikeBrand = new CustomerBikeBrand();
            $CustomerBikeBrand->setId($i + 1);
            $CustomerBikeBrand->setName($brand_list[$i]);
            $CustomerBikeBrand->setRank($i);
            $em->persist($CustomerBikeBrand);
        }

        $em->flush();

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
