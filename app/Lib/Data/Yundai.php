<?php
/**
 * Created by PhpStorm.
 * User: luojia
 * Date: 2017/6/21
 * Time: 9:27
 */
namespace App\Lib\Data;

use Illuminate\Support\Facades\Redis;

class Yundai
{
    const CLIENT_IDENTIFY = 'youfentong';                                   //第三方平台唯一标识
    const URL_INDEX = 'http://i.yunfenba.com/yunfen_youfentong_channel/';   //接口服务于民
    const URL_GETTOKEN = 'gettoken';                                        //获取token
    const URL_GETTASK = 'gettask';                                          //获取订单公众号
    const URL_GETAUTH = 'getauth';                                          //检查公众号是否关注
    const TOKEN_STORE = 'yunfenba_token';                                   //token存放key值
    const DEBUG = true;                                                     //调试模式
    private $token;                                                         //token
    private $redis;                                                         //redis对象


    /***
     * 构造函数
     */
    public function __construct(){
        if(!$this->token){
            if(!$this->redis)
                $this->get_redis_obj();
            if(!$this->redis->exists(self::TOKEN_STORE)) { //不存在token，token过期
                $this->get_token();
            }else{
                $this->token = $this->redis->get(self::TOKEN_STORE);
            }
        }
    }

    /***
     * 获取、更新token
     */
    private function get_token(){
        $param = array('identify'=>self::CLIENT_IDENTIFY);
        $param = http_build_query($param);
        $url = self::URL_INDEX.self::URL_GETTOKEN.'?'.$param;
        $token = $this->curl($url);
        $token = json_decode($token, true);
        if(self::DEBUG) $this->logger('get_token token=>'.var_export($token,TRUE));
        if($token['errcode'] == 0){
            $token = $token['data'];
            $this->token = $token['token'];
            $this->redis->set(self::TOKEN_STORE,$token['token']);
            $this->redis->expire(self::TOKEN_STORE,$token['expires_in']-5);
        }/*else{
            $this->logger('get_token error'.var_export($token,TRUE));
        }*/
    }

    /***
     * 获取订单公众号详情
     * @param $mac
     * @param string $sex
     * @param string $province
     * @param string $city
     * @param string $ip
     * @return bool|mixed|null
     */
    public function get_order($mac, $sex, $province, $city, $ip=''){
        $param = array(
            'token'=>$this->token,
            'mac'=>$mac,
            'sex'=>$sex,
            'province'=>$province,
            'city'=>$city,
            'ip'=>$ip,
        );
        $url = self::URL_INDEX.self::URL_GETTASK;
        $order = $this->http_post($url,$param);
        if(self::DEBUG) $this->logger('get_order url=>'.$url.' post=>'.var_export($param,TRUE));
        if(self::DEBUG) $this->logger('get_order res=>'.var_export($order,TRUE));
        $order = json_decode($order,true);
        if($order['errcode']==0){
            return $order['data'];
        }
        return false;
    }

    /***
     * 检查用户是否关注
     * @param $order_no
     * @param $mac
     * @param $openid
     * @return bool|mixed|null
     */
    public function check_subscribe($order_no,$mac,$openid){
        $param = array(
            'token'=>$this->token,
            'order_no'=>$order_no,
            'mac'=>$mac,
            'openid'=>$openid,
        );
        $url = self::URL_INDEX.self::URL_GETAUTH;
        $result = $this->http_post($url,$param);
        if(self::DEBUG) $this->logger('check_subscribe url=>'.$url.' post=>'.var_export($param,TRUE));
        if(self::DEBUG) $this->logger('check_subscribe res=>'.var_export($result,TRUE));
        $result = json_decode($result,true);
        if($result['errcode']==0){
            return $result['data']['sub_type'];
        }else{
            return 0;
        }
    }

    /***
     * 获取redis对象
     * @return \Predis\Client
     */
    private function get_redis_obj(){
//        $dirpath = dirname(__FILE__);
//        require_once $dirpath.'/../../../Predis/src/Autoloader.php';
//        \Predis\Autoloader::register();
//        $redis = new \Predis\Client(array(
//            'scheme' => 'tcp',
//            'host'   => '10.0.1.180',
//            'port'   => 6379,
//        ));
        $this->redis =  Redis::connection();
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
        $fd=fopen("./yundailog.txt", "a+");
        if(is_array($msg)){
            $msg=var_export($msg,TRUE);
        }
        $str="[".date("Y/m/d H:i:s",time())."]".$msg;
        fwrite($fd, $str."\n");
        fclose($fd);
    }
}