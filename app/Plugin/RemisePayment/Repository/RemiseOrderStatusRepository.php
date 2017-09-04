<?php
/*
 * Copyright(c) 2015 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ルミーズ受注状態リポジトリ
 */
class RemiseOrderStatusRepository extends EntityRepository
{
    /**
     * レコードの取得。id=0 指定時は新規作成
     *
     * @param  integer  $id
     * @return  RemiseOrderStatus
     */
    public function findOrCreate($id)
    {
        if ($id == 0)
        {
            $RemiseOrderStatus = new \Plugin\RemisePayment\Entity\RemiseOrderStatus();
            $RemiseOrderStatus
                ->setDelFlg(0)
                ->setUpdateDate('CURRENT_TIMESTAMP')
                ->setCreateDate('CURRENT_TIMESTAMP');
        }
        else
        {
            $RemiseOrderStatus = $this->find($id);
        }
        return $RemiseOrderStatus;
    }
}
