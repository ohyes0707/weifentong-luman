<?php
/**
 * Created by PhpStorm.
 * User: luojia
 * Date: 2017/6/21
 * Time: 9:27
 */
namespace App\Lib\Data;
use App\Models\Dandan\DandanModel;
use App\Models\Dandan\DandanlogModel;
use App\Lib\HttpUtils\Tools;

class Dandan
{
    const APPID = 264; //第三方id
    const APPKEY = 'EA0DDFF3E603420B4E0814954E845576'; //第三方key32位大写 MD5(ddz-264-@……￥)
    const NOTE_URL = 'http://service.dandanz.com/WebService/WFS/wifi_orders.ashx';
    const KEY = '6TdfcDYWTZ67259Mm2Xb';//请求蛋蛋赚接口需用的key
    /**
     * 检验token
     * @param string $token 签名
     * @param string $timestamp 时间戳
     * @return boolean
     */
    public static  function check_auth($token,$timestamp){

        $new_token = MD5( self::APPID.'_'.self::APPKEY.'_'.$timestamp);
        //echo $new_token;exit;
        if(time()>($timestamp+10)){
            return false;
        }
        if($new_token==$token){
            return true;
        }
        return false;
    }

    /**
     * 生成请求蛋蛋赚接口签名
     * @return string
     */    
    public static function get_keycode($order_id){
        //echo $order_id.'+'.self::APPID.'+'.self::KEY;exit;
        return MD5($order_id.self::APPID.self::KEY);
    }

    /**
     * 通知完成接口
     * @param array $data 参数数据
     * @return boolean
     */
    public static function task_complete($redisarray,$openid,$orderinfo){
        //Tools::NewLog('redisarray:'.json_encode($redisarray));
        //Tools::NewLog('openid:'.json_encode($openid));
        //Tools::NewLog('orderinfo:'.json_encode($orderinfo));
        $userid_arr = explode('*',$redisarray['mac']);
        $order_id = md5($openid.$userid_arr[0]);
        $keycode = self::get_keycode($order_id);
        $content_arr = unserialize($orderinfo['content']);

        $data = array(
            'orderid' => $order_id,
            'appid' =>  $redisarray['bid'],
            'adname' => $content_arr['ghname'],
            'openid' => $openid,
            'userid' => $userid_arr[0], 
            'deviceid'  => $redisarray['bmac'],
            'point' =>  $orderinfo['price'],
            'itime'  => time(),
            'price' =>  $orderinfo['price'],
            'keycode' =>  $keycode,
        );

        $url = self::NOTE_URL.'?'.http_build_query($data);
        //echo $url;exit;
        $res = self::http_get($url);
        $res = json_decode($res,true);
        //Tools::NewLog('resddz:'.$res);
        if($res['Success']==false){
            $data_add['url'] = $url;
            $data_add['time'] = time();
            $data_add['count'] = 1;
            $logic=DandanModel::add_data($data_add);
            return false;
        }   
        return true;
    }

    /**
     * 查询需要重复请求的数据
     * @param array $data 参数数据
     * @return boolean
     */
    public static function rsend_select(){
        $logic=DandanModel::rsend_select(); 
        return $logic;
    }

    /**
     * 重发请求通知
     * @param array $data 重发的记录信息
     * @return boolean
     */
    public static function rsend_note($data){
        $now_time = time();
        // $rsend = M('rsend_event');
        // $rsend_false_log = M('rsend_false_log');
        //print_r($data);exit;
        switch ($data['count']) {
            case 1:
                if($now_time>($data['time']+900)){
                    $res = self::http_get($data['url']);
                    $res = json_decode($res,true);
                    if($res['Success']==false){
                        DandanModel::setInc($data['id']);
                        // $rsend->where('id=%d',$data['id'])->setInc('count',1);
                    }else{
                        DandanModel::rsend_delete($data['id']);
                        // $rsend->where('id=%d',$data['id'])->delete();
                    }
                }
                 break;
            case 2:
                if($now_time>($data['time']+3600)){
                    $res = self::http_get($data['url']);
                    $res = json_decode($res,true);
                    $rs = DandanModel::rsend_delete($data['id']);
                    // $rs = $rsend->where('id=%d',$data['id'])->delete();
                    if($rs&&$res['Success']==false){
                        $arr['url'] = $data['url'];
                        $arr['time'] = time();
                        $arr['channel'] = '蛋蛋赚';
                        $add_log = DandanlogModel::add_log($arr);
                    }
                }
                break;
            default:
                exit('count error');
                break;
        }
    }


    public static function http_get($url){
        $oCurl = curl_init();
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


    /***
     * 记录日志
     * @param $msg
     */
    public function logger($msg){
        $fd=fopen("./dandanlog.txt", "a+");
        if(is_array($msg)){
            $msg=var_export($msg,TRUE);
        }
        $str="[".date("Y/m/d H:i:s",time())."]".$msg;
        fwrite($fd, $str."\n");
        fclose($fd);
    }
}