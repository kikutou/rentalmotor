<?php
/*
 * Copyright(c) 2015 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Entity;

use Eccube\Entity\AbstractEntity;

/**
 * ルミーズ受注結果情報エンティティ
 */
class RemiseOrderResult extends AbstractEntity
{
    /**
     * order_id
     */
    private $id;

    /**
     * credit_result
     */
    private $credit_result;

    /**
     * memo01
     */
    private $memo01;

    /**
     * memo02
     */
    private $memo02;

    /**
     * memo03
     */
    private $memo03;

    /**
     * memo04
     */
    private $memo04;

    /**
     * memo05
     */
    private $memo05;

    /**
     * memo06
     */
    private $memo06;

    /**
     * memo07
     */
    private $memo07;

    /**
     * memo08
     */
    private $memo08;

    /**
     * memo09
     */
    private $memo09;

    /**
     * memo10
     */
    private $memo10;

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
     * order_id の設定
     *
     * @param  integer $id
     * @return RemiseOrderResult
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * order_id の取得
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * credit_result の設定
     *
     * @param  string $credit_result
     * @return RemiseOrderResult
     */
    public function setCreditResult($credit_result)
    {
        $this->credit_result = $credit_result;
        return $this;
    }

    /**
     * credit_result の取得
     *
     * @return string
     */
    public function getCreditResult()
    {
        return $this->credit_result;
    }

    /**
     * memo01 の設定
     *
     * @param  string $memo01
     * @return RemiseOrderResult
     */
    public function setMemo01($memo01)
    {
        $this->memo01 = $memo01;
        return $this;
    }

    /**
     * memo01 の取得
     *
     * @return string
     */
    public function getMemo01()
    {
        return $this->memo01;
    }

    /**
     * memo02 の設定
     *
     * @param  string $memo02
     * @return RemiseOrderResult
     */
    public function setMemo02($memo02)
    {
        $this->memo02 = $memo02;
        return $this;
    }

    /**
     * memo02 の取得
     *
     * @return string
     */
    public function getMemo02()
    {
        return $this->memo02;
    }

    /**
     * memo03 の設定
     *
     * @param  string $memo03
     * @return RemiseOrderResult
     */
    public function setMemo03($memo03)
    {
        $this->memo03 = $memo03;
        return $this;
    }

    /**
     * memo03 の取得
     *
     * @return string
     */
    public function getMemo03()
    {
        return $this->memo03;
    }

    /**
     * memo04 の設定
     *
     * @param  string $memo04
     * @return RemiseOrderResult
     */
    public function setMemo04($memo04)
    {
        $this->memo04 = $memo04;
        return $this;
    }

    /**
     * memo04 の取得
     *
     * @return string
     */
    public function getMemo04()
    {
        return $this->memo04;
    }

    /**
     * memo05 の設定
     *
     * @param  string $memo05
     * @return RemiseOrderResult
     */
    public function setMemo05($memo05)
    {
        $this->memo05 = $memo05;
        return $this;
    }

    /**
     * memo05 の取得
     *
     * @return string
     */
    public function getMemo05()
    {
        return $this->memo05;
    }

    /**
     * memo06 の設定
     *
     * @param  string $memo06
     * @return RemiseOrderResult
     */
    public function setMemo06($memo06)
    {
        $this->memo06 = $memo06;
        return $this;
    }

    /**
     * memo06 の取得
     *
     * @return string
     */
    public function getMemo06()
    {
        return $this->memo06;
    }

    /**
     * memo07 の設定
     *
     * @param  string $memo07
     * @return RemiseOrderResult
     */
    public function setMemo07($memo07)
    {
        $this->memo07 = $memo07;
        return $this;
    }

    /**
     * memo07 の取得
     *
     * @return string
     */
    public function getMemo07()
    {
        return $this->memo07;
    }

    /**
     * memo08 の設定
     *
     * @param  string $memo08
     * @return RemiseOrderResult
     */
    public function setMemo08($memo08)
    {
        $this->memo08 = $memo08;
        return $this;
    }

    /**
     * memo08 の取得
     *
     * @return string
     */
    public function getMemo08()
    {
        return $this->memo08;
    }

    /**
     * memo09 の設定
     *
     * @param  string $memo09
     * @return RemiseOrderResult
     */
    public function setMemo09($memo09)
    {
        $this->memo09 = $memo09;
        return $this;
    }

    /**
     * memo09 の取得
     *
     * @return string
     */
    public function getMemo09()
    {
        return $this->memo09;
    }

    /**
     * memo10 の設定
     *
     * @param  string $memo10
     * @return RemiseOrderResult
     */
    public function setMemo10($memo10)
    {
        $this->memo10 = $memo10;
        return $this;
    }

    /**
     * memo10 の取得
     *
     * @return string
     */
    public function getMemo10()
    {
        return $this->memo10;
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

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getCreditResult();
    }
}
