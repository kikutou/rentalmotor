<?php
/*
 * Copyright(c) 2015 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Eccube\Event\EventArgs;

use Plugin\RemisePayment\Event\RemiseAdminEvent;
use Plugin\RemisePayment\Event\RemiseCustomerEvent;
use Plugin\RemisePayment\Event\RemiseShoppingEvent;

/**
 * イベント処理
 */
class RemiseEvent
{
    /**
     * Application
     */
    private $app;

    /**
     * RemiseAdminEvent
     */
    private $adminEvent;

    /**
     * RemiseShoppingEvent
     */
    private $shoppingEvent;

    /**
     * RemiseCustomerEvent
     */
    private $customerEvent;

    /**
     * コンストラクタ
     *
     * @param  Application  $app
     */
    public function __construct($app)
    {
        $this->app = $app;
        $this->adminEvent = new RemiseAdminEvent($app);
        $this->shoppingEvent = new RemiseShoppingEvent($app);
        $this->customerEvent = new RemiseCustomerEvent($app);
    }

    /**
     * [管理者画面]トップ：表示イベント前処理
     *
     * @param  FilterResponseEvent  $event
     */
    public function onRenderAdminHomepageBefore(FilterResponseEvent $event)
    {
        $this->adminEvent->onRenderAdminHomepageBefore($event);
    }

    /**
     * [管理者画面]受注情報：編集イベント前処理
     *
     * @param  FilterResponseEvent  $event
     */
    public function onRenderAdminOrderEditBefore(FilterResponseEvent $event)
    {
        $this->adminEvent->onRenderAdminOrderEditBefore($event);
    }

    /**
     * [管理者画面]会員情報：編集イベント前処理
     *
     * @param  FilterResponseEvent  $event
     */
    public function onRenderAdminCustomerEditBefore(FilterResponseEvent $event)
    {
        $this->adminEvent->onRenderAdminCustomerEditBefore($event);
    }

    /**
     * [管理者画面]支払方法設定：編集イベント前処理
     *
     * @param  FilterResponseEvent  $event
     */
    public function onRenderAdminSettingShopPaymentEdit(FilterResponseEvent $event)
    {
        $this->adminEvent->onRenderAdminSettingShopPaymentEdit($event);
    }

    /**
     * [管理者画面]支払方法設定：編集初期イベント処理
     *
     * @param  EventArgs  $event
     */
    public function onAdminSettingShopPaymentEditInitialize(EventArgs $event)
    {
        $this->adminEvent->onAdminSettingShopPaymentEditInitialize($event);
    }

    /**
     * ご注文内容のご確認：表示イベント前処理
     *
     * @param  FilterResponseEvent  $event
     */
    public function onRenderShoppingBefore(FilterResponseEvent $event)
    {
        $this->shoppingEvent->onRenderShoppingBefore($event);
    }

    /**
     * 決済リダイレクト：表示イベント前処理
     *
     * @param  FilterResponseEvent  $event
     */
    public function onRenderShoppingPaymentBefore(FilterResponseEvent $event)
    {
        $this->shoppingEvent->onRenderShoppingPaymentBefore($event);
    }

    /**
     * ご注文完了：表示イベント前処理
     *
     * @param  FilterResponseEvent  $event
     */
    public function onRenderShoppingCompleteBefore(FilterResponseEvent $event)
    {
        $this->shoppingEvent->onRenderShoppingCompleteBefore($event);
    }

    /**
     * ご注文内容のご確認のコントローラーイベント前処理
     */
    public function onControllerShoppingConfirmBefore($event = null)
    {
        $this->shoppingEvent->onControllerShoppingConfirmBefore($event);
    }

    /**
     * マイページ－会員情報編集：表示イベント前処理
     *
     * @param  FilterResponseEvent  $event
     */
    public function onRenderMypageChangeBefore(FilterResponseEvent $event)
    {
        $this->customerEvent->onRenderMypageChangeBefore($event);
    }
}
