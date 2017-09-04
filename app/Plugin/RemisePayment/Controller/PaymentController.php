<?php
/*
 * Copyright(c) 2015 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Controller;

use Symfony\Component\HttpFoundation\Request;
use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Event\EventArgs;

use Plugin\RemisePayment\Common\Errinfo;
use Plugin\RemisePayment\Event\RemiseEventBase;
use Plugin\RemisePayment\Form\Type\PaymentType;

/**
 * 決済リダイレクト画面制御
 */
class PaymentController
{
    /**
     * Application
     */
    public $app;

    /**
     * config情報
     */
    public $pluginConfig;

    /**
     * 決済リダイレクト
     */
    public function index(Application $app, Request $request)
    {
        $this->app = $app;
        // config取得
        $configService = $this->app['eccube.plugin.service.remise_config'];
        $this->pluginConfig = $configService->getPluginConfig();

        $remiseOrderService = $app['eccube.plugin.service.remise_order'];

        // リクエスト情報
        $requestData = $request->request->all();

        $checkMode = 0;
        // ルミーズからの返信があった場合
        if (isset($requestData['X-R_CODE']))
        {
            // 注文情報取得
            $Order = $remiseOrderService->getOrderById($requestData['X-S_TORIHIKI_NO']);
            $checkMode = 1;
        }
        // ルミーズの呼び出し時
        else
        {
            // 注文情報取得
            $Order = $remiseOrderService->getOrderByPreOrderId();
        }

        // 受注情報の受注状態チェック
        if (!$remiseOrderService->checkShoppingStatus($Order, $checkMode))
        {
            return $this->app['view']->render('error.twig', array(
                'error_title'   => '注文エラー',
                'error_message' => 'こちらのご注文は、処理が完了しているか、キャンセルされています。ご注文履歴をご確認ください。',
            ));
        }

        // ルミーズからの返信があった場合
        if (isset($requestData['X-R_CODE']))
        {
            return $this->completeProcess($Order, $request);
        }
        // ルミーズの呼び出し時
        else
        {
            return $this->redirectProcess($Order, $request);
        }
    }

