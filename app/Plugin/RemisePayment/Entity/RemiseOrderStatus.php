<?php
/*
 * Copyright(c) 2015 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Entity;

use Eccube\Entity\AbstractEntity;

/**
 * ルミーズ受注状態エンティティ
 */
class RemiseOrderStatus extends AbstractEntity
{
    /**
     * status_id
     */
    private $id;

    /**
     * status_type
     */
    private $type;

    /**
     * status_name
     */
    private $status_name;

    /**
     * status_color
     */
    private $status_color;

    /**
     * customer_status_name
     */
    private $customer_status_name;

    /**
     * del_flg
     */
    private $del_flg;

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
     * status_id の設定
     *
     * @param  integer $id
     * @return RemiseOrderStatus
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * status_id の取得
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * status_type の設定
     *
     * @param  integer $type
     * @return RemiseOrderStatus
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * status_type の取得
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * status_name の設定
     *
     * @param  string $status_name
     * @return RemiseOrderStatus
     */
    public function setStatusName($status_name)
    {
        $this->status_name = $status_name;
        return $this;
    }

    /**
     * status_name の取得
     *
     * @return string
     */
    public function getStatusName()
    {
        return $this->status_name;
    }

    /**
     * status_color の設定
     *
     * @param  string $status_color
     * @return RemiseOrderStatus
     */
    public function setStatusColor($status_color)
    {
        $this->status_color = $status_color;
        return $this;
    }

    /**
     * status_color の取得
     *
     * @return string
     */
    public function getStatusColor()
    {
        return $this->status_color;
    }

    /**
     * customer_status_name の設定
     *
     * @param  string $customer_status_name
     * @return RemiseOrderStatus
     */
    public function setCustomerStatusName($customer_status_name)
    {
        $this->customer_status_name = $customer_status_name;
        return $this;
    }

    /**
     * customer_status_name の取得
     *
     * @return string
     */
    public function getCustomerStatusName()
    {
        return $this->customer_status_name;
    }

    /**
     * del_flg の設定
     *
     * @param  integer $delFlg
     * @return RemiseOrderStatus
     */
    public function setDelFlg($delFlg)
    {
        $this->del_flg = $delFlg;
        return $this;
    }

    /**
     * del_flg の取得
     *
     * @return integer
     */
    public function getDelFlg()
    {
        return $this->del_flg;
    }

    /**
     * create_date の設定
     *
     * @param  \DateTime $createDate
     * @return RemiseOrderStatus
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
     * @return RemiseOrderStatus
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

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getStatusName();
    }
}
