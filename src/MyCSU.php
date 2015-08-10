<?php

namespace CSUInformation;

use CSUInformation\Exception\NoSessionException;
use CSUInformation\Exception\CurlException;

/**
* 中南大学信息门户
*/
class MyCSU extends BaseLoginWebsite
{
    private $session;

    protected function getUser() {
        return $this->passwordManager->getStudentNumber();
    }

    protected function getPassword() {
        return $this->passwordManager->getMyCSUPassword();
    }

    /**
     * 登陆
     * @param  string $user     学号
     * @param  string $password 信息门户密码
     */
    function login($user, $password) {
        $url = 'http://my.its.csu.edu.cn/';
        $data = "userName={$user}&passWord={$password}&enter=true";

        $this->session = $this->loginGetSession($url, $data, self::ASP_PATTERN,
            array('judge'=>"isPass = 'false'", 'pattern'=>'/对不起，(.+?)"/'));
    }

    /**
     * 从信息门户登入教务
     * @return string 登入POST信息
     */
    function gotoJw() {
        if(empty($this->session))
            throw new NoSessionException();

        $url = 'http://my.its.csu.edu.cn/SysInfo/SsoService/17';
        $header = array('Cookie:ASP.NET_SessionId=' . $this->session);
        $curl = curl_init ();
        curl_setopt_array ( $curl, array (
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 4
        ) );
        $result = curl_exec($curl);
        $errno = curl_errno($curl);
        $error = curl_error($curl);
        curl_close ( $curl );
        if($errno)
            throw new CurlException($errno, $error);
        
        if(preg_match_all('/value="(.+?)"/', $result, $value) == 3) {
            return 'tokenId=' . $value[1][0]
                . '&account=' . $value[1][1]
                . '&Thirdsys=' . $value[1][2];
        }
        return false;
    }

}