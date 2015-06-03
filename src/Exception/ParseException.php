<?php

namespace CSUInformation\Exception;

/**
* HTML解析失败
*/
class ParseException extends \Exception
{
    function __construct($error)
    {
        parent::__construct($error, 4);
    }
}