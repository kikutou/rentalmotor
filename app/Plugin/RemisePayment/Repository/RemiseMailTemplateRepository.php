<?php
/*
 * Copyright(c) 2016 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ルミーズメールテンプレート情報リポジトリ
 */
class RemiseMailTemplateRepository extends EntityRepository
{
    /**
     * レコードの取得。id=0 指定時は新規作成
     *
     * @param  integer  $id
     * @return  RemiseMailTemplate
     */
    public function findOrCreate($id)
    {
        if ($id == 0)
        {
            $RemiseMailTemplate = new \Plugin\RemisePayment\Entity\RemiseMailTemplate();
            $RemiseMailTemplate
                ->setDelFlg(0)
                ->setUpdateDate('CURRENT_TIMESTAMP')
                ->setCreateDate('CURRENT_TIMESTAMP');
        }
        else
        {
            $RemiseMailTemplate = $this->find($id);
        }
        return $RemiseMailTemplate;
    }
}
