<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Eccube\Entity\Category;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170825130600 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $app = \Eccube\Application::getInstance();
        /** @var EntityManager $em */
        $em = $app["orm.em"];

        foreach ($app['eccube.repository.category']->findBy(array(), array('level' => 'DESC', 'id' => 'DESC')) as $Category) {
            /** @var \Eccube\Entity\Category $Category */
            foreach ($Category->getProductCategories() as $ProductCategory) {
                $em->remove($ProductCategory);
            }
            $em->remove($Category);
        }

        $data = array(
            array(
                'parent_category' => null,
                'creator_id' => 1,
                'category_name' => 'YAMAHA',
                'level' => 1,
            ),
            array(
                'parent_category' => null,
                'creator_id' => 1,
                'category_name' => 'Kawasaki',
                'level' => 1,
            ),
            array(
                'parent_category' => null,
                'creator_id' => 1,
                'category_name' => 'HONDA',
                'level' => 1,
            ),
            array(
                'parent_category' => null,
                'creator_id' => 1,
                'category_name' => 'SUZUKI',
                'level' => 1,
            ),

            array(
                'parent_category' => 0,
                'creator_id' => 1,
                'category_name' => '400cc〜750cc',
                'level' => 2,
            ),
            array(
                'parent_category' => 0,
                'creator_id' => 1,
                'category_name' => '751cc〜1000cc',
                'level' => 2,
            ),
            array(
                'parent_category' => 1,
                'creator_id' => 1,
                'category_name' => '751cc〜1000cc',
                'level' => 2,
            ),
            array(
                'parent_category' => 1,
                'creator_id' => 1,
                'category_name' => '400cc〜750cc',
                'level' => 2,
            ),
            array(
                'parent_category' => 2,
                'creator_id' => 1,
                'category_name' => '400cc〜750cc',
                'level' => 2,
            ),
            array(
                'parent_category' => 2,
                'creator_id' => 1,
                'category_name' => '751cc〜1000cc',
                'level' => 2,
            ),
            array(
                'parent_category' => 3,
                'creator_id' => 1,
                'category_name' => '400cc〜750cc',
                'level' => 2,
            ),
            array(
                'parent_category' => 3,
                'creator_id' => 1,
                'category_name' => '751cc〜1000cc',
                'level' => 2,
            ),
            array(
                'parent_category' => 3,
                'creator_id' => 1,
                'category_name' => '1001cc〜',
                'level' => 2,
            ),

            array(
                'parent_category' => 4,
                'creator_id' => 1,
                'category_name' => 'YZF-R6',
                'level' => 3,
            ),
            array(
                'parent_category' => 5,
                'creator_id' => 1,
                'category_name' => 'YZF-R1',
                'level' => 3,
            ),
            array(
                'parent_category' => 5,
                'creator_id' => 1,
                'category_name' => 'YZF-R1M',
                'level' => 3,
            ),
            array(
                'parent_category' => 6,
                'creator_id' => 1,
                'category_name' => 'ZX-10R',
                'level' => 3,
            ),
            array(
                'parent_category' => 7,
                'creator_id' => 1,
                'category_name' => 'ZX-6R',
                'level' => 3,
            ),
            array(
                'parent_category' => 7,
                'creator_id' => 1,
                'category_name' => 'ZX-6RR',
                'level' => 3,
            ),
            array(
                'parent_category' => 8,
                'creator_id' => 1,
                'category_name' => 'CBR600RR',
                'level' => 3,
            ),
            array(
                'parent_category' => 9,
                'creator_id' => 1,
                'category_name' => 'CBR1000RR',
                'level' => 3,
            ),
            array(
                'parent_category' => 10,
                'creator_id' => 1,
                'category_name' => 'GSX-R600',
                'level' => 3,
            ),
            array(
                'parent_category' => 10,
                'creator_id' => 1,
                'category_name' => 'GSX-R750',
                'level' => 3,
            ),
            array(
                'parent_category' => 11,
                'creator_id' => 1,
                'category_name' => 'GSX-R1000',
                'level' => 3,
            ),
            array(
                'parent_category' => 12,
                'creator_id' => 1,
                'category_name' => 'GSX1300R HAYABUSA',
                'level' => 3,
            ),
            array(
                'parent_category' => 12,
                'creator_id' => 1,
                'category_name' => 'HAYABUSA1300',
                'level' => 3,
            ),

            array(
                'parent_category' => 13,
                'creator_id' => 1,
                'category_name' => '1999〜2000 5EB',
                'level' => 4,
            ),
            array(
                'parent_category' => 13,
                'creator_id' => 1,
                'category_name' => '2001〜2002 5MT',
                'level' => 4,
            ),
            array(
                'parent_category' => 13,
                'creator_id' => 1,
                'category_name' => '2003〜2005 5SL',
                'level' => 4,
            ),
            array(
                'parent_category' => 13,
                'creator_id' => 1,
                'category_name' => '2006〜2007 2C0',
                'level' => 4,
            ),
            array(
                'parent_category' => 13,
                'creator_id' => 1,
                'category_name' => '2008〜2009 13S',
                'level' => 4,
            ),
            array(
                'parent_category' => 13,
                'creator_id' => 1,
                'category_name' => '2010〜2016 13S後期',
                'level' => 4,
            ),
            array(
                'parent_category' => 13,
                'creator_id' => 1,
                'category_name' => '2017〜 BN6',
                'level' => 4,
            ),
            array(
                'parent_category' => 14,
                'creator_id' => 1,
                'category_name' => '1998〜1999 4XV',
                'level' => 4,
            ),
            array(
                'parent_category' => 14,
                'creator_id' => 1,
                'category_name' => '2000〜2003 5JJ / 5PW',
                'level' => 4,
            ),
            array(
                'parent_category' => 14,
                'creator_id' => 1,
                'category_name' => '2004〜2008 5VY 4C8',
                'level' => 4,
            ),
            array(
                'parent_category' => 14,
                'creator_id' => 1,
                'category_name' => '2009〜2014 14B / 1KB / 45B / 2SG',
                'level' => 4,
            ),
            array(
                'parent_category' => 14,
                'creator_id' => 1,
                'category_name' => '2015〜 2CR',
                'level' => 4,
            ),
            array(
                'parent_category' => 15,
                'creator_id' => 1,
                'category_name' => '2015〜 2KS',
                'level' => 4,
            ),
            array(
                'parent_category' => 16,
                'creator_id' => 1,
                'category_name' => '2004〜2005 ZX1000C',
                'level' => 4,
            ),
            array(
                'parent_category' => 16,
                'creator_id' => 1,
                'category_name' => '2006〜2007 ZX1000D',
                'level' => 4,
            ),
            array(
                'parent_category' => 16,
                'creator_id' => 1,
                'category_name' => '2008〜2009 ZX1000E',
                'level' => 4,
            ),
            array(
                'parent_category' => 16,
                'creator_id' => 1,
                'category_name' => '2010 ZX1000F',
                'level' => 4,
            ),
            array(
                'parent_category' => 16,
                'creator_id' => 1,
                'category_name' => '2011〜2015 ZX1000J/K',
                'level' => 4,
            ),
            array(
                'parent_category' => 16,
                'creator_id' => 1,
                'category_name' => '2016〜 ZX1000R/S',
                'level' => 4,
            ),
            array(
                'parent_category' => 17,
                'creator_id' => 1,
                'category_name' => '2003〜2004 ZX636A/B',
                'level' => 4,
            ),
            array(
                'parent_category' => 17,
                'creator_id' => 1,
                'category_name' => '2005〜2006 ZX636C',
                'level' => 4,
            ),
            array(
                'parent_category' => 17,
                'creator_id' => 1,
                'category_name' => '2007〜2008 ZX600P',
                'level' => 4,
            ),
            array(
                'parent_category' => 17,
                'creator_id' => 1,
                'category_name' => '2009〜2012 ZX600R',
                'level' => 4,
            ),
            array(
                'parent_category' => 17,
                'creator_id' => 1,
                'category_name' => '2013〜2017 ZX636E/F',
                'level' => 4,
            ),
            array(
                'parent_category' => 18,
                'creator_id' => 1,
                'category_name' => '2003〜2004 600K',
                'level' => 4,
            ),
            array(
                'parent_category' => 18,
                'creator_id' => 1,
                'category_name' => '2005〜2006 ZX600N',
                'level' => 4,
            ),
            array(
                'parent_category' => 19,
                'creator_id' => 1,
                'category_name' => '2003〜2004 PC37前期',
                'level' => 4,
            ),
            array(
                'parent_category' => 19,
                'creator_id' => 1,
                'category_name' => '2005〜2006 PC37後期',
                'level' => 4,
            ),
            array(
                'parent_category' => 19,
                'creator_id' => 1,
                'category_name' => '2007〜2008 PC40前期',
                'level' => 4,
            ),
            array(
                'parent_category' => 19,
                'creator_id' => 1,
                'category_name' => '2009〜2012 PC40中期',
                'level' => 4,
            ),
            array(
                'parent_category' => 19,
                'creator_id' => 1,
                'category_name' => '2013〜2016 PC40後期',
                'level' => 4,
            ),
            array(
                'parent_category' => 20,
                'creator_id' => 1,
                'category_name' => '2004〜2005 SC57前期',
                'level' => 4,
            ),
            array(
                'parent_category' => 20,
                'creator_id' => 1,
                'category_name' => '2006〜2007 SC57後期',
                'level' => 4,
            ),
            array(
                'parent_category' => 20,
                'creator_id' => 1,
                'category_name' => '2008〜2011 SC59前期',
                'level' => 4,
            ),
            array(
                'parent_category' => 20,
                'creator_id' => 1,
                'category_name' => '2012〜2016 SC59後期',
                'level' => 4,
            ),
            array(
                'parent_category' => 20,
                'creator_id' => 1,
                'category_name' => '2017〜 SC77',
                'level' => 4,
            ),
            array(
                'parent_category' => 21,
                'creator_id' => 1,
                'category_name' => '2001〜2003 K1/K2/K3',
                'level' => 4,
            ),
            array(
                'parent_category' => 21,
                'creator_id' => 1,
                'category_name' => '2004〜2005 K4/K5',
                'level' => 4,
            ),
            array(
                'parent_category' => 21,
                'creator_id' => 1,
                'category_name' => '2006〜2007 K6/K7',
                'level' => 4,
            ),
            array(
                'parent_category' => 21,
                'creator_id' => 1,
                'category_name' => '2008〜2010 K8/K9/L0',
                'level' => 4,
            ),
            array(
                'parent_category' => 21,
                'creator_id' => 1,
                'category_name' => '2011〜 L1〜',
                'level' => 4,
            ),
            array(
                'parent_category' => 22,
                'creator_id' => 1,
                'category_name' => '2004〜2005 K4/K5',
                'level' => 4,
            ),
            array(
                'parent_category' => 22,
                'creator_id' => 1,
                'category_name' => '2006〜2007 K6/K7',
                'level' => 4,
            ),
            array(
                'parent_category' => 22,
                'creator_id' => 1,
                'category_name' => '2008〜2010 K8/K9/L0',
                'level' => 4,
            ),
            array(
                'parent_category' => 22,
                'creator_id' => 1,
                'category_name' => '2011〜 L1〜',
                'level' => 4,
            ),
            array(
                'parent_category' => 23,
                'creator_id' => 1,
                'category_name' => '2001〜2002 K1/K2',
                'level' => 4,
            ),
            array(
                'parent_category' => 23,
                'creator_id' => 1,
                'category_name' => '2003〜2004 K3/K4',
                'level' => 4,
            ),
            array(
                'parent_category' => 23,
                'creator_id' => 1,
                'category_name' => '2005〜2006 K5/K6',
                'level' => 4,
            ),
            array(
                'parent_category' => 23,
                'creator_id' => 1,
                'category_name' => '2007〜2008 K7/K8',
                'level' => 4,
            ),
            array(
                'parent_category' => 23,
                'creator_id' => 1,
                'category_name' => '2009〜2011 K9/L0/L1',
                'level' => 4,
            ),
            array(
                'parent_category' => 23,
                'creator_id' => 1,
                'category_name' => '2012〜2016 L2/L3/L4/L5/L6',
                'level' => 4,
            ),
            array(
                'parent_category' => 24,
                'creator_id' => 1,
                'category_name' => '1999 X',
                'level' => 4,
            ),
            array(
                'parent_category' => 24,
                'creator_id' => 1,
                'category_name' => '2000〜2002 Y/K1/K2',
                'level' => 4,
            ),
            array(
                'parent_category' => 24,
                'creator_id' => 1,
                'category_name' => '2003〜2005 K3/K4/K5',
                'level' => 4,
            ),
            array(
                'parent_category' => 24,
                'creator_id' => 1,
                'category_name' => '2006〜2007 K6/K7',
                'level' => 4,
            ),
            array(
                'parent_category' => 25,
                'creator_id' => 1,
                'category_name' => '2008〜 K8〜',
                'level' => 4,
            )
        );

        $rank = 1;
        $last_level = 0;
        foreach ($data as &$d) {
            if ($d['level'] === $last_level) {
                $rank++;
            } else {
                $rank = 1;
                $last_level = $d['level'];
            }

            $Category = new Category();

            $Parent = is_null($d['parent_category']) ? null : $data[$d['parent_category']]['entity'];
            $Creator = $app['eccube.repository.member']->find($d['creator_id']);

            $Category
                ->setParent($Parent)
                ->setCreator($Creator)
                ->setName($d['category_name'])
                ->setLevel($d['level'])
                ->setRank($rank)
                ->setDelFlg(false);
            $em->persist($Category);

            $d['entity'] = $Category;
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
