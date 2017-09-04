<?php
/*
 * Copyright(c) 2016 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Repository;

use Doctrine\ORM\EntityRepository;

use Plugin\RemisePayment\Common\Confinfo;

/**
 * カード支払区分情報リポジトリ
 */
class RemiseCardMethodRepository extends EntityRepository
{
    public function findByUseCodes($app,array $info = null){
        $confinfo = new Confinfo($app);
        $arryUseCardMethods = $confinfo->getUseCardMethod($info);
        return $this->findBy(array("code"=>$arryUseCardMethods));
    }
}
