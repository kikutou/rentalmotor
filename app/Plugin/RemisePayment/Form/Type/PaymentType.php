<?php
/*
 * Copyright(c) 2015 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Eccube\Application;

/**
 * 決済リダイレクトフォーム
 */
class PaymentType extends AbstractType
{
    /**
     * Application
     */
    private $app;

    /**
     * 受注情報
     */
    private $Order;

    /**
     * コンストラクタ
     *
     * @param  Application  $app  
     * @param  Order  $Order  受注情報
     */
    public function __construct(Application $app, $Order = null)
    {
        $this->app = $app;
        $this->Order = $Order;
    }

    /**
     * フォームの生成
     *
     * @param  FormBuilderInterface  $builder
     * @param  array  $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }

    public function getName()
    {
        return 'remise_payment';
    }
}
