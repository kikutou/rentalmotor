<?php
/*
 * Copyright(c) 2015 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Eccube\Application;

/**
 * 入力フォーム
 */
class ConfigType extends AbstractType
{
    /**
     * Application
     */
    private $app;

    /**
     * 設定情報
     */
    private $info;

    /**
     * コンストラクタ
     *
     * @param  Application  $app  
     * @param  array  $info  設定情報
     */
    public function __construct(Application $app, array $info = null)
    {
        $this->app = $app;
        $this->info = $info;
    }

    /**
     * フォームの生成
     *
     * @param  FormBuilderInterface  $builder  
     * @param  array  $options  
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // 設定情報の初期化
        $this->init();

        $configService = $this->app['eccube.plugin.service.remise_config'];

        // カード支払方法一覧を取得
        $arrDbCardMethod = $this->app['eccube.plugin.remise.repository.remise_card_method']
            ->findAll();
        foreach ($arrDbCardMethod as $key => $value)
        {
            $arrCardMethod[$value->getCode()] = $value->getName();
        }
        // マルチ支払期限一覧を取得
        $arrPaydate = array();
        $arrPaydate[''] = "--";
        for ($i = 2; $i <= 30; $i++)
        {
            $arrPaydate[$i] = $i;
        }

        // フォーム内容の設定
        $builder
            // 基本設定
            ->add('code', 'text', array(
                'label' => '加盟店コード',
                'attr' => array(
                    'class' => 'config_code',
                ),
                'data' => $this->info['code'],
                'constraints' => array(
                    new Assert\NotBlank(array('message' => '※ 加盟店コードが入力されていません。')),
                    new Assert\Length(array('max' => 8, 'maxMessage' => '※ 加盟店コードは8桁の文字列です。')),
                    new Assert\Length(array('min' => 8, 'minMessage' => '※ 加盟店コードは8桁の文字列です。')),
                ),
            ))
            ->add('host_id', 'text', array(
                'label' => 'ホスト番号',
                'attr' => array(
                    'class' => 'config_host_id',
                ),
                'data' => $this->info['host_id'],
                'constraints' => array(
                    new Assert\NotBlank(array('message' => '※ ホスト番号が入力されていません。')),
                    new Assert\Length(array('max' => 8, 'maxMessage' => '※ ホスト番号は8桁の数字です。')),
                    new Assert\Length(array('min' => 8, 'minMessage' => '※ ホスト番号は8桁の数字です。')),
                ),
            ))
            ->add('use_payment', 'choice', array(
                'label' => 'ご利用の決済方法',
                'choices' => array(
                    '1' => 'カード決済',
                    '2' => 'マルチ決済',
                ),
                'multiple' => true,
                'expanded' => true,
                'attr' => array(
                    'class' => 'config_use_payment',
                ),
                'data' => $this->info['use_payment'],
                'constraints' => array(
                    new Assert\NotBlank(array('message' => '※ ご利用の決済方法が選択されていません。')),
                ),
            ))
            // カード決済設定
            ->add('credit_url', 'text', array(
                'label' => '決済情報送信先URL',
                'attr' => array(
                    'class' => 'config_credit_url',
                ),
                'data' => $this->info['credit_url'],
            ))
            ->add('job', 'choice', array(
                'label' => '処理区分',
                'choices' => array(
                    'AUTH' => 'AUTH(仮売上)',
                    'CAPTURE' => 'CAPTURE(売上)',
                ),
                'multiple' => false,
                'expanded' => true,
                'attr' => array(
                    'class' => 'config_job',
                ),
                'data' => $this->info['job'],
            ))
            ->add('payquick', 'choice', array(
                'label' => 'ペイクイック',
                'choices' => array(
                    '1' => '利用する',
                    '0' => '利用しない',
                ),
                'multiple' => false,
                'expanded' => true,
                'attr' => array(
                    'class' => 'config_payquick',
                ),
                'data' => $this->info['payquick'],
            ))
            ->add('use_cardmethod', 'choice', array(
                'label' => '支払方法',
                'choices' => $arrCardMethod,
                'multiple' => true,
                'expanded' => true,
                'attr' => array(
                    'class' => 'config_use_cardmethod',
                ),
                'data' => $this->info['use_cardmethod'],
            ))
            // マルチ決済設定
            ->add('cvs_url', 'text', array(
                'label' => '決済情報送信先URL',
                'attr' => array(
                    'class' => 'config_cvs_url',
                ),
                'data' => $this->info['cvs_url'],
            ))
            ->add('pay_date', 'choice', array(
                'label' => '支払期限',
                'choices' => $arrPaydate,
                'multiple' => false,
                'expanded' => false,
                'attr' => array(
                    'class' => 'config_pay_date',
                ),
                'data' => $this->info['pay_date'],
            ))
            ->add('receiptmail_flg', 'choice', array(
                'label' => '入金お知らせメール',
                'choices' => array(
                    '1' => '利用する',
                    '0' => '利用しない',
                ),
                'multiple' => false,
                'expanded' => true,
                'attr' => array(
                    'class' => 'config_receiptmail_flg',
                ),
                'data' => $this->info['receiptmail_flg'],
            ))

            ->addEventSubscriber(new \Eccube\Event\FormEventSubscriber());
    }

    /**
     * 設定情報の初期化
     */
    protected function init()
    {
        if (empty($this->info)) $this->info = array();

        // 加盟店コード
        if (!isset($this->info['code']))                $this->info['code'] = null;
        // ホスト番号
        if (!isset($this->info['host_id']))             $this->info['host_id'] = null;
        // ご利用の決済方法
        if (!isset($this->info['use_payment']))         $this->info['use_payment'] = array('1');

        // カード情報入力
        if (!isset($this->info['direct']))              $this->info['direct'] = null;
        // カード決済ＵＲＬ
        if (!isset($this->info['credit_url']))          $this->info['credit_url'] = null;
        // 処理区分
        if (!isset($this->info['job']))                 $this->info['job'] = null;
        // ペイクイック
        if (!isset($this->info['payquick']))            $this->info['payquick'] = null;
        // 支払方法
        if (!isset($this->info['use_cardmethod']))      $this->info['use_cardmethod'] = null;

        // マルチ決済ＵＲＬ
        if (!isset($this->info['cvs_url']))             $this->info['cvs_url'] = null;
        // 支払期限
        if (!isset($this->info['pay_date']))            $this->info['pay_date'] = null;
        // 入金お知らせメール
        if (!isset($this->info['receiptmail_flg']))     $this->info['receiptmail_flg'] = null;
    }

