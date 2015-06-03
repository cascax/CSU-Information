<?php

namespace CSUInformation\Exception;

/**
* Session不存在
*/
class NoSessionException extends SessionException
{
    function __construct()
    {
        parent::__construct("No session. Maybe need login", 2);
    }
}