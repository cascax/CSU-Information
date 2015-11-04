<?php

namespace CSUInformation\Exception;

/**
* Session异常
*/
class SessionException extends CSUInfoException
{
    function __construct()
    {
        parent::__construct("Error getting session using the pattern", 2);
    }
}