    public function getName()
    {
        return 'config';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
       $resolver->setDefaults(array(
            'constraints' => array(
                new Assert\Callback(array($this, 'validate')),
            ),
        ));
    }

    public function validate($data, $context)
    {
        $use_payment_card = false;
        $use_payment_multi = false;
        foreach ($data['use_payment'] as $key => $value)
        {
            if ($value == "1")
            {
                $use_payment_card = true;
            }
            else if ($value == "2")
            {
                $use_payment_multi = true;
            }
        }

        // カード決済設定
        if ($use_payment_card)
        {
            // 決済情報送信先URL
            $context->validateValue(
                $data['credit_url'],
                array(
                    new Assert\NotBlank(array('message' => '※ 決済情報送信先URLが入力されていません。')),
                ),
                '[credit_url]'
            );
            // 処理区分
            $context->validateValue(
                $data['job'],
                array(
                    new Assert\NotBlank(array('message' => '※ 処理区分が選択されていません。')),
                ),
                '[job]'
            );
            // ペイクイック
            $context->validateValue(
                $data['payquick'],
                array(
                    new Assert\NotBlank(array('message' => '※ ペイクイックの利用有無が選択されていません。')),
                ),
                '[payquick]'
            );
            // 支払方法
            if ($data['payquick'] == '1')
            {
                $context->validateValue(
                    count($data['use_cardmethod']),
                    array(
                        new Assert\GreaterThanOrEqual(array('value' => 1, 'message' => '※ 支払方法が選択されておりません。'))
                    ),
                    '[use_cardmethod]'
                );
            }
        }

        // マルチ決済設定
        if ($use_payment_multi)
        {
            // 決済情報送信先URL
            $context->validateValue(
                $data['cvs_url'],
                array(
                    new Assert\NotBlank(array('message' => '※ 決済情報送信先URLが入力されていません。')),
                ),
                '[cvs_url]'
            );
            // 支払期限
            $context->validateValue(
                $data['pay_date'],
                array(
                    new Assert\NotBlank(array('message' => '※ 支払期限が選択されていません。')),
                ),
                '[pay_date]'
            );
            // 入金お知らせメール
            $context->validateValue(
                $data['receiptmail_flg'],
                array(
                    new Assert\NotBlank(array('message' => '※ 入金お知らせメールの利用有無が選択されていません。')),
                ),
                '[receiptmail_flg]'
            );
        }
    }
}
