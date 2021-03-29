<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public $actionKey = 'common';
    //
    public function __construct()
    {
        // 设置返回类型
        header('Content-type: application/json');

    }
    /*提示并后退*/
    public function alert_back($content){
        header("Content-type: text/html; charset=utf-8");
        echo "<script>alert('" . $content . "');history.go(-1);</script>";
        exit();
    }

    /*** 截取字符
     * @param $begin
     * @param $end
     * @param $str
     * @return string
     */
    public function _cut($begin,$end,$str){
        if($begin == $end){
            $tmp = explode($begin, $str);
            return $tmp[1];
        }
        $b = mb_strpos($str,$begin) + mb_strlen($begin);
        $e = mb_strpos($str,$end) - $b;
        return mb_substr($str,$b,$e);
    }
}
