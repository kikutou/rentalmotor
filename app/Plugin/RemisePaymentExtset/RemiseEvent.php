<?php
/*
 * Copyright(c) 2016 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePaymentExtset;

use Plugin\RemisePaymentExtset\Event\RemiseAdminEvent;
use Eccube\Event\TemplateEvent;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

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
     * コンストラクタ
     *
     * @param  Application  $app
     */
    public function __construct($app)
    {
        $this->app = $app;
        $this->adminEvent = new RemiseAdminEvent($app);
    }

    /**
     * [管理者画面]受注情報：一覧フォーム処理
     *
     * @param  TemplateEvent  $event
     */
    public function onRenderAdminOrderIndex(TemplateEvent $event)
    {
        $this->adminEvent->onRenderAdminOrderIndex($event);
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
     * [管理者画面]受注情報：コントローラーイベント前処理
     */
    public function onAdminControllerOrderEditIndexBefore($event = null)
    {
        $this->adminEvent->onAdminControllerOrderEditIndexBefore($event);
    }
}
