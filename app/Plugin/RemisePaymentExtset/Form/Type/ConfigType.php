<?php
/*
 * Copyright(c) 2016 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePaymentExtset\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
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

        // フォーム内容の設定
        $builder
            ->add('extset_host_id', 'text', array(
                'label' => 'ホスト番号',
                'attr' => array(
                    'class' => 'config_host_id',
                ),
                'data' => $this->info['extset_host_id'],
                'constraints' => array(
                    new Assert\NotBlank(array('message' => '※ ホスト番号が入力されていません。')),
                    new Assert\Length(array('max' => 8, 'maxMessage' => '※ ホスト番号は8桁の数字です。')),
                    new Assert\Length(array('min' => 8, 'minMessage' => '※ ホスト番号は8桁の数字です。')),
                ),
            ))
            ->add('extset_url', 'text', array(
                'label' => '決済情報送信先URL',
                'attr' => array(
                    'class' => 'config_extset_url',
                ),
                'data' => $this->info['extset_url'],
                'constraints' => array(
                    new Assert\NotBlank(array('message' => '※ 決済情報送信先URLが入力されていません。')),
                ),
            ))
            ->addEventSubscriber(new \Eccube\Event\FormEventSubscriber());
    }

    /**
     * 設定情報の初期化
     */
    protected function init()
    {
        if (empty($this->info)) $this->info = array();

        // [拡張セット]ホスト番号
        if (!isset($this->info['extset_host_id']))  $this->info['extset_host_id'] = null;
        // [拡張セット]決済情報送信先ＵＲＬ
        if (!isset($this->info['extset_url']))      $this->info['extset_url'] = null;
    }

    public function getName()
    {
        return 'config';
    }
}
