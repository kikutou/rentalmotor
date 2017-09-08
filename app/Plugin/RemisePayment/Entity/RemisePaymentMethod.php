<?php
/*
 * Copyright(c) 2015 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Entity;

use Eccube\Entity\AbstractEntity;

/**
 * ルミーズ支払方法情報エンティティ
 */
class RemisePaymentMethod extends AbstractEntity
{
    /**
     * payment_id
     */
    private $id;

    /**
     * pay_type
     */
    private $type;

    /**
     * pay_name
     */
    private $name;

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
     * payment_id の設定
     *
     * @param  integer $id
     * @return RemisePaymentMethod
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * payment_id の取得
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * pay_type の設定
     *
     * @param  integer $type
     * @return RemisePaymentMethod
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * pay_type の取得
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * pay_name の設定
     *
     * @param  string $name
     * @return RemisePaymentMethod
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * pay_name の取得
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * del_flg の設定
     *
     * @param  integer $delFlg
     * @return RemisePaymentMethod
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
     * @return RemisePaymentMethod
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
     * @return RemisePaymentMethod
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
        return $this->getName();
    }
}
