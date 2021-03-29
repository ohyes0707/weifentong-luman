<?php
/**
 * Created by PhpStorm.
 * User: liwei
 * Date: 2017/5/17
 * Time: 下午1:29
 * class 接口返回格式Utils类
 */

namespace App\Lib\HttpUtils;


class ApiSuccessWrapper
{
    private static $_arrayErrorMap = array
    (
        '1000'=>'系统服务器繁忙,请稍后再试!',
        '1001'=>'签名错误',
        '1002'=>'请求参数错误',
        '1003'=>'access token 过期或错误',
        '1004'=>'appid错误',
        '1005'=>'代理添加失败或已添加',
        '1006'=>'',
        '1007'=>'',
        '1008'=>'',
        '1009'=>'',
        '1010'=>'',
        '1011'=>'',
        '1012'=>'',
        '1013'=>'',
        '1014'=>'',
        '1015'=>'',
    );

    /**
     * API接口返回格式
     * @param $data
     * @return array
     */
    public static function success($data){
        $result  = array(
            'success' => true,
            'data' => $data
        );
        return $result;

    }

    /**
     * API接口错误返回格式
     * @param int $code 错误编码参照 $_arrayErrorMap
     * @param string $trace 系统错误异常
     * @return array
     */
    public static function fail($code = 1000,$trace = ''){
        $message = '';
        if (isset(self::$_arrayErrorMap[$code])) {
            $message = self::$_arrayErrorMap[$code];
        }
        $result  = array(
            'success' => false,
            'errCode' => $code,
            'message' => $message,
            'trace' => $trace,
            'data'=>null,
        );
        return $result;

    }
}