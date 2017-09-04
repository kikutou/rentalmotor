<?php
/*
 * Copyright(c) 2016 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePaymentExtset\Service;

use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Event\EventArgs;

use Plugin\RemisePaymentExtset\Event\RemiseEventBase;

/**
 * 受注更新処理
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
     * 拡張セットで受注情報の更新
     *
     * @param  Order  $Order  受注情報
     * @param  RemiseOrderResult  $Order  ルミーズ受注結果情報
     * @param  string  $job  処理区分
     * @param  array  $retData  応答情報
     */
    public function updateExtsetOrder($Order, $RemiseResult, $job, $retData)
    {
        // 売上
        if ($job == $this->app['config']['job_sales']) {
            $RemiseResult->setMemo06($job);

            // 受注情報を更新
            $Order->setPaymentDate(new \DateTime());
            $Order->setOrderStatus($this->app['eccube.repository.order_status']->find($this->app['config']['order_pre_end']));
            $Order->setUpdateDate(new \DateTime());
            $this->app['orm.em']->persist($Order);
        }
        // キャンセル
        else if ($job == $this->app['config']['job_void'] || $job == $this->app['config']['job_return']) {
            $RemiseResult->setMemo06($job);

            // 受注情報を更新
            $Order->setOrderStatus($this->app['eccube.repository.order_status']->find($this->app['config']['order_cancel']));
            $Order->setUpdateDate(new \DateTime());
            $this->app['orm.em']->persist($Order);
        }
        // 金額変更
        else if ($job == $this->app['config']['job_change']) {
            $memo5 = unserialize($RemiseResult->getMemo05());
            $memo5['payment_total'] = $retData['X-TOTAL'];
            $RemiseResult->setMemo05(serialize($memo5));
        }
        $RemiseResult->setMemo04($retData['X-TRANID']);
        $RemiseResult->setMemo07(date("Y-m-d"));
        $RemiseResult->setUpdateDate(new \DateTime());

        if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
            // イベント生成
            $event = new EventArgs(
                array(
                    'RemiseResult' => $RemiseResult,
                )
            );
            $this->app['eccube.event.dispatcher']->dispatch(RemiseEventBase::SERVICE_PLUGIN_REMISE_EXTSET_UPDATE_RESULT, $event);
        }

        // ルミーズ受注結果情報の更新
        $this->app['orm.em']->persist($RemiseResult);
        $this->app['orm.em']->flush();
    }
}
