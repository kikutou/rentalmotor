<?php
/*
 * Copyright(c) 2015 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Service;

use Symfony\Component\Yaml\Yaml;
use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Util\EntityUtil;

/**
 * プラグイン設定処理
 */
class RemiseConfigService
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
     * コンストラクタ
     *
     * @param  Application  $app  
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        // configファイル読込
        $this->pluginConfig = Yaml::parse(__DIR__ . '/../config.yml');
    }

    /**
     * config取得
     *
     * @return  array  
     */
    public function getPluginConfig()
    {
        return $this->pluginConfig;
    }

    /**
     * プラグインの稼働確認
     *
     * @return integer
     */
    public function getEnablePlugin()
    {
        // プラグイン情報を検索
        $plugin = $this->app['eccube.repository.plugin']
            ->findOneBy(array('code' => $this->pluginConfig['code']));

        // 対象レコードが存在しない場合、未稼働
        if (empty($plugin)) return Constant::DISABLED;

        // プラグインが有効でない場合、未稼働
        if (!$plugin->getEnable()) return Constant::DISABLED;

        // ルミーズプラグイン設定情報の取得
        $RemiseConfig = $this->app['eccube.plugin.remise.repository.remise_config']
            ->findOneBy(array('code' => $this->pluginConfig['code']));

        // ルミーズプラグイン設定情報が存在しない場合、未稼働
        if (EntityUtil::isEmpty($RemiseConfig)) return Constant::DISABLED;

        // ルミーズプラグインが有効でない場合、未稼働
        if ($RemiseConfig->getDelFlg() != 0) return Constant::DISABLED;

        // 稼働
        return Constant::ENABLED;
    }

    /**
     * プラグイン設定情報登録
     *
     * @param array $info
     */
    public function regist(array $info)
    {
        // プラグイン設定情報登録
        $this->saveConfig($info);

        // ルミーズ受注状態登録
        // ※受注未確定
        $this->saveOrderStatus($this->app['config']['remise_order_status_pending']);

        // 支払方法登録
        // ※マルチ決済
        $this->savePayment($info, $this->app['config']['remise_payment_multi']);
        // ※クレジットカード決済
        $this->savePayment($info, $this->app['config']['remise_payment_credit']);

        // メールテンプレート登録
        // ※入金お知らせメール
        $this->saveMailTemplate($info, $this->app['config']['remise_mail_template_accept']);
    }

    /**
     * プラグイン設定情報登録
     *
     * @param  array  $info
     */
    protected function saveConfig(array $info)
    {
        if (empty($info)) return;

        // ルミーズプラグイン設定情報の取得
        $RemiseConfig = $this->app['eccube.plugin.remise.repository.remise_config']
            ->findOneBy(array('code' => $this->pluginConfig['code']));

        if (EntityUtil::isEmpty($RemiseConfig)) return;

        // 設定情報登録
        $RemiseConfig->setSerializeInfo($info);
        $this->app['orm.em']->persist($RemiseConfig);
        $this->app['orm.em']->flush();
    }

    /**
     * ルミーズ受注状態登録
     *
     * @param  integer  $type  状態種別
     */
    protected function saveOrderStatus($type)
    {
        $statusName = '';
        $statusColor = '';
        $customerStatusName = '';
        // 受注未確定
        if ($type == $this->app['config']['remise_order_status_pending'])
        {
            $statusName = $this->app['config']['remise_order_status_pending_name'];
            $statusColor = $this->app['config']['remise_order_status_pending_color'];
            $customerStatusName = $this->app['config']['remise_customer_order_status_pending_name'];
        }
        else
        {
            return;
        }
        if (empty($customerStatusName)) $customerStatusName = $statusName;

        // ルミーズ受注状態の取得
        $RemiseStatus = $this->app['eccube.plugin.remise.repository.remise_order_status']
            ->findOneBy(array('type' => $type));
        // EC-CUBE受注状態
        $OrderStatus = null;

        // ルミーズ受注状態が未登録の場合
        if (EntityUtil::isEmpty($RemiseStatus))
        {
            // EC-CUBE受注状態の新規生成
            $OrderStatus = $this->createOrderStatus($statusName);

            // EC-CUBE受注状態の登録
            $this->app['orm.em']->persist($OrderStatus);
            $this->app['orm.em']->flush();

            // ルミーズ受注状態の新規作成
            $RemiseStatus = $this->app['eccube.plugin.remise.repository.remise_order_status']->findOrCreate(0);
            $RemiseStatus->setId($OrderStatus->getId());
            $RemiseStatus->setType($type);
        }
        // ルミーズ受注状態が登録済の場合
        else
        {
            // EC-CUBE受注状態の取得
            $OrderStatus = $this->app['eccube.repository.master.order_status']
                ->findOneById(array('id' => $RemiseStatus->getId()));
            // 取得できなかった場合、新規生成
            if (EntityUtil::isEmpty($OrderStatus))
            {
                $OrderStatus = $this->createOrderStatus($statusName);
                $RemiseStatus->setId($OrderStatus->getId());
            }
            $OrderStatus->setName($statusName);

            // EC-CUBE受注状態の登録
            $this->app['orm.em']->persist($OrderStatus);
            $this->app['orm.em']->flush();
        }

        // EC-CUBE受注状態色の取得
        $OrderStatusColor = $this->app['orm.em']
            ->getRepository('Eccube\Entity\Master\OrderStatusColor')
            ->findOneById(array('id' => $OrderStatus->getId()));
        // 取得できなかった場合、新規生成
        if (EntityUtil::isEmpty($OrderStatusColor))
        {
            $OrderStatusColor = new \Eccube\Entity\Master\OrderStatusColor();
            $OrderStatusColor
                ->setId($OrderStatus->getId())
                ->setRank($OrderStatus->getRank());
        }
        $OrderStatusColor->setName($statusColor);

        // EC-CUBE受注状態色の登録
        $this->app['orm.em']->persist($OrderStatusColor);
        $this->app['orm.em']->flush();

        // EC-CUBE顧客受注状態の取得
        $CustomerOrderStatus = $this->app['orm.em']
            ->getRepository('Eccube\Entity\Master\CustomerOrderStatus')
            ->findOneById(array('id' => $OrderStatus->getId()));
        // 取得できなかった場合、新規生成
        if (EntityUtil::isEmpty($CustomerOrderStatus))
        {
            $CustomerOrderStatus = new \Eccube\Entity\Master\CustomerOrderStatus();
            $CustomerOrderStatus
                ->setId($OrderStatus->getId())
                ->setRank($OrderStatus->getRank());
        }
        $CustomerOrderStatus->setName($customerStatusName);

        // EC-CUBE顧客受注状態の登録
        $this->app['orm.em']->persist($CustomerOrderStatus);
        $this->app['orm.em']->flush();

        // ルミーズ受注状態の設定
        $RemiseStatus->setStatusName($statusName);
        $RemiseStatus->setStatusColor($statusColor);
        $RemiseStatus->setCustomerStatusName($customerStatusName);
        $RemiseStatus->setDelFlg(0);
        $RemiseStatus->setUpdateDate(new \DateTime());

        // ルミーズ受注状態の登録
        $this->app['orm.em']->persist($RemiseStatus);
        $this->app['orm.em']->flush();
    }

    /**
     * EC-CUBE受注状態の新規作成
     *
     * @param  string  $statusName  受注状態名
     *
     * @param  OrderStatus  $OrderStatus  EC-CUBE受注状態
     */
    protected function createOrderStatus($statusName)
    {
        // EC-CUBE受注状態のID採番
        $id = $this->app['eccube.repository.master.order_status']
            ->findOneBy(array(), array('id' => 'DESC'))
            ->getId() + 1;

        // EC-CUBE受注状態の表示順採番
        $rank = $this->app['eccube.repository.master.order_status']
            ->findOneBy(array(), array('rank' => 'DESC'))
            ->getRank() + 1;

        // EC-CUBE受注状態の新規生成
        $OrderStatus = new \Eccube\Entity\Master\OrderStatus();
        $OrderStatus
            ->setId($id)
            ->setRank($rank)
            ->setName($statusName);
        return $OrderStatus;
    }

    /**
     * 支払方法情報登録
     *
     * @param array $info  設定情報
     * @param  integer  $type  支払種別
     */
    protected function savePayment($info, $type)
    {
        // 支払方法利用可否
        $usePayment = false;
        foreach ($info['use_payment'] as $val)
        {
            // クレジットカード決済
            if ($type == $this->app['config']['remise_payment_credit'])
            {
                if ($val == '1') $usePayment = true;
            }
            // マルチ決済
            else if ($type == $this->app['config']['remise_payment_multi'])
            {
                if ($val == '2') $usePayment = true;
            }
        }

        // 論理削除中のレコードも取得対象とする
        $this->app['orm.em']->getFilters()->disable('soft_delete');

        // ルミーズ支払方法の取得
        $RemisePayments = $this->app['eccube.plugin.remise.repository.remise_payment_method']
            ->findBy(array('type' => $type));

        // 論理削除を有効に戻す
        $this->app['orm.em']->getFilters()->enable('soft_delete');

        // ルミーズ支払方法が未登録の場合
        if (EntityUtil::isEmpty($RemisePayments))
        {
            // 利用する場合
            if ($usePayment)
            {
                // 支払方法の新規登録
                $paymenId = $this->insertPayment($type);
            }
            // 利用しない場合
            else
            {
                // 処理抜け
                return;
            }
        }
        // ルミーズ支払方法が登録済の場合
        else
        {
            $update = 0;
            foreach ($RemisePayments as $RemisePayment)
            {
                // 利用する
                if ($usePayment)
                {
                    // 支払方法の利用開始
                    $paymenId = $this->startPayment($RemisePayment);
                    if ($paymenId != 0) $update = 1;
                }
                // 利用しない
                else
                {
                    // 支払方法の利用停止
                    $paymenId = $this->stopPayment($RemisePayment);
                }
            }
            // 利用する場合で１件も支払方法の利用開始にならない場合、支払方法の新規登録
            if ($usePayment && $update == 0)
            {
                // 支払方法の新規登録
                $paymenId = $this->insertPayment($type);
            }
        }
    }

    /**
     * 支払方法の新規登録
     *
     * @param  integer  $type  支払種別
     *
     * @return  integer  新規登録した支払方法ID
     */
    public function insertPayment($type)
    {
        // EC-CUBE支払方法の新規生成
        $Payment = $this->createPayment($type);

        // EC-CUBE支払方法の登録
        $this->app['orm.em']->persist($Payment);
        $this->app['orm.em']->flush();

        // ルミーズ支払方法の新規作成
        $RemisePayment = $this->app['eccube.plugin.remise.repository.remise_payment_method']->findOrCreate(0);
        $RemisePayment->setId($Payment->getId());
        $RemisePayment->setType($type);
        $RemisePayment->setName($Payment->getMethod());
        $RemisePayment->setDelFlg(0);
        $RemisePayment->setUpdateDate(new \DateTime());

        // ルミーズ支払方法の登録
        $this->app['orm.em']->persist($RemisePayment);
        $this->app['orm.em']->flush();

        return $Payment->getId();
    }

    /**
     * EC-CUBE支払方法の新規作成
     *
     * @param  integer  $type  支払種別
     *
     * @param  Payment  $Payment  EC-CUBE支払方法
     */
    protected function createPayment($type)
    {
        $Payment = $this->app['eccube.repository.payment']->findOrCreate(0);

        // クレジットカード決済
        if ($type == $this->app['config']['remise_payment_credit'])
        {
            $Payment->setMethod($this->app['config']['remise_payment_credit_name']);
            // 手数料設定不可
            $Payment->setChargeFlg(0);
            // 利用条件
            $Payment->setRuleMin(0);
        }
        // マルチ決済
        else if ($type == $this->app['config']['remise_payment_multi'])
        {
            $Payment->setMethod($this->app['config']['remise_payment_multi_name']);
            // 手数料設定可
            $Payment->setChargeFlg(1);
            // 利用条件
            $Payment->setRuleMin(1);
            $Payment->setRuleMax(999999);
        }

        $Payment->setCharge(0);
        $Payment->setFixFlg(1);
        $Payment->setCreateDate(new \DateTime());
        $Payment->setUpdateDate(new \DateTime());
        return $Payment;
    }

    /**
     * 支払方法の利用開始
     *
     * @param  $RemisePayment  ルミーズ支払方法
     *
     * @return  integer  利用開始した支払方法ID
     */
    public function startPayment($RemisePayment)
    {
        $paymentId = $RemisePayment->getId();

        // 論理削除中のレコードも取得対象とする
        $this->app['orm.em']->getFilters()->disable('soft_delete');

        // EC-CUBE支払方法の取得
        $Payment = $this->app['eccube.repository.payment']
            ->findOneById(array('id' => $paymentId));

        // 論理削除を有効に戻す
        $this->app['orm.em']->getFilters()->enable('soft_delete');

        // EC-CUBE支払方法が取得できない場合
        if (EntityUtil::isEmpty($Payment))
        {
            // ルミーズ支払方法削除
            $this->app['orm.em']->remove($RemisePayment);
            $this->app['orm.em']->flush();
            return 0;
        }
        // 削除状態が不一致の場合
        if ($RemisePayment->getDelFlg() != $Payment->getDelFlg())
        {
            // ルミーズ支払方法削除
            $this->app['orm.em']->remove($RemisePayment);
            $this->app['orm.em']->flush();
            return 0;
        }

        // EC-CUBE支払方法の更新
        $Payment->setDelFlg(0);
        $Payment->setUpdateDate(new \DateTime());

        $this->app['orm.em']->persist($Payment);
        $this->app['orm.em']->flush();

        // ルミーズ支払方法の更新
        $RemisePayment->setName($Payment->getMethod());
        $RemisePayment->setDelFlg(0);
        $RemisePayment->setUpdateDate(new \DateTime());

        $this->app['orm.em']->persist($RemisePayment);
        $this->app['orm.em']->flush();

        return $paymentId;
    }

    /**
     * 支払方法の利用停止
     *
     * @param  $RemisePayment  ルミーズ支払方法
     *
     * @return  integer  利用停止した支払方法ID
     */
    public function stopPayment($RemisePayment)
    {
        $paymentId = $RemisePayment->getId();

        // 論理削除中のレコードも取得対象とする
        $this->app['orm.em']->getFilters()->disable('soft_delete');

        // EC-CUBE支払方法の取得
        $Payment = $this->app['eccube.repository.payment']
            ->findOneById(array('id' => $paymentId));

        // 論理削除を有効に戻す
        $this->app['orm.em']->getFilters()->enable('soft_delete');

        // EC-CUBE支払方法が取得できない場合
        if (EntityUtil::isEmpty($Payment))
        {
            // ルミーズ支払方法削除
            $this->app['orm.em']->remove($RemisePayment);
            $this->app['orm.em']->flush();
            return 0;
        }
        // 削除状態が不一致の場合
        if ($RemisePayment->getDelFlg() != $Payment->getDelFlg())
        {
            // ルミーズ支払方法削除
            $this->app['orm.em']->remove($RemisePayment);
            $this->app['orm.em']->flush();
            return 0;
        }

        // EC-CUBE支払方法の更新
        $Payment->setDelFlg(1);
        $Payment->setUpdateDate(new \DateTime());

        $this->app['orm.em']->persist($Payment);
        $this->app['orm.em']->flush();

        // ルミーズ支払方法の更新
        $RemisePayment->setDelFlg(1);
        $RemisePayment->setUpdateDate(new \DateTime());

        $this->app['orm.em']->persist($RemisePayment);
        $this->app['orm.em']->flush();

        return $paymentId;
    }

    /**
     * 支払方法の複製登録
     *
     * @param  integer  $paymentId  支払方法ID
     *
     * @return  integer  複製した支払方法ID
     */
    public function copyPayment($paymentId)
    {
        // EC-CUBE支払方法の取得
        $Payment = $this->app['eccube.repository.payment']
            ->findOneById(array('id' => $paymentId));
        if (EntityUtil::isEmpty($Payment)) return $paymentId;

        // ルミーズ支払方法の取得
        $RemisePayments = $this->app['eccube.plugin.remise.repository.remise_payment_method']
            ->find($Payment->getId());
        if (EntityUtil::isEmpty($RemisePayments)) return $paymentId;

        // EC-CUBE支払方法の新規作成
        $NewPayment = $this->app['eccube.repository.payment']->findOrCreate(0);
        $NewPayment->setMethod($Payment->getMethod());
        $NewPayment->setChargeFlg($Payment->getChargeFlg());
        $NewPayment->setCharge($Payment->getCharge());
        $NewPayment->setRuleMin($Payment->getRuleMin());
        $NewPayment->setRuleMax($Payment->getRuleMax());
        $NewPayment->setFixFlg($Payment->getFixFlg());
        $NewPayment->setCreateDate(new \DateTime());
        $NewPayment->setUpdateDate(new \DateTime());

        // EC-CUBE支払方法の登録
        $this->app['orm.em']->persist($NewPayment);
        $this->app['orm.em']->flush();

        // ルミーズ支払方法の新規作成
        $NewRemisePayment = $this->app['eccube.plugin.remise.repository.remise_payment_method']->findOrCreate(0);
        $NewRemisePayment->setId($NewPayment->getId());
        $NewRemisePayment->setType($RemisePayments->getType());
        $NewRemisePayment->setName($RemisePayments->getName());
        $NewRemisePayment->setDelFlg(0);
        $NewRemisePayment->setUpdateDate(new \DateTime());

        // ルミーズ支払方法の登録
        $this->app['orm.em']->persist($NewRemisePayment);
        $this->app['orm.em']->flush();

        return $NewPayment->getId();
    }

    /**
     * メールテンプレート登録
     *
     * @param array  $info  設定情報
     * @param array  $type  テンプレート種別
     */
    protected function saveMailTemplate($info, $type)
    {
        // メールテンプレート利用可否
        $useMailTemplate = false;
        foreach ($info['use_payment'] as $val)
        {
            // 入金お知らせメール
            if ($type == $this->app['config']['remise_mail_template_accept'])
            {
                if ($val == '2') $useMailTemplate = true;
            }
        }

        // 論理削除中のレコードも取得対象とする
        $this->app['orm.em']->getFilters()->disable('soft_delete');

        // ルミーズメールテンプレートの取得
        $RemiseMailTemplate = $this->app['eccube.plugin.remise.repository.remise_mail_template']
            ->findOneBy(array('type' => $type));

        // 論理削除を有効に戻す
        $this->app['orm.em']->getFilters()->enable('soft_delete');

        // ルミーズメールテンプレートが未登録の場合
        if (EntityUtil::isEmpty($RemiseMailTemplate))
        {
            // 利用する場合
            if ($useMailTemplate)
            {
                // EC-CUBEメールテンプレートの新規生成
                $MailTemplate = $this->createMailTemplate($type);

                // EC-CUBEメールテンプレートの登録
                $this->app['orm.em']->persist($MailTemplate);
                $this->app['orm.em']->flush();

                // ルミーズメールテンプレートの新規作成
                $RemiseMailTemplate = $this->app['eccube.plugin.remise.repository.remise_mail_template']->findOrCreate(0);
                $RemiseMailTemplate->setId($MailTemplate->getId());
                $RemiseMailTemplate->setType($type);
                $RemiseMailTemplate->setDelFlg(0);
                $RemiseMailTemplate->setUpdateDate(new \DateTime());

                // ルミーズメールテンプレートの登録
                $this->app['orm.em']->persist($RemiseMailTemplate);
                $this->app['orm.em']->flush();
            }
            // 利用しない場合
            else
            {
                // 処理抜け
                return;
            }
        }
        // ルミーズメールテンプレートが登録済の場合
        else
        {
            // 論理削除中のレコードも取得対象とする
            $this->app['orm.em']->getFilters()->disable('soft_delete');

            // EC-CUBEメールテンプレートの取得
            $MailTemplate = $this->app['eccube.repository.mail_template']
                ->findOneById(array('id' => $RemiseMailTemplate->getId()));

            // 論理削除を有効に戻す
            $this->app['orm.em']->getFilters()->enable('soft_delete');

            // 利用する
            if ($useMailTemplate)
            {
                // EC-CUBEメールテンプレートが取得できなかった場合、新規生成
                if (EntityUtil::isEmpty($MailTemplate))
                {
                    $MailTemplate = $this->createMailTemplate($type);
                    $RemiseMailTemplate->setId($MailTemplate->getId());
                }

                $MailTemplate->setDelFlg(0);
                $MailTemplate->setUpdateDate(new \DateTime());

                // EC-CUBEメールテンプレートの登録
                $this->app['orm.em']->persist($MailTemplate);
                $this->app['orm.em']->flush();

                $RemiseMailTemplate->setDelFlg(0);
            }
            // 利用しない
            else
            {
                // EC-CUBEメールテンプレートが取得できた場合、削除
                if (EntityUtil::isNotEmpty($MailTemplate))
                {
                    // EC-CUBEメールテンプレートの更新
                    $MailTemplate->setDelFlg(1);
                    $MailTemplate->setUpdateDate(new \DateTime());

                    $this->app['orm.em']->persist($MailTemplate);
                    $this->app['orm.em']->flush();
                }

                $RemiseMailTemplate->setDelFlg(1);
            }

            $RemiseMailTemplate->setUpdateDate(new \DateTime());

            // ルミーズメールテンプレートの登録
            $this->app['orm.em']->persist($RemiseMailTemplate);
            $this->app['orm.em']->flush();
        }
    }

    /**
     * EC-CUBEメールテンプレートの新規作成
     *
     * @param  integer  $type  支払種別
     *
     * @param  MailTemplate  $MailTemplate  EC-CUBEメールテンプレート
     */
    protected function createMailTemplate($type)
    {
        $MailTemplate = $this->app['eccube.repository.mail_template']->findOrCreate(0);

        // 入金お知らせメール
        if ($type == $this->app['config']['remise_mail_template_accept'])
        {
            $MailTemplate->setName($this->app['config']['remise_mail_template_accept_name']);
            $MailTemplate->setFileName($this->app['config']['remise_mail_template_accept_filename']);
            $MailTemplate->setSubject($this->app['config']['remise_mail_template_accept_subject']);
            $MailTemplate->setHeader($this->app['config']['remise_mail_template_accept_header']);
            $MailTemplate->setFooter($this->app['config']['remise_mail_template_accept_footer']);
        }

        // 元にするテンプレート
        $BaseMailTemplate = $this->app['eccube.repository.mail_template']->find(1);

        // 設定値が空の場合は元にするテンプレートから複写
        $filename = $MailTemplate->getFileName();
        if (empty($filename)) $filename = $BaseMailTemplate->getFileName();
        $MailTemplate->setFileName($filename);

        $header = $MailTemplate->getHeader();
        if (empty($header)) $header = $BaseMailTemplate->getHeader();
        $MailTemplate->setHeader($header);

        $footer = $MailTemplate->getFooter();
        if (empty($footer)) $footer = $BaseMailTemplate->getFooter();
        $MailTemplate->setFooter($footer);

        $MailTemplate->setCreateDate(new \DateTime());
        $MailTemplate->setUpdateDate(new \DateTime());
        return $MailTemplate;
    }
}
