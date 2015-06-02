<?php
/*
 * 校内通告类
 * 抓取校内通告列为图文消息
 */
class Announce {
    private static $url = "http://202.197.61.42";
    private static $appUrl = "http://stu.csu.edu.cn/wechat/app/announce.php?url=";
    private static $tzUrl = "http://tz.its.csu.edu.cn/Home/Release_TZTG_zd/";
    private static $realUrl = "http://tz.its.csu.edu.cn";
    private $announceUrl;
    private $httpHeader = array(
            "Host: tz.its.csu.edu.cn"
        );

    function __construct($isMobile = false) {
    	if($isMobile)
    		$this->announceUrl = self::$appUrl;
    	else
    		$this->announceUrl = self::$tzUrl;
    }

    /**
     * 获取最新通告
     * @param  integer $count 获取数量 默认8条
     * @return array          最近几条通告
     */
    function getAnnouncement($count = 8) {
        $curl = curl_init ();
        curl_setopt_array($curl, array(
                CURLOPT_URL => self::$url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $this->httpHeader,
                CURLOPT_TIMEOUT => 4
            ));
        $html = curl_exec($curl);
        $errno = curl_errno($curl);
        $error = curl_error($curl);
        curl_close ( $curl );
        if($errno) {
            throw new Exception("Curl Error: ({$errno}) {$error}", 3);
        }

        $content = str_replace ( '<img style="border:0px;" alt="" src="../../Content/images/form/release/new.gif" />', '', $html ); // 去掉img
        $regCount = preg_match_all ( "/open \('\/Home\/Release_TZTG_zd\/(\w{32})'[^>]+title='\[([^\]]*)\]([^']+)'>(?:[^\/]+\/){4}[^\/]+(\d{4}\/\d+\/\d+)\s*</", $content, $ret );
        $count = $count > $regCount ? $regCount : $count;
        $items = array ();
        for($i = 0; $i < $count; $i ++) {
            $items [$i] = array (
            	'office' => $ret[2][$i],
                'title' => $ret [3][$i],
                'time' => $ret [4][$i],
                'url' => $this->announceUrl . $ret [1] [$i]
            );
        }
        return $items;
    }
    /**
     * 获取最新电影信息
     * @return array 最新电影信息
     */
    function getFilm() {
        $items = $this->filmList();
        if(empty($items))
            return array();
        $filmUrl = self::$realUrl . '/Home/Release_TZTG_zd/' . $items[0]['url'];

        $curl = curl_init ();
        curl_setopt_array($curl, array(
                CURLOPT_URL => $filmUrl,
                CURLOPT_HTTPHEADER => $this->httpHeader,
                CURLOPT_RETURNTRANSFER => true
            ));
        $html = curl_exec($curl);
        $errno = curl_errno($curl);
        $error = curl_error($curl);
        curl_close ( $curl );
        if($errno) {
            throw new Exception("Curl Error: ({$errno}) {$error}", 3);
        }

        if(preg_match ( '/<img[^>]+src="(.+)"/', $html, $image ))
            $image = self::$realUrl . $image[1];
        else
            $image = '';

        if(preg_match ( '/为丰富[\s\S]+观看。/', $html, $desc )) {
            $desc = preg_replace ( '/<[^>]+>/', '', $desc[0] );
            preg_match ( '/\d+月\d+日/', $desc, $date );
        } else
            $desc = '详情点击进入。';
        $desc .= ' - 发布日期 ' . $items[0]['date'];

        if(! $date[0]) $date[0] = '';
        if(preg_match ( '/《(.+)》/', $html, $title ))
            $title = $title[0] . $date[0];
        else
            $title = $date[0] . '影讯';

        return array(
        		'title' => $title,
        		'desc' => $desc,
        		'image' => $image,
        		'url' => $this->announceUrl . $items[0]['url']
        	);
    }

    function filmList() {
        $curl = curl_init ();
        curl_setopt_array($curl, array(
        		CURLOPT_URL => self::$url . '/Home/Release_TZTG/0-%E7%94%B5%E5%BD%B1',
        		CURLOPT_HTTPHEADER => $this->httpHeader,
        		CURLOPT_RETURNTRANSFER => true,
        		CURLOPT_TIMEOUT => 4
        	));
        $html = curl_exec($curl);
        $errno = curl_errno($curl);
        $error = curl_error($curl);
        curl_close ( $curl );
        if($errno) {
            throw new Exception("Curl Error: ({$errno}) {$error}", 3);
        }

        $content = str_replace ( '<img style="border:0px;" alt="" src="../../Content/images/form/release/new.gif" />', '', $html ); // 去掉img
        preg_match_all ( "/open \('\/Home\/Release_TZTG_zd\/(\w{32})'[^>]+title='\[校团委\]([^']+)'>(?:[^\/]+\/){4}[^\/]+(\d{4}\/\d+\/\d+)\s*</", $content, $ret );
        $items = array();
        $count = count($ret[1]);
        $count = $count > 8 ? 8 : $count;

        for($i = 0; $i < $count; $i ++) {
            $items [$i] = array (
                'title' => $ret [2] [$i],
                'date' => $ret [3] [$i],
                'url' => $ret [1] [$i]
            );
        }
        return $items;
    }
}