<?php 
namespace App\Lib\Data;
use App\Lib\HttpUtils\Tools;
use Illuminate\Support\Facades\Redis;

class Hcforward{
    const API_URL = 'http://login.gwifi.com.cn/cmps';
    //const API_URL = 'http://test.gwifi.com.cn/cmps';
    const METHOD_URL = '/wpi/finish';
    const FOLLOW_URL = '/wpi/follow';
    const ERROR_URL = '/wpi/reload';
    const AUTHKEY = '5447aa08b53e8dac4';
    const OK_FIELD = 'result';
    const OK_VALUE = 'OK';
    const METHOD = 'GET';
    const IS_AJAX = 0;

    public static function get_mac($mac){
        $mac = strtoupper(str_replace(array(':','-','：'),'',$mac));
        if(strlen($mac) == 12) return $mac;
        return '';
    }
    //返回当前的毫秒时间戳
    public static function msectime() {
           list($tmp1, $tmp2) = explode(' ', microtime());
           return (float)sprintf('%.0f', (floatval($tmp1) + floatval($tmp2)) * 1000);
    }
    public static function make_data($arr){
        $data = null;
        if($arr['mac'] && strlen($arr['openid']) == 28)
        {
            $md5_mac = md5(strtoupper($arr['old_mac']).'gfrom');
            unset($arr['old_mac']);

            $data['appid'] = $arr['appid'];
            $data['aid'] = $arr['aid'];
            $data['mac'] = $arr['mac'];
            $data['authkey'] = self::AUTHKEY;
            $data['time'] = time();

            //判断取号是否是单个还是多个
            // $redis = self::get_redis_obj();
            if(Redis::exists($md5_mac)){ //用户未访问
                $data['gfrom'] = Redis::get($md5_mac);
                if(!empty($arr['gfrom'])){
                    if($arr['gfrom'] == 1){
                        $arr['gfrom'] = 1;
                    }elseif ($arr['gfrom'] != 1) {
                        $arr['gfrom'] = 2;
                    }
                 }
            }

        }
        return $data;
    }

    public static function make_data2($arr){
        $data = null;
        if($arr['mac'] && strlen($arr['openid']) == 28)
        {
            // $mac = $arr['mac'];
            // $appid = $arr['appid'];
            // $openid = $arr['openid'];
            // $data2[] = array(
            //     "mac"=>"$mac",
            //     "appid"=>"$appid",
            //     "openid"=>"$openid"
            // );
            $md5_mac = md5(strtoupper($arr['old_mac']).'gfrom');
            unset($arr['old_mac']);
            //判断取号是否是单个还是多个
             // $redis = self::get_redis_obj();
             // Tools::NewLog('md5_mac:'.json_encode($md5_mac));
             // Tools::NewLog('Redis:'.json_encode(Redis::exists($md5_mac)));
             // Tools::NewLog('get:'.json_encode(Redis::get($md5_mac)));
             if(Redis::exists($md5_mac)){ //用户未访问
                 $arr['gfrom'] = Redis::get($md5_mac);
                 if(!empty($arr['gfrom'])){
                    if($arr['gfrom'] == 1){
                        $arr['gfrom'] = 1;
                    }elseif ($arr['gfrom'] != 1) {
                        $arr['gfrom'] = 2;
                    }
                 }
             }

            $follows = array(
                "data"=>array($arr),
            );
            $data['follows'] = json_encode($follows);
            $data['authkey'] = self::AUTHKEY;
            $data['time'] = time();

        }
        return $data;
    }
     //未关注完成跳转页
    public static function error_url($data){
        //判断取号是否是单个还是多个
        $md5_mac = md5(strtoupper($data['mac']).'gfrom');
        // $redis = self::get_redis_obj();
        if(Redis::exists($md5_mac)){ //用户未访问
            $data['gfrom'] = Redis::get($md5_mac);
            if(!empty($arr['gfrom'])){
                if($arr['gfrom'] == 1){
                    $arr['gfrom'] = 1;
                }elseif ($arr['gfrom'] != 1) {
                    $arr['gfrom'] = 2;
                }
             }
        }

        $url = self::API_URL.self::ERROR_URL.'?mac='.$data['mac'].'&bmac='.$data['bmac'].'&authkey='.self::AUTHKEY.'&aid='.$data['aid'].'&gfrom='.$data['gfrom'];

        header('Location:'.$url);
    }
    public static function success($arr,$if_send = false)
    {
         $send_data = self::make_data($arr);
         if(!$if_send){ return $send_data;
         }elseif($send_data){
            return self::send($send_data);
         }
    }
    public static function fail($arr,$if_send = false){
        $send_data = self::make_data($arr);

        if(!$if_send){ return $send_data;}
        elseif($send_data){
          return self::send($send_data);
        }
    }

