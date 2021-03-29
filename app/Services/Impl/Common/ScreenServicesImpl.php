<?php

namespace App\Services\Impl\Common;
/**
 * 主要用来过滤筛选条件
 */
class ScreenServicesImpl {
    
    //构造where
    static public function getWhereArray($array,$time=null) {
        $newarray=array();
        foreach ($array as $key => $value) {
            switch ($key) {
                case 'startdate':
                    $newarray[]=array($time,'>=',$value);
                    break;
                case 'enddate':
                    $newarray[]=array($time,'<=',$value);
                    break;
                default:
                    if($value==null||$value==0){
                        
                    }else{
                        $newarray[$key]=$value;
                    }
                    //echo $key;
                    break;
            }
        }
        unset($newarray['type']);
        unset($newarray['page']);
        unset($newarray['pagesize']);
        return $newarray;
    }
    
    
    //常用参数赋初始值
    static public function getArray($array) {
        $newarray=array();
        foreach ($array as $key => $value) {
            switch ($key) {
                case 'startdate':
                    $newarray[$key]=empty($value)?date('Y-m-d',strtotime("-1 week")):$value;
                    break;
                case 'enddate':
                    $newarray[$key]=empty($value)?date('Y-m-d'):$value;
                    break;
                case 'page':
                    $newarray[$key]=empty($value)?1:$value;
                    break;
                case 'pagesize':
                    $newarray[$key]=empty($value)?10:$value;
                    break;
                default:
                    $newarray[$key]=$value;
                    break;
            }
        }
        return $newarray;
    }
    
    //除法
    static public function getDivision($divisor,$dividend,$num=4) {
        if($dividend==0){
            return 0;
        }
        return round($divisor/$dividend,$num);
    }
    
    
    //常用参数赋初始值
    static public function getSetArray($array) {
        $newarray=array();
        foreach ($array as $key => $value) {
            switch ($key) {
                case 'startdate':
                    $newarray[$key]=empty($value)?date('Y-m-d',strtotime("1997-01-01")):$value;
                    break;
                case 'enddate':
                    $newarray[$key]=empty($value)?date('Y-m-d'):$value;
                    break;
                case 'page':
                    $newarray[$key]=empty($value)?1:$value;
                    break;
                case 'pagesize':
                    $newarray[$key]=empty($value)?10:$value;
                    break;
                default:
                    $newarray[$key]=$value;
                    break;
            }
        }
        return $newarray;
    }
}
