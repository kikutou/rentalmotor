<?php
/*
 * Copyright(c) 2015 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Eccube\Application;

/**
 * 入力フォーム
 */
class PayquickType extends AbstractType
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
        // 設定された支払区分を取得
        $RemiseCardMethod = $this->app['eccube.plugin.remise.repository.remise_card_method']->findByUseCodes($this->app,$this->info);
        $methods = array();
        foreach( $RemiseCardMethod as $cardMethod){
            $strCode = $cardMethod->getCode();
            $strName = $cardMethod->getName();
            $methods[$strCode]=$strName;
        }
        // 設定情報から取得
        $configService = $this->app['eccube.plugin.remise.repository.remise_config']->findOneBy(array('code' => "RemisePayment"));
        $info = $configService->getUnserializeInfo();

        // ルミーズのカード情報入力画面から戻った際の値反映
        $payquickCardFlg = $this->app['session']->get('eccube.plugin.remise.back.payquick.card_flg');
        $payquickFlg     = $this->app['session']->get('eccube.plugin.remise.back.payquick.flg');
        $payquickMethod     = $this->app['session']->get('eccube.plugin.remise.back.payquick.method');
        if (!isset($payquickCardFlg)) $payquickCardFlg = '';
        if (!isset($payquickFlg)    ) $payquickFlg     = '1';
        if (!isset($payquickMethod)) $payquickMethod = '';

        // フォーム内容の設定
        $builder
            ->add('payquick_check', 'choice', array(
                'choices' => array(
                    '1' => '登録されているクレジットカードを利用する。',
                    '2' => '新しいクレジットカードを利用する。',
                ),
                'multiple' => false,
                'expanded' => true,
                'constraints' => array(
                ),
                'data' => ($payquickFlg == "1") ? '1' : '2',
                'mapped' => false,
            ))
            ->add('payquick_method', 'choice', array(
                'label' => 'お支払方法',
                'choices' => $methods,
                'multiple' => false,
                'expanded' => true,
                'constraints' => array(
                ),
                'data' => ($payquickMethod) ? $payquickMethod : '',
                'mapped' => false,
            ))
            ->add('card_check', 'choice', array(
                'label' => 'このカードを利用する',
                'choices' => array(
                    '1' => '今回利用するクレジットカードを登録する。',
                ),
                'multiple' => true,
                'expanded' => true,
                'constraints' => array(
                ),
                'data' => ($payquickCardFlg == "1") ? array('0' => '1') : array(),
                'mapped' => false,
            ))
            ->addEventSubscriber(new \Eccube\Event\FormEventSubscriber());
    }

    public function getName()
    {
        return 'remise_payquick';
    }
}
