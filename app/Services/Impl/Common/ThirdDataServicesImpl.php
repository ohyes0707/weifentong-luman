<?php
namespace App\Services\Impl\Common;

use App\Lib\Data\Yundai;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;
use App\Models\ThirdData\UpSubYunDaiModel;
use App\Models\ThirdData\YunDaiSubLogModel;
use App\Models\Count\FansLogModel;

class ThirdDataServicesImpl {

    /***
     * 云袋判断是否关注方法
     * @param $oid
     * @param $mac
     * @param $openid
     * @param $bid
     * @return bool
     */
    static public function yunDaiCheckSubscribe($oid,$mac,$openid,$bid, $id = null){
        $yundai_class = new Yundai();
        $res = $yundai_class->check_subscribe($oid,$mac,$openid);
        
        //$where = array('oid'=>$oid,'mac'=>$mac,'openid'=>$openid,'bid'=>$bid);
        $where = array('id'=>$id);
        $list = UpSubYunDaiModel::getUpSubInfo($where);
        if($list){
            $rs = YunDaiSubLogModel::addYundaiSubLog($bid,$oid,$mac,$openid,$list['bmac'],$res);
            if($rs){
                UpSubYunDaiModel::delUpSubInfo($where);
            }
        } else {
            return 0;
        }
//        $useropenid = Redis::hget($mac ,'openid');
//        if($res==2&&strpos($useropenid, $openid) === FALSE){
//                Redis::hset($mac, 'openid',$useropenid.','.$openid);
//                YunDaiSubLogModel::addYundaiFansLog($bid,$oid,$mac,$openid,$list['bmac'],$res);
//                return 2;
//        }
        if($res==1) {
            YunDaiSubLogModel::addYundaiFansLog($bid,153,$mac,$openid,$list['bmac'],$res);
            return $res;
        } else {
            return 0;
        }
            
//        if($res==1) {
////            Redis::hset($mac, 'openid',$useropenid.','.$openid);
//            YunDaiSubLogModel::addYundaiFansLog($bid,$oid,$mac,$openid,$list['bmac'],$res);
//            return 1;
//        }else{
//            return 0;
//        }
    }
}
