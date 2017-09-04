<?php
/*
 * Copyright(c) 2016 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Entity;

use Eccube\Entity\AbstractEntity;

/**
 * ルミーズマルチ決済支払方法案内情報エンティティ
 */
class RemiseMultiPayinfo extends AbstractEntity
{
    /**
     * payinfo_id
     */
    private $id;

    /**
     * pay_no1_label
     */
    private $pay_no1_label;

    /**
     * pay_no2_label
     */
    private $pay_no2_label;

    /**
     * telno_label
     */
    private $telno_label;

    /**
     * dsk_label
     */
    private $dsk_label;

    /**
     * message
     */
    private $message;

    /**
     * note
     */
    private $note;

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
     * payinfo_id の設定
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
     * payinfo_id の取得
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * pay_no1_label の設定
     *
     * @param  string $payNo1Label
     * @return RemiseMultiPayinfo
     */
    public function setPayNo1Label($payNo1Label)
    {
        $this->pay_no1_label = $payNo1Label;
        return $this;
    }

    /**
     * pay_no1_label の取得
     *
     * @return string
     */
    public function getPayNo1Label()
    {
        return $this->pay_no1_label;
    }

    /**
     * pay_no2_label の設定
     *
     * @param  string $payNo2Label
     * @return RemiseMultiPayinfo
     */
    public function setPayNo2Label($payNo2Label)
    {
        $this->pay_no2_label = $payNo2Label;
        return $this;
    }

    /**
     * pay_no2_label の取得
     *
     * @return string
     */
    public function getPayNo2Label()
    {
        return $this->pay_no2_label;
    }

    /**
     * telno_label の設定
     *
     * @param  string $telnoLabel
     * @return RemiseMultiPayinfo
     */
    public function setTelnoLabel($telnoLabel)
    {
        $this->telno_label = $telnoLabel;
        return $this;
    }

    /**
     * telno_label の取得
     *
     * @return string
     */
    public function getTelnoLabel()
    {
        return $this->telno_label;
    }

    /**
     * dsk_label の設定
     *
     * @param  string $dskLabel
     * @return RemiseMultiPayinfo
     */
    public function setDskLabel($dskLabel)
    {
        $this->dsk_label = $dskLabel;
        return $this;
    }

    /**
     * dsk_label の取得
     *
     * @return string
     */
    public function getDskLabel()
    {
        return $this->dsk_label;
    }

    /**
     * message の設定
     *
     * @param  string $message
     * @return RemiseMultiPayinfo
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * message の取得
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * note の設定
     *
     * @param  string $note
     * @return RemiseMultiPayinfo
     */
    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    /**
     * note の取得
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * create_date の設定
     *
     * @param  \DateTime $createDate
     * @return RemiseMultiPayinfo
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
     * @return RemiseMultiPayinfo
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
