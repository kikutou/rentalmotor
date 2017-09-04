<?php
/*
 * Copyright(c) 2016 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Entity;

use Eccube\Entity\AbstractEntity;

/**
 * カード支払区分情報エンティティ
 */
class RemiseCardMethod extends AbstractEntity
{
    /**
     * card_method_id
     */
    private $id;

    /**
     * card_method_code
     */
    private $code;

    /**
     * card_method_name
     */
    private $name;

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
     * card_method_id の設定
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
     * card_method_id の取得
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * card_method_code の設定
     *
     * @param  integer $type
     * @return RemiseCardMethod
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * card_method_code の取得
     *
     * @return integer
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * card_method_name の設定
     *
     * @param  string $name
     * @return RemiseCardMethod
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * card_method_name の取得
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * create_date の設定
     *
     * @param  \DateTime $createDate
     * @return RemiseCardMethod
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
     * @return RemiseCardMethod
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
        return "";
    }
}
