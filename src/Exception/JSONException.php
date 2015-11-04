<?php

namespace CSUInformation\Exception;

/**
* json解析异常
*/
class JSONException extends CSUInfoException
{
    function __construct()
    {
        parent::__construct("Decode JSON Error.", 5);
    }
}