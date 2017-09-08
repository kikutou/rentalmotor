<?php
/*
 * Copyright(c) 2015 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Common;

/**
 * エラー情報
 */
class Errinfo
{
    /**
     * 結果通知・拡張セット 戻りコード
     */
    static $MTB_ERR_CD_XRCODE = array(
        '5:1000' => '結果通知トランザクションにおいて正常なステータスが取得できませんでした。',
        '5:2000' => '結果通知トランザクションにおいて原因不明なエラーが発生しました。',
        '7:0001' => '退会対象の定期購買会員情報が見つかりませんでした。',
        '7:0002' => 'この注文は既に退会処理が完了しています。',
        '7:0003' => '定期購買の退会処理中にエラーが発生しました。',
        '8:4003' => 'メンテナンス中により受付できませんでした。',
        '8:5801' => '取引停止中により受付できませんでした。',
        '8:5804' => 'お取扱できないカードです。',
        '8:5805' => 'お取扱できないカードです。',
        '8:5810' => '売上対象のトランザクションが見つかりませんでした。',
        '8:5812' => 'キャンセル対象のトランザクションが見つかりませんでした。',
        '8:5819' => 'このカード番号での送信は制限されました。',
        '8:5820' => 'この取引は制限されました。既に決済が済んでいます。',
        '8:5821' => '3-D Secure取引中に原因不明なエラーが発生しました。お客様には大変ご迷惑をおかけしますが、しばらく、時間を置いて、再度お手続きをお願い致します。',
        '8:5822' => 'オーソリ保持期限を過ぎています。',
        '8:5823' => '取消可能期限を過ぎています。',
        '8:5826' => '同一リモートIPアドレスによる送信制限がかかっています。',
        '9:0000' => '原因不明なエラーが発生しました。',
    );

    /**
     * マルチ決済・戻りコード
     */
    static $MTB_ERR_CVS_XRCODE = array(
        '1:0000' => '提携サイトの一時的なサーバ負荷により受付できませんでした。',
        '2:0000' => '提携サイトのメンテナンス中により受付できませんでした。',
        '8:4003' => 'メンテナンス中により受付できませんでした。',
        '9:0000' => '原因不明なエラーが発生しました。',
        '9:0001' => '原因不明なエラーが発生しました。'
    );

    /**
     * カード決済・戻りコードメッセージ取得
     *
     * @param  string  $code  戻りコード
     * @return  string  $ret  戻りコードメッセージ
     */
    public static function getErrCdXRCode($code)
    {
        if (empty($code) || $code == "0:0000") return "";

        $ret = null;
        if (array_key_exists($code, self::$MTB_ERR_CD_XRCODE))
        {
            $ret = self::$MTB_ERR_CD_XRCODE[$code];
        }

        if (empty($ret))
        {
            $ret = self::getErrOther($code);
            if (empty($ret))
            {
                $ret = "クレジットカード決済においてエラーが発生しました。";
            }
        }
        $ret = "決済処理エラー：" . $ret . "(" . $code . ")";
        return $ret;
    }

    /**
     * マルチ決済・戻りコードメッセージ取得
     *
     * @param  string  $code  戻りコード
     * @return  string  $ret  戻りコードメッセージ
     */
    public static function getErrCvsXRCode($code)
    {
        if (empty($code) || $code == "0:0000") return "";

        $ret = null;
        if (array_key_exists($code, self::$MTB_ERR_CVS_XRCODE))
        {
            $ret = self::$MTB_ERR_CVS_XRCODE[$code];
        }

        if (empty($ret))
        {
            $ret = self::getErrOther($code);
            if (empty($ret))
            {
                $ret = "コンビニ・電子マネー・銀行決済においてエラーが発生しました。";
            }
        }
        $ret = "決済処理エラー：" . $ret . "(" . $code . ")";
        return $ret;
    }

    /**
     * カード、マルチ決済共通・その他メッセージ取得
     *
     * @param  string  $code  戻りコード
     * @return  string  $ret  戻りコードメッセージ
     */
    public static function getErrOther($code)
    {
        $ret = "";
        switch (substr($code, 0, 3))
        {
            case "8:1":
                $ret = "送信データにおいて設定されていない項目が存在します。";
                break;
            case "8:2":
                $ret = "送信データにおいて桁不足もしくは桁あふれの項目が存在します。";
                break;
            case "8:3":
                $ret = "送信データにおいて不正なデータが設定されている項目が存在します。";
        }
        if(preg_match("/^8:[123]008$/", $code)){
             $ret = "メールアドレスのご変更をお願い致します。";
        }
        return $ret;
    }
}
