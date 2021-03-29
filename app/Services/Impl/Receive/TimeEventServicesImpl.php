<?php
/**
 * Created by PhpStorm.
 * User: luojia
 * Date: 2017/7/13
 * Time: 19:13
 */

namespace App\Services\Impl\Receive;

use App\Models\Count\UnsubEventModel;
use App\Services\UserServices;
use Illuminate\Support\Facades\Redis;

class TimeEventServicesImpl implements UserServices{

    /***
     * 处理取关记录定时任务
     */
    static public function delUnSubEvent(){
        $date = date("Y-m-d",strtotime("-2 husrs"));
        $result_count = UnsubEventModel::getUnSubCount($date);
        if($result_count<1000){
            $count = $result_count;
            self::doUnSubEvent($date,$count);
        }else{
            for ($count=1000; $count < $result_count; $count=$count+1000) {
                self::doUnSubEvent($date,$count);
            }
        }
    }

    /***
     * 处理取关记录方法
     * @param $date
     * @param $count
     */
    static public function doUnSubEvent($date,$count){
        $result = UnsubEventModel::getUnSubEventList($date,$count);
        if(!empty($result)){
            foreach($result as $key => $value){
                $ghids = Redis::hget($value['mac'],'ghid');
                if($ghids==null){
                    $ghids = str_replace($value['ghid'],'',$ghids);
                    Redis::hset($value['mac'],'ghid',$ghids);
                    UnsubEventModel::delUnSubEvent($value['id']);
                }
            }
        }
    }
}