<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Eccube\Entity\Master\QuestionnaireQuestion1;
use Eccube\Entity\Master\QuestionnaireQuestion2;
use Eccube\Entity\Master\QuestionnaireQuestion4;
use Eccube\Entity\Master\QuestionnaireQuestion5;
use Eccube\Entity\Master\QuestionnaireQuestion6;
use Eccube\Entity\Master\QuestionnaireQuestion7;


/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170829202506 extends AbstractMigration
{

    const MASTER_TABLE_NAME1 = 'mtb_questionnaire_question1';
    const MASTER_TABLE_NAME2 = 'mtb_questionnaire_question2';
    // 問題3はデータ形式なので、回答マスタデータはいらない
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

        $app = \Eccube\Application::getInstance();
        $em = $app["orm.em"];

        // this up() migration is auto-generated, please modify it to your needs
        if ($schema->hasTable(self::MASTER_TABLE_NAME1)) {

            $questionType = new QuestionnaireQuestion1();
            $questionType->setId(1);
            $questionType->setName('知人の紹介');
            $questionType->setRank(1);
            $em->persist($questionType);

            $questionType = new QuestionnaireQuestion1();
            $questionType->setId(2);
            $questionType->setName('検索サイト');
            $questionType->setRank(2);
            $em->persist($questionType);

            $questionType = new QuestionnaireQuestion1();
            $questionType->setId(3);
            $questionType->setName('ポスター、ビラ');
            $questionType->setRank(3);
            $em->persist($questionType);

            $questionType = new QuestionnaireQuestion1();
            $questionType->setId(4);
            $questionType->setName('雑誌の広告');
            $questionType->setRank(4);
            $em->persist($questionType);

            $questionType = new QuestionnaireQuestion1();
            $questionType->setId(5);
            $questionType->setName('その他');
            $questionType->setRank(5);
            $em->persist($questionType);

        }

        // this up() migration is auto-generated, please modify it to your needs
        if ($schema->hasTable(self::MASTER_TABLE_NAME2)) {

            $questionType = new QuestionnaireQuestion2();
            $questionType->setId(1);
            $questionType->setName('1台');
            $questionType->setRank(1);
            $em->persist($questionType);

            $questionType = new QuestionnaireQuestion2();
            $questionType->setId(2);
            $questionType->setName('2台');
            $questionType->setRank(2);
            $em->persist($questionType);

            $questionType = new QuestionnaireQuestion2();
            $questionType->setId(3);
            $questionType->setName('3台以上');
            $questionType->setRank(3);
            $em->persist($questionType);

        }

        // this up() migration is auto-generated, please modify it to your needs
        if ($schema->hasTable(self::MASTER_TABLE_NAME4)) {

            $questionType = new QuestionnaireQuestion4();
            $questionType->setId(1);
            $questionType->setName('思う');
            $questionType->setRank(1);
            $em->persist($questionType);

            $questionType = new QuestionnaireQuestion4();
            $questionType->setId(2);
            $questionType->setName('少し思う');
            $questionType->setRank(2);
            $em->persist($questionType);

            $questionType = new QuestionnaireQuestion4();
            $questionType->setId(3);
            $questionType->setName('あまり思わない');
            $questionType->setRank(3);
            $em->persist($questionType);

            $questionType = new QuestionnaireQuestion4();
            $questionType->setId(4);
            $questionType->setName('思わない');
            $questionType->setRank(4);
            $em->persist($questionType);

            $questionType = new QuestionnaireQuestion4();
            $questionType->setId(5);
            $questionType->setName('その他');
            $questionType->setRank(5);
            $em->persist($questionType);

        }

        // this up() migration is auto-generated, please modify it to your needs
        if ($schema->hasTable(self::MASTER_TABLE_NAME5)) {

            $questionType = new QuestionnaireQuestion5();
            $questionType->setId(1);
            $questionType->setName('分かりやすかった');
            $questionType->setRank(1);
            $em->persist($questionType);

            $questionType = new QuestionnaireQuestion5();
            $questionType->setId(2);
            $questionType->setName('普通であった');
            $questionType->setRank(2);
            $em->persist($questionType);

            $questionType = new QuestionnaireQuestion5();
            $questionType->setId(3);
            $questionType->setName('分かりにくかった');
            $questionType->setRank(3);
            $em->persist($questionType);

            $questionType = new QuestionnaireQuestion5();
            $questionType->setId(4);
            $questionType->setName('その他');
            $questionType->setRank(4);
            $em->persist($questionType);

        }

        // this up() migration is auto-generated, please modify it to your needs
        if ($schema->hasTable(self::MASTER_TABLE_NAME6)) {

            $questionType = new QuestionnaireQuestion6();
            $questionType->setId(1);
            $questionType->setName('充分');
            $questionType->setRank(1);
            $em->persist($questionType);

            $questionType = new QuestionnaireQuestion6();
            $questionType->setId(2);
            $questionType->setName('ちょうどいい');
            $questionType->setRank(2);
            $em->persist($questionType);

            $questionType = new QuestionnaireQuestion6();
            $questionType->setId(3);
            $questionType->setName('不充分');
            $questionType->setRank(3);
            $em->persist($questionType);
        }

        // this up() migration is auto-generated, please modify it to your needs
        if ($schema->hasTable(self::MASTER_TABLE_NAME7)) {

            $questionType = new QuestionnaireQuestion7();
            $questionType->setId(1);
            $questionType->setName('やっぱりホンダ！');
            $questionType->setRank(1);
            $em->persist($questionType);

            $questionType = new QuestionnaireQuestion7();
            $questionType->setId(2);
            $questionType->setName('カワサキでしょう！');
            $questionType->setRank(2);
            $em->persist($questionType);

            $questionType = new QuestionnaireQuestion7();
            $questionType->setId(3);
            $questionType->setName('そこはヤマハだね。');
            $questionType->setRank(3);
            $em->persist($questionType);

            $questionType = new QuestionnaireQuestion7();
            $questionType->setId(4);
            $questionType->setName('元気！やる気！スズキ！');
            $questionType->setRank(4);
            $em->persist($questionType);

            $questionType = new QuestionnaireQuestion7();
            $questionType->setId(5);
            $questionType->setName('外車がいいよねー');
            $questionType->setRank(5);
            $em->persist($questionType);
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
