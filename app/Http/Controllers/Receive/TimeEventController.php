<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/6/30
 * Time: 15:28
 */
namespace App\Http\Controllers\Receive;
use App\Http\Controllers\Controller;
use App\Services\Impl\Receive\TimeEventServicesImpl;
use App\Services\Impl\Common\ThirdDataServicesImpl;
use App\Models\ThirdData\UpSubYunDaiModel;
use App\Services\Impl\Common\WriteBehaviorServicesImpl;
use Illuminate\Support\Facades\Redis;

class TimeEventController extends Controller{
    /***
     * 处理取关事件
     */
    public function dealUnSubEvent(){
        set_time_limit(0);
        TimeEventServicesImpl::delUnSubEvent();
    }

    /***
     * 云袋关注检测任务
     */
    public function yunDaiCheckSubTask(){
        //set_time_limit(0);
        $datetime=date('Ymd');
        $date_time = date('Y-m-d H:i:s',(time()-300));
        $where[] = array( 'date', '<=', $date_time);
        $upsub_list = UpSubYunDaiModel::getUpSubInfoList($where);
        if(!empty($upsub_list)){
            foreach ($upsub_list as $key => $value) {
                if(Redis::hexists($datetime.'openid',$value['openid'])){
                    $where2 = array('id'=>$value['id']);
                    UpSubYunDaiModel::delUpSubInfo($where2);
                    continue;
                }
                $key = ThirdDataServicesImpl::yunDaiCheckSubscribe($value['third_oid'],$value['mac'],$value['openid'],$value['bid'],$value['id']);
                if($key == 1){
                    WriteBehaviorServicesImpl::setFollowRedisCount($value,'new');
                    Redis::hset($datetime.'openid',$value['openid'],1);
                    // WriteBehaviorServicesImpl::setYundaiRedisFollow($value,'new');
                }elseif ($key == 2) {
                    WriteBehaviorServicesImpl::setFollowRedisCount($value,'old');
                    //WriteBehaviorServicesImpl::setYundaiRedisFollow($value,'old');
                } else {
                    
                }
                
            }
        }
    }
}