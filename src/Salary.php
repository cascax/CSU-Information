<?php

namespace CSUInformation;

use CSUInformation\Exception\CurlException;
use CSUInformation\Exception\NoSessionException;

/**
* 个人资金发放信息
*/
class Salary extends BaseLoginWebsite{
    private $session;

    function __construct($user='', $password='') {
        if(!empty($user) && !empty($password))
            $this->login($user, $password);
    }

    /**
     * 登陆
     * @param  string $user     校园卡账户
     * @param  string $password 校园卡查询密码
     */
    function login($user, $password) {
        $url = 'http://infoport.its.csu.edu.cn:807/ywbl.aspx';
        $data = "__VIEWSTATE=%2FwEPDwUKMTIyMDg1NDMxNA9kFgICAQ9kFgICBw8PZBYCHgdvbmNsaWNrBRJyZXR1cm4gY2hlY2tfb2soKTtkZIhHNy6mnCk3wNMH%2FVwkudFdkLCC&__VIEWSTATEGENERATOR=50DF6E9C&__EVENTVALIDATION=%2FwEWBQLMoJ2MCAL%2Fz7thApLOk64PAovO864MArursYYIxJlvKvvduPkPoOqfn5wuimxte7Q%3D&TB_name={$user}&TB_ps={$password}&TB_yzm=0000&Button2=%E7%99%BB%E9%99%86";
        $header = array(
                'Cookie: CheckCode=0000'
            );

        $this->session = $this->loginGetSession($url, $data, self::ASP_PATTERN,
            array('judge'=>'script', 'pattern'=>"/script>alert\('([^']+)'/"),
            'UTF-8', $header);
    }

    /**
     * 获取工资发放记录
     * @return array 发放记录数组
     */
    function getSalary() {
        if(empty($this->session))
            throw new NoSessionException();

        $url = 'http://infoport.its.csu.edu.cn:807/ywbl_zzffb_cx.aspx';
        $header = array(
                'User-Agent: Mozilla/5.0',
                'Cookie: ASP.NET_SessionId=' . $this->session
            );
        $curl = curl_init();
        curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_URL => $url,
                CURLOPT_HTTPHEADER => $header
            ));
        $result = curl_exec($curl);
        $errno = curl_errno($curl);
        $error = curl_error($curl);
        curl_close ($curl);
        if($errno)
            throw new CurlException($errno, $error);
        $result = str_replace('&nbsp;', '', $result);

        if(! preg_match_all("/(?<=<td>)[^<]*(?=<\/td>)/", $result, $content))
            return array();
        return array_chunk($content[0], 10);
    }
    
}