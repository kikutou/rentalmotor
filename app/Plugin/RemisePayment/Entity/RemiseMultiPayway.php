<?php
/*
 * Copyright(c) 2016 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Entity;

use Eccube\Entity\AbstractEntity;

/**
 * ルミーズマルチ決済支払方法情報エンティティ
 */
class RemiseMultiPayway extends AbstractEntity
{
    /**
     * payway_id
     */
    private $id;

    /**
     * cvs_code
     */
    private $code;

    /**
     * cvs_way
     */
    private $way;

    /**
     * cvs_name
     */
    private $name;

    /**
     * payinfo_id
     */
    private $payinfo_id;

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
     * payway_id の設定
     *
     * @param  integer $id
     * @return RemiseMultiPayinfo
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * payway_id の取得
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * cvs_code の設定
     *
     * @param  string $code
     * @return RemiseMultiPayway
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * cvs_code の取得
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * cvs_way の設定
     *
     * @param  string $way
     * @return RemiseMultiPayway
     */
    public function setWay($way)
    {
        $this->way = $way;
        return $this;
    }

    /**
     * cvs_way の取得
     *
     * @return string
     */
    public function getWay()
    {
        return $this->way;
    }

    /**
     * cvs_name の設定
     *
     * @param  string $name
     * @return RemiseMultiPayway
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * cvs_name の取得
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * payinfo_id の設定
     *
     * @param  integer $payinfoId
     * @return RemiseMultiPayway
     */
    public function setPayinfoId($payinfoId)
    {
        $this->payinfo_id = $payinfoId;
        return $this;
    }

    /**
     * payinfo_id の取得
     *
     * @return integer
     */
    public function getPayinfoId()
    {
        return $this->payinfo_id;
    }

    /**
     * del_flg の設定
     *
     * @param  integer $delFlg
     * @return RemiseMultiPayway
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
     * @return RemiseMultiPayway
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
     * @return RemiseMultiPayway
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
