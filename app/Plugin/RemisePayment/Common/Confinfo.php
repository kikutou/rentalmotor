<?php
/*
 * Copyright(c) 2015 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Common;

/**
 * 設定情報
 */
class Confinfo
{
    private $app;

    public function __construct(\Eccube\Application $app)
    {
        $this->app = $app;
    }

    /**
     * ペイクイックの利用可否を取得
     *
     * @param array $info
     * @return boolean $blnRet true:利用可、false:利用不可
     */
    public function isPayquick(array $info = null)
    {
        $blnRet = false;

        // ゲストユーザの場合、利用不可
        if (!$this->app->isGranted('IS_AUTHENTICATED_FULLY')) return $blnRet;

        // ペイクイックの利用判定
        $info = $this->getConfigInfo($info);
        if (isset($info["payquick"]) && !empty($info["payquick"]) && $info["payquick"] == "1")
        {
            $blnRet = true;
        }

        return $blnRet;
    }

    /**
     * カード支払区分を取得
     *
     * @param array $info
     * @return array $arrCardMethod 設定されているカード支払区分のIDリスト
     */
    public function getUseCardMethod(array $info = null)
    {
        $arrCardMethod = array();
        $info = $this->getConfigInfo($info);
        if (isset($info["use_cardmethod"]) && !empty($info["use_cardmethod"]))
        {
            $arrCardMethod = $info["use_cardmethod"];
        }
        return $arrCardMethod;
    }

    /**
     * プラグイン設定オブジェクトを取得
     *
     * @param array $info
     * @return $info
     */
    private function getConfigInfo(array $info = null)
    {
        if ($info == null || empty($info))
        {
            $configService = $this->app['eccube.plugin.service.remise_config'];
            $pluginConfig = $configService->getPluginConfig();
            $RemiseConfig = $this->app['eccube.plugin.remise.repository.remise_config']
                            ->findOneBy(array('code' => $pluginConfig['code']));
             // 設定情報取得
            $info = $RemiseConfig->getUnserializeInfo();
        }
        return $info;
    }
}
