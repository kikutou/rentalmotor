<?php
/*
 * Copyright(c) 2015 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ルミーズ受注結果情報リポジトリ
 */
class RemiseOrderResultRepository extends EntityRepository
{
    /**
     * レコードの取得。id=0 指定時は新規作成
     *
     * @param  integer  $id
     * @return  RemiseOrderResult
     */
    public function findOrCreate($id)
    {
        if ($id == 0)
        {
            $RemiseOrderResult = new \Plugin\RemisePayment\Entity\RemiseOrderResult();
            $RemiseOrderResult
                ->setUpdateDate('CURRENT_TIMESTAMP')
                ->setCreateDate('CURRENT_TIMESTAMP');
        }
        else
        {
            $RemiseOrderResult = $this->find($id);
        }
        return $RemiseOrderResult;
    }

    /**
     * ペイクイック結果情報の取得
     *
     * @param type $paymentTypeId
     * @param type $app
     * @return type
     */
    public function getResult($customereId, $app)
    {
        $softDeleteFilter = $app['orm.em']->getFilters()->getFilter('soft_delete');
        $originExcludes = $softDeleteFilter->getExcludes();

        $softDeleteFilter->setExcludes(array(
            'Plugin\RemisePayment\Entity\RemiseOrderResult',
            'Eccube\Entity\Order',
            'Eccube\Entity\Customer'
        ));

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('r')
            ->from('\Plugin\RemisePayment\Entity\RemiseOrderResult', 'r')
            ->join('\Eccube\Entity\Order', 'o', 'WITH', 'r.id = o.id')
            ->join('\Eccube\Entity\Customer', 'c', 'WITH', 'o.Customer = c.id')
            ->where(
                $qb->expr()->eq('c.id', ':x')
            )
            ->orderBy("r.update_date", "DESC");
        $qb->setParameter('x', $customereId);

        $ret = $qb
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();

        $softDeleteFilter->setExcludes($originExcludes);

        return $ret;
    }
}
