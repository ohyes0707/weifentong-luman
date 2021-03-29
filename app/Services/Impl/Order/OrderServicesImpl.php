<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/5/27
 * Time: 14:12
 */
namespace App\Services\Impl\Order;
use App\Models\Count\OrderTaskModel;
use App\Models\Count\TaskSummaryModel;
use App\Models\Order\DataWifiModel;
use App\Models\Order\OrderModel;
use App\Models\Order\RedisModel;
use App\Models\Order\TaskModel;
use App\Services\OrderServices;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class OrderServicesImpl implements OrderServices{
    static public function getUserInfo($userId)
    {
        // TODO: Implement getUserInfo() method.
    }


    static public function getOrderList($start_date,$end_date,$wx_name,$order_status,$uid,$page,$pagesize)
    {
        $arr = OrderModel::getOrderList($start_date,$end_date,$wx_name,$order_status,$uid,$page,$pagesize);
        if($arr['data']){
            foreach($arr['data'] as $k=>$v){
                $order[] = $v['order_id'];
                $arr['data'][$k]['total_fans'] = 0;
                $arr['data'][$k]['day_fans'] = 0;
                $arr['data'][$k]['un_per'] = '0.00%';
            }
            $sum_arr = TaskSummaryModel::select(DB::raw('sum(new_unfollow_repeat) as new_unfollow'),DB::raw('sum(old_unfollow_repeat) as old_unfollow'),'order_id')
                ->whereIn('order_id',$order)->groupBy('order_id')->get()->toArray();
            if($sum_arr){
                foreach($sum_arr as $k=>$v){
                    $unfollow_arr[] = array(
                        'unfollow'=>$v['new_unfollow']+$v['old_unfollow']+0,
                        'order_id'=>$v['order_id']
                    );
                }
            }
            $buss = OrderTaskModel::select('buss_id','order_id')->whereIn('order_id',$order)->groupBy('order_id')->groupBy('buss_id')->get()->toArray();
            $redis = date('Ymd',time());
            if(!empty($buss)){
                foreach($order as $k=>$v){
                    foreach ($buss as $kk=>$vv){
                        if($vv['order_id'] == $v){
                            $list[$k]['order_id'] = $vv['order_id'];
                            $list[$k]['buss'][] = $vv['buss_id'];
                        }
                    }
                }
                foreach($list as $k=>$v){
                    $total = 0;
                    $day = 0;
                    $unfollow = 0;
                    foreach($list[$k]['buss'] as $kk=>$vv){
                        $unfollow += Redis::hget($redis,'new-'.$v['order_id'].'-'.$vv.'-5-1')+Redis::hget($redis,'old-'.$v['order_id'].'-'.$vv.'-5-1');
                        $total += Redis::get('tot-'.$v['order_id'].'-'.$vv);
                        $day += Redis::hget($redis,'new-'.$v['order_id'].'-'.$vv.'-3-1')+Redis::hget($redis,'old-'.$v['order_id'].'-'.$vv.'-3-1');
                    }
                    foreach($arr['data'] as $kk=>$vv){
                        if($v['order_id'] == $vv['order_id']){
                            $arr['data'][$kk]['total_fans'] = $total;
                            $arr['data'][$kk]['day_fans'] = $day;
                            $arr['data'][$kk]['unfollow'] = $unfollow;
                            if($total != 0)
                                $arr['data'][$kk]['un_per'] = sprintf('%.2f',$unfollow/$total*100).'%';
                        }
                    }
                }
            }
            foreach($arr['data'] as $k=>$v){
                if(isset($unfollow_arr) && !empty($unfollow_arr)){
                    foreach($unfollow_arr as $kk=>$vv){
                        if($v['order_id'] == $vv['order_id'] && $v['total_fans']!=0){
                            if($arr['data'][$k]['un_per']){
                                $arr['data'][$k]['un_per'] = sprintf('%.2f',($vv['unfollow']+$v['unfollow'])/($v['total_fans'])*100).'%';
                            }else{
                                $arr['data'][$k]['un_per'] = sprintf('%.2f',$vv['unfollow']/($v['total_fans'])*100).'%';
                            }
                        }
                    }
                }
            }
            $model['data'] = $arr['data'];
            $model['count']=$arr['count'];
            return $model;
        }else{
            return false;
        }

    }
    static public function getWxList($uid,$start_date,$end_date,$order_status)
    {
        $model = OrderModel::getWxList($uid,$start_date,$end_date,$order_status);
        return $model;
    }

    static public function setRedis($redis){
        $data = RedisModel::setRedis($redis);
        return $data;
    }

    static public function closeTask($appid){
        $data = OrderModel::getInfo($appid);
        if($data){
            //关闭订单
            $order = OrderModel::closeOrder($data['id']);
            //关闭任务
            if($order){
                $task = TaskModel::closeTask($order);
                if($task)
                    return $task;
            }
            return false;
        }
        return false;
    }

    static public function updateRedis($buss,$order,$level){
        $data = RedisModel::updateRedis($buss,$order,$level);
        return $data;
    }

    static public function orderSearch($date,$page,$pagesize){
        $data = TaskModel::orderSearch($date,$page,$pagesize);
        if($data){
            $area = DataWifiModel::orderSearch($data['data']);
            foreach($area as $k=>$v){
                if(isset($v['sex']) && $v['sex'] == 1){
                    $area[$k]['sex'] = '男';
                }elseif(isset($v['sex']) && $v['sex'] == 2){
                    $area[$k]['sex'] = '女';
                }else{
                    $area[$k]['sex'] = '不限';
                }
            }
            $arr['data'] = $area;
            $arr['count'] = $data['count'];
            return $arr;
        }else{
            return false;
        }
    }
}