<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/6/12
 * Time: 9:56
 */
namespace App\Services\Impl\Sell;
use App\Models\Order\OrderModel;
use App\Services\CommonServices;

class SellServicesImpl extends CommonServices{
    static public function sellCount($start_date,$end_date,$wx_name,$uid,$page,$pagesize){
        $data = OrderModel::sellCount($start_date,$end_date,$wx_name,$uid,$page,$pagesize);
        if($data){
            foreach($data['list'] as $k=>$v){
                foreach($data['data'] as $kk=>$vv){
                    if($v['wx_name'] == $vv['wx_name']){
                        if(isset($data['list'][$k]['data'][$vv['date_time']])){
                            $data['list'][$k]['data'][$vv['date_time']]['follow'] += $vv['follow'];
                            $data['list'][$k]['data'][$vv['date_time']]['unfollow'] += $vv['unfollow'];
                            $data['list'][$k]['data'][$vv['date_time']]['money'] += $vv['follow']*$vv['o_per_price'];
                        }else{
                            $data['list'][$k]['data'][$vv['date_time']] = array(
                                'date_time'=>$vv['date_time'],
                                'follow'=>$vv['follow'],
                                'unfollow'=>$vv['unfollow'],
                                'money'=>$vv['follow']*$vv['o_per_price'],
                            );
                        }
                    }
                }
            }
            foreach($data['list'] as $k=>$v){
                if(isset($v['data']) && !empty($v['data'])){
                    foreach($v['data'] as $kk=>$vv){
                        foreach($data['avg_price'] as $key=>$val){
                            if($v['wx_name'] == $val['wx_name'] && $vv['date_time'] == $val['date_time']){
                                $data['list'][$k]['data'][$kk]['price'] = sprintf('%.2f',$val['price']);
                            }
                        }
                    }
                }
            }
            $arr['data'] = $data['list'];
            $arr['count'] = $data['count'];
            return $arr;
        }else{
            return false;
        }
    }
    static public function agentSale($start_date,$end_date,$wx_name,$uid,$page,$pagesize){
        $data = OrderModel::agentSale($start_date,$end_date,$wx_name,$uid,$page,$pagesize);
        if($data){
            foreach($data['list'] as $k=>$v){
                foreach($data['data'] as $kk=>$vv){
                    if($v['wx_name'] == $vv['wx_name']){
                        if(isset($data['list'][$k]['data'][$vv['date_time']])){
                            $data['list'][$k]['data'][$vv['date_time']]['follow'] += $vv['follow'];
                            $data['list'][$k]['data'][$vv['date_time']]['unfollow'] += $vv['unfollow'];
                            $data['list'][$k]['data'][$vv['date_time']]['money'] += $vv['follow']*$vv['o_per_price'];
                        }else{
                            $data['list'][$k]['data'][$vv['date_time']] = array(
                                'date_time'=>$vv['date_time'],
                                'follow'=>$vv['follow'],
                                'unfollow'=>$vv['unfollow'],
                                'money'=>$vv['follow']*$vv['o_per_price'],
                            );
                        }
                    }
                }
            }
            foreach($data['list'] as $k=>$v){
                if(isset($v['data']) && !empty($v['data'])){
                    foreach($v['data'] as $kk=>$vv){
                        foreach($data['avg_price'] as $key=>$val){
                            if($v['wx_name'] == $val['wx_name'] && $vv['date_time'] == $val['date_time']){
                                $data['list'][$k]['data'][$kk]['price'] = sprintf('%.2f',$val['price']);
                            }
                        }
                    }
                }
            }
            $arr['data'] = $data['list'];
            $arr['count'] = $data['count'];
            return $arr;
        }else{
            return false;
        }
    }
}