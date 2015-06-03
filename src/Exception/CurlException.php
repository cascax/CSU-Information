<?php

namespace CSUInformation\Exception;

/**
* CURL连接异常
*/
class CurlException extends \Exception
{
    public $errno;
    public $error;
    
    function __construct($errno, $error)
    {
        parent::__construct("Curl Error: ({$errno}) {$error}", 3);
        $this->errno = $errno;
        $this->error = $error;
    }
}