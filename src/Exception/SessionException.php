<?php

namespace CSUInformation\Exception;

/**
* Session异常
*/
class SessionException extends \Exception
{
    function __construct()
    {
        parent::__construct("Error getting session", 2);
    }
}