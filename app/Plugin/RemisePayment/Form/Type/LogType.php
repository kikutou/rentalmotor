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
 * ログフォーム
 */
class LogType extends AbstractType
{
    /**
     * Application
     */
    private $app;

    /**
     * 設定情報
     */
    public $formData;

    /**
     * コンストラクタ
     *
     * @param  Application  $app  
     */
    public function __construct(Application $app, array $formData = array())
    {
        $this->app = $app;
        $this->formData = $formData;
    }

    /**
     * フォームの生成
     *
     * @param  FormBuilderInterface  $builder  
     * @param  array  $options  
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!array_key_exists('card_files',    $this->formData)) $this->formData['card_files'] = array();
        if (!array_key_exists('card_sel_file', $this->formData)) $this->formData['card_sel_file'] = '';
        if (!array_key_exists('card_count',    $this->formData)) $this->formData['card_count'] = 50;

        if (!array_key_exists('multi_files',    $this->formData)) $this->formData['multi_files'] = array();
        if (!array_key_exists('multi_sel_file', $this->formData)) $this->formData['multi_sel_file'] = '';
        if (!array_key_exists('multi_count',    $this->formData)) $this->formData['multi_count'] = 50;

        // フォーム内容の設定
        $builder
            ->add('card_files', 'choice', array(
                'label'       => 'ログファイル',
                'choices'     => $this->formData['card_files'],
                'data'        => $this->formData['card_sel_file'],
                'expanded'    => false,
                'multiple'    => false,
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            ->add('card_count', 'text', array(
                'label'       => '表示行数',
                'data'        => $this->formData['card_count'],
                'constraints' => array(
                    new Assert\Type(array('type' => 'numeric', 'message' => 'form.type.numeric.invalid')),
                    new Assert\NotBlank(),
                ),
            ))
            ->add('multi_files', 'choice', array(
                'label'       => 'ログファイル',
                'choices'     => $this->formData['multi_files'],
                'data'        => $this->formData['multi_sel_file'],
                'expanded'    => false,
                'multiple'    => false,
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            ->add('multi_count', 'text', array(
                'label'       => '表示行数',
                'data'        => $this->formData['multi_count'],
                'constraints' => array(
                    new Assert\Type(array('type' => 'numeric', 'message' => 'form.type.numeric.invalid')),
                    new Assert\NotBlank(),
                ),
            ))
            ->addEventSubscriber(new \Eccube\Event\FormEventSubscriber());
    }

    public function getName()
    {
        return 'log';
    }
}
