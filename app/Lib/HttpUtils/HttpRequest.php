<?php
/**
 * Created by PhpStorm.
 * User: li wei
 * Date: 2017/5/16
 * Time: 下午11:25
 * Demo
 *     $parameter = array('userId'=>1);
 *     $data = HttpRequest::getApiServices('user','getUserInfo','GET',$parameter);
 *     $data = HttpRequest::getApiServices('user','saveUserInfo','POST',$parameter);
 */
namespace App\Lib\HttpUtils;

use Config;
//use Illuminate\Support\Facades\Log;

class HttpRequest
{

    const TIMEOUT = 20; // 请求响应时间
    const VERSION = 'v1.0'; // 版本
//    const SERVICE_URL = 'http://api.wft.com/'; // 域名
    const SERVICE_URL = 'lumen5.4.com';
    const ACTION_KEY = 'common';

    /**
     * 获取Lumen 服务API方法
     * @param $models 模块
     * @param $function 方法名
     * @param $method 请求方式
     * @param $parameter 参数
     * @return httpData
     */
    static public function getApiServices($models, $function, $method, $parameter=null, $www='http://api.weifentong.com.cn/index.php/')
    {
        $httpData = null;
        $method_array = array('GET', 'POST');
        if (!in_array($method, $method_array)) {
            return $httpData;
        }
        //$url = Config::get('config.API_URL') . $models . '/' . $function . '/' . self::VERSION;
        if($function==''&&$models==''){
            $url = $www;
        } else {
            $url = $www . $models . '/' . $function . '/' . self::VERSION;
        }
        
        $request = array();
        $parameter['timestamp'] = time();
        $signVal = SignUtils::verifySign(self::ACTION_KEY, $parameter, $method);
        if ($method == "GET") {
            $url .= "?" . SignUtils::parseUrlParamString($parameter) . "&sign=" . $signVal;
        } else if ($method == "POST") {
            $url .= "?timestamp=" . $parameter['timestamp'] . "&sign=" . $signVal;
            $request ['postData'] = json_encode($parameter);
        }
        $request['url'] = $url;
        //Log::info('url:'.$url);
        //print_r($url);
        $httpData = self::getCurl($request);
        if ($httpData && isset($httpData['success'], $httpData['data']) && $httpData['success']) {
            return json_decode($httpData['data'],TRUE);
        }
        return $httpData;
    }

    /**
     * http curl 请求
     * @param $request
     * @return array success,httpCode,totalTime,data
     */
    static private function getCurl($request)
    {
        $curlStartTime = microtime(true);

        $ch = curl_init($request ['url']);

        if (stripos($request ['url'], "https://") !== FALSE) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        if (isset($request ['referer']))
            curl_setopt($ch, CURLOPT_REFERER, $request ['referer']);
        if (isset($request ['headers']))
            curl_setopt($ch, CURLOPT_HTTPHEADER, $request ['headers']);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::TIMEOUT);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);
        if (isset($request ['header']))
            curl_setopt($ch, CURLOPT_HEADER, $request ['header']);

        if (isset($request ['postData'])) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request ['postData']);
        }

        $data = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        $curlEndTime = microtime(true);
        $isBool = false;
        if (isset($info['http_code']) && $info['http_code'] == 200) {
            $isBool = true;
        }
        return array(
            'success' => $isBool,
//            'httpCode' => isset($info['http_code']) ? $info['http_code'] : null,
            'totalTime' => round($curlEndTime - $curlStartTime, 2),
            'data' => $data,
            //'info' => $info
        );
    }

}