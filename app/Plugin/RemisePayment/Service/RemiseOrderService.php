<?php
/*
 * Copyright(c) 2015 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Service;

use Symfony\Component\Validator\Constraints as Assert;
use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Entity\MailHistory;
use Eccube\Event\EventArgs;
use Eccube\Util\EntityUtil;

use Plugin\RemisePayment\Common\Confinfo;
use Plugin\RemisePayment\Event\RemiseEventBase;

/**
 * 受注処理
 */
class RemiseOrderService
{
    /**
     * Application
     */
    public $app;

    /**
     * コンストラクタ
     *
     * @param  Application  $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * 受注情報の受注状態チェック
     *
     * @param  Order  $Order  受注情報
     * @param  integer  $checkMode  呼び出し種別（0:決済画面呼び出し前、1:決済画面呼び出し後）
     *
     * @return  買い物の継続可否
     */
    public function checkShoppingStatus($Order, $checkMode = 0)
    {
        $cartService = $this->app['eccube.service.cart'];

        // 受注情報なしは、継続不可
        if (EntityUtil::isEmpty($Order)) {
            // カート削除
            $cartService->clear()->save();
            return false;
        }

        // 決済処理中は、継続可
        if ($Order->getOrderStatus()->getId() == $this->app['config']['order_pending'])
        {
            return true;
        }
        // 購入処理中は、継続可
        if ($Order->getOrderStatus()->getId() == $this->app['config']['order_processing'])
        {
            return true;
        }

        // 決済画面呼び出し後
        if ($checkMode == 1)
        {
            // 受注未確定のステータス取得
            $RemiseStatus = $this->app['eccube.plugin.remise.repository.remise_order_status']
                ->findOneBy(array('type' => $this->app['config']['remise_order_status_pending']));

            // 受注未確定は、継続可
            if ($Order->getOrderStatus()->getId() == $RemiseStatus->getId())
            {
                return true;
            }
        }

        // その他の場合、継続不可
        $cartService->clear()->save();
        return false;
    }

    /**
     * カートチェック
     *
     * @return  カートロック状態
     */
    public function isCartLocked()
    {
        $cartService = $this->app['eccube.service.cart'];
        return $cartService->isLocked();
    }

    /**
     * 受注情報の取得
     *
     * @return  受注情報
     */
    public function getOrder()
    {
        $Order = null;
        switch (Constant::VERSION)
        {
            case "3.0.0":
            case "3.0.1":
            case "3.0.2":
            case "3.0.3":
            case "3.0.4":
                $Order = $this->getOrderByPreOrderId();
                break;
            default:
                $Order = $this->app['eccube.service.shopping']
                    ->getOrder($this->app['config']['order_processing']);
                if (EntityUtil::isEmpty($Order)) {
                    $Order = $this->getOrderByPreOrderId();
                }
                break;
        }
        return $Order;
    }
    /**
     * 受注情報の取得
     *
     * @param  integer  $id  受注ID
     *
     * @return  受注情報
     */
    public function getOrderById($id)
    {
        $Order = $this->app['eccube.repository.order']
            ->findOneBy(array('id' => $id));
        return $Order;
    }
    /**
     * 受注情報の取得
     *
     * @return  受注情報
     */
    public function getOrderByPreOrderId()
    {
        $Order = $this->app['eccube.repository.order']
            ->findOneBy(array('pre_order_id' => $this->app['eccube.service.cart']->getPreOrderId()));
        return $Order;
    }

