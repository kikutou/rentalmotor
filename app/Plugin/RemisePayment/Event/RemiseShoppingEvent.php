<?php
/*
 * Copyright(c) 2016 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Event;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Eccube\Common\Constant;
use Eccube\Util\EntityUtil;

use Plugin\RemisePayment\Common\Confinfo;

/**
 * 購入処理用イベント処理
 */
class RemiseShoppingEvent extends RemiseEventBase
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
    public function onRenderShoppingBefore(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        try
        {
            $configService = $this->app['eccube.plugin.service.remise_config'];
            $remiseOrderService = $this->app['eccube.plugin.service.remise_order'];

            // 受注IDのクリア
            $session = $request->getSession();
            $session->remove('remise_order_id');

            // プラグインの稼働確認
            if (!$configService->getEnablePlugin()) return;

            $Order = $remiseOrderService->getOrder();

            if (EntityUtil::isEmpty($Order)) return;
            if (EntityUtil::isEmpty($Order->getPayment())) return;

            // ルミーズ支払方法取得
            $RemisePaymentMethod = $this->app['eccube.plugin.remise.repository.remise_payment_method']
                ->find($Order->getPayment()->getId());

            // ルミーズ支払方法でない場合、処理抜け
            if (EntityUtil::isEmpty($RemisePaymentMethod)) return;

            // ソース取得
            $html = $response->getContent();
            libxml_use_internal_errors(true);
            $crawler = new Crawler($html);

            // 「注文する」ボタンの取得
            $Element = $crawler->filter('[type=submit]');
            $btnNodes = null;
            foreach ($Element as $node)
            {
                if (strstr($node->attributes->getNamedItem('class')->nodeValue, 'btn-primary'))
                {
                    $btnNodes = $node;
                }
            }

            if (empty($btnNodes)) return;

            // クレジットカード決済
            if ($RemisePaymentMethod->getType() == $this->app['config']['remise_payment_credit'])
            {
                // ボタン名称の変更
                if ($btnNodes != null)
                {
                    $btnNodes->nodeValue = $this->app['config']['remise_payment_credit_btnmsg'];
                }

                // 行程番号の追加
                $crawler = $this->addFlowNumber($crawler, $this->app['config']['remise_payment_credit_flow']);

                // ペイクイック情報表示
                $confinfo = new Confinfo($this->app);
                $blnPayquick = $confinfo->isPayquick();
                if ($blnPayquick)
                {
                    //ペイクイック
                    $crawler = $this->addPayQuickInformation($crawler, $request, $Order);
                }
            }
            // マルチ決済
            else if ($RemisePaymentMethod->getType() == $this->app['config']['remise_payment_multi'])
            {
                // ボタン名称の変更
                if ($btnNodes != null)
                {
                    $btnNodes->nodeValue = $this->app['config']['remise_payment_multi_btnmsg'];
                }

                // 行程番号の追加
                $crawler = $this->addFlowNumber($crawler, $this->app['config']['remise_payment_multi_flow']);
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
     * 行程番号の追加
     *
     * @param  Crawler  $crawler
     * @param  string  $flowName
     * @return  Crawler
     */
    private function addFlowNumber($crawler, $flowName)
    {
        // 行程変更
        $Element = $crawler->filter('.flow_number');
        if (empty($Element)) return $crawler;

        // 「完了」行程の番号を取得
        $completeNode = $Element->getNode(count($Element)-1);
        if (!is_numeric($completeNode->nodeValue)) return $crawler;

        // 「完了」行程ノード（li）
        $completeLiNode = $completeNode->parentNode;
        // 行程ノード（ul）
        $parentUlNode = $completeLiNode->parentNode;
        // 行程全体ノード（div）
        $parentDivNode = $parentUlNode->parentNode;

        // 行程全体ノード（div）のスタイル変更（会員対応）
        if ($parentDivNode->attributes->getNamedItem('class')->nodeValue == "flowline step3")
        {
            // ３工程→４工程
            $parentDivNode->attributes->getNamedItem('class')->nodeValue = 'flowline step4';
        }
        // 行程全体ノード（div）のスタイル変更（非会員対応）
        else if ($parentDivNode->attributes->getNamedItem('class')->nodeValue == "flowline step4")
        {
            // ４工程→５工程
            $parentDivNode->attributes->getNamedItem('class')->nodeValue = 'flowline step5';

            $styleElm = '<style type="text/css">' . "\n"
                      . '.flowline.step5 ul::before {' . "\n"
                      . '    width: 80%;' . "\n"
                      . '    left: 10%;' . "\n"
                      . '}' . "\n"
                      . '.flowline.step5 ul li {' . "\n"
                      . '    width: 20%;' . "\n"
                      . '}' . "\n"
                      . '.flowline.step5 ul {' . "\n"
                      . '    max-width: 650px;' . "\n"
                      . '}' . "\n"
                      . '</style>' . "\n";

            $html = $this->getHtml($crawler);
            $html = html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');
            $html = str_replace('</head>', $styleElm . '</head>', $html);
            $crawler = new Crawler($html);

            // 行程変更
            $Element = $crawler->filter('.flow_number');
            $completeNode = $Element->getNode(count($Element)-1);
            // 「完了」行程ノード（li）
            $completeLiNode = $completeNode->parentNode;
            // 行程ノード（ul）
            $parentUlNode = $completeLiNode->parentNode;
            // 行程全体ノード（div）
            $parentDivNode = $parentUlNode->parentNode;
        }

        // 挿入用ノード（「完了」行程ノードを複製）
        $newNode = $completeLiNode->cloneNode(true);
        $newNode->lastChild->nodeValue = $flowName;
        if ($newNode->attributes->getNamedItem('class') != null) $newNode->attributes->getNamedItem('class')->nodeValue = '';

        // 行程ノード（ul）に行程追加
        $parentUlNode->insertBefore($newNode, $completeLiNode);

        // 「完了」行程の番号を加算
        $completeNode->nodeValue = intval($completeNode->nodeValue) + 1;

        return $crawler;
    }

    /**
     * ペイクイック情報の追加
     *
     * @param  Crawler  $crawler
     * @param  Request  $request
     * @param  Order  $Order
     * @return  Crawler
     */
    private function addPayQuickInformation($crawler, $request, $Order)
    {
        $configService = $this->app['eccube.plugin.service.remise_config'];

        // プラグインの稼働確認
        if (!$configService->getEnablePlugin()) return;

        $form = $this->app['form.factory']
            ->createBuilder('remise_payquick')
            ->getForm();
        $form->handleRequest($request);

        // 会員のペイクイック情報を取得
        $CustomerId = "";
        $CustomerPayquick = array();
        if ($Order->getCustomer() != null) {
            $CustomerId = $Order->getCustomer()->getId();
            $CustomerPayquick = $this->app['eccube.plugin.remise.repository.remise_customer_payquick']
                ->findOneById(array('id' => $CustomerId));
        }

        $twig = $this->app->renderView(
            'RemisePayment/Resource/template/remise_payquick.twig',
            array(
                'form' => $form->createView(),
                'customer_id' => $CustomerId,
                'payquick' => $CustomerPayquick,
            )
        );

        $oldHtml = $crawler
            ->filter('div#confirm_main')
            ->last()
            ->html()
        ;

        // Payquick情報をお支払方法の下に追加する
        $editHtml = explode("<remise_divide>", str_replace("<h2", "<remise_divide><h2", $oldHtml));
        $methodHtml = preg_grep('/お支払方法/', $editHtml);
        reset($methodHtml);
        $in = key($methodHtml) + 1;
        $arrTwig = array();
        $arrTwig[] = $twig;
        array_splice($editHtml, $in, 0, $arrTwig);
        $newHtml = implode("", $editHtml);

        // html出力
        $html = $this->getHtml($crawler);
        $html = html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');
        $html = str_replace($oldHtml, $newHtml, $html);
        $crawler = new Crawler($html);

        return $crawler;
    }

    /**
     * 決済リダイレクト：表示イベント前処理
     *
     * @param  FilterResponseEvent  $event
     */
    public function onRenderShoppingPaymentBefore(FilterResponseEvent $event)
    {
        $response = $event->getResponse();

        // ソース取得
        $html = $response->getContent();
        libxml_use_internal_errors(true);
        $crawler = new Crawler($html);

        $html = $this->getHtml($crawler);
        $html = html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');

        // エンコード変更
        $html = str_replace('<meta charset="utf-8">', '<meta charset="Shift_JIS">', $html);
        $html = mb_convert_encoding($html, 'Shift_JIS', 'UTF-8');

        $response->setContent($html);
        $response->headers->set('Content-Type', 'text/html; charset=Shift_JIS');
        $event->setResponse($response);
    }

    /**
     * ご注文完了：表示イベント前処理
     *
     * @param  FilterResponseEvent  $event
     */
    public function onRenderShoppingCompleteBefore(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        $configService = $this->app['eccube.plugin.service.remise_config'];
        $remiseOrderService = $this->app['eccube.plugin.service.remise_order'];

        // 受注IDの取得
        $session = $request->getSession();
        $orderId = $session->get('remise_order_id');

        if (empty($orderId)) return;

        // プラグインの稼働確認
        if (!$configService->getEnablePlugin()) return;

        $Order = $remiseOrderService->getOrderById($orderId);

        if (EntityUtil::isEmpty($Order)) return;

        // ルミーズ支払方法取得
        $RemisePaymentMethod = $this->app['eccube.plugin.remise.repository.remise_payment_method']
            ->find($Order->getPayment()->getId());

        // ルミーズ支払方法でない場合、処理抜け
        if (EntityUtil::isEmpty($RemisePaymentMethod)) return;

        // ソース取得
        $html = $response->getContent();
        libxml_use_internal_errors(true);
        $crawler = new Crawler($html);

        // クレジットカード決済
        if ($RemisePaymentMethod->getType() == $this->app['config']['remise_payment_credit'])
        {
            // 結果通知用
            $this->app['session']->remove('eccube.plugin.remise.payquick.flg');
            $this->app['session']->remove('eccube.plugin.remise.payquick.id');
            $this->app['session']->remove('eccube.plugin.remise.payquick.card_flg');
            $this->app['session']->remove('eccube.plugin.remise.payquick.method');
            // 戻り用
            $this->app['session']->remove('eccube.plugin.remise.back.payquick.card_flg');
            $this->app['session']->remove('eccube.plugin.remise.back.payquick.flg');
            $this->app['session']->remove('eccube.plugin.remise.back.payquick.method');

            // 行程番号の追加
            $crawler = $this->addFlowNumber($crawler, $this->app['config']['remise_payment_credit_flow']);
        }
        // マルチ決済
        else if ($RemisePaymentMethod->getType() == $this->app['config']['remise_payment_multi'])
        {
            // 行程番号の追加
            $crawler = $this->addFlowNumber($crawler, $this->app['config']['remise_payment_multi_flow']);

            // ルミーズ受注結果情報を取得
            $RemiseResult = $this->app['eccube.plugin.remise.repository.remise_order_result']
                ->findOneBy(array('id' => $Order->getId()));

            // お支払情報
            $arrMemo02 = array();
            if (EntityUtil::isNotEmpty($RemiseResult))
            {
                $arrMemo02 = $RemiseResult->getMemo02();
                if (!empty($arrMemo02))
                {
                    $arrMemo02 = unserialize($arrMemo02);
                    // データを編集
                    foreach ($arrMemo02 as $key => $val)
                    {
                        $arrMemo02[$key]["button"] = "0";
                        // URLの場合にはリンクつきで表示させる
                        if (preg_match('/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/', $val["value"]))
                        {
                            $arrMemo02[$key]["button"] = "1";
                        }
                    }
                }
            }

            // 追加文
            $form = $this->app['form.factory']->createBuilder()->getForm();
            $twig = $this->app->renderView(
                'RemisePayment/Resource/template/shopping_complete_payinfo.twig',
                array(
                    'form' => $form->createView(),
                    'arrOther' => $arrMemo02,
                )
            );

            $html = $this->getHtml($crawler);

            // 挿入位置を特定
            $idx = strpos($html, "complete_message");   // 「ご注文ありがとうございました」
            $idx = strpos($html, "</div>", $idx) + 7;   // トップページ：開始divタグ

            // 追加文挿入
            $html = substr($html, 0, $idx) . $twig . substr($html, $idx);

            $crawler = new Crawler($html);
        }

        $html = $this->getHtml($crawler);
        $html = html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');
        $response->setContent($html);
        $event->setResponse($response);
    }

    /**
     * ご注文内容のご確認のコントローラーイベント前処理
     */
    public function onControllerShoppingConfirmBefore($event = null)
    {
        // POST送信
        if ('POST' !== $this->app['request']->getMethod()) return;

        try
        {
            $configService = $this->app['eccube.plugin.service.remise_config'];
            $logService = $this->app['eccube.plugin.service.remise_log'];
            $remiseOrderService = $this->app['eccube.plugin.service.remise_order'];

            // プラグインの稼働確認
            if (!$configService->getEnablePlugin()) return;

            // カートチェック
            if (!$remiseOrderService->isCartLocked())
            {
                // カートが存在しない、カートがロックされていない時はエラー
                if ($event != null && version_compare(Constant::VERSION, '3.0.11', '>=')) {
                    $event->setResponse($this->app->redirect($this->app->url('cart')));
                    return;
                } else {
                    header("Location: " . $this->app->url('cart'));
                    exit;
                }
            }

            $Order = $remiseOrderService->getOrder();
            $form = $remiseOrderService->getOrderForm($Order);
            $form->handleRequest($this->app['request']);

            // 入力チェック
            if ($form->isValid())
            {
                $formData = $form->getData();

                // ルミーズ支払方法取得
                $RemisePaymentMethod = $this->app['eccube.plugin.remise.repository.remise_payment_method']
                    ->find($Order->getPayment()->getId());

                // ルミーズ支払方法でない場合、処理抜け
                if (EntityUtil::isEmpty($RemisePaymentMethod)) return;

                // 受注情報の受注状態チェック
                if (!$remiseOrderService->checkShoppingStatus($Order))
                {
                    // エラー画面表示用に以下処理を通過
                    if ($event != null && version_compare(Constant::VERSION, '3.0.11', '>=')) {
                        $event->setResponse($this->app->redirect($this->app->url('remise_shopping_payment')));
                        return;
                    } else {
                        header("Location: " . $this->app->url('remise_shopping_payment'));
                        exit;
                    }
                }

                $em = $this->app['orm.em'];
                $em->getConnection()->beginTransaction();
                try
                {
                    // 商品公開ステータスチェック、商品制限数チェック、在庫チェック
                    $check = $remiseOrderService->isOrderProduct($em, $Order);
                    if (!$check)
                    {
                        $this->app->addError('front.shopping.stock.error');
                        if ($event != null && version_compare(Constant::VERSION, '3.0.11', '>=')) {
                            $event->setResponse($this->app->redirect($this->app->url('shopping_error')));
                            return;
                        } else {
                            header("Location: " . $this->app->url('shopping_error'));
                            exit;
                        }
                    }

                    // クレジットカード決済
                    if ($RemisePaymentMethod->getType() == $this->app['config']['remise_payment_credit'])
                    {
                        // 結果通知用
                        $this->app['session']->set('eccube.plugin.remise.payquick.card_flg', '');
                        $this->app['session']->set('eccube.plugin.remise.payquick.flg',      '');
                        $this->app['session']->set('eccube.plugin.remise.payquick.id',       '');
                        $this->app['session']->set('eccube.plugin.remise.payquick.method',   '');
                        // 戻り用
                        $this->app['session']->remove('eccube.plugin.remise.back.payquick.card_flg');
                        $this->app['session']->remove('eccube.plugin.remise.back.payquick.flg');
                        $this->app['session']->remove('eccube.plugin.remise.back.payquick.method');

                        $confinfo = new Confinfo($this->app);
                        $blnPayquick = $confinfo->isPayquick();
                        // ペイクイック利用判定
                        if ($blnPayquick && $Order->getCustomer() != null)
                        {
                            $CustomerId = $Order->getCustomer()->getId();
                            $RemiseCustomerPayquick = $this->app['eccube.plugin.remise.repository.remise_customer_payquick']
                                ->findOneById(array('id' => $CustomerId));

                            $post = $this->app['request']->request->all();

                            // カードを登録する
                            if (isset($post['remise_payquick']['card_check']))
                            {
                                $this->app['session']->set('eccube.plugin.remise.payquick.card_flg', $post['remise_payquick']['card_check'][0]);
                            }

                            // ペイクイック情報確認
                            if (EntityUtil::isNotEmpty($RemiseCustomerPayquick))
                            {
                                // ペイクイックを利用する
                                if ($post['remise_payquick']['payquick_check'] == "1")
                                {
                                    $this->app['session']->set('eccube.plugin.remise.payquick.flg', $post['remise_payquick']['payquick_check']);
                                    $this->app['session']->set('eccube.plugin.remise.payquick.id', $RemiseCustomerPayquick['payquick_id']);
                                }
                                // 支払方法選択
                                if (isset($post['remise_payquick']['payquick_method']))
                                {
                                    $this->app['session']->set('eccube.plugin.remise.payquick.method', $post['remise_payquick']['payquick_method']);
                                }
                            }
                        }
                    }
                    // マルチ決済
                    else if ($RemisePaymentMethod->getType() == $this->app['config']['remise_payment_multi'])
                    {
                        // 何もしない
                    }

                    // 決済処理中への更新処理
                    $remiseOrderService->updateOrderPending($Order, $formData);

                    $em->flush();
                    $em->getConnection()->commit();
                    $em->close();

                    // ログメッセージ出力（デバッグ用）
                    $RemiseLog = $logService->createLogForShopping();
                    $RemiseLog->addMessage('payment_id:' . $formData['payment']->getId());
                    $RemiseLog->addMessage('order_id:' . $Order->getId());
                    $logService->outputRemiseLog($RemiseLog);

                    // $urlの画面表示
                    if ($event != null && version_compare(Constant::VERSION, '3.0.11', '>=')) {
                        $event->setResponse($this->app->redirect($this->app->url('remise_shopping_payment')));
                        return;
                    } else {
                        header("Location: " . $this->app->url('remise_shopping_payment'));
                        exit;
                    }
                }
                catch (\Exception $e)
                {
                    $em->getConnection()->rollback();
                    $em->close();

                    // エラーログ出力
                    $RemiseLog = $logService->createLogForShopping(3);
                    $RemiseLog->addMessage('ErrCode:' . $e->getCode());
                    $RemiseLog->addMessage('ErrMessage:' . $e->getMessage());
                    $RemiseLog->addMessage($e);
                    $logService->outputRemiseLog($RemiseLog);

                    if ($event != null && version_compare(Constant::VERSION, '3.0.11', '>=')) {
                        $event->setResponse($this->app->redirect($this->app->url('shopping_error')));
                        return;
                    } else {
                        header("Location: " . $this->app->url('shopping_error'));
                        exit;
                    }
                }
            }
        }
        catch (\Exception $e)
        {
            if (isset($this->app['eccube.logger'])) {
                $this->app['eccube.logger']->error('#### Remise Error ####', array('exception' => $e));
            }
        }
    }
}
