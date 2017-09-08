<?php
/*
 * Copyright(c) 2015 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Service;

use Eccube\Application;

use Plugin\RemisePayment\Entity\RemiseLog;

/**
 * ログ出力処理
 */
class RemiseLogService
{
    /**
     * Application
     */
    public $app;

    /**
     * コンストラクタ
     *
     * @param  Application  $app  
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * ルミーズプラグイン設定情報のログ情報生成
     *
     * @param  integer  $level  エラーレベル
     *
     * @return  RemiseLog
     */
    public function createLogForConfig($level = 0)
    {
        $RemiseLog = new \Plugin\RemisePayment\Entity\RemiseLog();
        $RemiseLog->setFilename('remise_config.log');
        $RemiseLog->setAction('config');
        $RemiseLog->setLevel($level);
        return $RemiseLog;
    }

    /**
     * ルミーズ決済画面呼び出し時のログ情報生成
     *
     * @param  integer  $level  エラーレベル
     *
     * @return  RemiseLog
     */
    public function createLogForShopping($level = 0)
    {
        $RemiseLog = new \Plugin\RemisePayment\Entity\RemiseLog();
        $RemiseLog->setFilename('remise_shopping.log');
        $RemiseLog->setAction('shopping');
        $RemiseLog->setLevel($level);
        return $RemiseLog;
    }

    /**
     * 決済画面呼び出し時のログ情報生成
     *
     * @param  string  $paymentType  決済種別
     * @param  array  $arrSendData  送信データ
     * @param  integer  $level  エラーレベル
     *
     * @return  RemiseLog
     */
    public function createLogForRedirect($paymentType, $arrSendData, $level = 0)
    {
        $RemiseLog = new \Plugin\RemisePayment\Entity\RemiseLog();
        if ($level == 0)
        {
            $RemiseLog->addMessage($arrSendData);
        }

        // カード決済
        if ($paymentType == $this->app['config']['remise_payment_credit'])
        {
            $RemiseLog->setFilename('remise_card.log');
            $RemiseLog->setAction('card payment');
            if ($level != 0)
            {
                $logInfo = array(
                    'S_TORIHIKI_NO' => $arrSendData['S_TORIHIKI_NO'],   // 請求番号
                    'MAIL'          => $arrSendData['MAIL'],            // e-mail
                    'AMOUNT'        => $arrSendData['AMOUNT'],          // 金額
                    'TAX'           => $arrSendData['TAX'],             // 税送料
                    'TOTAL'         => $arrSendData['TOTAL'],           // 合計金額
                    'JOB'           => $arrSendData['JOB'],             // 処理区分
                    'METHOD'        => $arrSendData['METHOD'],          // 支払区分
                    'PAYQUICK'      => $arrSendData['PAYQUICK'],        // ペイクイック機能
                );
                $RemiseLog->addMessage($logInfo);
            }
        }
        // マルチ決済
        else if ($paymentType == $this->app['config']['remise_payment_multi'])
        {
            $RemiseLog->setFilename('remise_multi.log');
            $RemiseLog->setAction('multi payment');
            if ($level != 0)
            {
                $logInfo = array(
                    'S_TORIHIKI_NO' => $arrSendData['S_TORIHIKI_NO'],   // 請求番号
                    'NAME1'         => $arrSendData['NAME1'],           // 顧客名1
                    'NAME2'         => $arrSendData['NAME2'],           // 顧客名2
                    'MAIL'          => $arrSendData['MAIL'],            // e-mail
                    'TOTAL'         => $arrSendData['TOTAL'],           // 合計金額
                    'S_PAYDATE'     => $arrSendData['S_PAYDATE'],       // 支払期限
                );
                $RemiseLog->addMessage($logInfo);
            }
        }

        $RemiseLog->setLevel($level);
        return $RemiseLog;
    }

    /**
     * 決済完了通知時のログ情報生成
     *
     * @param  string  $paymentType  決済種別
     * @param  integer  $level  エラーレベル
     *
     * @return  RemiseLog
     */
    public function createLogForComplete($paymentType, $level = 1)
    {
        $RemiseLog = new \Plugin\RemisePayment\Entity\RemiseLog();

        // カード決済
        if ($paymentType == $this->app['config']['remise_payment_credit'])
        {
            $RemiseLog->setFilename('remise_card.log');
            $RemiseLog->setAction('card complete');
        }
        // マルチ決済
        else if ($paymentType == $this->app['config']['remise_payment_multi'])
        {
            $RemiseLog->setFilename('remise_multi.log');
            $RemiseLog->setAction('multi complete');
        }

        $RemiseLog->setLevel($level);
        return $RemiseLog;
    }

    /**
     * カード決済結果通知時のログ情報生成
     *
     * @param  integer  $level  エラーレベル
     *
     * @return  RemiseLog
     */
    public function createLogForCardResult($level = 1)
    {
        $RemiseLog = new \Plugin\RemisePayment\Entity\RemiseLog();
        $RemiseLog->setFilename('remise_card.log');
        $RemiseLog->setAction('card result');
        $RemiseLog->setLevel($level);
        return $RemiseLog;
    }

    /**
     * マルチ決済収納情報通知時のログ情報生成
     *
     * @param  integer  $level  エラーレベル
     *
     * @return  RemiseLog
     */
    public function createLogForMultiResult($level = 1)
    {
        $RemiseLog = new \Plugin\RemisePayment\Entity\RemiseLog();
        $RemiseLog->setFilename('remise_multi.log');
        $RemiseLog->setAction('multi result');
        $RemiseLog->setLevel($level);
        return $RemiseLog;
    }