    /**
     * 決済フォーム情報の取得
     *
     * @return  Order  $Order  受注情報
     *
     * @return  $form  決済フォーム情報
     */
    public function getOrderForm($Order)
    {
        $form = null;

        switch (Constant::VERSION)
        {
            case "3.0.0":
            case "3.0.1":
            case "3.0.2":
                $form = $this->app['form.factory']->createBuilder('shopping')->getForm();

                $orderService = $this->app['eccube.service.order'];
                $deliveries = $this->findDeliveriesFromOrderDetails($Order->getOrderDetails());

                // 配送業社の設定
                $shippings = $Order->getShippings();
                $delivery = $shippings[0]->getDelivery();

                // 配送業社の設定
                $this->setFormDelivery($form, $deliveries, $delivery);
                // お届け日の設定
                $this->setFormDeliveryDate($form, $Order);
                // お届け時間の設定
                $this->setFormDeliveryTime($form, $delivery);
                // 支払い方法選択
                $this->setFormPayment($form, $delivery, $Order);

                break;

            case "3.0.3":
            case "3.0.4":
                $form = $this->app['form.factory']->createBuilder('shopping')->getForm();

                $orderService = $this->app['eccube.service.order'];
                $deliveries = $orderService->findDeliveriesFromOrderDetails($this->app, $Order->getOrderDetails());

                // 配送業社の設定
                $shippings = $Order->getShippings();
                $delivery = $shippings[0]->getDelivery();

                // 配送業社の設定
                $orderService->setFormDelivery($form, $deliveries, $delivery);
                // お届け日の設定
                $orderService->setFormDeliveryDate($form, $Order, $this->app);
                // お届け時間の設定
                $orderService->setFormDeliveryTime($form, $delivery);
                // 支払い方法選択
                $orderService->setFormPayment($form, $delivery, $Order, $this->app);

                break;

            default:
                // form作成
                $form = $this->app['eccube.service.shopping']->getShippingForm($Order);
                break;
        }

        return $form;
    }

    /**
     * 商品公開ステータスチェック、在庫チェック、購入制限数チェック
     *
     * @param  $em
     * @param  Order  $Order  受注情報
     *
     * @return  チェック結果
     */
    public function isOrderProduct($em, $Order)
    {
        $check = null;
        switch (Constant::VERSION)
        {
            case "3.0.0":
            case "3.0.1":
            case "3.0.2":
            case "3.0.3":
            case "3.0.4":
                $check = $this->app['eccube.service.order']->isOrderProduct($em, $Order);
                break;
            default:
                $check = $this->app['eccube.service.shopping']->isOrderProduct($em, $Order);
                break;
        }
        return $check;
    }

    /**
     * 決済処理中への更新処理
     *
     * @param  Order  $Order  受注情報
     * @param  array  $data  画面設定値
     */
    public function updateOrderPending($Order, $data)
    {
        switch (Constant::VERSION)
        {
            case "3.0.0":
            case "3.0.1":
            case "3.0.2":
            case "3.0.3":
            case "3.0.4":
                // 受注情報、配送情報を更新
                $this->app['eccube.service.order']->setOrderUpdate($this->app['orm.em'], $Order, $data);
                break;

            default:
                // 受注情報、配送情報を更新
                $this->app['eccube.service.shopping']->setOrderUpdate($Order, $data);
                break;
        }

        // ステータスは決済処理中
        $Order->setOrderStatus($this->app['eccube.repository.order_status']->find($this->app['config']['order_pending']));
        // 受注日はクリア
        $Order->setOrderDate(null);

        $this->app['orm.em']->persist($Order);
        $this->app['orm.em']->flush();
    }

    /**
     * 受注未確定への更新処理
     *
     * @param  Order  $Order  受注情報
     */
    public function updateRemiseOrderPending($Order)
    {
        // 受注未確定のステータス取得
        $RemiseStatus = $this->app['eccube.plugin.remise.repository.remise_order_status']
            ->findOneBy(array('type' => $this->app['config']['remise_order_status_pending']));

        // 受注情報を更新
        $Order->setOrderStatus($this->app['eccube.repository.order_status']->find($RemiseStatus->getId()));
        $Order->setOrderDate(new \DateTime());

        $this->app['orm.em']->persist($Order);
        $this->app['orm.em']->flush();
    }

    /**
     * 購入処理中への更新処理
     *
     * @param  Order  $Order  受注情報
     */
    public function updateOrderProcessing($Order)
    {
        // 受注情報を更新
        $Order->setOrderStatus($this->app['eccube.repository.order_status']->find($this->app['config']['order_processing']));

        $this->app['orm.em']->persist($Order);
        $this->app['orm.em']->flush();
    }

