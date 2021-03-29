<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/7/20
 * Time: 11:01
 */
namespace App\Services\Impl\Count;
use App\Models\Count\TaskSummaryModel;
use App\Services\CommonServices;

class RevenueSummaryServicesImpl extends CommonServices{
    public static function revenueCountBuss($start_date,$end_date,$user,$page,$pagesize,$buss,$newpage,$newpagesize){
        if($buss){
            $data = TaskSummaryModel::revenueCount_oneBuss($start_date,$end_date,$page,$pagesize,$buss);
        }else{
            $data = TaskSummaryModel::revenueCountBuss($start_date,$end_date,$page,$pagesize,$newpage,$newpagesize);
        }
        if($data){
            if(isset($data['data']) && !empty($data['data'])){
                foreach($data['buss'] as $k=>$v){
                    foreach($data['data'] as $kk=>$vv){
                        if(!isset($data['data'][$kk]['cost_price']))
                            $data['data'][$kk]['cost_price'] = 0;
                        if(!isset($data['data'][$kk]['reduce_percent']))
                            $data['data'][$kk]['reduce_percent'] = 0;
//                        $data['data'][$kk]['cost_price'] = 0.6;
//                        $data['data'][$kk]['reduce_percent'] = 0.1;
                        if($v['id'] == $vv['buss_id']){
                            $data['data'][$kk]['cost_price'] = $v['cost_price']+0;
                            $data['data'][$kk]['reduce_percent'] = $v['reduce_percent']*0.01+0;
                        }
                    }
                }
                foreach($data['data'] as $k=>$v){
                    if($user==0){
                        $arr[] = array(
                            'buss_id'=>$v['buss_id'],
                            'order_id'=>$v['order_id'],
                            'o_per_price'=>$v['o_per_price'],
                            'date_time'=>$v['date_time'],
                            'follow'=>$v['new_follow_repeat']+$v['old_follow_repeat']+0,
                            'float'=>($v['new_follow_repeat']+$v['old_follow_repeat']+0)*$v['o_per_price'],
                            'cost'=>($v['new_follow_repeat']+$v['old_follow_repeat']+0)*(1-$v['reduce_percent'])*$v['cost_price'],
                            'rest'=>($v['new_follow_repeat']+$v['old_follow_repeat']+0)*$v['reduce_percent']*$v['o_per_price'],
                            'unfollow'=>$v['new_unfollow_repeat']+$v['old_unfollow_repeat']
                        );
                    }elseif($user==1){
                        $arr[] = array(
                            'buss_id'=>$v['buss_id'],
                            'order_id'=>$v['order_id'],
                            'o_per_price'=>$v['o_per_price'],
                            'date_time'=>$v['date_time'],
                            'follow'=>$v['new_follow_repeat']+0,
                            'float'=>($v['new_follow_repeat']+0)*$v['o_per_price'],
                            'cost'=>($v['new_follow_repeat']+0)*(1-$v['reduce_percent'])*$v['cost_price'],
                            'rest'=>($v['new_follow_repeat']+0)*$v['reduce_percent']*$v['o_per_price'],
                            'unfollow'=>$v['new_unfollow_repeat']
                        );
                    }else{
                        $arr[] = array(
                            'buss_id'=>$v['buss_id'],
                            'order_id'=>$v['order_id'],
                            'o_per_price'=>$v['o_per_price'],
                            'date_time'=>$v['date_time'],
                            'follow'=>$v['old_follow_repeat']+0,
                            'float'=>($v['old_follow_repeat']+0)*$v['o_per_price'],
                            'cost'=>($v['old_follow_repeat']+0)*(1-$v['reduce_percent'])*$v['cost_price'],
                            'rest'=>($v['old_follow_repeat']+0)*$v['reduce_percent']*$v['o_per_price'],
                            'unfollow'=>$v['old_unfollow_repeat']
                        );
                    }
                }
                foreach($data['buss'] as $k=>$v){
                    foreach($arr as $kk=>$vv){
                        if($vv['buss_id'] == $v['id']){
                            $data['buss'][$k]['count'][] = $arr[$kk]+array('pbid'=>$v['pbid']);
                            unset($arr[$kk]);
                        }
                    }
                }
                foreach($data['buss'] as $k=>$v){
                    if(isset($v['count'])){
                        foreach($v['count'] as $kk=>$vv){
                            if(isset($data['buss'][$k]['list'][$vv['date_time'].$vv['buss_id']])){
                                $data['buss'][$k]['list'][$vv['date_time'].$vv['buss_id']]['follow'] += $vv['follow'];
                                $data['buss'][$k]['list'][$vv['date_time'].$vv['buss_id']]['float'] += $vv['float'];
                                $data['buss'][$k]['list'][$vv['date_time'].$vv['buss_id']]['cost'] += $vv['cost'];
                                $data['buss'][$k]['list'][$vv['date_time'].$vv['buss_id']]['rest'] += $vv['rest'];
                            }else{
                                $data['buss'][$k]['list'][$vv['date_time'].$vv['buss_id']] = $data['buss'][$k]['count'][$kk];
                            }
                        }
                        unset($data['buss'][$k]['count']);
                    }
                }
                foreach($data['buss'] as $k=>$v){
                    if($v['pbid'] == 0){
                        $father[] = $data['buss'][$k];
                    }else{
                        $child[] = $data['buss'][$k];
                    }
                }
                if(isset($father) && !empty($father))
                    $f_arr = $father;
                if(isset($child) && !empty($child))
                    $c_arr = $child;
                if(isset($father) && !empty($father)){
                    foreach($father as $k=>$v){
                        if(isset($child) && !empty($child)){
                            foreach($child as $kk=>$vv){
                                if($v['id'] == $vv['pbid']){
                                    $father[$k]['child'][] = $child[$kk];
                                }
                            }
                        }
                    }
                }
                foreach($father as $k=>$v){
                    $child_arr = array();
                    if(isset($v['child']) && !empty($v['child'])){
                        foreach($v['child'] as $kk=>$vv){
                            if(isset($vv['list']) && !empty($vv['list'])){
                                foreach($vv['list'] as $key=>$val){
                                    if(isset($child_arr[$val['date_time'].$v['id']])){
                                        $child_arr[$val['date_time'].$v['id']]['follow'] += $val['follow'];
                                        $child_arr[$val['date_time'].$v['id']]['float'] += $val['float'];
                                        $child_arr[$val['date_time'].$v['id']]['cost'] += $val['cost'];
                                        $child_arr[$val['date_time'].$v['id']]['rest'] += $val['rest'];
                                        unset($father[$k]['child'][$kk]['list'][$key]);
                                    }else{
                                        $child_arr[$val['date_time'].$v['id']] = $val;
                                        unset($father[$k]['child'][$kk]['list'][$key]);
                                    }
                                }
                            }
                        }
                        $father[$k]['child'] = $child_arr;
                    }
                }
                foreach($father as $k=>$v){
                    if(isset($v['list']) && isset($v['child'])){
                        foreach($v['list'] as $kk=>$vv){
                            foreach($v['child'] as $key=>$val){
                                if($kk == $key){
                                    $father[$k]['list'][$kk]['follow'] += $val['follow'];
                                    $father[$k]['list'][$kk]['float'] += $val['float'];
                                    $father[$k]['list'][$kk]['cost'] += $val['cost'];
                                    $father[$k]['list'][$kk]['rest'] += $val['rest'];
                                    $father[$k]['list'][$kk]['unfollow'] += $val['unfollow'];
                                }elseif(!isset($father[$k]['list'][$key])){
                                    $father[$k]['list'][$key] = $val;
                                }
                            }
                        }
                    }elseif(isset($v['child']) && !isset($v['list'])){
                        $father[$k]['list'] = $father[$k]['child'];
                    }
                    unset($father[$k]['child']);
                    if(isset($father[$k]['list'])){
                        krsort($father[$k]['list']);
                    }
                }
                if($buss){
                    if(isset($c_arr) && !empty($c_arr)){
                        foreach($c_arr as $v){
                            $f_arr[] = $v;
                        }
                    }
                    $list['list'] = $f_arr;
                }
                $list['data'] = $father;
                $list['num'] = $data['num'];
            }
            $list['buss'] = $data['list'];
            if(!empty($newpagesize)){
                $list['date'] = $data['date_data'];
                $list['plat_num'] = $data['plat_num'];
            }
            return $list;
        }else{
            $list['buss'] = $data['list'];
            return $list;
        }

    }
    public static function revenueCountBussExcel($start_date,$end_date,$user,$buss){
        if($buss){
            $data = TaskSummaryModel::revenueCount_oneBussExcel($start_date,$end_date,$buss);
        }else{
            $data = TaskSummaryModel::revenueCountBussExcel($start_date,$end_date);
        }
        if(isset($data['data']) && !empty($data['data'])){
            foreach($data['buss'] as $k=>$v){
                foreach($data['data'] as $kk=>$vv){
                    $data['data'][$kk]['cost_price'] = 0.6;
                    $data['data'][$kk]['reduce_percent'] = 0.1;
                    if($v['id'] == $vv['buss_id']){
                        $data['data'][$kk]['cost_price'] = $v['cost_price'];
                        $data['data'][$kk]['reduce_percent'] = $v['reduce_percent']*0.01;
                    }
                }
            }
        }
        foreach($data['data'] as $k=>$v){
            if($user==0){
                $arr[] = array(
                    'buss_id'=>$v['buss_id'],
                    'order_id'=>$v['order_id'],
                    'o_per_price'=>$v['o_per_price'],
                    'date_time'=>$v['date_time'],
                    'follow'=>$v['new_follow_repeat']+$v['old_follow_repeat']+0,
                    'float'=>($v['new_follow_repeat']+$v['old_follow_repeat']+0)*$v['o_per_price'],
                    'cost'=>($v['new_follow_repeat']+$v['old_follow_repeat']+0)*(1-$v['reduce_percent'])*$v['cost_price'],
                    'rest'=>($v['new_follow_repeat']+$v['old_follow_repeat']+0)*$v['reduce_percent']*$v['o_per_price']
                );
            }elseif($user==1){
                $arr[] = array(
                    'buss_id'=>$v['buss_id'],
                    'order_id'=>$v['order_id'],
                    'o_per_price'=>$v['o_per_price'],
                    'date_time'=>$v['date_time'],
                    'follow'=>$v['new_follow_repeat']+0,
                    'float'=>($v['new_follow_repeat']+0)*$v['o_per_price'],
                    'cost'=>($v['new_follow_repeat']+0)*(1-$v['reduce_percent'])*$v['cost_price'],
                    'rest'=>($v['new_follow_repeat']+0)*$v['reduce_percent']*$v['o_per_price']
                );
            }else{
                $arr[] = array(
                    'buss_id'=>$v['buss_id'],
                    'order_id'=>$v['order_id'],
                    'o_per_price'=>$v['o_per_price'],
                    'date_time'=>$v['date_time'],
                    'follow'=>$v['old_follow_repeat']+0,
                    'float'=>($v['old_follow_repeat']+0)*$v['o_per_price'],
                    'cost'=>($v['old_follow_repeat']+0)*(1-$v['reduce_percent'])*$v['cost_price'],
                    'rest'=>($v['old_follow_repeat']+0)*$v['reduce_percent']*$v['o_per_price']
                );
            }
        }
        foreach($data['buss'] as $k=>$v){
            foreach($arr as $kk=>$vv){
                if($vv['buss_id'] == $v['id']){
                    $data['buss'][$k]['count'][] = $arr[$kk]+array('pbid'=>$v['pbid']);
                }
                if(isset($data['buss'][$k][$v['username']]) && !empty($data['buss'][$k][$v['username']])){
                    foreach($v[$v['username']] as $key=>$value){
                        if($vv['buss_id'] == $value['id']){
                            $data['buss'][$k][$v['username']][$key]['count'][] = $arr[$kk]+array('pbid'=>$v['pbid']);
                        }
                    }
                }
            }
        }
        foreach($data['buss'] as $k=>$v){
            if(isset($v['count'])){
                foreach($v['count'] as $kk=>$vv){
                    if(isset($data['buss'][$k]['list'][$vv['date_time'].$vv['buss_id']])){
                        $data['buss'][$k]['list'][$vv['date_time'].$vv['buss_id']]['follow'] += $vv['follow'];
                        $data['buss'][$k]['list'][$vv['date_time'].$vv['buss_id']]['float'] += $vv['float'];
                        $data['buss'][$k]['list'][$vv['date_time'].$vv['buss_id']]['cost'] += $vv['cost'];
                        $data['buss'][$k]['list'][$vv['date_time'].$vv['buss_id']]['rest'] += $vv['rest'];
                    }else{
                        $data['buss'][$k]['list'][$vv['date_time'].$vv['buss_id']] = $data['buss'][$k]['count'][$kk];
                    }
                }
                unset($data['buss'][$k]['count']);
            }
        }
        foreach($data['buss'] as $k=>$v){
            if($v['pbid'] == 0){
                $father[] = $data['buss'][$k];
            }else{
                $child[] = $data['buss'][$k];
            }
        }
        if(isset($father) && !empty($father))
            $f_arr = $father;
        if(isset($child) && !empty($child))
            $c_arr = $child;
        if(isset($father) && !empty($father)){
            foreach($father as $k=>$v){
                if(isset($child) && !empty($child)){
                    foreach($child as $kk=>$vv){
                        if($v['id'] == $vv['pbid']){
                            $father[$k]['child'][] = $child[$kk];
                        }
                    }
                }
            }
        }
        foreach($father as $key=>$val){
            if(isset($val['child'])){
                foreach($val['child'] as $k=>$v){
                    if(isset($v['list'])){
                        foreach($v['list'] as $kk=>$vv){
                            if(isset($c_child[$vv['date_time'].$vv['pbid']])){
                                $c_child[$vv['date_time'].$vv['pbid']]['follow'] += $vv['follow'];
                                $c_child[$vv['date_time'].$vv['pbid']]['float'] += $vv['float'];
                                $c_child[$vv['date_time'].$vv['pbid']]['cost'] += $vv['cost'];
                                $c_child[$vv['date_time'].$vv['pbid']]['rest'] += $vv['rest'];
                            }else{
                                $c_child[$vv['date_time'].$vv['pbid']] = $vv;
                            }
                        }
                    }
                }
                $father[$key]['child'] = $c_child;
            }

        }
        foreach($father as $k=>$v){
            if(isset($v['list']) && isset($v['child'])){
                foreach($v['list'] as $kk=>$vv){
                    foreach($v['child'] as $key=>$val){
                        if($kk==$key){
                            $father[$k]['list'][$kk]['follow'] += $val['follow'];
                            $father[$k]['list'][$kk]['float'] += $val['float'];
                            $father[$k]['list'][$kk]['cost'] += $val['cost'];
                            $father[$k]['list'][$kk]['rest'] += $val['rest'];
                            unset($father[$k]['child'][$key]);
                        }
                    }
                }
            }
        }
        foreach($father as $k=>$v){
            if(isset($v['list']) && isset($v['child'])){
                $father[$k]['list'] += $v['child'];
                unset($father[$k]['child']);
            }
            if(isset($v['child']) && !isset($v['list'])){
                $father[$k]['list'] = $father[$k]['child'];
                unset($father[$k]['child']);
            }
            krsort($father[$k]['list']);
        }
        if($buss){
            if(isset($c_arr) && !empty($c_arr)){
                foreach($c_arr as $v){
                    $f_arr[] = $v;
                }
            }
            $list['list'] = $f_arr;
        }
        $list['data'] = $father;
        return $list;
    }

