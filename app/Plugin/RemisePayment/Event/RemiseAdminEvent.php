<?php
/*
 * Copyright(c) 2016 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Event;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Eccube\Event\EventArgs;
use Eccube\Util\EntityUtil;

/**
 * 管理者画面用イベント処理
 */
class RemiseAdminEvent extends RemiseEventBase
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
     * トップ：表示イベント前処理
     *
     * @param  FilterResponseEvent  $event
     */
    public function onRenderAdminHomepageBefore(FilterResponseEvent $event)
    {
        $response = $event->getResponse();

        try
        {
            $configService = $this->app['eccube.plugin.service.remise_config'];

            // プラグインの稼働確認
            if (!$configService->getEnablePlugin()) return;

            // ソース取得
            $html = $response->getContent();
            libxml_use_internal_errors(true);
            $crawler = new Crawler($html);

            // 受注状態の取得
            $Element = $crawler->filter('div');
            foreach ($Element as $node)
            {
                if ($node->nodeValue == "受注未確定")
                {
                    $statusANode = $node->parentNode;
                    $statusANode->parentNode->removeChild($statusANode);
                }
            }

            $html = $this->getHtml($crawler);
            $html = html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');
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

    /**
     * 受注情報：編集イベント前処理
     *
     * @param  FilterResponseEvent  $event
     */
    public function onRenderAdminOrderEditBefore(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        try
        {
            $remiseOrderService = $this->app['eccube.plugin.service.remise_order'];

            // 受注IDの取得
            $orderId = $request->attributes->get('id');
            if (empty($orderId)) return;

            // 受注情報を取得
            $Order = $remiseOrderService->getOrderById($orderId);
            if (EntityUtil::isEmpty($Order)) return;

            // mod (s) 2017/04/12
            //// 受注未確定のステータス取得
            //$RemiseStatus = $this->app['eccube.plugin.remise.repository.remise_order_status']
            //    ->findOneBy(array('type' => $this->app['config']['remise_order_status_pending']));
            //
            //// ステータス　新規受付、入金待ち、取り寄せ中、発送済み、入金済み、受注未確定　のみ対象
            //if ($Order->getOrderStatus()->getId() != $this->app['config']['order_new']
            // && $Order->getOrderStatus()->getId() != $this->app['config']['order_pay_wait']
            // && $Order->getOrderStatus()->getId() != $this->app['config']['order_back_order']
            // && $Order->getOrderStatus()->getId() != $this->app['config']['order_deliv']
            // && $Order->getOrderStatus()->getId() != $this->app['config']['order_pre_end']
            // && $Order->getOrderStatus()->getId() != $RemiseStatus->getId()) {
            //    return;
            //}

            // ステータス　決済処理中、キャンセル、購入処理中　を対象外
            if ($Order->getOrderStatus()->getId() == $this->app['config']['order_pending']
             || $Order->getOrderStatus()->getId() == $this->app['config']['order_cancel']
             || $Order->getOrderStatus()->getId() == $this->app['config']['order_processing']) {
                return;
            }
            // mod (e) 2017/04/12

            // ルミーズ支払方法取得
            $RemisePaymentMethod = $this->app['eccube.plugin.remise.repository.remise_payment_method']
                ->find($Order->getPayment()->getId());

            // ルミーズ支払方法でない場合、処理抜け
            if (EntityUtil::isEmpty($RemisePaymentMethod)) return;

            // ルミーズ受注結果情報を取得
            $RemiseResult = $this->app['eccube.plugin.remise.repository.remise_order_result']
                ->findOneBy(array('id' => $orderId));
            if (EntityUtil::isEmpty($RemiseResult)) return;

            // ソース取得
            $html = $response->getContent();
            libxml_use_internal_errors(true);
            $crawler = new Crawler($html);
            $html = $this->getHtml($crawler);
            $html = html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');

            // 「お支払方法」部取得
            // add (s) 2017/04/12
            $Element = $crawler->filter("div#payment_info_box__body")->each(function (Crawler $node, $i) {
                return $node;
            });
            if (!empty($Element)) {
                $oldHtml = $Element[0]->html();
            } else {
            // add (e) 2017/04/12
                $Element = $crawler->filter('div.accordion > div.accpanel > dl > dt')->each(function (Crawler $node, $i) {
                    if ($node->text() == "お支払方法") {
                        return $node;
                    }
                    return null;
                });
                if (empty($Element)) return;

                $oldHtml = $Element[0]->parents()->first()->parents()->first()->html();
            // add (s) 2017/04/12
            }
            // add (e) 2017/04/12

            // 追加情報
            $arrForm = array();
            //   決済種別
            $arrForm['payment_type'] = $RemisePaymentMethod->getType();
            //   カード決済：決済状態
            $arrForm['payment_job'] = $RemiseResult->getMemo06();
            //   カード決済：トランザクションID／マルチ決済：ジョブID
            $arrForm['payment_tranid'] = $RemiseResult->getMemo04();
            //   カード決済：決済処理日
            $arrForm['payment_credit_date'] = $RemiseResult->getCreateDate();
            //   マルチ決済：お支払情報
            $arrMemo02 = $RemiseResult->getMemo02();
            if (!empty($arrMemo02))
            {
                $arrMemo02 = unserialize($arrMemo02);
                foreach ($arrMemo02 as $key => $val)
                {
                    $arrMemo02[$key]["button"] = "0";
                    // URLの場合
                    if (preg_match('/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/', $val["value"]))
                    {
                        $arrMemo02[$key]["button"] = "1";
                    }
                }
            }
            $arrForm['payment_how_info'] = $arrMemo02;
            //   マルチ決済：収納日
            $arrForm['receipt'] = $RemiseResult->getMemo08();

            // 追加文
            $form = $this->app['form.factory']->createBuilder()->getForm();
            $twig = $this->app->renderView(
                'RemisePayment/Resource/template/admin/order_edit_payinfo.twig',
                array(
                    'form' => $form->createView(),
                    'arrForm' => $arrForm,
                    'config' => $this->app['config'],
                    'orderStatus' => $Order->getOrderStatus()->getId(),
                    'orderPayment' => $Order->getPayment()->getId(),
                )
            );

            // 追加文挿入
            $html = str_replace($oldHtml, $oldHtml . $twig, $html);

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

    /**
     * 会員情報：編集イベント前処理
     *
     * @param  FilterResponseEvent  $event
     */
    public function onRenderAdminCustomerEditBefore(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        try
        {
            // 会員IDの取得
            $customerId = $request->attributes->get('id');
            if (empty($customerId)) return;

            // ペイクイック情報取得
            $CustomerPayquicks = $this->app['eccube.plugin.remise.repository.remise_customer_payquick']
                ->findBy(array('id' => $customerId));

            // ペイクイックが存在しない場合、処理抜け
            if (EntityUtil::isEmpty($CustomerPayquicks)) return;

            // ソース取得
            $html = $response->getContent();
            libxml_use_internal_errors(true);
            $crawler = new Crawler($html);
            $html = $this->getHtml($crawler);
            $html = html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');

            // 「購入履歴」部取得
            // add (s) 2017/04/12
            $Element = $crawler->filter("div#history_box")->each(function (Crawler $node, $i) {
                return $node;
            });
            if (!empty($Element)) {
                $oldHtml = $Element[0]->html();
            } else {
            // add (e) 2017/04/12
                $Element = $crawler->filter('div.accordion > div.toggle > h3')->each(function (Crawler $node, $i) {
                    if ($node->text() == "購入履歴") {
                        return $node;
                    }
                    return null;
                });
                if (empty($Element)) return;

                $oldHtml = $Element[1]->parents()->first()->parents()->first()->html();
            // add (s) 2017/04/12
            }
            // add (e) 2017/04/12

            // 全体から「購入履歴」を特定するための検索用キーワード取得
            $idx = strpos($oldHtml, "購入履歴") + 12;
            $keyword = substr($oldHtml, 0, $idx);
            // キーワード位置取得
            $idx = strpos($html, $keyword);
            // 全体から挿入位置取得
            $idx = strrpos($html, "<div", -(strlen($html) - $idx));

            // 追加文
            $form = $this->app['form.factory']->createBuilder()->getForm();
            $twig = $this->app->renderView(
                'RemisePayment/Resource/template/admin/customer_edit_payquick.twig',
                array(
                    'form' => $form->createView(),
                    'payquicks' => $CustomerPayquicks,
                    'url' => $this->app->url('remise_admin_payquickdel', array('id' => $customerId)),
                )
            );

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

    /**
     * 支払方法設定：編集イベント前処理
     *
     * @param  FilterResponseEvent  $event
     */
    public function onRenderAdminSettingShopPaymentEdit(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        try
        {
            // 支払方法IDの取得
            $paymentId = $request->attributes->get('id');
            if (empty($paymentId)) return;

            // ルミーズ支払方法取得
            $RemisePaymentMethod = $this->app['eccube.plugin.remise.repository.remise_payment_method']
                ->find($paymentId);

            // ルミーズ支払方法でない場合、処理抜け
            if (EntityUtil::isEmpty($RemisePaymentMethod)) return;

            // 支払方法がマルチ決済でない場合、処理抜け
            if ($RemisePaymentMethod->getType() != $this->app['config']['remise_payment_multi']) return;

            // ソース取得
            $html = $response->getContent();
            libxml_use_internal_errors(true);
            $crawler = new Crawler($html);
            $html = $this->getHtml($crawler);
            $html = html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');

            // 「登録」ボタン部取得
            $Element = $crawler->filter('div.box-body > div.row > div > button.btn')->each(function (Crawler $node, $i) {
                if ($node->text() == "登録") {
                    return $node;
                }
                return null;
            });
            if (empty($Element)) return;

            $oldHtml = $Element[0]->parents()->first()->parents()->first()->parents()->first()->html();

            // 追加文
            $form = $this->app['form.factory']->createBuilder()->getForm();
            $twig = $this->app->renderView(
                'RemisePayment/Resource/template/admin/setting_payment_edit.twig',
                array(
                    'form' => $form->createView(),
                    'url' => $this->app->url('remise_admin_paymentcopy', array('id' => $paymentId)),
                )
            );

            // 追加文挿入
            $html = str_replace($oldHtml, $oldHtml . $twig, $html);

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

    /**
     * 支払方法設定：編集初期イベント処理
     *
     * @param  EventArgs  $event
     */
    public function onAdminSettingShopPaymentEditInitialize(EventArgs $event)
    {
        try
        {
            // 支払方法
            $Payment = $event->getArgument('Payment');

            // 支払方法ID
            $paymentId = $Payment->getId();
            if (empty($paymentId)) return;

            // ルミーズ支払方法取得
            $RemisePaymentMethod = $this->app['eccube.plugin.remise.repository.remise_payment_method']
                ->find($paymentId);

            // ルミーズ支払方法でない場合、処理抜け
            if (EntityUtil::isEmpty($RemisePaymentMethod)) return;

            // 支払方法がカード決済でない場合、処理抜け
            if ($RemisePaymentMethod->getType() != $this->app['config']['remise_payment_credit']) return;

            // リクエスト情報
            $Request = $this->app['request'];

            // 支払方法
            $paymentRegister = $Request->get('payment_register');
            if (!$paymentRegister) return;

            // 手数料を必須ではなくす
            $builder = $event->getArgument('builder');
            $builder->remove('charge');
        }
        catch (\Exception $e)
        {
            if (isset($this->app['eccube.logger'])) {
                $this->app['eccube.logger']->error('#### Remise Error ####', array('exception' => $e));
            }
        }
    }
}
