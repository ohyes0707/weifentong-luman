<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/6/14
 * Time: 14:40
 */
namespace App\Models\Order;
use App\Models\CommonModel;
use Illuminate\Support\Facades\Redis;

class RedisModel extends CommonModel{
    public static function setRedis($redis){
        foreach($redis as $k=>$v){
            foreach($v as $kk=>$vv){
                Redis::hset($k,$kk,json_encode($vv));
            }
        }
        return true;
    }

    public static function updateRedis($buss,$order,$level){
        $data = Redis::hget($buss,$order);
        $arr = json_decode($data,true);
        $arr['alreadynum'] = $level;
        $json = json_encode($arr);
        $rtn = Redis::hset($buss,$order,$json);
        return $rtn;
    }
}