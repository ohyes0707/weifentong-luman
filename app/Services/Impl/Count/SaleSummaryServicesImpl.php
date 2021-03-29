<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/8/29
 * Time: 9:57
 */
namespace App\Services\Impl\Count;
use App\Models\Count\TaskSummaryModel;
use App\Services\CommonServices;

class SaleSummaryServicesImpl extends CommonServices{
    static public function saleStatistics($start_date,$end_date,$page,$pagesize,$sales){
        $data = TaskSummaryModel::saleStatistics($start_date,$end_date,$page,$pagesize,$sales);
        if($data['data']){
            foreach($data['data'] as $k=>$v){
                $data['data'][$k]['follow'] = $v['new_follow']+$v['old_follow']+0;
                $data['data'][$k]['unfollow'] = $v['new_unfollow']+$v['old_unfollow']+0;
                $data['data'][$k]['cost'] = $v['o_per_price']*($v['new_follow']+$v['old_follow'])+0;
            }
            foreach($data['order'] as $k=>$v){
                $data['order'][$k]['follow'] = 0;
                $data['order'][$k]['unfollow'] = 0;
                $data['order'][$k]['cost'] = 0;
                foreach($data['data'] as $kk=>$vv){
                    if($v['o_uid'] == $vv['o_uid']){
                        $data['order'][$k]['nick_name'] = $vv['nick_name'];
                        $data['order'][$k]['follow'] += $vv['follow'];
                        $data['order'][$k]['unfollow'] += $vv['unfollow'];
                        $data['order'][$k]['cost'] += $vv['cost'];
                    }
                }
            }
            $arr['data'] = $data['order'];
            $arr['count'] = $data['count'];
            $arr['user'] = $data['user'];
            return $arr;
        }else{
            return false;
        }
    }
}