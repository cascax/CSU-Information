<?php

namespace CSUInformation\Exception;

/**
* 登陆失败
*/
class LoginException extends CSUInfoException
{
    public $error;
    function __construct($error)
    {
        parent::__construct('Error Login: ' . $error, 1);
        $this->error = $error;
    }
}