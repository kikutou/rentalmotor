<?php
/*
 * Copyright(c) 2016 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Entity;

use Eccube\Entity\AbstractEntity;

/**
 * ルミーズメールテンプレート情報エンティティ
 */
class RemiseMailTemplate extends AbstractEntity
{
    /**
     * mail_template_id
     */
    private $id;

    /**
     * template_type
     */
    private $type;

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
     * mail_template_id の設定
     *
     * @param  integer $id
     * @return RemiseMailTemplate
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * mail_template_id の取得
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * template_type の設定
     *
     * @param  integer $type
     * @return RemiseMailTemplate
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * template_type の取得
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * del_flg の設定
     *
     * @param  integer $delFlg
     * @return RemiseMailTemplate
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
     * @return RemiseMailTemplate
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
     * @return RemiseMailTemplate
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
        return $this->getId();
    }
}
