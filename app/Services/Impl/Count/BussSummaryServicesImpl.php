<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/7/10
 * Time: 14:05
 */
namespace App\Services\Impl\Count;
use App\Models\Count\TaskSummaryModel;
use App\Services\CommonServices;

class BussSummaryServicesImpl extends CommonServices{
    public static function bussCount($start_date,$end_date,$status,$user,$page,$pagesize,$buss){
//        status = 1  次数   (不去重)
//        status = 2  人数   (去重)
//        user = 0  全部
//        user = 1  新用户
//        user = 2  老用户
        $data = TaskSummaryModel::bussCount($start_date,$end_date,$page,$pagesize,$buss);
        $count_data = $data['data'];
        if($count_data){
            if($status == 1){
                if($user == 0){
                    //次数、全部用户
                    foreach($count_data as $k=>$v){
                        $count[$k]['date_time'] = $v['date_time'];     //获取公众号次数
                        $count[$k]['sumgetwx'] = $v['new_sumgetwx_repeat']+$v['old_sumgetwx_repeat']+0;     //获取公众号次数
                        $count[$k]['getwx'] = $v['new_getwx_repeat']+$v['old_getwx_repeat']+0;              //成功获取公众号次数
                        $count[$k]['complet'] = $v['new_complet_repeat']+$v['old_complet_repeat']+0;        //连接次数(微信认证次数)
                        $count[$k]['follow'] = $v['new_follow_repeat']+$v['old_follow_repeat']+0;           //成功关注次数
                        $count[$k]['end'] = $v['new_end_repeat']+$v['old_end_repeat']+0;                    //点击完成次数
                        $count[$k]['buss_id'] = $v['buss_id'];                                              //渠道id
                    }
                }else if($user == 1){
                    //次数、新用户
                    foreach($count_data as $k=>$v){
                        $count[$k]['date_time'] = $v['date_time'];        //获取微信次数
                        $count[$k]['sumgetwx'] = $v['new_sumgetwx_repeat']+0;        //获取微信次数
                        $count[$k]['getwx'] = $v['new_getwx_repeat']+0;              //成功获取微信次数
                        $count[$k]['complet'] = $v['new_complet_repeat']+0;          //连接次数(微信认证次数)
                        $count[$k]['follow'] = $v['new_follow_repeat']+0;            //成功关注次数
                        $count[$k]['end'] = $v['new_end_repeat']+0;                  //点击完成次数
                        $count[$k]['buss_id'] = $v['buss_id'];                       //渠道id
                    }
                }else{
                    //次数、老用户
                    foreach($count_data as $k=>$v){
                        $count[$k]['date_time'] = $v['date_time'];        //获取微信次数
                        $count[$k]['sumgetwx'] = $v['old_sumgetwx_repeat']+0;        //获取微信次数
                        $count[$k]['getwx'] = $v['old_getwx_repeat']+0;              //成功获取微信次数
                        $count[$k]['complet'] = $v['old_complet_repeat']+0;          //连接次数(微信认证次数)
                        $count[$k]['follow'] = $v['old_follow_repeat']+0;            //成功关注次数
                        $count[$k]['end'] = $v['old_end_repeat']+0;                  //点击完成次数
                        $count[$k]['buss_id'] = $v['buss_id'];                       //渠道id
                    }
                }
            }else{
                if($user == 0){
                    //人数、全部用户
                    foreach($count_data as $k=>$v){
                        $count[$k]['date_time'] = $v['date_time'];         //获取微信次数
                        $count[$k]['sumgetwx'] = $v['new_sumgetwx_only']+$v['old_sumgetwx_only']+0;         //获取微信次数
                        $count[$k]['getwx'] = $v['new_getwx_only']+$v['old_getwx_only']+0;                  //成功获取微信次数
                        $count[$k]['complet'] = $v['new_complet_only']+$v['old_complet_only']+0;            //连接次数(微信认证次数)
                        $count[$k]['follow'] = $v['new_follow_only']+$v['old_follow_only']+0;               //成功关注次数
                        $count[$k]['end'] = $v['new_end_only']+$v['old_end_only']+0;                        //点击完成次数
                        $count[$k]['buss_id'] = $v['buss_id'];                                              //渠道id
                    }
                }else if($user == 1){
                    //人数、新用户
                    foreach($count_data as $k=>$v){
                        $count[$k]['date_time'] = $v['date_time'];          //获取微信次数
                        $count[$k]['sumgetwx'] = $v['new_sumgetwx_only']+0;          //获取微信次数
                        $count[$k]['getwx'] = $v['new_getwx_only']+0;                //成功获取微信次数
                        $count[$k]['complet'] = $v['new_complet_only']+0;            //连接次数(微信认证次数)
                        $count[$k]['follow'] = $v['new_follow_only']+0;              //成功关注次数
                        $count[$k]['end'] = $v['new_end_only']+0;                    //点击完成次数
                        $count[$k]['buss_id'] = $v['buss_id'];                       //渠道id
                    }
                }else{
                    //人数、老用户
                    foreach($count_data as $k=>$v){
                        $count[$k]['date_time'] = $v['date_time'];          //获取微信次数
                        $count[$k]['sumgetwx'] = $v['old_sumgetwx_only']+0;          //获取微信次数
                        $count[$k]['getwx'] = $v['old_getwx_only']+0;                //获取微信次数
                        $count[$k]['complet'] = $v['old_complet_only']+0;            //连接次数(微信认证次数)
                        $count[$k]['follow'] = $v['old_follow_only']+0;              //成功关注次数
                        $count[$k]['end'] = $v['old_end_only']+0;                    //点击完成次数
                        $count[$k]['buss_id'] = $v['buss_id'];                       //渠道id
                    }
                }
            }
            foreach($data['buss'] as $k=>$v){
                $data['buss'][$k]['count']['sumgetwx'] = 0;
                $data['buss'][$k]['count']['getwx'] = 0;
                $data['buss'][$k]['count']['complet'] = 0;
                $data['buss'][$k]['count']['follow'] = 0;
                $data['buss'][$k]['count']['end'] = 0;
                $data['buss'][$k]['count']['buss_id'] = 0;
                foreach($count as $kk=>$vv){
                    if($v['id'] == $vv['buss_id']){
                        $data['buss'][$k]['count']['sumgetwx'] += $vv['sumgetwx'];
                        $data['buss'][$k]['count']['getwx'] += $vv['getwx'];
                        $data['buss'][$k]['count']['complet'] += $vv['complet'];
                        $data['buss'][$k]['count']['follow'] += $vv['follow'];
                        $data['buss'][$k]['count']['end'] += $vv['end'];
                        $data['buss'][$k]['count']['buss_id'] += $vv['buss_id'];
                        unset($count[$kk]);
                    }
                    if(isset($v[$v['username']])){
                        foreach($v[$v['username']] as $key=>$val){
                            if($vv['buss_id'] == $val['id']){
                                if(isset($data['buss'][$k][$v['username']][$key]['count'])){
                                    $data['buss'][$k][$v['username']][$key]['count']['sumgetwx'] += $vv['sumgetwx'];
                                    $data['buss'][$k][$v['username']][$key]['count']['getwx'] += $vv['getwx'];
                                    $data['buss'][$k][$v['username']][$key]['count']['complet'] += $vv['complet'];
                                    $data['buss'][$k][$v['username']][$key]['count']['follow'] += $vv['follow'];
                                    $data['buss'][$k][$v['username']][$key]['count']['end'] += $vv['end'];
                                }else{
                                    $data['buss'][$k][$v['username']][$key]['count']['sumgetwx'] = $vv['sumgetwx'];
                                    $data['buss'][$k][$v['username']][$key]['count']['getwx'] = $vv['getwx'];
                                    $data['buss'][$k][$v['username']][$key]['count']['complet'] = $vv['complet'];
                                    $data['buss'][$k][$v['username']][$key]['count']['follow'] = $vv['follow'];
                                    $data['buss'][$k][$v['username']][$key]['count']['end'] = $vv['end'];
                                    $data['buss'][$k][$v['username']][$key]['count']['buss_id'] = $vv['buss_id'];
                                }
                                unset($count[$kk]);
                            }
                        }
                    }
                }
            }
            $arr['data'] = $data['buss'];
            $arr['num'] = $data['num'];
            $arr['buss'] = $data['buss_list'];
            return $arr;
        }else{
            return false;
        }
    }

