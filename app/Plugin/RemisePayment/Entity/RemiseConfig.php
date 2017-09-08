<?php
/*
 * Copyright(c) 2015 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Entity;

use Eccube\Entity\AbstractEntity;

/**
 * ルミーズプラグイン設定情報エンティティ
 */
class RemiseConfig extends AbstractEntity
{
    /**
     * plugin_id
     */
    private $id;

    /**
     * plugin_code
     */
    private $code;

    /**
     * plugin_name
     */
    private $name;

    /**
     * info
     */
    private $info;

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
     * plugin_id の取得
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * plugin_code の設定
     *
     * @param  string $code
     * @return RemiseConfig
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * plugin_code の取得
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * plugin_name の設定
     *
     * @param  string $name
     * @return RemiseConfig
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * plugin_name の取得
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * info の設定
     *
     * @param  string $info
     * @return RemiseConfig
     */
    public function setInfo($info)
    {
        $this->info = $info;
        return $this;
    }

    /**
     * info の設定
     *
     * @param  array $info
     * @return RemiseConfig
     */
    public function setSerializeInfo($info)
    {
        $this->info = serialize($info);
        return $this;
    }

    /**
     * info の取得
     *
     * @return string
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * info の取得
     *
     * @return array
     */
    public function getUnserializeInfo()
    {
        if (empty($this->info)) return null;
        return unserialize($this->info);
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
     * @return RemiseConfig
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
     * @return RemiseConfig
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
