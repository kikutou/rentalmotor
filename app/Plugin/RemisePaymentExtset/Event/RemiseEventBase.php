<?php
/*
 * Copyright(c) 2016 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePaymentExtset\Event;

use Symfony\Component\DomCrawler\Crawler;

/**
 * ルミーズイベント処理
 */
class RemiseEventBase
{
    /**
     * Event
     */
    const ADMIN_PLUGIN_REMISE_EXTSET_CONFIG_EDIT_INITIALIZE = 'admin.plugin.remise.extset.config.edit.initialize';
    const ADMIN_PLUGIN_REMISE_EXTSET_CONFIG_EDIT_PROGRESS   = 'admin.plugin.remise.extset.config.edit.progress';
    const ADMIN_PLUGIN_REMISE_EXTSET_CONFIG_EDIT_COMPLETE   = 'admin.plugin.remise.extset.config.edit.complete';

    const ADMIN_PLUGIN_REMISE_EXTSET_EXTSET_SALES_INITIALIZE= 'admin.plugin.remise.extset.extset.sales.initialize';
    const ADMIN_PLUGIN_REMISE_EXTSET_EXTSET_SALES_COMPLETE  = 'admin.plugin.remise.extset.extset.sales.complete';
    const ADMIN_PLUGIN_REMISE_EXTSET_EXTSET_SALES_RESULT    = 'admin.plugin.remise.extset.extset.sales.result';

    const ADMIN_PLUGIN_REMISE_EXTSET_RECV_INDEX_INITIALIZE  = 'admin.plugin.remise.extset.recv.index.initialize';
    const ADMIN_PLUGIN_REMISE_EXTSET_RECV_INDEX_COMPLETE    = 'admin.plugin.remise.extset.recv.index.complete';

    const SERVICE_PLUGIN_REMISE_EXTSET_EXEC_POST                 = 'service.plugin.remise.extset.exec.post';
    const SERVICE_PLUGIN_REMISE_EXTSET_UPDATE_RESULT        = 'service.plugin.remise.extset.update.result';

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