    /**
     * ペイクイック情報更新時のログ情報生成
     *
     * @param  integer  $level  エラーレベル
     *
     * @return  RemiseLog
     */
    public function createLogForUpdatePayquick($level = 1)
    {
        $RemiseLog = new \Plugin\RemisePayment\Entity\RemiseLog();
        $RemiseLog->setFilename('remise_card.log');
        $RemiseLog->setAction('Payquick Update');
        $RemiseLog->setLevel($level);
        return $RemiseLog;
    }

    /**
     * ペイクイック情報削除時のログ情報生成
     *
     * @param  integer  $level  エラーレベル
     *
     * @return  RemiseLog
     */
    public function createLogForDeletePayquick($level = 1)
    {
        $RemiseLog = new \Plugin\RemisePayment\Entity\RemiseLog();
        $RemiseLog->setFilename('remise_card.log');
        $RemiseLog->setAction('Payquick Delete');
        $RemiseLog->setLevel($level);
        return $RemiseLog;
    }

    /**
     * ログ出力
     *
     * @param  RemiseLog  $remiseLog  ログ情報
     */
    public function outputRemiseLog(RemiseLog $RemiseLog)
    {
        // ログ出力開始メッセージ
        $this->outputLog(
            $RemiseLog->getFilename(),
            'remise ' . $RemiseLog->getAction() . ' start  ----------',
            $RemiseLog->getLevel()
        );

        // ログ内容出力
        $messages = $RemiseLog->getMessages();
        foreach ($messages as $msg)
        {
            $this->outputLog(
                $RemiseLog->getFilename(),
                $this->makeMessage($msg),
                $RemiseLog->getLevel()
            );
        }

        // ログ出力終了メッセージ
        $this->outputLog(
            $RemiseLog->getFilename(),
            'remise ' . $RemiseLog->getAction() . ' end    ----------',
            $RemiseLog->getLevel()
        );
    }

    /**
     * ログメッセージ生成
     *
     * @param  mixed  $msg  メッセージ
     *
     * @param  string
     */
    protected function makeMessage($msg, $idx = 0)
    {
        $head = '';
        for ($i = 0; $i <= $idx * 2; $i++) $head .= '  ';

        // 配列以外
        if (!is_array($msg) || empty($msg))
        {
            $logMsg = $head;
            if (!empty($msg)) $logMsg .= $msg;
            return $logMsg;
        }

        // 配列
        $logMsg = '';
        if ($idx == 0) $logMsg .= "\n";
        foreach ($msg as $key => $val)
        {
            if (!is_array($val) || empty($val)) {
                $logMsg .= $head;
                $logMsg .= $key . ' => ';
                if (!empty($val)) $logMsg .= $val;
                $logMsg .= "\n";
            } else {
                $logMsg .= $head;
                if ($idx != 0) {
                    $logMsg .= $key . ' => ' . "\n";
                }
                $logMsg .= '(' . "\n";
                $logMsg .= $this->makeMessage($val, $idx+1);
                $logMsg .= $head . ')' . "\n";
            }
        }
        return $logMsg;
    }


    /**
     * ログ出力
     *
     * @param  string  $filename  ファイル名
     * @param  mixed  $msg  メッセージ
     * @param  integer  $level  エラーレベル
     */
    public function outputLog($filename, $msg, $level = 0)
    {
        if ($level == 0) return;

        try
        {
            // ログ出力先を生成
            $path = $this->makeLogDir();

            // ログファイル名を生成
            $logFilename = $this->makeLogFilename($filename);

            $strLevel = '';
            switch ($level)
            {
                // DEBUG
                case 0:
                    $strLevel = 'DEBUG';
                    break;
                // INFO
                case 1:
                    $strLevel = 'INFO';
                    break;
                // WARN
                case 2:
                    $strLevel = 'WARN';
                    break;
                // ERROR
                case 3:
                    $strLevel = 'ERROR';
                    break;
                default:
                    $strLevel = 'INFO';
                    break;
            }

            if (empty($_SERVER['REMOTE_ADDR'])) {
                $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
            }

            // ログメッセージを生成
            $logMessage = $strLevel . '  ' . date('Y/m/d H:i:s') . ' [' . $_SERVER['SCRIPT_NAME'] . '] ';
            if (is_array($msg)) {
                $logMessage .= "\n";
                foreach ($msg as $key => $val)
                {
                    if (empty($val)) continue;
                    $logMessage .= '    ' . $key . ' => ' . $val . "\n";
                }
            } else {
                $logMessage .= $msg;
            }
            $logMessage .= ' from ' . $_SERVER['REMOTE_ADDR'];
            $logMessage .= "\n";

            // ログ出力
            error_log(mb_convert_encoding($logMessage, "UTF-8", "UTF-8,SJIS"), 3, $path . $logFilename);

        }
        catch (\Exception $e)
        {
        }
    }

    /**
     * ログ出力先を生成
     * ※年月毎生成
     *   例）/app/log/RemisePayment/201509/
     *
     * @return  string
     */
    protected function makeLogDir()
    {
        // config取得
        $configService = $this->app['eccube.plugin.service.remise_config'];
        $pluginConfig = $configService->getPluginConfig();

        // ディレクトリ生成
        $path = $this->app['config']['root_dir'] . '/app/log/';
        $path .= $pluginConfig['code'] . '/' . date('Ym') . '/';
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        return $path;
    }

    /**
     * ログファイル名を生成
     * ※拡張子前に日付を挿入
     *   例）remise.log -> remise_20150901.log
     *
     * @return  string
     */
    protected function makeLogFilename($filename)
    {
        $logFilename = $filename . '_' . date('Ymd');
        $idx = strrpos($filename, '.');
        if ($idx) {
            $logFilename = substr($filename, 0, $idx) . '_' . date('Ymd') . substr($filename, $idx);
        }
        return $logFilename;
    }
}