    /**
     * 決済完了時のステータス更新処理
     *
     * @param  Order  $Order  受注情報
     * @param  array  $postData  応答情報
     */
    public function updateOrderComplete($Order, $postData)
    {
        // config取得
        $configService = $this->app['eccube.plugin.service.remise_config'];
        $pluginConfig = $configService->getPluginConfig();

        // ルミーズ支払方法取得
        $RemisePaymentMethod = $this->app['eccube.plugin.remise.repository.remise_payment_method']
            ->find($Order->getPayment()->getId());

        // 支払種別
        $paymentType = $RemisePaymentMethod->getType();

        // ルミーズプラグイン設定情報の取得
        $RemiseConfig = $this->app['eccube.plugin.remise.repository.remise_config']
            ->findOneBy(array('code' => $pluginConfig['code']));

        // 設定情報取得
        $info = $RemiseConfig->getUnserializeInfo();

        // ルミーズ受注結果情報の登録
        $this->registRemiseResult($Order, $postData);

        // カード決済
        if ($paymentType == $this->app['config']['remise_payment_credit'])
        {
            // 受注結果の更新（実売上）
            if ($info['job'] == "CAPTURE")
            {
                $this->updatePreEnd($Order);
            }
            // 受注結果の更新（仮売上）
            else
            {
                $this->updateOrderNew($Order);
            }
        }
        // マルチ決済
        else if ($paymentType == $this->app['config']['remise_payment_multi'])
        {
            // 入金待ちへの更新処理
            $this->updatePayWait($Order);
        }
    }

