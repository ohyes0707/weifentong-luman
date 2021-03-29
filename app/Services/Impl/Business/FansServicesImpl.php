<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/8/4
 * Time: 10:56
 */
namespace App\Services\Impl\Business;
use App\Models\Business\MoneyModel;
use App\Models\Count\FansLogModel;
use App\Models\Count\TaskSummaryModel;
use App\Services\CommonServices;

class FansServicesImpl extends CommonServices{
    public static function fansEarn($start_date,$end_date,$page,$pagesize,$buss){
        $arr = MoneyModel::fansEarn($start_date,$end_date,$page,$pagesize,$buss);
        return $arr;
        $data = TaskSummaryModel::fansEarn($start_date,$end_date,$page,$pagesize,$buss);
        if($data){
            if(isset($data['count'])){
                $num = $data['count'];
                $father = $data['father'];
                unset($data['count']);
                unset($data['father']);
            }
            foreach($data as $k=>$v){
                if(isset($v['data']) && !empty($v['data'])){
                    foreach($v['data'] as $kk=>$vv){
                        $data[$k]['count'][] = array(
                            'follow'=>$vv['new_follow_repeat']+$vv['old_follow_repeat']+0,
                            'unfollow'=>$vv['new_unfollow_repeat']+$vv['old_unfollow_repeat']+0,
                            'date_time'=>$vv['date_time'],
                            'buss_id'=>$vv['buss_id'],
                            'o_per_price'=>$vv['o_per_price'],
                        );
                    }
                    unset($data[$k]['data']);
                }
            }
            foreach($data as $k=>$v){
                if(isset($v['count']) && !empty($v['count'])){
                    foreach($v['count'] as $kk=>$vv){
                        if($v['cost_price']){
                            $data[$k]['count'][$kk]['cost'] = $vv['follow']*$v['cost_price'];
                        }else{
                            $data[$k]['count'][$kk]['cost'] = $vv['follow']*$vv['o_per_price'];
                        }
                    }
                }
            }
            foreach($data as $k=>$v){
                if(isset($v['count']) && !empty($v['count'])){
                    foreach($v['count'] as $kk=>$vv){
                        if(isset($data[$k]['count'][$vv['date_time']])){
                            $data[$k]['count'][$vv['date_time']]['follow'] += $vv['follow'];
                            $data[$k]['count'][$vv['date_time']]['unfollow'] += $vv['unfollow'];
                            $data[$k]['count'][$vv['date_time']]['cost'] += $vv['cost'];
                        }else{
                            $data[$k]['count'][$vv['date_time']] = array(
                                'follow'=>$vv['follow'],
                                'unfollow'=>$vv['unfollow'],
                                'date_time'=>$vv['date_time'],
                                'buss_id'=>$vv['buss_id'],
                                'cost'=>$vv['cost'],
                            );
                        }
                        unset($data[$k]['count'][$kk]);
                    }
                }
            }
            $data['count'] = $num;
            $data['father'] = $father;
            return $data;
        }else{
            return false;
        }

    }
    public static function fansEarn_child($start_date,$end_date,$page,$pagesize,$buss){
        $data = MoneyModel::fansEarn_child($start_date,$end_date,$page,$pagesize,$buss);
        return $data;
        $data = TaskSummaryModel::fansEarn_child($start_date,$end_date,$page,$pagesize,$buss);
        foreach($data['data'] as $k=>$v){
            if(isset($v['count']) && !empty($v['count'])){
                foreach($v['count'] as $kk=>$vv){
                    $data['data'][$k]['count'][$kk]['follow'] = $vv['new_follow_repeat']+$vv['old_follow_repeat'];
                    $data['data'][$k]['count'][$kk]['unfollow'] = $vv['new_unfollow_repeat']+$vv['old_unfollow_repeat'];
                    if($data['price']){
                        $data['data'][$k]['count'][$kk]['cost'] = $data['price']*($vv['new_follow_repeat']+$vv['old_follow_repeat']);
                    }else{
                        $data['data'][$k]['count'][$kk]['cost'] = $vv['o_per_price']*($vv['new_follow_repeat']+$vv['old_follow_repeat']);
                    }
                }
            }
        }
        foreach($data['data'] as $k=>$v){
            $data['data'][$k]['follow'] = 0;
            $data['data'][$k]['unfollow'] = 0;
            $data['data'][$k]['cost'] = 0;
            if(isset($v['count']) && !empty($v['count'])){
                foreach($v['count'] as $kk=>$vv){
                    $data['data'][$k]['follow'] += $vv['follow'];
                    $data['data'][$k]['unfollow'] += $vv['unfollow'];
                    $data['data'][$k]['cost'] += $vv['cost'];
                }
                unset($data['data'][$k]['count']);
            }
        }
        $data['total'][0]['follow'] = 0;
        $data['total'][0]['unfollow'] = 0;
        $data['total'][0]['cost'] = 0;
        foreach($data['data'] as $k=>$v){
            $data['total'][0]['follow'] += $v['follow'];
            $data['total'][0]['unfollow'] += $v['unfollow'];
            $data['total'][0]['cost'] += $v['cost'];
        }
        $arr['data'] = $data['data'];
        $arr['count'] = $data['count'];
        $arr['total'] = $data['total'];
        return $arr;
    }

    //订单粉丝详情
    static public function orderFans($order_id,$start_date,$end_date){
        $data = FansLogModel::orderFans($order_id,$start_date,$end_date);
        return $data;
    }
}