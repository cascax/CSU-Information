<?php

namespace CSUInformation;

use CSUInformation\Exception\NoSessionException;
use CSUInformation\Exception\CurlException;
use CSUInformation\Exception\LoginException;

/**
* 
*/
class EducationalAdmin extends BaseLoginWebsite
{
    private $session;
    
    /**
     * 从信息门户登陆教务
     * @param  string $user     学号
     * @param  string $password 信息门户密码
     */
    function loginFromMyCSU($user, $password) {
        $mycsu = new MyCSU($user, $password);
        $data = $mycsu->gotoJw();
        $url = 'http://csujwc.its.csu.edu.cn/cas.zndx.aspx';
        $header = array(
            'User-Agent: Mozilla/5.0',
            'Referer: http://my.its.csu.edu.cn/SysInfo/SsoService/17'
            );

        $this->session = $this->loginGetSession($url, $data, self::ASP_PATTERN,
            array('judge'=>'MAINFRM', 'pattern'=>'/MAINFRM/', 'reverse'=>true),
            'UTF-8', $header);
        // 上面 教务实际编码为GBK 但在这里没必要转码
    }

    /**
     * 获取学生基本信息
     * @return array 第一行为项名称 第二行是值
     */
    function getMyInfo() {
        if(empty($this->session))
            throw new NoSessionException();
        // 获取的项目 要注意转义
        $items = '学号|姓名|性别|身份证号|出生日期|民族|生源省份|入学成绩|入学时间|培养层次|学习年限|院\(系\)\/部|专业|行政班级';

        $url = 'http://csujwc.its.csu.edu.cn/xsxj/Stu_MyInfo_RPT.aspx';
        $header = array('Cookie:ASP.NET_SessionId=' . $this->session);
        $curl = curl_init ();
        curl_setopt_array ( $curl, array (
            CURLOPT_URL => $url,
            CURLOPT_ENCODING => '',
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
        
        // 处理html字符串
        $infoStr = preg_replace(
                array('/<style>.+<\/style>|&nbsp;| /','/<.+?>/'),
                array('','|'),
                mb_convert_encoding($result, 'UTF-8', 'GBK')
            );
        // 获取项目
        preg_match_all('/('.$items.')\|+([^\|]+)/', $infoStr, $infoArr);
        array_shift($infoArr);
        return $infoArr;
    }
}