<?php
/*
 * Copyright(c) 2016 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Event;

use Symfony\Component\DomCrawler\Crawler;

/**
 * ルミーズイベント処理
 */
class RemiseEventBase
{
    /**
     * Event
     */
    const ADMIN_PLUGIN_REMISE_CONFIG_EDIT_INITIALIZE        = 'admin.plugin.remise.config.edit.initialize';
    const ADMIN_PLUGIN_REMISE_CONFIG_EDIT_PROGRESS          = 'admin.plugin.remise.config.edit.progress';
    const ADMIN_PLUGIN_REMISE_CONFIG_EDIT_COMPLETE          = 'admin.plugin.remise.config.edit.complete';

    const ADMIN_PLUGIN_REMISE_PAYQUICK_DELETE_INITIALIZE    = 'admin.plugin.remise.payquick.delete.initialize';
    const ADMIN_PLUGIN_REMISE_PAYQUICK_DELETE_COMPLETE      = 'admin.plugin.remise.payquick.delete.complete';

    const FRONT_PLUGIN_REMISE_RECV_INDEX_INITIALIZE         = 'front.plugin.remise.recv.index.initialize';
    const FRONT_PLUGIN_REMISE_RECV_INDEX_COMPLETE           = 'front.plugin.remise.recv.index.complete';
    const FRONT_PLUGIN_REMISE_ACPT_INDEX_INITIALIZE         = 'front.plugin.remise.acpt.index.initialize';
    const FRONT_PLUGIN_REMISE_ACPT_INDEX_COMPLETE           = 'front.plugin.remise.acpt.index.complete';

    const FRONT_PLUGIN_REMISE_PAYQUICK_DELETE_INITIALIZE    = 'front.plugin.remise.payquick.delete.initialize';
    const FRONT_PLUGIN_REMISE_PAYQUICK_DELETE_COMPLETE      = 'front.plugin.remise.payquick.delete.complete';

    const FRONT_PLUGIN_REMISE_PAYMENT_INDEX_INITIALIZE      = 'front.plugin.remise.payment.index.initialize';
    const FRONT_PLUGIN_REMISE_PAYMENT_INDEX_CREATE_CARD     = 'front.plugin.remise.payment.index.create.card';
    const FRONT_PLUGIN_REMISE_PAYMENT_INDEX_CREATE_MULTI    = 'front.plugin.remise.payment.index.create.multi';
    const FRONT_PLUGIN_REMISE_PAYMENT_BACK_INITIALIZE       = 'front.plugin.remise.payment.back.initialize';
    const FRONT_PLUGIN_REMISE_PAYMENT_BACK_COMPLETE         = 'front.plugin.remise.payment.back.complete';
    const FRONT_PLUGIN_REMISE_PAYMENT_COMPLETE_INITIALIZE   = 'front.plugin.remise.payment.complete.initialize';
    const FRONT_PLUGIN_REMISE_PAYMENT_COMPLETE_PROGRESS     = 'front.plugin.remise.payment.complete.progress';
    const FRONT_PLUGIN_REMISE_PAYMENT_COMPLETE_COMPLETE     = 'front.plugin.remise.payment.complete.complete';

    const SERVICE_PLUGIN_REMISE_REGIST_RESULT               = 'service.plugin.remise.regist.result';
    const SERVICE_PLUGIN_REMISE_UPDATE_RESULT               = 'service.plugin.remise.update.result';
    const SERVICE_PLUGIN_REMISE_UPDATE_PAYQUICK             = 'service.plugin.remise.update.payquick';

    const MAIL_PLUGIN_REMISE_RECEIPT                        = 'mail.plugin.remise.receipt';
    const MAIL_PLUGIN_REMISE_ORDER                          = 'mail.plugin.remise.order';

    /**
     * Application
     */
    protected $app;

    /**
     * コンストラクタ
     *
     * @param  Application  $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * HTMLの出力
     *
     * @param  Crawler  $crawler
     *
     * @return  HTML
     */
    public function getHtml(Crawler $crawler)
    {
        $html = '';
        foreach ($crawler as $Element) {
            $Element->ownerDocument->formatOutput = true;
            $html .= $Element->ownerDocument->saveHTML();
        }
        return $html;
    }
}