    /**
     * 決済リダイレクト
     *
     * @param  Order  $Order  受注情報
     * @param  array  $request  リクエスト情報
     */
    protected function redirectProcess($Order, Request $request)
    {
        $logService = $this->app['eccube.plugin.service.remise_log'];

        // ルミーズ支払方法取得
        $RemisePaymentMethod = $this->app['eccube.plugin.remise.repository.remise_payment_method']
            ->find($Order->getPayment()->getId());

        // 支払種別
        $paymentType = $RemisePaymentMethod->getType();

        // ルミーズプラグイン設定情報の取得
        $RemiseConfig = $this->app['eccube.plugin.remise.repository.remise_config']
            ->findOneBy(array('code' => $this->pluginConfig['code']));

        // 設定情報取得
        $info = $RemiseConfig->getUnserializeInfo();

        // カード決済
        if ($paymentType == $this->app['config']['remise_payment_credit'])
        {
            if (empty($info) || empty($info["credit_url"]))
            {
                return $this->app['view']->render('error.twig', array(
                    'error_title'   => '設定不備エラー',
                    'error_message' => 'クレジットカード決済の設定が不正です。管理者に連絡してください。',
                ));
            }

            // カード決済用送信データの取得
            $arrSendData = $this->getCardArrSendData($Order, $info);
            // 送信URL
            $send_url = $info["credit_url"];
        }
        // マルチ決済
        else if ($paymentType == $this->app['config']['remise_payment_multi'])
        {
            if (empty($info) || empty($info["cvs_url"]))
            {
                return $this->app['view']->render('error.twig', array(
                    'error_title'   => '設定不備エラー',
                    'error_message' => 'コンビニ・電子マネー・銀行決済の設定が不正です。管理者に連絡してください。',
                ));
            }

            // マルチ決済用送信データの取得
            $arrSendData = $this->getMultiArrSendData($Order, $info);
            // 送信URL
            $send_url = $info["cvs_url"];
        }

        // 入力フォーム
        $type = new PaymentType($this->app, $Order);
        $builder = $this->app['form.factory']->createBuilder($type);

        if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
            // イベント生成
            $event = new EventArgs(
                array(
                    'builder' => $builder,
                    'Order' => $Order,
                    'RemisePaymentMethod' => $RemisePaymentMethod,
                    'RemiseConfig' => $RemiseConfig,
                ),
                $request
            );
            $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::FRONT_PLUGIN_REMISE_PAYMENT_INDEX_INITIALIZE, $event);
        }

        $form = $builder->getForm();

        // ログメッセージ出力（デバッグ用）
        $RemiseLog = $logService->createLogForRedirect($paymentType, $arrSendData);
        $logService->outputRemiseLog($RemiseLog);
        // ログメッセージ出力
        $RemiseLog = $logService->createLogForRedirect($paymentType, $arrSendData, 1);
        $logService->outputRemiseLog($RemiseLog);

        // 画面返却
        return $this->app['view']->render('RemisePayment/Resource/template/remise_redirect.twig', array(
            'form'          => $form->createView(),
            'send_url'      => $send_url,
            'arrSendData'   => $arrSendData,
            'tpl_onload'    => 'fnCheckSubmit();',
        ));
    }

    /**
     * カード決済用送信データの取得
     *
     * @param  Order  $Order  受注情報
     * @param  array  $info  設定情報
     */
    private function getCardArrSendData($Order, $info)
    {
        if (!isset($info["payquick"]) || empty($info["payquick"])) $info["payquick"] = '';

        // リクエスト情報
        $payquickFlg = $this->app['session']->get('eccube.plugin.remise.payquick.flg');
        $payquickCardFlg = $this->app['session']->get('eccube.plugin.remise.payquick.card_flg');
        $payquickId = $this->app['session']->get('eccube.plugin.remise.payquick.id');
        $payquickMethod = $this->app['session']->get('eccube.plugin.remise.payquick.method');
        if (empty($payquickFlg)) $payquickFlg = '';
        if (empty($payquickCardFlg)) $payquickCardFlg = '';
        if (empty($payquickId)) $payquickId = '';
        if (empty($payquickMethod)) $payquickMethod = '';

        $opt = '';
        if ($Order->getCustomer() != null)
        {
            $opt = $Order->getCustomer()->getId() . ',';
        }

        // 基本情報部
        $pluginVerLabel = strtoupper($this->pluginConfig['code']) . '_PLG_VER';
        $arrSendData = array(
            'ECCUBE_VER'    => Constant::VERSION,               // EC-CUBEバージョン番号
            $pluginVerLabel => $this->pluginConfig['version'],  // ルミーズ決済プラグインバージョン番号
            'S_TORIHIKI_NO' => $Order->getId(),                 // 請求番号
            'MAIL'          => $Order->getEmail(),              // e-mail
            'AMOUNT'        => $Order->getPaymentTotal(),       // 金額
            'TAX'           => '0',                             // 税送料
            'TOTAL'         => $Order->getPaymentTotal(),       // 合計金額
            'SHOPCO'        => $info['code'],                   // 加盟店コード
            'HOSTID'        => $info['host_id'],                // ホストID
            'JOB'           => $info['job'],                    // 処理区分
            'ITEM'          => '0000120',                       // 商品コード(ルミーズ固定)
            'REMARKS3'      => 'A0000155',                      // 代理店コード
            'OPT'           => $opt,                            // オプション
        );

        // 設定情報部
        $arrSendData['RETURL']      = $this->app->url('remise_shopping_payment');       // 完了通知URL
        $arrSendData['NG_RETURL']   = $this->app->url('remise_shopping_payment');       // NG完了通知URL
        $arrSendData['EXITURL']     = $this->app->url('remise_shopping_payment_back');  // 中止URL

        // ペイクイック
        $arrSendData['PAYQUICK']    = '';   // ペイクイック機能
        $arrSendData['PAYQUICKID']  = '';   // ペイクイックID

        // ダイレクトモード
        $arrSendData['DIRECT']  = 'OFF';    // ダイレクトモード

        $arrSendData['CARD']    = '';   // カード番号、セキュリティコード
        $arrSendData['EXPIRE']  = '';   // 有効期限
        $arrSendData['NAME']    = '';   // 名義人
        $arrSendData['FORWARD'] = '';   // 仕向先コード
        $arrSendData['METHOD']  = '';   // 支払区分
        $arrSendData['PTIMES']  = '';   // 分割回数
        $arrSendData['BTIMES']  = '';   // ボーナス回数
        $arrSendData['BAMOUNT'] = '';   // ボーナス金額

        // ペイクイック
        if ($payquickFlg == "1" || $payquickCardFlg == "1")
        {
            $arrSendData['PAYQUICK'] = $info["payquick"];   // ペイクイック機能
            if (!empty($payquickId))
            {
                $arrSendData['PAYQUICKID'] = $payquickId;   // ペイクイックID
            }
        }
        if (!empty($payquickMethod))
        {
            $arrSendData['METHOD']  = $payquickMethod;   // 支払区分
        }

        // add (s) 2017/04/12
        if ($Order->getCustomer() != null)
        {
            $arrSendData['REMARKS5'] = $Order->getCustomer()->getId();
        }
        // add (e) 2017/04/12

        if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
            // イベント生成
            $event = new EventArgs(
                array(
                    'arrSendData' => $arrSendData,
                )
            );
            $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::FRONT_PLUGIN_REMISE_PAYMENT_INDEX_CREATE_CARD, $event);
        }

        return $arrSendData;
    }

    /**
     * マルチ決済用送信データの取得
     *
     * @param  Order  $Order  受注情報
     * @param  array  $info  設定情報
     */
    private function getMultiArrSendData($Order, $info)
    {
        // 名前整形
        $name1 = mb_convert_kana($Order->getName01(), "ASKHV", "UTF-8");
        $name2 = mb_convert_kana($Order->getName02(), "ASKHV", "UTF-8");
        // 住所整形
        $add1 = mb_convert_kana($Order->getPref()->getName(), "ASKHV", "UTF-8");
        $add2 = mb_convert_kana($Order->getAddr01(), "ASKHV", "UTF-8");
        $add3 = mb_convert_kana($Order->getAddr02(), "ASKHV", "UTF-8");
        // 電話番号整形
        $tel = $Order->getTel01() . $Order->getTel02() . $Order->getTel03();
        // 支払期限
        $payDate = date("Ymd", strtotime("+" . $info['pay_date'] . "day"));

        // 基本情報部
        $pluginVerLabel = strtoupper($this->pluginConfig['code']) . '_PLG_VER';
        $arrSendData = array(
            'ECCUBE_VER'    => Constant::VERSION,               // EC-CUBEバージョン番号
            $pluginVerLabel => $this->pluginConfig['version'],  // ルミーズ決済プラグインバージョン番号
            'SHOPCO'        => $info['code'],                   // 加盟店コード
            'HOSTID'        => $info['host_id'],                // ホストID
            'S_TORIHIKI_NO' => $Order->getId(),                 // 請求番号
            'NAME1'         => $name1,                          // 顧客名1
            'NAME2'         => $name2,                          // 顧客名2
            'KANA1'         => '',                              // 顧客名カナ1
            'KANA2'         => '',                              // 顧客名カナ2
            'YUBIN1'        => $Order->getZip01(),              // 郵便番号1
            'YUBIN2'        => $Order->getZip02(),              // 郵便番号2
            'ADD1'          => $add1,                           // 住所1
            'ADD2'          => $add2,                           // 住所2
            'ADD3'          => $add3,                           // 住所3
            "TEL"           => $tel,                            // 電話番号
            'MAIL'          => $Order->getEmail(),              // e-mail
            'TOTAL'         => $Order->getPaymentTotal(),       // 合計金額
            'TAX'           => '0',                             // 外税分消費税
            'S_PAYDATE'     => $payDate,                        // 支払期限
            'MNAME_01'      => '商品代金',                      // 明細品名1（最大7個のため、商品代金として全体で出力する）
            'MSUM_01'       => $Order->getPaymentTotal(),       // 明細金額1
            'REMARKS3'      => 'A0000155',                      // 代理店コード
        );

        // 設定情報部
        $arrSendData['RETURL']      = $this->app->url('remise_shopping_payment');       // 完了通知URL
        $arrSendData['NG_RETURL']   = $this->app->url('remise_shopping_payment');       // NG完了通知URL
        $arrSendData['EXITURL']     = $this->app->url('remise_shopping_payment_back');  // 中止URL

        if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
            // イベント生成
            $event = new EventArgs(
                array(
                    'arrSendData' => $arrSendData,
                )
            );
            $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::FRONT_PLUGIN_REMISE_PAYMENT_INDEX_CREATE_MULTI, $event);
        }

        return $arrSendData;
    }

    /**
     * 決済画面からの戻り
     */
    public function back(Application $app)
    {
        $this->app = $app;

        $remiseOrderService = $this->app['eccube.plugin.service.remise_order'];

        $Order = $remiseOrderService->getOrderByPreOrderId();

        // 受注情報の受注状態チェック
        if (!$remiseOrderService->checkShoppingStatus($Order))
        {
            return $app['view']->render('error.twig', array(
                'error_title'   => '注文エラー',
                'error_message' => 'こちらのご注文は、処理が完了しているか、キャンセルされています。ご注文履歴をご確認ください。',
            ));
        }

        // ルミーズ支払方法取得
        $RemisePaymentMethod = $this->app['eccube.plugin.remise.repository.remise_payment_method']
            ->find($Order->getPayment()->getId());

        if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
            // イベント生成
            $event = new EventArgs(
                array(
                    'Order' => $Order,
                    'RemisePaymentMethod' => $RemisePaymentMethod,
                )
            );
            $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::FRONT_PLUGIN_REMISE_PAYMENT_BACK_INITIALIZE, $event);
        }

        // 購入処理中への更新処理
        $remiseOrderService->updateOrderProcessing($Order);

        // カード決済
        if ($RemisePaymentMethod->getType() == $this->app['config']['remise_payment_credit'])
        {
            // ルミーズのカード情報入力画面から戻った際の値反映
            $payquickCardFlg = $this->app['session']->get('eccube.plugin.remise.payquick.card_flg');
            $payquickFlg     = $this->app['session']->get('eccube.plugin.remise.payquick.flg');
            $payquickMethod  = $this->app['session']->get('eccube.plugin.remise.payquick.method');
            if (empty($payquickCardFlg)) $payquickCardFlg = '0';
            if (empty($payquickFlg)    ) $payquickFlg     = '0';
            if (empty($payquickMethod) ) $payquickMethod  = '0';
            $this->app['session']->set('eccube.plugin.remise.back.payquick.card_flg', $payquickCardFlg);
            $this->app['session']->set('eccube.plugin.remise.back.payquick.flg',      $payquickFlg    );
            $this->app['session']->set('eccube.plugin.remise.back.payquick.method',   $payquickMethod );

            // 結果通知用クリア
            $this->app['session']->remove('eccube.plugin.remise.payquick.flg');
            $this->app['session']->remove('eccube.plugin.remise.payquick.id');
            $this->app['session']->remove('eccube.plugin.remise.payquick.card_flg');
            $this->app['session']->remove('eccube.plugin.remise.payquick.method');
        }

        if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
            // イベント生成
            $event = new EventArgs(
                array(
                    'Order' => $Order,
                    'RemisePaymentMethod' => $RemisePaymentMethod,
                )
            );
            $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::FRONT_PLUGIN_REMISE_PAYMENT_BACK_COMPLETE, $event);
        }

        return $this->app->redirect($app->url('shopping'));
    }

    /**
     * 決済完了通知
     *
     * @param  Order  $Order  受注情報
     * @param  array  $request  リクエスト情報
     */
    protected function completeProcess($Order, Request $request)
    {
        $session = $request->getSession();

        $logService = $this->app['eccube.plugin.service.remise_log'];

        // リクエスト情報
        $requestData = $request->request->all();

        // ルミーズ支払方法取得
        $RemisePaymentMethod = $this->app['eccube.plugin.remise.repository.remise_payment_method']
            ->find($Order->getPayment()->getId());

        // 支払種別
        $paymentType = $RemisePaymentMethod->getType();
        $errMsg = '';

        if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
            // イベント生成
            $event = new EventArgs(
                array(
                    'Order' => $Order,
                    'RemisePaymentMethod' => $RemisePaymentMethod,
                ),
                $request
            );
            $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::FRONT_PLUGIN_REMISE_PAYMENT_COMPLETE_INITIALIZE, $event);
        }

        // カード決済
        if ($paymentType == $this->app['config']['remise_payment_credit'])
        {
            // 通信時エラー
            if ($requestData["X-R_CODE"] != $this->app['config']['remise_r_code_ok']
             || $requestData["X-ERRLEVEL"] != $this->app['config']['remise_errlevel_normal'])
            {
                // エラーコード選択
                if ($requestData["X-R_CODE"] != $this->app['config']['remise_r_code_ok']) {
                    $errCode = $requestData["X-R_CODE"];
                } else {
                    $errCode = $requestData["X-ERRCODE"];
                }

                // エラーメッセージ取得
                $errMsg = Errinfo::getErrCdXRCode($errCode);
            }
        }
        // マルチ決済
        else if ($paymentType == $this->app['config']['remise_payment_multi'])
        {
            // 通信時エラー
            if ($requestData["X-R_CODE"] != $this->app['config']['remise_r_code_ok'])
            {
                // エラーコード選択
                $errCode = $requestData["X-R_CODE"];

                // エラーメッセージ取得
                $errMsg = Errinfo::getErrCvsXRCode($errCode);
            }
        }

        // エラーあり
        if (!empty($errMsg))
        {
            // ログメッセージ出力
            $RemiseLog = $logService->createLogForComplete($paymentType, 3);
            $RemiseLog->addMessage($errMsg);
            $RemiseLog->addMessage($requestData);
            $logService->outputRemiseLog($RemiseLog);

            return $this->app['view']->render('error.twig', array(
                'error_title' => 'エラーが発生致しました。',
                'error_message' => $errMsg,
            ));
        }

        // 金額の整合性チェック
        if ($Order->getPaymentTotal() != $requestData["X-TOTAL"])
        {
            // エラーメッセージ取得
            $errMsg = '決済エラー：ご注文金額と決済金額が違います。';

            // ログメッセージ出力
            $RemiseLog = $logService->createLogForComplete($paymentType, 2);
            $RemiseLog->addMessage($errMsg);
            $RemiseLog->addMessage('PaymentTotal=' . $Order->getPaymentTotal());
            $RemiseLog->addMessage('X-TOTAL=' . $requestData["X-TOTAL"]);
            $logService->outputRemiseLog($RemiseLog);

            return $this->app['view']->render('error.twig', array(
                'error_title' => 'エラーが発生致しました。',
                'error_message' => $errMsg,
            ));
        }

        if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
            // イベント生成
            $event = new EventArgs(
                array(
                    'Order' => $Order,
                    'RemisePaymentMethod' => $RemisePaymentMethod,
                ),
                $request
            );
            $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::FRONT_PLUGIN_REMISE_PAYMENT_COMPLETE_PROGRESS, $event);
        }

        // 正常終了時設定
        try
        {
            $this->setSuccessComplete($Order, $requestData);
        }
        catch (\Exception $e)
        {
            return $this->app->redirect($this->app->url('shopping_error'));
        }

        // 受注IDの保持
        $session->set('remise_order_id', $Order->getId());

        if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
            // イベント生成
            $event = new EventArgs(
                array(
                    'Order' => $Order,
                    'RemisePaymentMethod' => $RemisePaymentMethod,
                ),
                $request
            );
            $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::FRONT_PLUGIN_REMISE_PAYMENT_COMPLETE_COMPLETE, $event);
        }

        // 受注完了ページへ遷移
        return $this->app->redirect($this->app->url('shopping_complete'));
    }

    /**
     * 正常終了時設定
     *
     * @param  Order  $Order  受注情報
     * @param  array  $requestData  リクエスト情報
     */
    protected function setSuccessComplete($Order, $requestData)
    {
        $cartService = $this->app['eccube.service.cart'];

        $logService = $this->app['eccube.plugin.service.remise_log'];
        $remiseOrderService = $this->app['eccube.plugin.service.remise_order'];

        // ルミーズ支払方法取得
        $RemisePaymentMethod = $this->app['eccube.plugin.remise.repository.remise_payment_method']
            ->find($Order->getPayment()->getId());

        // 支払種別
        $paymentType = $RemisePaymentMethod->getType();

        $em = $this->app['orm.em'];
        $em->getConnection()->beginTransaction();
        try
        {
            // 決済完了時のステータス更新処理
            $remiseOrderService->updateOrderComplete($Order, $requestData);

            $em->flush();
            $em->getConnection()->commit();

            // 受注完了
            $remiseOrderService->notifyComplete($Order);

            // ログメッセージ出力
            $RemiseLog = $logService->createLogForComplete($paymentType);
            $RemiseLog->addMessage($requestData);
            $logService->outputRemiseLog($RemiseLog);
        }
        catch (\Exception $e)
        {
            $em->getConnection()->rollback();
            $em->close();

            // エラーログ出力
            $RemiseLog = $logService->createLogForComplete($paymentType, 3);
            $RemiseLog->addMessage('ErrCode:' . $e->getCode());
            $RemiseLog->addMessage('ErrMessage:' . $e->getMessage());
            $RemiseLog->addMessage($e);
            $logService->outputRemiseLog($RemiseLog);

            throw $e;
        }

        // カート削除
        $cartService->clear()->save();

        // 受注完了メールを送信
        $remiseOrderService->sendOrderMail($Order);

        $em->close();
    }
}
