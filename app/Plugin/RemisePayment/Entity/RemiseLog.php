<?php
/*
 * Copyright(c) 2015 REMISE Corporation. All Rights Reserved.
 * http://www.remise.jp/
 */

namespace Plugin\RemisePayment\Entity;

use Eccube\Entity\AbstractEntity;

/**
 * ルミーズログ情報エンティティ
 */
class RemiseLog extends AbstractEntity
{
    /**
     * filename
     */
    private $filename;

    /**
     * action
     */
    private $action;

    /**
     * level
     */
    private $level;

    /**
     * messages
     */
    private $messages;

    /**
     * コンストラクタ
     */
    public function __construct()
    {
    }

    /**
     * filename の設定
     *
     * @param  string $filename
     * @return RemiseLog
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * filename の取得
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * action の設定
     *
     * @param  string $action
     * @return RemiseLog
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * action の取得
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * level の設定
     *
     * @param  integer $level
     * @return RemiseLog
     */
    public function setLevel($level)
    {
        $this->level = $level;
        return $this;
    }

    /**
     * level の取得
     *
     * @return integer
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * messages の設定
     *
     * @param  array $messages
     * @return RemiseLog
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
        return $this;
    }

    /**
     * messages の設定
     *
     * @param  string $message
     * @return RemiseLog
     */
    public function addMessage($message)
    {
        if (empty($this->messages)) $this->messages = array();
        $this->messages[] = $message;
        return $this;
    }

    /**
     * messages の取得
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getAction();
    }
}
