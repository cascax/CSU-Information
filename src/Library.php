<?php

namespace CSUInformation;

use CSUInformation\Exception\CurlException;
use CSUInformation\Exception\NoSessionException;

class Library extends BaseLoginWebsite{
    private $session;

    /**
     * 登陆
     * @param  string $user     我的图书馆用户名 (学号)
     * @param  string $password 我的图书馆密码
     */
    function login($user, $password) {
        $url = "http://opac.its.csu.edu.cn/NTRdrLogin.aspx";
        $data = "__VIEWSTATE=%2FwEPDwUJOTQ2ODIyODMwD2QWAgIDD2QWAgIBDw8WAh4EVGV4dAUh5oKo6L%2BY5pyq55m75b2V77yM6K%2B35YWI55m75b2V77yBZGQYAQUeX19Db250cm9sc1JlcXVpcmVQb3N0QmFja0tleV9fFgcFCVJibnRSZWNubwUKUmJudENhcmRubwUKUmJudENhcmRubwUJUmJudEVtYWlsBQlSYm50RW1haWwFB1JibnRUZWwFB1JibnRUZWzUaNjmXsmJB4nKEZkOPp0dMyhcmw%3D%3D&__VIEWSTATEGENERATOR=BFEF4FC6&__EVENTVALIDATION=%2FwEWCALAs5qQBQLEhISFCwK1qbSWCwKxi8njCgLY78S0CwLJ8fnVDwLahr8QAuLjh4YMH7vgjTBqB6SDDPmmn12FBXKKGN8%3D&txtName={$user}&txtPassWord={$password}&Logintype=RbntRecno&BtnLogin=%E7%99%BB+%E5%BD%95";
        
        $this->session = $this->loginGetSession($url, $data, self::ASP_PATTERN,
            array('judge'=>'LabMessage', 'pattern'=>'/LabMessage">(.+?)</'));
    }

    /**
     * 获取借阅书籍列表
     * @return array 借阅书籍列表数组
     */
    function getBookLoan() {
        if(empty($this->session))
            throw new NoSessionException();

        $url = "http://opac.its.csu.edu.cn/NTBookLoanRetr.aspx";
        $header = array('Cookie:ASP.NET_SessionId=' . $this->session);
        $curl = curl_init ();
        curl_setopt_array ( $curl, array (
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 2
        ) );
        $result = curl_exec($curl);
        $errno = curl_errno($curl);
        $error = curl_error($curl);
        curl_close ( $curl );
        if($errno)
            throw new CurlException($errno, $error);
        // 获取借阅记录
        if(! preg_match_all('/="14%">(.*)<\/t[\s\S]+?="26%">(.*)<\/t[\s\S]+?="10%">(.*)<\/t[\s\S]+?="7%">(.*)<\/t[\s\S]+?="9%">(.*)<\/t[\s\S]+?="9%">(.*)<\/t[\s\S]+?="9%">(.*)<\/t[\s\S]+?="5%">(.*)<\/t[\s\S]+?="5%">(.*)<\/t[\s\S]+?/', $result, $list))
            return array();
        $listArray = array();
        $count = count($list[1]);
        // 生成记录数组
        for ($i=0; $i<$count; $i++) {
            $listArray[] = array(
                    'barcode' => $list[1][$i],
                    'title' => $list[2][$i],
                    'callno' => $list[3][$i],
                    'author' => $list[4][$i],
                    'place' => $list[5][$i],
                    'lendDate' => $list[6][$i],
                    'returnDate' => $list[7][$i],
                    'price' => $list[8][$i],
                    'renewTime' => $list[9][$i]
                );
        }
        return $listArray;
    }

    /**
     * 续借书籍
     * @param  array $barcode 书籍条码号数组
     * @return boolean
     */
    function renewBook($barcode = NULL) {
        if(empty($this->session))
            throw new NoSessionException();

        // 组合续借书籍条码
        if(empty($barcode)) {
            $books = $this->getBookLoan();
            $barcode = '';
            foreach ($books as $book) {
                if($book['renewTime'] == 0)
                    $barcode .= $book['barcode'] . ';';
            }
            if(! $barcode) return FALSE;
        } elseif(is_array($barcode))
            $barcode = implode(';', $barcode) . ';';
        else
            return FALSE;

        // 发送续借请求
        $url = 'http://opac.its.csu.edu.cn/NTBookloanResult.aspx?barno=' . $barcode;
        $header = array('Cookie:ASP.NET_SessionId=' . $this->session);
        $curl = curl_init ();
        curl_setopt_array ( $curl, array (
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 2
        ) );
        $result = curl_exec($curl);
        $errno = curl_errno($curl);
        $error = curl_error($curl);
        curl_close ( $curl );
        if($errno)
            throw new CurlException($errno, $error);
        return TRUE;
    }
}