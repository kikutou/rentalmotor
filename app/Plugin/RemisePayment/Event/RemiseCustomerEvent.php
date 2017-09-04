<?php
/*
 * Copyright(c) 2016 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Event;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * 会員情報用イベント処理
 */
class RemiseCustomerEvent extends RemiseEventBase
{
    /**
     * コンストラクタ
     *
     * @param  Application  $app
     */
    public function __construct($app)
    {
        parent::__construct($app);
    }

    /**
     * ご注文内容のご確認：表示イベント前処理
     *
     * @param  FilterResponseEvent  $event
     */
    public function onRenderMypageChangeBefore(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        try
        {
            // 会員情報
            $Customer = $this->app->user();

            // ペイクイック情報取得
            $CustomerPayquicks = $this->app['eccube.plugin.remise.repository.remise_customer_payquick']
                ->findBy(array('id' => $Customer->getId()));

            // ソース取得
            $html = $response->getContent();
            libxml_use_internal_errors(true);
            $crawler = new Crawler($html);
            $html = $this->getHtml($crawler);
            $html = html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');

            // 「変更する」ボタン部取得
            $Element = $crawler->filter('div.btn_group > p > button.btn')->each(function (Crawler $node, $i) {
                if ($node->text() == "変更する") {
                    return $node;
                }
                return null;
            });
            if (empty($Element)) return;

            $oldHtml = $Element[0]->parents()->first()->parents()->first()->parents()->first()->html();

            // 全体から「変更する」ボタンを特定するための検索用キーワード取得
            $idx = strpos($oldHtml, "変更する") + 12;
            $keyword = substr($oldHtml, 0, $idx);
            // キーワード位置取得
            $idx = strpos($html, $keyword);
            // 全体から挿入位置取得
            $idx = strrpos($html, "<div", -(strlen($html) - $idx));

            // ペイクイック削除フラグ
            $payquickDelete = $this->app['session']->get('eccube.plugin.remise.payquick.delete');
            if (empty($payquickDelete)) $payquickDelete = '0';

            // 追加文
            $form = $this->app['form.factory']->createBuilder()->getForm();
            $twig = $this->app->renderView(
                'RemisePayment/Resource/template/mypage_payquick.twig',
                array(
                    'form' => $form->createView(),
                    'payquicks' => $CustomerPayquicks,
                    'url' => $this->app->url('remise_payquickdel'),
                    'delflg' => $payquickDelete,
                )
            );

            // ペイクイック削除フラグクリア
            $this->app['session']->remove('eccube.plugin.remise.payquick.delete');

            // 追加文挿入
            $html = substr($html, 0, $idx) . $twig . substr($html, $idx);

            $response->setContent($html);
            $event->setResponse($response);
        }
        catch (\Exception $e)
        {
            if (isset($this->app['eccube.logger'])) {
                $this->app['eccube.logger']->error('#### Remise Error ####', array('exception' => $e));
            }
        }
    }
}
