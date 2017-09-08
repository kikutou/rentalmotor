<?php
/*
 * Copyright(c) 2016 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePaymentExtset\Event;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Eccube\Common\Constant;
use Eccube\Event\TemplateEvent;

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
     * 受注情報：一覧フォーム処理
     *
     * @param  TemplateEvent  $event
     */
    public function onRenderAdminOrderIndex(TemplateEvent $event)
    {
        $extsetConfigService = $this->app['eccube.plugin.service.remise_extset_config'];

        // プラグインの稼働確認
        if (!$extsetConfigService->getEnablePlugin()) return;

        $parameters = $event->getParameters();

        // 検索結果がある場合を対象
        if (!isset($parameters['pagination'])) return;
        if (count($parameters['pagination']) == 0) return;

        // ソース取得
        $html = $event->getSource();
        $html = html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');

        // ボタン部に「一括売上」項目を挿入
        $parts = "{% include 'RemisePaymentExtset/Resource/template/admin/order_index_parts_list_buttons.twig' %}";
        $idx = strpos($html, ">CSVダウンロード<") + 23;
        $idx = strpos($html, "</ul>", $idx) + 5;
        $idx = strpos($html, "</li>", $idx) + 5;
        $html = substr($html, 0, $idx) . $parts . substr($html, $idx);

        // ヘッダ部に「カード決済」項目を挿入
        $parts = "{% include 'RemisePaymentExtset/Resource/template/admin/order_index_parts_list_header.twig' %}";
        $idx = strpos($html, ">支払方法</th>") + 18;
        $html = substr($html, 0, $idx) . $parts . substr($html, $idx);

        // データ部に「カード決済」項目を挿入
        $parts = "{% include 'RemisePaymentExtset/Resource/template/admin/order_index_parts_list_body.twig' %}";
        $idx = strpos($html, ">{{ Order.payment_method }}</td>") + 32;
        $html = substr($html, 0, $idx) . $parts . substr($html, $idx);

        $event->setSource($html);

        if ($extsetConfigService->getEnablePointPlugin()) {
            $parameters['pointPlugin'] = "1";
        } else {
            $parameters['pointPlugin'] = "0";
        }

        // 受注未確定のステータス取得
        $RemiseStatus = $this->app['eccube.plugin.remise.repository.remise_order_status']
            ->findOneBy(array('type' => $this->app['config']['remise_order_status_pending']));

        foreach ($parameters['pagination'] as $Order) {
            if (!isset($Order['Payment']) || empty($Order['Payment'])) continue;

            // ステータス　新規受付、入金待ち、取り寄せ中、発送済み、入金済み、受注未確定　のみ対象
            if ($Order->getOrderStatus()->getId() != $this->app['config']['order_new']
             && $Order->getOrderStatus()->getId() != $this->app['config']['order_pay_wait']
             && $Order->getOrderStatus()->getId() != $this->app['config']['order_back_order']
             && $Order->getOrderStatus()->getId() != $this->app['config']['order_deliv']
             && $Order->getOrderStatus()->getId() != $this->app['config']['order_pre_end']
             && $Order->getOrderStatus()->getId() != $RemiseStatus->getId()) {
                continue;
            }

            // 受注のID取得
            $orderId = $Order['id'];
            // 支払方法のID取得
            $paymentId = $Order['Payment']['id'];

            // ルミーズ支払方法取得
            $RemisePaymentMethod = $this->app['eccube.plugin.remise.repository.remise_payment_method']
                ->find($paymentId);
            if (!isset($RemisePaymentMethod) || empty($RemisePaymentMethod)) continue;

            // カード決済のみ
            if ($RemisePaymentMethod->getType() != $this->app['config']['remise_payment_credit']) continue;

            // ルミーズ受注結果情報を取得
            $RemiseResult = $this->app['eccube.plugin.remise.repository.remise_order_result']
                ->findOneBy(array('id' => $orderId));
            if (!isset($RemiseResult) || empty($RemiseResult)) continue;

            $Order->memo06 = $RemiseResult->getMemo06();
        }

        $event->setParameters($parameters);
    }

    /**
     * 受注情報：編集イベント前処理
     *
     * @param  FilterResponseEvent  $event
     */
    public function onRenderAdminOrderEditBefore(FilterResponseEvent $event)
    {
        $extsetConfigService = $this->app['eccube.plugin.service.remise_extset_config'];

        // プラグインの稼働確認
        if (!$extsetConfigService->getEnablePlugin()) return;

        $request = $event->getRequest();
        $response = $event->getResponse();

        $remiseOrderService = $this->app['eccube.plugin.service.remise_order'];

        // 受注IDの取得
        $orderId = $request->attributes->get('id');
        if (empty($orderId)) return;

        // 受注情報を取得
        $Order = $remiseOrderService->getOrderById($orderId);
        if (empty($Order)) return;

        // 受注未確定のステータス取得
        $RemiseStatus = $this->app['eccube.plugin.remise.repository.remise_order_status']
            ->findOneBy(array('type' => $this->app['config']['remise_order_status_pending']));

        // ステータス　新規受付、入金待ち、取り寄せ中、発送済み、入金済み、受注未確定　のみ対象
        if ($Order->getOrderStatus()->getId() != $this->app['config']['order_new']
         && $Order->getOrderStatus()->getId() != $this->app['config']['order_pay_wait']
         && $Order->getOrderStatus()->getId() != $this->app['config']['order_back_order']
         && $Order->getOrderStatus()->getId() != $this->app['config']['order_deliv']
         && $Order->getOrderStatus()->getId() != $this->app['config']['order_pre_end']
         && $Order->getOrderStatus()->getId() != $RemiseStatus->getId()) {
            return;
        }

        // ルミーズ支払方法取得
        $RemisePaymentMethod = $this->app['eccube.plugin.remise.repository.remise_payment_method']
            ->find($Order->getPayment()->getId());

        // ルミーズ支払方法でない場合、処理抜け
        if (!isset($RemisePaymentMethod) || empty($RemisePaymentMethod)) return;

        // カード決済のみ
        if ($RemisePaymentMethod->getType() != $this->app['config']['remise_payment_credit']) return;

        // ルミーズ受注結果情報を取得
        $RemiseResult = $this->app['eccube.plugin.remise.repository.remise_order_result']
            ->findOneBy(array('id' => $orderId));
        if (!isset($RemiseResult) || empty($RemiseResult)) return;

        // 仮売上、売上の状態を対象
        if ($RemiseResult->getMemo06() != $this->app['config']['state_auth']
         && $RemiseResult->getMemo06() != $this->app['config']['state_sales']
         && $RemiseResult->getMemo06() != $this->app['config']['state_capture']) {
            return;
        }

        $memo5 = unserialize($RemiseResult->getMemo05());

        // ソース取得
        $html = $response->getContent();
        libxml_use_internal_errors(true);
        $crawler = new Crawler($html);
        $html = $this->getHtml($crawler);
        $html = html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');

        // 「ルミーズ決済情報」部取得
        $Element = $crawler->filter('div.accordion > div.accpanel > dl > dt')->reduce(function (Crawler $node, $i) {
            if ($node->text() != "ルミーズ決済情報") {
                return false;
            }
        })->first();
        if (count($Element) == 0) {
            // 「お支払方法」部取得
            $Element = $crawler->filter('div.accordion > div.accpanel > dl > dt')->reduce(function (Crawler $node, $i) {
                if ($node->text() != "お支払方法") {
                    return false;
                }
            })->first();
            if (count($Element) == 0) return;

            $oldHtml = $Element->parents()->first()->parents()->first()->html();
        }
        else {
            $oldHtml = $Element->parents()->first()->parents()->first()->html();

            $idx = strrpos($oldHtml, "<dl");
            $oldHtml = substr($oldHtml, $idx);
        }

        // メインプラグイン情報を検索
        $pluginMain = $this->app['eccube.repository.plugin']
            ->findOneBy(array('code' => $this->app["config"]["main_plugin_code"]));
        // メインプラグインが 1.0.4 以下の場合は、CSSを読み込む
        $readCss = 0;
        if (version_compare($pluginMain->getVersion(), '1.0.4', '<=')) {
            $readCss = 1;
        }

        $pointPlugin = 0;
        if ($extsetConfigService->getEnablePointPlugin()) {
            $pointPlugin = 1;
        }

        // 追加文
        $form = $this->app['form.factory']->createBuilder()->getForm();
        $twig = $this->app->renderView(
            'RemisePaymentExtset/Resource/template/admin/order_edit_extset.twig',
            array(
                'form' => $form->createView(),
                'readCss' => $readCss,
                'RemiseResult' => $RemiseResult,
                'Order' => $Order,
                'payment_total' => $memo5['payment_total'],
                'pointPlugin' => $pointPlugin,
            )
        );

        // 追加文挿入
        $html = str_replace($oldHtml, $oldHtml . $twig, $html);

        $response->setContent($html);
        $event->setResponse($response);
    }

    /**
     * 受注情報：コントローラーイベント前処理
     */
    public function onAdminControllerOrderEditIndexBefore($event = null)
    {
        $request = $this->app['request'];
        $session = $request->getSession();

        // POST
        if ('POST' === $request->getMethod()) {
            // 更新時
            if ('register' === $request->get('mode')) {
                // 受注ID
                $orderId = $request->attributes->get('id');
                // 処理区分
                $job = $request->get('remise-extset-job');

                // 処理区分指定時
                if (!empty($job)) {
                    // 拡張セット処理
                    $extsetService = $this->app['eccube.plugin.service.remise_extset'];
                    $result = $extsetService->exec($orderId, $job, $request, true);

                    // 結果判定
                    switch ($result['errcode']) {
                        // 成功
                        case "1":
                            // 金額変更時
                            if ($job == $this->app['config']['job_change']) {
                                $session->set('eccube.plugin.remise.extset.change', '1');
                                // 処理抜けさせる
                                $result['errcode'] = "5";
                            }
                            // それ以外
                            else {
                                $this->app->addSuccess($result['message'], 'admin');
                            }
                            break;
                        // 失敗
                        case "2":
                            $this->app->addError($result['message'], 'admin');
                            $this->app->addError("加盟店バックヤードシステムで該当の注文情報の確認をお願いいたします。", 'admin');
                            break;
                        // 環境不備
                        case "3":
                            // メッセージは設定済み
                            break;
                        // 処理対象外
                        case "4":
                            $this->app->addWarning($result['message'], 'admin');
                            break;
                        // 処理抜け
                        case "5":
                            // 何もしない
                            break;
                    }

                    // 処理抜け以外は、編集画面を再表示
                    if ($result['errcode'] != "5") {
                        if ($event != null && version_compare(Constant::VERSION, '3.0.11', '>=')) {
                            $event->setResponse($this->app->redirect($this->app->url('admin_order_edit', array('id' => $orderId))));
                            return;
                        } else {
                            header("Location: " . $this->app->url('admin_order_edit', array('id' => $orderId)));
                            exit;
                        }
                    }
                }
            }
        }
        else {
            $flg = $session->get('eccube.plugin.remise.extset.change');
            if (isset($flg) && $flg == "1") {
                $session->remove('eccube.plugin.remise.extset.change');

                if (version_compare(Constant::VERSION, '3.0.5', '<=')) {
                    $this->app['session']->getFlashBag()->clear();
                } else {
                    $this->app->clearMessage();
                }
                $message = "金額変更処理が完了しました。";
                $this->app->addSuccess($message, 'admin');
            }
        }
    }
}
