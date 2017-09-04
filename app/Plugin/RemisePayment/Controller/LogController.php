<?php
/*
 * Copyright(c) 2015 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Controller;

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Eccube\Application;
use Eccube\Common\Constant;

use Plugin\RemisePayment\Form\Type\LogType;

/**
 * ログ画面制御
 */
class LogController
{
    /**
     * Application
     */
    public $app;

    /**
     * ログ画面
     */
    public function index(Application $app, Request $request)
    {
        $this->app = $app;

        // config取得
        $configService = $this->app['eccube.plugin.service.remise_config'];
        $pluginConfig = $configService->getPluginConfig();

        // ログディレクトリ
        $path = $this->app['config']['root_dir'] . '/app/log/' . $pluginConfig['code'] . '/';

        // 設定情報
        $formData = array();
        $formData = $this->getLogFiles($formData, $path, 'card'); // カード決済
        $formData = $this->getLogFiles($formData, $path, 'multi'); // マルチ決済

        // 入力フォーム
        $type = new LogType($this->app, $formData);
        $builder = $this->app['form.factory']->createBuilder($type);
        $form = $builder->getForm();
        $log = array();

        $initTab = '1';

        // 読み込みボタン押下時
        if ('POST' === $request->getMethod())
        {
            $form->handleRequest($request);
            if ($form->isValid())
            {
                $formData = $form->getData();
            }

            $formData['card_sel_file'] = $formData['card_files'];
            if (is_array($formData['card_files']))
            {
                $formData['card_sel_file'] = reset($formData['card_files']);
            }
            $formData['multi_sel_file'] = $formData['multi_files'];
            if (is_array($formData['multi_files']))
            {
                $formData['multi_sel_file'] = reset($formData['multi_files']);
            }

            $initTab = $request->request->get('log_load');
        }

        // ログ読込
        $card_log = $this->parseLogFile($formData['card_sel_file'],  $formData['card_count']);
        $multi_log = $this->parseLogFile($formData['multi_sel_file'],  $formData['multi_count']);

        // 画面返却
        return $app['view']->render('RemisePayment/Resource/template/admin/log.twig', array(
            'form' => $form->createView(),
            'init_tab' => $initTab,
            'card_log' => $card_log,
            'multi_log' => $multi_log,
        ));
    }

    /**
     * ログ一覧の読み込み
     *
     * @param  $formData  設定情報
     * @param  $path  ログディレクトリ
     * @param  $key  検索キー
     *
     * @return  ログ一覧
     */
    public function getLogFiles($formData, $path, $key)
    {
        $formData[$key . '_files']    = array(); // ログファイル一覧
        $formData[$key . '_sel_file'] = '';      // 選択ファイル
        $formData[$key . '_count']    = 50;      // 表示ブロック数

        if (file_exists($path))
        {
            // カード決済ログ一覧の読み込み
            $finder = new Finder();
            $finder->files()->in($path);
            $finder->name('remise_' . $key . '_*.log');
            $finder->sort(function ($a, $b) {
                return strcmp($b->getRealpath(), $a->getRealpath());
            });
            $cnt = 0;
            foreach ($finder as $file)
            {
                // ログファイル一覧へ追加
                $formData[$key . '_files'][$file->getFilename()] = $file->getFilename();
                // 初期表示用の選択ファイルを決定
                if (empty($formData[$key . '_sel_file'])) $formData[$key . '_sel_file'] = $file->getFilename();
                // 30ファイル（1ヵ月分）を表示対象
                $cnt++;
                if ($cnt >= 30) break;
            }
        }

        return $formData;
    }

    /**
     * ログファイルの読み込み
     *
     * @param  $filename  ログファイル名
     * @param  $count  ブロック件数
     */
    private function parseLogFile($filename, $count)
    {
        $log = array();
        if (empty($filename)) return $log;
        if ($count <= 0) return $log;

        // config取得
        $configService = $this->app['eccube.plugin.service.remise_config'];
        $pluginConfig = $configService->getPluginConfig();

        // ログディレクトリ
        $path = $this->app['config']['root_dir'] . '/app/log/' . $pluginConfig['code'] . '/';
        if (!file_exists($path)) return $log;

        // 対象ログ特定
        $fullpath = '';
        $finder = new Finder();
        $finder->files()->in($path);
        $finder->name($filename);
        foreach ($finder as $file)
        {
            $fullpath = $file->getRealpath();
            break;
        }
        if (empty($fullpath)) return $log;

        // ログ読み込み
        $logBlock = array();
        $cnt = 0;
        foreach (array_reverse(file($fullpath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)) as $line)
        {
            // 先頭へ追加
            array_unshift($logBlock, $line);
            // ブロック開始位置を特定
            if (strpos($line, 'remise ') !== false)
            {
                if (strpos($line, ' start  ----------') !== false)
                {
                    $log = array_merge($log, $logBlock);
                    $logBlock = array();
                    // 表示ブロック数に達したら処理中断
                    $cnt++;
                    if ($count <= $cnt) break;
                }
            }
        }
        if (!empty($logBlock)) array_push($log, $logBlock);

        return $log;
    }
}