    public static function success_set($redisarray,$openid,$orderinfo,$if_send = false){
        // Tools::NewLog('redisarray:'.json_encode($redisarray));
        // Tools::NewLog('openid:'.json_encode($openid));
        // Tools::NewLog('orderinfo:'.json_encode($orderinfo));
        
        $content_arr = unserialize($orderinfo['content']);
        $data['appid'] =  $content_arr['appid'];
        $data['openid'] = $openid;
        $mac_list = "";
        for($j=0; $j<6; $j++){
            if($j==0){
                $mac_list .= substr($redisarray['mac'],0,2).':';
            }else{
                $mac_list .= substr($redisarray['mac'],$j*2,2).':';
            }
        }
        // \My\Tools::log($mac_list);
        $data['old_mac'] = $redisarray['mac'];
        $data['mac'] = substr($mac_list,0,17);

        // $obj = new \My\Hcforward();
        // $return_hc = $obj->success_set($data,true);

        $send_data = self::make_data2($data);
       // Tools::NewLog('$data:'.json_encode($data));
       // Tools::NewLog('$send_data:'.json_encode($send_data));
        if(!$if_send){ return $send_data;}
        elseif($send_data){
            return self::send2($send_data);
        }
    }
    
    public static function send($send_data){
        
        if(self::METHOD == 'GET'){
            $res = self::http_get(self::API_URL.self::METHOD_URL.'?'.http_build_query($send_data));
            //mylog('send_data:'.var_export($send_data,true));
            $res = json_decode($res,true);
            //mylog('res:'.var_export($res,true));
            if($res['response'] == 100){
                $auth_url = $res['data']['finishUrl'];
                header('Location:'.$auth_url);
            }
            return false;
        }else{
            $res = self::http_post(self::API_URL.self::METHOD_URL,$send_data);
            //var_dump($res);exit;
            //mylog($send_data);
            $res = json_decode($res,true);
            if($res['response'] == 100){
                $auth_url = $res['data']['finishUrl'];
                header('Location:'.$auth_url);
            }
            return false;
            
        }
    }

    public static function send2($send_data){

        if(self::METHOD == 'GET'){
            $send_data_hc['authkey'] = $send_data['authkey'];
            $send_data_hc['time'] = $send_data['time'];
            
            $res = self::http_get(self::API_URL.self::FOLLOW_URL.'?'.http_build_query($send_data_hc).'&follows='.$send_data['follows']);
            //mylog('send_data:'.var_export($send_data,true));
//            \My\Tools::log($res);
            // $res = json_decode($res,true);
            //mylog('res:'.var_export($res,true));
            // Tools::NewLog('$res:'.json_encode($res));
            return $res;
        }else{
            $res = self::http_post(self::API_URL.self::FOLLOW_URL,$send_data);
            //var_dump($res);exit;
            //mylog($send_data);
            // $res = json_decode($res,true);
            // Tools::NewLog('$res2:'.json_encode($res));
            return $res;

        }
    }


    private static function http_get($url){
        $oCurl = curl_init();
//        \My\Tools::log($url);
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
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
    private static function http_post($url,$param,$post_file=false){
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
        curl_setopt($oCurl, CURLOPT_TIMEOUT,10);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        //var_dump($sContent);exit;
        curl_close($oCurl);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }

    // /*
    //  * 
    //  * @todo 获取redis对象
    //  * 
    //  */
    // private static function get_redis_obj(){
    //     $redis = new Redis();
    //     $redis->connect('192.168.8.222', 6379);
    //     // $redis->auth("j7d9KhLaXP31tP4MQ2nYPeLj358w");
    //     return $redis;
    // }
}
?>
