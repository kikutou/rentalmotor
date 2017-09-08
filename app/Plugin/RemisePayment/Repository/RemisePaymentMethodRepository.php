<?php
/*
 * Copyright(c) 2015 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ルミーズ支払方法情報リポジトリ
 */
class RemisePaymentMethodRepository extends EntityRepository
{
    /**
     * レコードの取得。id=0 指定時は新規作成
     *
     * @param  integer  $id
     * @return  RemisePaymentMethod
     */
    public function findOrCreate($id)
    {
        if ($id == 0)
        {
            $RemisePaymentMethod = new \Plugin\RemisePayment\Entity\RemisePaymentMethod();
            $RemisePaymentMethod
                ->setDelFlg(0)
                ->setUpdateDate('CURRENT_TIMESTAMP')
                ->setCreateDate('CURRENT_TIMESTAMP');
        }
        else
        {
            $RemisePaymentMethod = $this->find($id);
        }
        return $RemisePaymentMethod;
    }
}
