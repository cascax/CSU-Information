<?php

namespace CSUInformation\uitls;

use CSUInformation\Exception\JSONException;
use CSUInformation\Exception\LoginException;

/**
* 密码管理器
*/
class PasswordManager
{
    private static $handle;
    public $passwordFileName = 'psd.json';
    private $json;
    
    static function getInstance($fileName = NULL) {
        if(!is_object(self::$handle))
            self::$handle = new self($fileName);
        return self::$handle;
    }

    private function __construct($fileName = NULL) {
        if($fileName)
            $this->passwordFileName = $fileName;
        $uri = dirname(__FILE__) . '/';
        $this->passwordFileName = $uri . $this->passwordFileName;
        $fileContent = file_get_contents($this->passwordFileName);
        $this->json = json_decode($fileContent, true);
        if(! $this->json)
            throw new JSONException();
    }

    function getStudentNumber() {
        return $this->getValue('baseinfo', 'studentno', '学号');
    }

    function getCardNumber() {
        return $this->getValue('baseinfo', 'cardno', '校园卡号');
    }

    function getMyCSUPassword() {
        return $this->getValue('mycsu', 'pass', '信息门户密码');
    }

    function getCardPassword() {
        return $this->getValue('card', 'pass', '校园卡查询密码');
    }

    function getLibraryPassword() {
        return $this->getValue('library', 'pass', '我的图书馆密码');
    }

    private function getValue($title, $item, $errorName) {
        if(isset($this->json[$title][$item])
            && $this->json[$title][$item])
            return $this->json[$title][$item];
        throw new LoginException($errorName . '字段为空');
    }
}