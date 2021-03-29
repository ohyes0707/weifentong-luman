<?php
/**
 * Created by PhpStorm.
 * User: liujunhuang
 * Date: 2017/10/25
 * Time: 16:45
 */
namespace App\Lib\Data;
class Su2Pass_BH
{

    const URL_INDEX = 'http://res.weifentong.com.cn/';             //接口服务地址
    const URL_PASS_AUTH = 'send_bh.php?';                          //授权URL接口
    const DEBUG = false;                                           //调试模式
    private $mac;       //用户mac
    private $dmac;       //设备mac


    /***
     * 构造函数
     * @param $mac
     * @param $dmac
     */
    public function __construct($mac,$dmac){
        $this->mac = $mac;
        $this->dmac = $dmac;
    }


    /***
     * 放行通知方法
     */
    public function pass_authorize(){
        $data['mac'] = $this->mac;
        $data['bmac'] = $this->dmac;
        $url = self::URL_INDEX.self::URL_PASS_AUTH. http_build_query($data);
        if(self::DEBUG) $this->logger('pass_authorize url=>'.$url);
        $result = $this->http_post($url,$data);
        if(self::DEBUG) $this->logger('pass_authorize res=>'.var_export($result,TRUE));
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

    /****
     * @param $url
     * @param int $timeout
     * @return bool|mixed
     */
    private function http_get($url,$timeout = 2){
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        if($timeout>0){
            curl_setopt($oCurl, CURLOPT_TIMEOUT,$timeout);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
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
        $fd=fopen("./su2pass_bh.txt", "a+");
        if(is_array($msg)){
            $msg=var_export($msg,TRUE);
        }
        $str="[".date("Y/m/d H:i:s",mktime())."]".$msg;
        fwrite($fd, $str."\n");
        fclose($fd);
    }
}