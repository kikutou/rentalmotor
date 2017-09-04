<?php
/*
 * Copyright(c) 2015 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Entity;

use Eccube\Entity\AbstractEntity;

/**
 * ルミーズペイクイック情報エンティティ
 */
class RemiseCustomerPayquick extends AbstractEntity
{
    /**
     * customer_id
     */
    private $id;

    /**
     * payquick_no
     */
    private $payquick_no;

    /**
     * old_payquick_id
     */
    private $old_payquick_id;

    /**
     * old_card
     */
    private $old_card;

    /**
     * old_expire
     */
    private $old_expire;

    /**
     * old_cardbrand
     */
    private $old_cardbrand;

    /**
     * old_payquick_date
     */
    private $old_payquick_date;

    /**
     * payquick_id
     */
    private $payquick_id;

    /**
     * card
     */
    private $card;

    /**
     * expire
     */
    private $expire;

    /**
     * cardbrand
     */
    private $cardbrand;

    /**
     * payquick_date
     */
    private $payquick_date;

    /**
     * payquick_flg
     */
    private $payquick_flg;

    /**
     * create_date
     */
    private $create_date;

    /**
     * update_date
     */
    private $update_date;

    /**
     * コンストラクタ
     */
    public function __construct()
    {
    }

    /**
     * customer_id の設定
     *
     * @param  integer $id
     * @return RemiseCustomerPayQuick
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * customer_id の取得
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * payquick_no の設定
     *
     * @param  string $payquick_no
     * @return RemiseOrderResult
     */
    public function setPayquickNo($payquick_no)
    {
        $this->payquick_no = $payquick_no;
        return $this;
    }

    /**
     * payquick_no の取得
     *
     * @return string
     */
    public function getPayquickNo()
    {
        return $this->payquick_no;
    }

    /**
     * old_payquick_id の設定
     *
     * @param  string $old_payquick_id
     * @return RemiseOrderResult
     */
    public function setOldPayquickId($old_payquick_id)
    {
        $this->old_payquick_id = $old_payquick_id;
        return $this;
    }

    /**
     * old_payquick_id の取得
     *
     * @return string
     */
    public function getOldPayquickId()
    {
        return $this->old_payquick_id;
    }

    /**
     * old_card の設定
     *
     * @param  string $old_card
     * @return RemiseOrderResult
     */
    public function setOldCard($old_card)
    {
        $this->old_card = $old_card;
        return $this;
    }

    /**
     * old_card の取得
     *
     * @return string
     */
    public function getOldCard()
    {
        return $this->old_card;
    }

    /**
     * old_expire の設定
     *
     * @param  string $old_expire
     * @return RemiseOrderResult
     */
    public function setOldExpire($old_expire)
    {
        $this->old_expire = $old_expire;
        return $this;
    }

    /**
     * old_expire の取得
     *
     * @return string
     */
    public function getOldExpire()
    {
        return $this->old_expire;
    }

    /**
     * old_cardbrand の設定
     *
     * @param  string $old_cardbrand
     * @return RemiseOrderResult
     */
    public function setOldCardbrand($old_cardbrand)
    {
        $this->old_cardbrand = $old_cardbrand;
        return $this;
    }

    /**
     * old_cardbrand の取得
     *
     * @return string
     */
    public function getOldCardbrand()
    {
        return $this->old_cardbrand;
    }

    /**
     * old_payquick_date の設定
     *
     * @param  string $old_payquick_date
     * @return RemiseOrderResult
     */
    public function setOldPayquickDate($old_payquick_date)
    {
        $this->old_payquick_date = $old_payquick_date;
        return $this;
    }

    /**
     * old_payquick_date の取得
     *
     * @return string
     */
    public function getOldPayquickDate()
    {
        return $this->old_payquick_date;
    }

    /**
     * payquick_id の設定
     *
     * @param  string $payquick_id
     * @return RemiseOrderResult
     */
    public function setPayquickId($payquick_id)
    {
        $this->payquick_id = $payquick_id;
        return $this;
    }

    /**
     * payquick_id の取得
     *
     * @return string
     */
    public function getPayquickId()
    {
        return $this->payquick_id;
    }

    /**
     * card の設定
     *
     * @param  string $card
     * @return RemiseOrderResult
     */
    public function setCard($card)
    {
        $this->card = $card;
        return $this;
    }

    /**
     * card の取得
     *
     * @return string
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * expire の設定
     *
     * @param  string $expire
     * @return RemiseOrderResult
     */
    public function setExpire($expire)
    {
        $this->expire = $expire;
        return $this;
    }

    /**
     * expire の取得
     *
     * @return string
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * cardbrand の設定
     *
     * @param  string $cardbrand
     * @return RemiseOrderResult
     */
    public function setCardBrand($cardbrand)
    {
        $this->cardbrand = $cardbrand;
        return $this;
    }

    /**
     * cardbrand の取得
     *
     * @return string
     */
    public function getCardBrand()
    {
        return $this->cardbrand;
    }

    /**
     * payquick_date の設定
     *
     * @param  string $payquick_date
     * @return RemiseOrderResult
     */
    public function setPayquickDate($payquick_date)
    {
        $this->payquick_date = $payquick_date;
        return $this;
    }

    /**
     * payquick_date の取得
     *
     * @return string
     */
    public function getPayquickDate()
    {
        return $this->payquick_date;
    }

    /**
     * payquick_flg の設定
     *
     * @param  string $payquick_flg
     * @return RemiseOrderResult
     */
    public function setPayquickFlg($payquick_flg)
    {
        $this->payquick_flg = $payquick_flg;
        return $this;
    }

    /**
     * payquick_flg の取得
     *
     * @return string
     */
    public function getPayquickFlg()
    {
        return $this->payquick_flg;
    }

    /**
     * create_date の設定
     *
     * @param  \DateTime $createDate
     * @return RemiseOrderResult
     */
    public function setCreateDate($createDate)
    {
        $this->create_date = $createDate;
        return $this;
    }

    /**
     * create_date の取得
     *
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * update_date の設定
     *
     * @param  \DateTime $updateDate
     * @return RemiseOrderResult
     */
    public function setUpdateDate($updateDate)
    {
        $this->update_date = $updateDate;
        return $this;
    }

    /**
     * update_date の取得
     *
     * @return \DateTime
     */
    public function getUpdateDate()
    {
        return $this->update_date;
    }

}