    /**
     * ルミーズ受注結果情報の登録
     *
     * @param  Order  $Order  受注情報
     * @param  array  $postData  応答情報
     */
    public function registRemiseResult($Order, $postData)
    {
        // 結果通知の場合
        if (isset($postData['REC_TYPE']) && $postData['REC_TYPE'] == "RET") {
            // 受注未確定のステータス取得
            $RemiseStatus = $this->app['eccube.plugin.remise.repository.remise_order_status']
                ->findOneBy(array('type' => $this->app['config']['remise_order_status_pending']));

            // 決済処理中、購入処理中、受注未確定以外の場合は、処理抜け
            $orderStatusId = $Order->getOrderStatus()->getId();
            if ($orderStatusId != $this->app['config']['order_pending']
             && $orderStatusId != $this->app['config']['order_processing']
             && $orderStatusId != $RemiseStatus->getId()) {
                return;
             }
        }

        // config取得
        $configService = $this->app['eccube.plugin.service.remise_config'];
        $pluginConfig = $configService->getPluginConfig();

        // ルミーズ支払方法取得
        $RemisePaymentMethod = $this->app['eccube.plugin.remise.repository.remise_payment_method']
            ->find($Order->getPayment()->getId());

        // 支払種別
        $paymentType = $RemisePaymentMethod->getType();

        // ルミーズプラグイン設定情報の取得
        $RemiseConfig = $this->app['eccube.plugin.remise.repository.remise_config']
            ->findOneBy(array('code' => $pluginConfig['code']));

        // 設定情報取得
        $info = $RemiseConfig->getUnserializeInfo();
        if (empty($info)) $info = array();

        // ルミーズ受注結果情報を取得
        $RemiseResult = $this->app['eccube.plugin.remise.repository.remise_order_result']
            ->findOneBy(array('id' => $Order->getId()));
        // ルミーズ受注結果情報が未登録の場合、新規生成
        if (EntityUtil::isEmpty($RemiseResult))
        {
            $RemiseResult = $this->app['eccube.plugin.remise.repository.remise_order_result']->findOrCreate(0);
        }
        $RemiseResult->setId($Order->getId());
        $RemiseResult->setMemo01($paymentType);
        $RemiseResult->setMemo03($pluginConfig['code']);

        // 決済送信データ作成
        $arrModule["plugin_code"]   = $pluginConfig['code'];
        $arrModule["payment_total"] = $Order->getPaymentTotal();
        $arrModule["payment_id"]    = $paymentType;
        $RemiseResult->setMemo05(serialize($arrModule));

        $arrMemo02 = array();

        // カード決済
        if ($paymentType == $this->app['config']['remise_payment_credit'])
        {
            $RemiseResult->setCreditResult($postData['X-TRANID']);

            // トランザクションコード
            $arrMemo02['trans_code'] = array(
                'name'  => "Remiseトランザクションコード",
                'value' => $postData['X-TRANID']
            );

            $RemiseResult->setMemo04($postData['X-TRANID']);
            $RemiseResult->setMemo06($info['job']);
            if ($postData["X-TOTAL"] =="0") {
                $RemiseResult->setMemo06('CHECK');
            }
        }
        // マルチ決済
        else if ($paymentType == $this->app['config']['remise_payment_multi'])
        {
            // マルチ決済結果情報の取得
            $arrMemo02 = $this->getMultiArrMemo02($Order, $postData);

            $RemiseResult->setMemo04($postData['X-JOB_ID']);
            $RemiseResult->setMemo06($postData['X-PAY_CSV']);
        }

        $RemiseResult->setMemo02(serialize($arrMemo02));

        $RemiseResult->setCreateDate(new \DateTime());
        $RemiseResult->setUpdateDate(new \DateTime());

        if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
            // イベント生成
            $event = new EventArgs(
                array(
                    'RemiseResult' => $RemiseResult,
                )
            );
            $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::SERVICE_PLUGIN_REMISE_REGIST_RESULT, $event);
        }

        // ルミーズ受注結果情報の登録
        $this->app['orm.em']->persist($RemiseResult);
        $this->app['orm.em']->flush();
    }

    /**
     * マルチ決済結果情報の取得
     *
     * @param  Order  $Order  受注情報
     * @param  array  $postData  応答情報
     */
    public function getMultiArrMemo02($Order, $postData)
    {
        $confinfo = new Confinfo($this->app);

        $arrMemo02 = array();

        // ルミーズマルチ決済支払方法取得
        $RemiseMultiPayway = $this->app['eccube.plugin.remise.repository.remise_multi_payway']
            ->findOneBy(array('code' => $postData["X-PAY_CSV"]));
        if (EntityUtil::isEmpty($RemiseMultiPayway)) return $arrMemo02;

        // ルミーズマルチ決済支払方法案内取得
        $RemiseMultiPayinfo = $this->app['eccube.plugin.remise.repository.remise_multi_payinfo']
            ->findOneBy(array('id' => $RemiseMultiPayway->getPayinfoId()));
        if (EntityUtil::isEmpty($RemiseMultiPayinfo)) return $arrMemo02;

        // タイトル
        $arrMemo02['title'] = array(
            'name'  => $this->app['config']['remise_payment_multi_name'],
            'value' => true,
        );
        // コンビニの種類
        $arrMemo02['cv_type'] = array(
            "name"  => "お支払い先",
            "value" => $RemiseMultiPayway->getName(),
        );
        // 支払い期限
        $arrMemo02['cv_payment_limit'] = array(
            'name'  => "お支払い期限",
            'value' => substr($postData["X-PAYDATE"], 0, 4) . "年"
                     . substr($postData["X-PAYDATE"], 4, 2) . "月"
                     . substr($postData["X-PAYDATE"], 6, 2) . "日",
        );
        // 払出番号1
        $payNo1Label = $RemiseMultiPayinfo->getPayNo1Label();
        if (!empty($payNo1Label))
        {
            $arrMemo02['cv_payno1'] = array(
                'name'  => $payNo1Label,
                'value' => $postData["X-PAY_NO1"],
            );

            // ローソン、ミニストップ
            if ($postData["X-PAY_CSV"] == "D002" || $postData["X-PAY_CSV"] == "D005")
            {
                $pos = strpos($payNo1Label, ",");
                if ($pos !== false)
                {
                    $arrMemo02['cv_payno1'] = array(
                        'name'  => substr($payNo1Label, 0, $pos),
                        'value' => substr($postData["X-PAY_NO1"], 0, 8),
                    );
                    $arrMemo02['cv_payno1_2'] = array(
                        'name'  => substr($payNo1Label, $pos+1),
                        'value' => substr($postData["X-PAY_NO1"], 8, 9),
                    );
                }
            }
        }
        // 登録電話番号
        $telnoLabel = $RemiseMultiPayinfo->getTelnoLabel();
        if (!empty($telnoLabel))
        {
            $arrMemo02['cv_tel_no'] = array(
                'name'  => $telnoLabel,
                'value' => $Order->getTel01() . $Order->getTel02() . $Order->getTel03(),
            );
        }
        // 払出番号2
        $payNo2Label = $RemiseMultiPayinfo->getPayNo2Label();
        if (!empty($payNo2Label))
        {
            $arrMemo02['cv_payno2'] = array(
                'name'  => $payNo2Label,
                'value' => $postData["X-PAY_NO2"],
            );
        }
        // 各支払先窓口でのお支払い方法
        $dskLabel = $RemiseMultiPayinfo->getDskLabel();
        if (!empty($dskLabel))
        {
            $arrMemo02['cv_dsk'] = array(
                'name'  => $dskLabel,
                'value' => $this->app['config']['remise_dsk_url'],
            );
        }
        // 支払方法説明
        $arrMemo02['cv_msg'] = array(
            'name'  => "",
            'value' => $RemiseMultiPayinfo->getMessage(),
        );

        return $arrMemo02;
    }

    /**
     * ルミーズ受注結果情報の更新
     *
     * @param  Order  $Order  受注情報
     * @param  array  $postData  応答情報
     */
    public function updateRemiseResult($Order, $postData)
    {
        // ルミーズ支払方法取得
        $RemisePaymentMethod = $this->app['eccube.plugin.remise.repository.remise_payment_method']
            ->find($Order->getPayment()->getId());

        // 支払種別
        $paymentType = $RemisePaymentMethod->getType();

        // ルミーズ受注結果情報を取得
        $RemiseResult = $this->app['eccube.plugin.remise.repository.remise_order_result']
            ->findOneBy(array('id' => $Order->getId()));

        // マルチ決済
        if ($paymentType == $this->app['config']['remise_payment_multi'])
        {
            $RemiseResult->setMemo08($postData['RECDATE']);
        }

        $RemiseResult->setUpdateDate(new \DateTime());

        if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
            // イベント生成
            $event = new EventArgs(
                array(
                    'RemiseResult' => $RemiseResult,
                )
            );
            $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::SERVICE_PLUGIN_REMISE_UPDATE_RESULT, $event);
        }

        // ルミーズ受注結果情報の登録
        $this->app['orm.em']->persist($RemiseResult);
        $this->app['orm.em']->flush();
    }

    /**
     * 新規受付への更新処理
     *
     * @param  Order  $Order  受注情報
     */
    public function updateOrderNew($Order)
    {
        // 受注情報を更新
        $Order->setOrderDate(new \DateTime());
        $Order->setOrderStatus($this->app['eccube.repository.order_status']->find($this->app['config']['order_new']));

        // 在庫情報を更新
        $this->updateStock($Order);

        $this->app['orm.em']->persist($Order);
        $this->app['orm.em']->flush();
    }

    /**
     * 入金済みへの更新処理
     *
     * @param  Order  $Order  受注情報
     * @param  bool  $isMulti  マルチ決済での入金か否か
     */
    public function updatePreEnd($Order, $isMulti = false)
    {
        // 受注情報を更新
        if (!$isMulti) {
            $Order->setOrderDate(new \DateTime());
        }
        $Order->setPaymentDate(new \DateTime());
        $Order->setOrderStatus($this->app['eccube.repository.order_status']->find($this->app['config']['order_pre_end']));

        if (!$isMulti) {
            // 在庫情報を更新
            $this->updateStock($Order);
        }

        $this->app['orm.em']->persist($Order);
        $this->app['orm.em']->flush();
    }

    /**
     * 入金待ちへの更新処理
     *
     * @param  Order  $Order  受注情報
     */
    public function updatePayWait($Order)
    {
        // 受注情報を更新
        $Order->setOrderDate(new \DateTime());
        $Order->setOrderStatus($this->app['eccube.repository.order_status']->find($this->app['config']['order_pay_wait']));

        // 在庫情報を更新
        $this->updateStock($Order);

        $this->app['orm.em']->persist($Order);
        $this->app['orm.em']->flush();
    }

    /**
     * 在庫情報を更新
     *
     * @param  Order  $Order  受注情報
     */
    public function updateStock($Order)
    {
        switch (Constant::VERSION)
        {
            case "3.0.0":
            case "3.0.1":
            case "3.0.2":
                $orderService = $this->app['eccube.service.order'];
                // 在庫情報を更新
                $orderService->setStockUpdate($this->app['orm.em'], $Order);
                // 会員の場合、購入金額を更新
                if ($this->app['security']->isGranted('ROLE_USER'))
                {
                    $orderService->setCustomerUpdate($this->app['orm.em'], $Order, $this->app->user());
                }
                break;

            case "3.0.3":
            case "3.0.4":
                $orderService = $this->app['eccube.service.order'];
                // 在庫情報を更新
                $orderService->setStockUpdate($this->app['orm.em'], $Order);
                // 会員の場合、購入金額を更新
                if ($this->app->isGranted('ROLE_USER'))
                {
                    $orderService->setCustomerUpdate($this->app['orm.em'], $Order, $this->app->user());
                }
                break;

            default:
                $shoppingService = $this->app['eccube.service.shopping'];
                // 在庫情報を更新
                $shoppingService->setStockUpdate($this->app['orm.em'], $Order);
                if ($this->app->isGranted('ROLE_USER'))
                {
                    // 会員の場合、購入金額を更新
                    $shoppingService->setCustomerUpdate($Order, $this->app->user());
                }
                break;
        }
    }

    /**
     * ペイクイック情報更新処理
     *
     * @param  Order  $customerId  customer_id
     * @param  array  $requestData  リクエスト情報
     */
    public function updatePayquick($customerId, $requestData)
    {
        // POSTデータを保存
        $Payquick = $this->app['eccube.plugin.remise.repository.remise_customer_payquick']
            ->findOneBy(array('id' => $customerId));
        // 未登録の場合、plg_remise_customer_payquick 新規生成
        if (EntityUtil::isEmpty($Payquick))
        {
            $Payquick = $this->app['eccube.plugin.remise.repository.remise_customer_payquick']->findOrCreate(0);
        }
        try
        {
            $Payquick->setId($customerId);

            // ペイクイック情報登録
            $payquickId = $Payquick->getPayquickId();
            if (!empty($payquickId)) {
                $Payquick->setOldPayquickId($Payquick->getPayquickId());
                $Payquick->setOldCard($Payquick->getCard());
                $Payquick->setOldExpire($Payquick->getExpire());
                $Payquick->setOldCardBrand($Payquick->getCardBrand());
                $Payquick->setOldPayquickDate($Payquick->getPayquickDate());
            }

            // setPayquickNo 複数カード情報保持用項目
            $Payquick->setPayquickNo("1");
            $Payquick->setPayquickFlg("1");
            $Payquick->setPayquickId($requestData["X-PAYQUICKID"]);
            $Payquick->setCard($requestData["X-PARTOFCARD"]);
            $Payquick->setExpire($requestData["X-EXPIRE"]);
            $Payquick->setCardBrand($requestData["X-CARDBRAND"]);
            $Payquick->setPayquickDate(new \DateTime());

            if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
                // イベント生成
                $event = new EventArgs(
                    array(
                        'Payquick' => $Payquick,
                    )
                );
                $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::SERVICE_PLUGIN_REMISE_UPDATE_PAYQUICK, $event);
            }

            // ルミーズ受注結果情報の登録
            $this->app['orm.em']->persist($Payquick);
            $this->app['orm.em']->flush();

            return true;
        }
        catch (\Exception $e)
        {
            // エラーログ出力
            $logService = $this->app['eccube.plugin.service.remise_log'];
            $RemiseLog = $logService->createLogForShopping(3);
            $RemiseLog->addMessage('ErrCode:' . $e->getCode());
            $RemiseLog->addMessage('ErrMessage:' . $e->getMessage());
            $RemiseLog->addMessage($e);
            $logService->outputRemiseLog($RemiseLog);

            return false;
        }
    }

    /**
     * 受注完了メールを送信
     *
     * @param  Order  $Order  受注情報
     */
    public function sendOrderMail($Order)
    {
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
            }
        }

        // 受注完了メールを送信
        //$this->app['eccube.service.mail']->sendOrderMail($Order);
        //------------------------------------------------------------------------------------------
        $BaseInfo = $this->app['eccube.repository.base_info']->get();
        $MailTemplate = $this->app['eccube.repository.mail_template']->find(1);

        // メール本文
        $body = $this->app->renderView($MailTemplate->getFileName(), array(
            'header' => $MailTemplate->getHeader(),
            'footer' => $MailTemplate->getFooter(),
            'Order' => $Order,
        ));

        // 追加文
        $form = $this->app['form.factory']->createBuilder()->getForm();
        $twig = $this->app->renderView(
            'RemisePayment/Resource/template/mail/order_payinfo.twig',
            array(
                'form' => $form->createView(),
                'arrOther' => $arrMemo02,
            )
        );

        // 挿入位置を特定
        $searchKey = "************************************************";
        $idx = strlen($MailTemplate->getHeader());  // ヘッダー除外
        $idx = strpos($body, $searchKey, $idx) + strlen($searchKey);    // ご請求金額：開始タグ
        $idx = strpos($body, $searchKey, $idx) + strlen($searchKey);    // ご請求金額：終了タグ
        $idx = strpos($body, $searchKey, $idx);    // ご注文商品明細：開始タグ

        // 追加文挿入
        $body = substr($body, 0, $idx) . $twig . substr($body, $idx);

        $message = \Swift_Message::newInstance()
            ->setSubject('[' . $BaseInfo->getShopName() . '] ' . $MailTemplate->getSubject())
            ->setFrom(array($BaseInfo->getEmail01() => $BaseInfo->getShopName()))
            ->setTo(array($Order->getEmail()))
            ->setBcc($BaseInfo->getEmail01())
            ->setReplyTo($BaseInfo->getEmail03())
            ->setReturnPath($BaseInfo->getEmail04())
            ->setBody($body);

        if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
            // イベント生成
            $event = new EventArgs(
                array(
                    'Order' => $Order,
                    'message' => $message,
                )
            );
            $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::MAIL_PLUGIN_REMISE_ORDER, $event);
        }

        $this->app->mail($message);
        //------------------------------------------------------------------------------------------

        switch (Constant::VERSION)
        {
            case "3.0.0":
            case "3.0.1":
            case "3.0.2":
            case "3.0.3":
            case "3.0.4":
            case "3.0.5":
                break;

            default:
                // 受注IDをセッションにセット
                $this->app['session']->set('eccube.front.shopping.order.id', $Order->getId());

                // メール送信履歴を保存.
                $MailHistory = new MailHistory();
                $MailHistory
                    ->setSubject('[' . $this->app['eccube.repository.base_info']->get()->getShopName() . '] ' . $MailTemplate->getSubject())
                    ->setMailBody($body)
                    ->setMailTemplate($MailTemplate)
                    ->setSendDate(new \DateTime())
                    ->setOrder($Order);
                $this->app['orm.em']->persist($MailHistory);
                $this->app['orm.em']->flush($MailHistory);

                break;
        }
    }

    /**
     * 入金確認メールを送信
     *
     * @param  Order  $Order  受注情報
     */
    public function sendReceiptMail($Order)
    {
        $logService = $this->app['eccube.plugin.service.remise_log'];

        // ルミーズメールテンプレートの取得
        $RemiseMailTemplate = $this->app['eccube.plugin.remise.repository.remise_mail_template']
            ->findOneBy(array('type' => $this->app['config']['remise_mail_template_accept']));

        if (EntityUtil::isEmpty($RemiseMailTemplate))
        {
            // 収納結果ログ作成
            $RemiseLog = $logService->createLogForMultiResult(3);
            $RemiseLog->addMessage('remise mail template not found.');
            $logService->outputRemiseLog($RemiseLog);
            return;
        }

        // 入金確認メールを送信
        //------------------------------------------------------------------------------------------
        $BaseInfo = $this->app['eccube.repository.base_info']->get();
        $MailTemplate = $this->app['eccube.repository.mail_template']
            ->findOneById(array('id' => $RemiseMailTemplate->getId()));

        if (EntityUtil::isEmpty($MailTemplate))
        {
            // 収納結果ログ作成
            $RemiseLog = $logService->createLogForMultiResult(3);
            $RemiseLog->addMessage('mail template(' . $RemiseMailTemplate->getId() . ') not found.');
            $logService->outputRemiseLog($RemiseLog);
            return;
        }

        // メール本文
        $body = $this->app->renderView($MailTemplate->getFileName(), array(
            'header' => $MailTemplate->getHeader(),
            'footer' => $MailTemplate->getFooter(),
            'Order' => $Order,
        ));

        $message = \Swift_Message::newInstance()
            ->setSubject('[' . $BaseInfo->getShopName() . '] ' . $MailTemplate->getSubject())
            ->setFrom(array($BaseInfo->getEmail01() => $BaseInfo->getShopName()))
            ->setTo(array($Order->getEmail()))
            ->setBcc($BaseInfo->getEmail01())
            ->setReplyTo($BaseInfo->getEmail03())
            ->setReturnPath($BaseInfo->getEmail04())
            ->setBody($body);

        if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
            // イベント生成
            $event = new EventArgs(
                array(
                    'Order' => $Order,
                    'message' => $message,
                )
            );
            $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::MAIL_PLUGIN_REMISE_RECEIPT, $event);
        }

        //$this->app->mail($message);
        $mailer = \Swift_Mailer::newInstance($this->app['swiftmailer.transport']);
        $mailer->send($message);
        //------------------------------------------------------------------------------------------

        switch (Constant::VERSION)
        {
            case "3.0.0":
            case "3.0.1":
            case "3.0.2":
            case "3.0.3":
            case "3.0.4":
            case "3.0.5":
                break;

            default:
                // メール送信履歴を保存.
                $MailHistory = new MailHistory();
                $MailHistory
                    ->setSubject('[' . $this->app['eccube.repository.base_info']->get()->getShopName() . '] ' . $MailTemplate->getSubject())
                    ->setMailBody($body)
                    ->setMailTemplate($MailTemplate)
                    ->setSendDate(new \DateTime())
                    ->setOrder($Order);
                $this->app['orm.em']->persist($MailHistory);
                $this->app['orm.em']->flush($MailHistory);

                break;
        }
    }

    /**
     * 受注処理完了通知
     *
     * @param  Order  $Order  受注情報
     */
    public function notifyComplete($Order)
    {
        if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
            // 受注完了を他プラグインへ通知する
            $this->app['eccube.service.shopping']->notifyComplete($Order);
        }
    }

    // --------------------------------------------------------------------------------------------
    // EC-CUBE 3.0.3からサービス化
    /**
     * 配送業者を取得
     */
    private function findDeliveriesFromOrderDetails($details)
    {
        $productTypes = array();
        foreach ($details as $detail) {
            $productTypes[] = $detail->getProductClass()->getProductType();
        }

        $qb = $this->app['orm.em']->createQueryBuilder();
        $deliveries = $qb->select("d")
            ->from("\Eccube\Entity\Delivery", "d")
            ->where($qb->expr()->in('d.ProductType', ':productTypes'))
            ->setParameter('productTypes', $productTypes)
            ->andWhere("d.del_flg = :delFlg")
            ->setParameter('delFlg', Constant::DISABLED)
            ->orderBy("d.rank", "ASC")
            ->getQuery()
            ->getResult();

        return $deliveries;
    }

    /**
     * 配送業者のフォームを設定
     */
    private function setFormDelivery($form, $deliveries, $delivery = null)
    {
        // 配送業社の設定
        $form->add('delivery', 'entity', array(
            'class' => 'Eccube\Entity\Delivery',
            'property' => 'name',
            'choices' => $deliveries,
            'data' => $delivery,
        ));
    }

    /**
     * お届け日のフォームを設定
     */
    private function setFormDeliveryDate($form, $Order)
    {
        // お届け日の設定
        $minDate = 0;
        $deliveryDateFlag = false;

        // 配送時に最大となる商品日数を取得
        foreach ($Order->getOrderDetails() as $detail) {
            $deliveryDate = $detail->getProductClass()->getDeliveryDate();
            if (!is_null($deliveryDate)) {
                if ($minDate < $deliveryDate->getValue()) {
                    $minDate = $deliveryDate->getValue();
                }
                // 配送日数が設定されている
                $deliveryDateFlag = true;
            }
        }

        // 配達最大日数期間を設定
        $deliveryDates = array();

        // 配送日数が設定されている
        if ($deliveryDateFlag) {
            $period = new \DatePeriod (
                new \DateTime($minDate . ' day'),
                new \DateInterval('P1D'),
                new \DateTime($minDate + $this->app['config']['deliv_date_end_max'] . ' day')
            );

            foreach ($period as $day) {
                $deliveryDates[$day->format('Y/m/d')] = $day->format('Y/m/d');
            }
        }

        $form->add('deliveryDate', 'choice', array(
            'choices' => $deliveryDates,
            'required' => false,
            'empty_value' => '指定なし',
        ));
    }

    /**
     * お届け時間のフォームを設定
     */
    private function setFormDeliveryTime($form, $delivery)
    {
        // お届け時間の設定
        $form->add('deliveryTime', 'entity', array(
            'class' => 'Eccube\Entity\DeliveryTime',
            'property' => 'deliveryTime',
            'choices' => $delivery->getDeliveryTimes(),
            'required' => false,
            'empty_value' => '指定なし',
            'empty_data' => null,
        ));
    }

    /**
     * 支払い方法のフォームを設定
     */
    private function setFormPayment($form, $delivery, $Order)
    {
        $orderService = $this->app['eccube.service.order'];
        $paymentOptions = $delivery->getPaymentOptions();
        $payments = $orderService->getPayments($paymentOptions, $Order->getSubTotal());

        $form->add('payment', 'entity', array(
            'class' => 'Eccube\Entity\Payment',
            'property' => 'method',
            'choices' => $payments,
            'data' => $Order->getPayment(),
            'expanded' => true,
            'constraints' => array(
                new Assert\NotBlank(),
            ),
        ));
    }
}
