<?php
/**
 * Created by PhpStorm.
 * User: luojia
 * Date: 2017/9/6
 * Time: 17:13
 */
namespace App\Lib\Data;
class BiHu
{
    /*
     * 生产环境
        放行地址 http://wefans.bhuwifi.com:8082/wefans/authorize
        qid: 586821
        key: ICxMVFtVXUFCH0EKXlUNJxxTWgI
        测试环境
        放行地址 https://test.unicorn.bhuwifi.com:8082/wefans/authorize
        qid : 140146
        key: MCRLUFdTVEdFH0EKXwMKc0sCVFo
     */
    const QID = '586821';                                                   //合作id
    const KEY = 'ICxMVFtVXUFCH0EKXlUNJxxTWgI';                              //加密key
    const URL_INDEX = 'http://wefans.bhuwifi.com:8082/';             //接口服务
    const URL_PASS_AUTH = 'wefans/authorize';                                //授权URL接口
    const DEBUG = false;                                                     //调试模式
    private $redis;                                                         //redis对象
    private $tid;        //公众号appid
    private $umac;       //用户mac
    private $dmac;       //设备mac
    private $openid;     //openid
    private $timestamp;  //时间戳
    private $flag;       //关注结果 1-新关注 0-已关注
    private $ext1;       //extend参数
    private $sign;       //签名加密串


    /****
     * 构造函数
     * @param $tid
     * @param $umac
     * @param $dmac
     * @param $openid
     * @param $ext1
     */
    public function __construct($tid,$umac,$dmac,$openid,$ext1){
        $this->tid = $tid;
        $this->umac = $umac;
        $this->dmac = $dmac;
        $this->timestamp = time();
        $this->flag = 1;
        $this->openid = $openid;
        $this->ext1 = $ext1;
        $this->sign = $this->make_sign();
    }


    /****
     * 构造签名
     * @return string
     */
    private function make_sign(){
        $data  = array();
        $data['qid'] = self::QID;
        $data['tid'] = $this->tid;
        $data['umac'] = $this->umac;
        $data['dmac'] = $this->dmac;
        $data['tm'] = $this->timestamp;
        $data['flag'] = $this->flag;
        $data['openid'] = $this->openid;
        $data['ext1'] = $this->ext1;
        ksort($data);
        $paramString =  self::parseUrlParamString($data);
        if(self::DEBUG) $this->logger('paramString=>'.var_export($paramString,TRUE));
        $paramString .= self::KEY;
        if(self::DEBUG) $this->logger('paramString=>'.var_export($paramString,TRUE));
        return hash("sha256", $paramString);
    }

    public function pass_authorize(){
        $data['qid'] = self::QID;
        $data['tid'] = $this->tid;
        $data['umac'] = $this->umac;
        $data['dmac'] = $this->dmac;
        $data['tm'] = $this->timestamp;
        $data['flag'] = $this->flag;
        $data['openid'] = $this->openid;
        $data['ext1'] = $this->ext1;
        $data['sign'] = $this->sign;
        $url = self::URL_INDEX.self::URL_PASS_AUTH;
        if(self::DEBUG) $this->logger('pass_authorize url=>'.$url.' post=>'.var_export($data,TRUE));
        $result = $this->http_post($url,$data);
        if(self::DEBUG) $this->logger('pass_authorize res=>'.var_export($result,TRUE));
    }

    /***
     * 拼接参数成字符串
     * @param $param
     * @return string
     */
    static public function parseUrlParamString($param){
        $arg = "";
        if($param) {
            foreach ($param as $key => $val) {
                $arg .= $key . "=" . $val;
            }
        }
        return $arg;
    }

    /***
     * @param $curl
     * @param array $param
     * @param string $ref
     * @param array $header
     * @return mixed
     */
    private function curl($curl ,$param=array(), $ref = "",$header = array()){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $curl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        if(count($param)){
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        }
        if(!empty($ref)){
            curl_setopt ($ch,CURLOPT_REFERER,$ref);
        }
        curl_setopt($ch, CURLOPT_ENCODING, "gzip, deflate, sdch");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        @$data = curl_exec($ch);
        $curl_info = @curl_getinfo($ch);
        $http_code = $curl_info['http_code'];
        curl_close($ch);
        if($http_code == '301' || $http_code == '302'){
            return $curl_info['redirect_url'];
        }
        return $data;
    }

    /***
     * post_curl请求方法
     * @param $url
     * @param $param
     * @param bool $post_file
     * @return bool|mixed
     */
    private function http_post($url,$param,$post_file=false){
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        if (is_string($param) || $post_file) {
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach($param as $key=>$val){
                $aPOST[] = $key."=".urlencode($val);
            }
            $strPOST =  join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($oCurl, CURLOPT_POST,true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }


    /***
     * 记录日志
     * @param $msg
     */
    public function logger($msg){
        $fd=fopen("./bihu.txt", "a+");
        if(is_array($msg)){
            $msg=var_export($msg,TRUE);
        }
        $str="[".date("Y/m/d H:i:s",mktime())."]".$msg;
        fwrite($fd, $str."\n");
        fclose($fd);
    }
}