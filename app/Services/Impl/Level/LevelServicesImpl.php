<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/7/5
 * Time: 15:44
 */
namespace App\Services\Impl\Level;
use App\Models\Order\RedisModel;
use App\Models\Order\TaskModel;
use App\Services\CommonServices;

class LevelServicesImpl extends CommonServices{
    public static function getLevelList($page,$pagesize,$buss_f,$buss_c){
        $data = TaskModel::getLevelList($page,$pagesize,$buss_f,$buss_c);
        return $data;
    }
    public static function setLevel($list){
        foreach($list as $k=>$v){
            $list[$k] = array_reverse(array_reverse($list[$k]),true);
        }
        $data = TaskModel::setLevel($list);
        return $data;
    }
//    public static function getLevelList($page,$pagesize,$buss_name,$wx_name){
//        $data = TaskModel::getLevelList($page,$pagesize,$buss_name,$wx_name);
//        foreach($data['data'] as $k=>$v){
//            $data['data'][$k]['count'] = count($data['data'][$k]);
//        }
//        return $data;
//    }
//    public static function setLevel($buss_id,$order_id,$level){
//        $data = TaskModel::setLevel($buss_id,$order_id,$level);
//        $rtn = RedisModel::updateRedis($buss_id,$order_id,$level);
//        return $data;
//    }
}