    public static function revenueDetail_buss($bid,$start_date,$end_date,$page,$pagesize,$wx_id){
        $data = TaskSummaryModel::revenueDetail_buss($bid,$start_date,$end_date,$page,$pagesize,$wx_id);
        if($data){
            foreach($data['data'] as $k=>$v){
                if(isset($v['data'])){
                    foreach($v['data'] as $kk=>$vv){
                        $data['data'][$k]['data'][$kk]['follow'] = $vv['new_follow_repeat']+$vv['old_follow_repeat']+0;
                        $num = $data['data'][$k]['data'][$kk]['follow']-($data['data'][$k]['data'][$kk]['follow']*$vv['reduce_percent']*0.01);
                        if($num<0)
                            $num = 0;
                        $data['data'][$k]['data'][$kk]['real_follow'] = $num;
                        $data['data'][$k]['data'][$kk]['float'] = $data['data'][$k]['data'][$kk]['follow']*$vv['per_price'];
                        $data['data'][$k]['data'][$kk]['rest'] = $data['data'][$k]['data'][$kk]['follow']*$vv['reduce_percent']*0.01;
                    }
                }
            }
            foreach($data['data'] as $k=>$v){
                if(isset($v['data']) && !empty($v['data'])){
                    foreach($v['data'] as $kk=>$vv){
                        if(isset($data['data'][$k]['data'][$vv['date_time'].$vv['buss_id']])){
                            $data['data'][$k]['data'][$vv['date_time'].$vv['buss_id']]['follow'] += $vv['follow'];
                            $data['data'][$k]['data'][$vv['date_time'].$vv['buss_id']]['real_follow'] += $vv['real_follow'];
                            $data['data'][$k]['data'][$vv['date_time'].$vv['buss_id']]['float'] += $vv['float'];
                            $data['data'][$k]['data'][$vv['date_time'].$vv['buss_id']]['rest'] += $vv['rest'];
                            unset($data['data'][$k]['data'][$kk]);
                        }else{
                            $data['data'][$k]['data'][$vv['date_time'].$vv['buss_id']] = array(
                                'wx_id'=>$vv['wx_id'],
                                'order_id'=>$vv['order_id'],
                                'buss_id'=>$vv['buss_id'],
                                'date_time'=>$vv['date_time'],
                                'follow'=>$vv['follow'],
                                'real_follow'=>$vv['real_follow'],
                                'float'=>$vv['float'],
                                'rest'=>$vv['rest'],
                            );
                            unset($data['data'][$k]['data'][$kk]);
                        }
                    }
                }
            }
            foreach($data['data'] as $k=>$v){
                if(isset($v['data']) && !empty($v['data'])){
                    foreach($v['data'] as $kk=>$vv){
                        if(isset($data['data'][$k]['list'][$vv['date_time']])){
                            $data['data'][$k]['list'][$vv['date_time']]['follow'] += $vv['follow'];
                            $data['data'][$k]['list'][$vv['date_time']]['real_follow'] += $vv['real_follow'];
                            $data['data'][$k]['list'][$vv['date_time']]['float'] += $vv['float'];
                            $data['data'][$k]['list'][$vv['date_time']]['rest'] += $vv['rest'];
                        }else{
                            $data['data'][$k]['list'][$vv['date_time']] = $vv;
                        }
                    }
                    unset($data['data'][$k]['data']);
                }
            }
            return $data;
        }else{
            return false;
        }
    }

