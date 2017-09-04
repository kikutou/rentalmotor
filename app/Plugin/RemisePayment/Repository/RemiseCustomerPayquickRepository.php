<?php
/*
 * Copyright(c) 2015 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Repository;

use Doctrine\ORM\EntityRepository;
use Eccube\Util\EntityUtil;

/**
 * ルミーズ受注結果情報リポジトリ
 */
class RemiseCustomerPayquickRepository extends EntityRepository
{
    /**
     * レコードの取得。id=0 指定時は新規作成
     *
     * @param  integer  $id
     * @return  RemiseCustomerPayquick
     */
    public function findOrCreate($id)
    {
        if ($id == 0)
        {
            $RemiseCustomerPayquick = new \Plugin\RemisePayment\Entity\RemiseCustomerPayquick();
            $RemiseCustomerPayquick
                ->setUpdateDate('CURRENT_TIMESTAMP')
                ->setCreateDate('CURRENT_TIMESTAMP');
        }
        else
        {
            $RemiseCustomerPayquick = $this->find($id);
        }
        return $RemiseCustomerPayquick;
    }

    /**
     * レコードの削除
     *
     * @param  integer  $id
     * @param  integer  $payquickNo
     * @return  payquickId
     */
    public function deleteByPayquickNo($id, $payquickNo)
    {
        // ペイクイック情報取得
        $RemiseCustomerPayquick = $this->findOneBy(array('id' => $id, 'payquick_no' => $payquickNo));

        $payquickId = "";
        if (EntityUtil::isNotEmpty($RemiseCustomerPayquick))
        {
            $payquickId = $RemiseCustomerPayquick->getPayquickId();

            // ペイクイック情報削除
            $em = $this->getEntityManager();
            $em->remove($RemiseCustomerPayquick);
            $em->flush();
        }
        return $payquickId;
    }
}