    public static function bussCount_detail($start_date,$end_date,$user,$page,$pagesize,$buss,$child){
        $data = TaskSummaryModel::bussCount_detail($start_date,$end_date,$page,$pagesize,$buss,$child);
        if($data){
            if(isset($data['father'])){
                foreach($data as $k=>$v){
                    if(isset($v['data']) && !empty($v['data'])){
                        foreach($v['data'] as $kk=>$vv){
                            if($user == 0){
                                $data[$k]['data'][$kk]['sumgetwx'] = $vv['old_sumgetwx_repeat']+$vv['new_sumgetwx_repeat']+0;
                                $data[$k]['data'][$kk]['getwx'] = $vv['old_getwx_repeat']+$vv['new_getwx_repeat']+0;
                                $data[$k]['data'][$kk]['complet'] = $vv['old_complet_repeat']+$vv['new_complet_repeat']+0;
                                $data[$k]['data'][$kk]['follow'] = $vv['old_follow_repeat']+$vv['new_follow_repeat']+0;
                                $data[$k]['data'][$kk]['end'] = $vv['old_end_repeat']+$vv['new_end_repeat']+0;
                            }elseif($user == 1){
                                $data[$k]['data'][$kk]['sumgetwx'] = $vv['new_sumgetwx_repeat']+0;
                                $data[$k]['data'][$kk]['getwx'] = $vv['new_getwx_repeat']+0;
                                $data[$k]['data'][$kk]['complet'] = $vv['new_complet_repeat']+0;
                                $data[$k]['data'][$kk]['follow'] = $vv['new_follow_repeat']+0;
                                $data[$k]['data'][$kk]['end'] = $vv['new_end_repeat']+0;
                            }else{
                                $data[$k]['data'][$kk]['sumgetwx'] = $vv['old_sumgetwx_repeat']+0;
                                $data[$k]['data'][$kk]['getwx'] = $vv['old_getwx_repeat']+0;
                                $data[$k]['data'][$kk]['complet'] = $vv['old_complet_repeat']+0;
                                $data[$k]['data'][$kk]['follow'] = $vv['old_follow_repeat']+0;
                                $data[$k]['data'][$kk]['end'] = $vv['old_end_repeat']+0;
                            }
                        }
                    }
                }
                $total = array();
                if(isset($data['total']) && !empty($data['total'])){
                    foreach($data['total'] as $k=>$v){
                        if(isset($total[$v['date_time']])){
                            if($user == 0){
                                $total[$v['date_time']]['sumgetwx'] += $v['old_sumgetwx_repeat']+$v['new_sumgetwx_repeat'];
                                $total[$v['date_time']]['getwx'] += $v['old_getwx_repeat']+$v['new_getwx_repeat'];
                                $total[$v['date_time']]['complet'] += $v['old_complet_repeat']+$v['new_complet_repeat'];
                                $total[$v['date_time']]['follow'] += $v['old_follow_repeat']+$v['new_follow_repeat'];
                                $total[$v['date_time']]['end'] += $v['old_end_repeat']+$v['new_end_repeat'];
                            }elseif($user == 1){
                                $total[$v['date_time']]['sumgetwx'] += $v['new_sumgetwx_repeat'];
                                $total[$v['date_time']]['getwx'] += $v['new_getwx_repeat'];
                                $total[$v['date_time']]['complet'] += $v['new_complet_repeat'];
                                $total[$v['date_time']]['follow'] += $v['new_follow_repeat'];
                                $total[$v['date_time']]['end'] += $v['new_end_repeat'];
                            }else{
                                $total[$v['date_time']]['sumgetwx'] += $v['old_sumgetwx_repeat'];
                                $total[$v['date_time']]['getwx'] += $v['old_getwx_repeat'];
                                $total[$v['date_time']]['complet'] += $v['old_complet_repeat'];
                                $total[$v['date_time']]['follow'] += $v['old_follow_repeat'];
                                $total[$v['date_time']]['end'] += $v['old_end_repeat'];
                            }
                        }else{
                            if($user == 0){
                                $total[$v['date_time']] = array(
                                    'buss_id'=>$v['bid'],
                                    'date_time'=>$v['date_time'],
                                    'sumgetwx'=>$v['old_sumgetwx_repeat']+$v['new_sumgetwx_repeat']+0,
                                    'getwx'=>$v['old_getwx_repeat']+$v['new_getwx_repeat']+0,
                                    'complet'=>$v['old_complet_repeat']+$v['new_complet_repeat']+0,
                                    'follow'=>$v['old_follow_repeat']+$v['new_follow_repeat']+0,
                                    'end'=>$v['old_end_repeat']+$v['new_end_repeat']+0,
                                );
                            }elseif($user == 1){
                                $total[$v['date_time']] = array(
                                    'buss_id'=>$v['bid'],
                                    'date_time'=>$v['date_time'],
                                    'sumgetwx'=>$v['new_sumgetwx_repeat']+0,
                                    'getwx'=>$v['new_getwx_repeat']+0,
                                    'complet'=>$v['new_complet_repeat']+0,
                                    'follow'=>$v['new_follow_repeat']+0,
                                    'end'=>$v['new_end_repeat']+0,
                                );
                            }else{
                                $total[$v['date_time']] = array(
                                    'buss_id'=>$v['bid'],
                                    'date_time'=>$v['date_time'],
                                    'sumgetwx'=>$v['old_sumgetwx_repeat']+0,
                                    'getwx'=>$v['old_getwx_repeat']+0,
                                    'complet'=>$v['old_complet_repeat']+0,
                                    'follow'=>$v['old_follow_repeat']+0,
                                    'end'=>$v['old_end_repeat']+0,
                                );
                            }
                        }
                    }
                }
                unset($data['total']);
                $data['father']['data'] = $total;
            }else{
                if(isset($data['data']) && !empty($data['data'])){
                    foreach($data['data'] as $k=>$v){
                        if($user == 0){
                            $data['data'][$k]['sumgetwx'] = $v['old_sumgetwx_repeat']+$v['new_sumgetwx_repeat']+0;
                            $data['data'][$k]['getwx'] = $v['old_getwx_repeat']+$v['new_getwx_repeat']+0;
                            $data['data'][$k]['complet'] = $v['old_complet_repeat']+$v['new_complet_repeat']+0;
                            $data['data'][$k]['follow'] = $v['old_follow_repeat']+$v['new_follow_repeat']+0;
                            $data['data'][$k]['end'] = $v['old_end_repeat']+$v['new_end_repeat']+0;
                        }elseif($user == 1){
                            $data['data'][$k]['sumgetwx'] = $v['new_sumgetwx_repeat']+0;
                            $data['data'][$k]['getwx'] = $v['new_getwx_repeat']+0;
                            $data['data'][$k]['complet'] = $v['new_complet_repeat']+0;
                            $data['data'][$k]['follow'] = $v['new_follow_repeat']+0;
                            $data['data'][$k]['end'] = $v['new_end_repeat']+0;
                        }else{
                            $data['data'][$k]['sumgetwx'] = $v['old_sumgetwx_repeat']+0;
                            $data['data'][$k]['getwx'] = $v['old_getwx_repeat']+0;
                            $data['data'][$k]['complet'] = $v['old_complet_repeat']+0;
                            $data['data'][$k]['follow'] = $v['old_follow_repeat']+0;
                            $data['data'][$k]['end'] = $v['old_end_repeat']+0;
                        }
                    }
                }
            }
            return $data;
        }else{
            return false;
        }

    }
}