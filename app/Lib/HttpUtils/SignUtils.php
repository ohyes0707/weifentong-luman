<?php
/**
 * Created by PhpStorm.
 * User: li wei 
 * Date: 2017/5/17
 * Time: 下午12:48
 * class 接口签名加密Utils类
 */

namespace App\Lib\HttpUtils;


class SignUtils
{

    /**
     * 请求校验约定的私钥
     * @var array
     */
    private static $_actionKeyMap = array(
        'common' => '5c7903c3d3fb1c7f3cb9cc612d26a451',   //一个通用密钥，只要不属于特殊指定的密钥，都采用此密钥来进行哈希
    );

    /**
     * 验证签名
     * @param $action
     * @param $data
     * @return bool
     */
    public static function verifySign($actionKey, $data, $method = "GET")
    {
        if (!array_key_exists($actionKey, self::$_actionKeyMap)) {
            $actionKey = 'common';
        }
        if ($method == "GET") {
            $signValue = self::dataSort($data);
        } else if ($method == "POST") {
            $signValue = json_encode($data);
        }
        $key = self::$_actionKeyMap[$actionKey]; // Key指定参数值
        $hMacStr = hash_hmac('sha1', $signValue, $key, true);
        $base64Str = base64_encode($hMacStr);
        $signValue = strtoupper(md5($base64Str));
        //echo $signValue;
        return $signValue;
    }

    /**
     * 数组排序
     * @param $data
     * @return string
     */
    public static function dataSort($data)
    {
        $param = self::paraFilter($data); // 过滤sign签名和空参数
        $param = self::argSort($param); // 数组重组排序
        $signValue = self::parseUrlParamString($param); // URL 参数以“&”拼接字符串
        return $signValue;
    }

    /**
     * 除去数组中的空值和签名参数
     * @param $param 签名参数组
     * return 去掉空值与签名参数后的新签名参数数组
     */
    public static function paraFilter($param)
    {
        $para_filter = array();
        foreach ($param as $key => $val) {
            if ($key == "sign" || $val == "" || $key == "client" || $key == "lm_debug") continue;
            else $para_filter[$key] = $param[$key];
        }
        return $para_filter;
    }

    /**
     * 对数组排序
     * @param $param 排序前的数组
     * return 排序后的数组
     */
    public static function argSort($param)
    {
        ksort($param); //函数对关联数组按照键名进行升序排序
        reset($param); //函数把数组的内部指针指向第一个元素，并返回这个元素的值。
        return $param;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * @param $param 需要拼接的数组
     * return 拼接完成以后的字符串
     */
    public static function parseUrlParamString($param)
    {
        $arg = "";
        if($param) {
            foreach ($param as $key => $val) {
                $arg .= $key . "=" . $val . "&";
            }
            //去掉最后一个&字符
            $arg = substr($arg, 0, count($arg) - 2);

            //如果存在转义字符，那么去掉转义
            if (get_magic_quotes_gpc()) {
                $arg = stripslashes($arg);
            }
        }
        return $arg;
    }


}