    public static function revenueDetail_wechat($wid,$user,$page,$pagesize,$start_date,$end_date,$bid)
    {
        $data = TaskSummaryModel::revenueDetail_wechat($wid, $page, $pagesize, $start_date, $end_date, $bid);
        if ($data) {
            foreach ($data['pid'] as $k => $v) {
                if (isset($v['data']) && !empty($v['data'])) {
                    foreach ($v['data'] as $kk => $vv) {
                        if ($user == 0) {
                            $data['pid'][$k]['data'][$kk]['follow'] = $vv['new_follow_repeat'] + $vv['old_follow_repeat'] + 0;
                            $data['pid'][$k]['data'][$kk]['float'] = $data['pid'][$k]['data'][$kk]['follow'] * $vv['o_per_price'];
                            $data['pid'][$k]['data'][$kk]['cost'] = $data['pid'][$k]['data'][$kk]['follow'] * (1 - $v['reduce_percent']*0.01) * $v['cost_price'];
                            $data['pid'][$k]['data'][$kk]['rest'] = $data['pid'][$k]['data'][$kk]['float'] * $v['reduce_percent']*0.01;
                        } elseif ($user == 1) {
                            $data['pid'][$k]['data'][$kk]['follow'] = $vv['new_follow_repeat'] + 0;
                            $data['pid'][$k]['data'][$kk]['float'] = $data['pid'][$k]['data'][$kk]['follow'] * $vv['o_per_price'];
                            $data['pid'][$k]['data'][$kk]['cost'] = $data['pid'][$k]['data'][$kk]['follow'] * (1 - $v['reduce_percent']*0.01) * $v['cost_price'];
                            $data['pid'][$k]['data'][$kk]['rest'] = $data['pid'][$k]['data'][$kk]['float'] * $v['reduce_percent']*0.01;
                        } else {
                            $data['pid'][$k]['data'][$kk]['follow'] = $vv['old_follow_repeat'] + 0;
                            $data['pid'][$k]['data'][$kk]['float'] = $data['pid'][$k]['data'][$kk]['follow'] * $vv['o_per_price'];
                            $data['pid'][$k]['data'][$kk]['cost'] = $data['pid'][$k]['data'][$kk]['follow'] * (1 - $v['reduce_percent']*0.01) * $v['cost_price'];
                            $data['pid'][$k]['data'][$kk]['rest'] = $data['pid'][$k]['data'][$kk]['float'] * $v['reduce_percent']*0.01;
                        }
                    }
                }
            }
            foreach ($data['cid'] as $k => $v) {
                if (isset($v['data']) && !empty($v['data'])) {
                    foreach ($v['data'] as $kk => $vv) {
                        if ($user == 0) {
                            $data['cid'][$k]['data'][$kk]['follow'] = $vv['new_follow_repeat'] + $vv['old_follow_repeat'] + 0;
                            $data['cid'][$k]['data'][$kk]['float'] = $data['cid'][$k]['data'][$kk]['follow'] * $vv['o_per_price'];
                            $data['cid'][$k]['data'][$kk]['cost'] = $data['cid'][$k]['data'][$kk]['follow'] * (1 - $v['reduce_percent']*0.01) * $v['cost_price'];
                            $data['cid'][$k]['data'][$kk]['rest'] = $data['cid'][$k]['data'][$kk]['float'] * $v['reduce_percent']*0.01;
                        } elseif ($user == 1) {
                            $data['cid'][$k]['data'][$kk]['follow'] = $vv['new_follow_repeat'] + 0;
                            $data['cid'][$k]['data'][$kk]['float'] = $data['cid'][$k]['data'][$kk]['follow'] * $vv['o_per_price'];
                            $data['cid'][$k]['data'][$kk]['cost'] = $data['cid'][$k]['data'][$kk]['follow'] * (1 - $v['reduce_percent']*0.01) * $v['cost_price'];
                            $data['cid'][$k]['data'][$kk]['rest'] = $data['cid'][$k]['data'][$kk]['float'] * $v['reduce_percent']*0.01;
                        } else {
                            $data['cid'][$k]['data'][$kk]['follow'] = $vv['old_follow_repeat'] + 0;
                            $data['cid'][$k]['data'][$kk]['float'] = $data['cid'][$k]['data'][$kk]['follow'] * $vv['o_per_price'];
                            $data['cid'][$k]['data'][$kk]['cost'] = $data['cid'][$k]['data'][$kk]['follow'] * (1 - $v['reduce_percent']*0.01) * $v['cost_price'];
                            $data['cid'][$k]['data'][$kk]['rest'] = $data['cid'][$k]['data'][$kk]['float'] * $v['reduce_percent']*0.01;
                        }
                    }
                }
            }
            foreach ($data['pid'] as $k => $v) {
                if (isset($v['data']) && !empty($v['data'])) {
                    foreach ($v['data'] as $kk => $vv) {
                        if (isset($data['pid'][$k]['list'][$vv['date_time']])) {
                            $data['pid'][$k]['list'][$vv['date_time']]['follow'] += $vv['follow'];
                            $data['pid'][$k]['list'][$vv['date_time']]['float'] += $vv['float'];
                            $data['pid'][$k]['list'][$vv['date_time']]['cost'] += $vv['cost'];
                            $data['pid'][$k]['list'][$vv['date_time']]['rest'] += $vv['rest'];
                        } else {
                            $data['pid'][$k]['list'][$vv['date_time']] = array(
                                'date_time' => $vv['date_time'],
                                'follow' => $vv['follow'],
                                'float' => $vv['float'],
                                'cost' => $vv['cost'],
                                'rest' => $vv['rest']
                            );
                        }
                    }
                    unset($data['pid'][$k]['data']);
                }
            }
            foreach ($data['cid'] as $k => $v) {
                if (isset($v['data']) && !empty($v['data'])) {
                    foreach ($v['data'] as $kk => $vv) {
                        if (isset($data['cid'][$k]['list'][$vv['date_time']])) {
                            $data['cid'][$k]['list'][$vv['date_time']]['follow'] += $vv['follow'];
                            $data['cid'][$k]['list'][$vv['date_time']]['float'] += $vv['float'];
                            $data['cid'][$k]['list'][$vv['date_time']]['cost'] += $vv['cost'];
                            $data['cid'][$k]['list'][$vv['date_time']]['rest'] += $vv['rest'];
                        } else {
                            $data['cid'][$k]['list'][$vv['date_time']] = array(
                                'date_time' => $vv['date_time'],
                                'follow' => $vv['follow'],
                                'float' => $vv['float'],
                                'cost' => $vv['cost'],
                                'rest' => $vv['rest']
                            );
                        }
                    }
                    unset($data['cid'][$k]['data']);
                }
            }
            $p_arr = $data['pid'];
            $c_arr = $data['cid'];
            foreach ($data['pid'] as $k => $v) {
                $data['pid'][$k]['child'] = array();
                foreach ($data['cid'] as $kk => $vv) {
                    if ($vv['pbid'] == $v['id'] && isset($vv['list']) && !empty($vv['list'])) {
                        foreach($vv['list'] as $key=>$val){
                            if(isset($data['pid'][$k]['child'][$key])){
                                $data['pid'][$k]['child'][$key]['follow'] += $val['follow'];
                                $data['pid'][$k]['child'][$key]['float'] += $val['float'];
                                $data['pid'][$k]['child'][$key]['cost'] += $val['cost'];
                                $data['pid'][$k]['child'][$key]['rest'] += $val['rest'];
                            }else{
                                $data['pid'][$k]['child'][$key] = $val;
                            }
                        }
                    }
                }
                krsort($data['pid'][$k]['child']);
            }
            foreach ($data['pid'] as $k => $v) {
                if (isset($v['list']) && isset($v['child'])) {
                    foreach ($v['list'] as $kk => $vv) {
                        foreach ($v['child'] as $key => $val) {
                            if ($kk == $key) {
                                $data['pid'][$k]['list'][$kk]['follow'] += $val['follow'];
                                $data['pid'][$k]['list'][$kk]['float'] += $val['float'];
                                $data['pid'][$k]['list'][$kk]['cost'] += $val['cost'];
                                $data['pid'][$k]['list'][$kk]['rest'] += $val['rest'];
                                unset($data['pid'][$k]['child'][$key]);
                            }
                        }
                    }
                }
                if (isset($v['child']) && !isset($v['list'])) {
                    $data['pid'][$k]['list'] = $data['pid'][$k]['child'];
                    unset($data['pid'][$k]['child']);
                }
            }
            foreach ($data['pid'] as $k => $v) {
                if (isset($v['list']) && isset($v['child'])) {
                    foreach ($v['list'] as $kk => $vv) {
                        foreach ($v['child'] as $key => $val) {
                            $data['pid'][$k]['list'][$key] = $val;
                            unset($data['pid'][$k]['child'][$key]);
                        }
                    }
                    unset($data['pid'][$k]['child']);
                    krsort($data['pid'][$k]['list']);
                }
            }
            if ($bid) {
                $arr['list'] = $p_arr;
                if (isset($c_arr) && !empty($c_arr)) {
                    foreach ($c_arr as $k => $v) {
                        $arr['list'][] = $v;
                    }
                }
            }
            $arr['data'] = $data['pid'];
            $arr['buss'] = $data['buss'];
            $arr['num'] = $data['num'];
            return $arr;
        } else {
            return false;
        }
    }
    public static function revenueDetail_wechatOne($wid,$user,$page,$pagesize,$start_date,$end_date,$bid)
    {
        $data = TaskSummaryModel::revenueDetail_wechatOne($wid, $page, $pagesize, $start_date, $end_date, $bid);
        if ($data) {
            foreach ($data['pid'] as $k => $v) {
                if (isset($v['data']) && !empty($v['data'])) {
                    foreach ($v['data'] as $kk => $vv) {
                        if ($user == 0) {
                            $data['pid'][$k]['data'][$kk]['follow'] = $vv['new_follow_repeat'] + $vv['old_follow_repeat'] + 0;
                            $data['pid'][$k]['data'][$kk]['float'] = $data['pid'][$k]['data'][$kk]['follow'] * $vv['o_per_price'];
                            $data['pid'][$k]['data'][$kk]['cost'] = $data['pid'][$k]['data'][$kk]['follow'] * (1 - $v['reduce_percent']*0.01) * $v['cost_price'];
                            $data['pid'][$k]['data'][$kk]['rest'] = $data['pid'][$k]['data'][$kk]['float'] * $v['reduce_percent']*0.01;
                        } elseif ($user == 1) {
                            $data['pid'][$k]['data'][$kk]['follow'] = $vv['new_follow_repeat'] + 0;
                            $data['pid'][$k]['data'][$kk]['float'] = $data['pid'][$k]['data'][$kk]['follow'] * $vv['o_per_price'];
                            $data['pid'][$k]['data'][$kk]['cost'] = $data['pid'][$k]['data'][$kk]['follow'] * (1 - $v['reduce_percent']*0.01) * $v['cost_price'];
                            $data['pid'][$k]['data'][$kk]['rest'] = $data['pid'][$k]['data'][$kk]['float'] * $v['reduce_percent']*0.01;
                        } else {
                            $data['pid'][$k]['data'][$kk]['follow'] = $vv['old_follow_repeat'] + 0;
                            $data['pid'][$k]['data'][$kk]['float'] = $data['pid'][$k]['data'][$kk]['follow'] * $vv['o_per_price'];
                            $data['pid'][$k]['data'][$kk]['cost'] = $data['pid'][$k]['data'][$kk]['follow'] * (1 - $v['reduce_percent']*0.01) * $v['cost_price'];
                            $data['pid'][$k]['data'][$kk]['rest'] = $data['pid'][$k]['data'][$kk]['float'] * $v['reduce_percent']*0.01;
                        }
                    }
                }
            }
            foreach ($data['cid'] as $k => $v) {
                if (isset($v['data']) && !empty($v['data'])) {
                    foreach ($v['data'] as $kk => $vv) {
                        if ($user == 0) {
                            $data['cid'][$k]['data'][$kk]['follow'] = $vv['new_follow_repeat'] + $vv['old_follow_repeat'] + 0;
                            $data['cid'][$k]['data'][$kk]['float'] = $data['cid'][$k]['data'][$kk]['follow'] * $vv['o_per_price'];
                            $data['cid'][$k]['data'][$kk]['cost'] = $data['cid'][$k]['data'][$kk]['follow'] * (1 - $v['reduce_percent']*0.01) * $v['cost_price'];
                            $data['cid'][$k]['data'][$kk]['rest'] = $data['cid'][$k]['data'][$kk]['float'] * $v['reduce_percent']*0.01;
                        } elseif ($user == 1) {
                            $data['cid'][$k]['data'][$kk]['follow'] = $vv['new_follow_repeat'] + 0;
                            $data['cid'][$k]['data'][$kk]['float'] = $data['cid'][$k]['data'][$kk]['follow'] * $vv['o_per_price'];
                            $data['cid'][$k]['data'][$kk]['cost'] = $data['cid'][$k]['data'][$kk]['follow'] * (1 - $v['reduce_percent']*0.01) * $v['cost_price'];
                            $data['cid'][$k]['data'][$kk]['rest'] = $data['cid'][$k]['data'][$kk]['float'] * $v['reduce_percent']*0.01;
                        } else {
                            $data['cid'][$k]['data'][$kk]['follow'] = $vv['old_follow_repeat'] + 0;
                            $data['cid'][$k]['data'][$kk]['float'] = $data['cid'][$k]['data'][$kk]['follow'] * $vv['o_per_price'];
                            $data['cid'][$k]['data'][$kk]['cost'] = $data['cid'][$k]['data'][$kk]['follow'] * (1 - $v['reduce_percent']*0.01) * $v['cost_price'];
                            $data['cid'][$k]['data'][$kk]['rest'] = $data['cid'][$k]['data'][$kk]['float'] * $v['reduce_percent']*0.01;
                        }
                    }
                }
            }
            foreach ($data['pid'] as $k => $v) {
                if (isset($v['data']) && !empty($v['data'])) {
                    foreach ($v['data'] as $kk => $vv) {
                        if (isset($data['pid'][$k]['list'][$vv['date_time']])) {
                            $data['pid'][$k]['list'][$vv['date_time']]['follow'] += $vv['follow'];
                            $data['pid'][$k]['list'][$vv['date_time']]['float'] += $vv['float'];
                            $data['pid'][$k]['list'][$vv['date_time']]['cost'] += $vv['cost'];
                            $data['pid'][$k]['list'][$vv['date_time']]['rest'] += $vv['rest'];
                        } else {
                            $data['pid'][$k]['list'][$vv['date_time']] = array(
                                'date_time' => $vv['date_time'],
                                'follow' => $vv['follow'],
                                'float' => $vv['float'],
                                'cost' => $vv['cost'],
                                'rest' => $vv['rest']
                            );
                        }
                    }
                    unset($data['pid'][$k]['data']);
                }
            }
            foreach ($data['cid'] as $k => $v) {
                if (isset($v['data']) && !empty($v['data'])) {
                    foreach ($v['data'] as $kk => $vv) {
                        if (isset($data['cid'][$k]['list'][$vv['date_time']])) {
                            $data['cid'][$k]['list'][$vv['date_time']]['follow'] += $vv['follow'];
                            $data['cid'][$k]['list'][$vv['date_time']]['float'] += $vv['float'];
                            $data['cid'][$k]['list'][$vv['date_time']]['cost'] += $vv['cost'];
                            $data['cid'][$k]['list'][$vv['date_time']]['rest'] += $vv['rest'];
                        } else {
                            $data['cid'][$k]['list'][$vv['date_time']] = array(
                                'date_time' => $vv['date_time'],
                                'follow' => $vv['follow'],
                                'float' => $vv['float'],
                                'cost' => $vv['cost'],
                                'rest' => $vv['rest']
                            );
                        }
                    }
                    unset($data['cid'][$k]['data']);
                }
            }
            $p_arr = $data['pid'];
            $c_arr = $data['cid'];
            foreach ($data['pid'] as $k => $v) {
                $data['pid'][$k]['child'] = array();
                foreach ($data['cid'] as $kk => $vv) {
                    if ($vv['pbid'] == $v['id'] && isset($vv['list']) && !empty($vv['list'])) {
                        foreach($vv['list'] as $key=>$val){
                            if(isset($data['pid'][$k]['child'][$key])){
                                $data['pid'][$k]['child'][$key]['follow'] += $val['follow'];
                                $data['pid'][$k]['child'][$key]['float'] += $val['float'];
                                $data['pid'][$k]['child'][$key]['cost'] += $val['cost'];
                                $data['pid'][$k]['child'][$key]['rest'] += $val['rest'];
                            }else{
                                $data['pid'][$k]['child'][$key] = $val;
                            }
                        }
                        $data['pid'][$k]['child'] += $vv['list'];
                    }
                }
                krsort($data['pid'][$k]['child']);
            }
            foreach ($data['pid'] as $k => $v) {
                if (isset($v['list']) && isset($v['child'])) {
                    foreach ($v['list'] as $kk => $vv) {
                        foreach ($v['child'] as $key => $val) {
                            if ($kk == $key) {
                                $data['pid'][$k]['list'][$kk]['follow'] += $val['follow'];
                                $data['pid'][$k]['list'][$kk]['float'] += $val['float'];
                                $data['pid'][$k]['list'][$kk]['cost'] += $val['cost'];
                                $data['pid'][$k]['list'][$kk]['rest'] += $val['rest'];
                                unset($data['pid'][$k]['child'][$key]);
                            }
                        }
                    }
                }
                if (isset($v['child']) && !isset($v['list'])) {
                    $data['pid'][$k]['list'] = $data['pid'][$k]['child'];
                    unset($data['pid'][$k]['child']);
                }
            }
            foreach ($data['pid'] as $k => $v) {
                if (isset($v['list']) && isset($v['child'])) {
                    foreach ($v['list'] as $kk => $vv) {
                        foreach ($v['child'] as $key => $val) {
                            $data['pid'][$k]['list'][$key] = $val;
                            unset($data['pid'][$k]['child'][$key]);
                        }
                    }
                    unset($data['pid'][$k]['child']);
                    krsort($data['pid'][$k]['list']);
                }
            }
            if ($bid) {
                $arr['list'] = $p_arr;
                if (isset($c_arr) && !empty($c_arr)) {
                    foreach ($c_arr as $k => $v) {
                        $arr['list'][] = $v;
                    }
                }
            }
            $arr['data'] = $data['pid'];
            $arr['buss'] = $data['buss'];
            $arr['num'] = $data['num'];
            return $arr;
        } else {
            return false;
        }
    }
}