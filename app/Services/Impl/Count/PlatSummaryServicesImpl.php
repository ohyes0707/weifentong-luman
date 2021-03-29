<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/7/4
 * Time: 16:28
 */
namespace App\Services\Impl\Count;
use App\Models\Count\TaskSummaryModel;
use App\Services\CommonServices;

class PlatSummaryServicesImpl extends CommonServices{
    public static function platCount($start_date,$end_date,$status,$user,$page,$pagesize){
//        status = 1  次数   (不去重)
//        status = 2  人数   (去重)
//        user = 0  全部
//        user = 1  新用户
//        user = 2  老用户
        $data = TaskSummaryModel::platCount($start_date,$end_date,$page,$pagesize);
        if($status == 1){
            if($user == 0){
                //次数、全部用户
                foreach($data['count_arr']['data'] as $k=>$v){
                    $data['database'][$k]['sumgetwx'] = $v['new_sumgetwx_repeat']+$v['old_sumgetwx_repeat']+0;     //获取公众号次数
                    $data['database'][$k]['getwx'] = $v['new_getwx_repeat']+$v['old_getwx_repeat']+0;              //成功获取公众号次数
                    $data['database'][$k]['complet'] = $v['new_complet_repeat']+$v['old_complet_repeat']+0;        //连接次数(微信认证次数)
                    $data['database'][$k]['follow'] = $v['new_follow_repeat']+$v['old_follow_repeat']+0;           //成功关注次数
                    $data['database'][$k]['end'] = $v['new_end_repeat']+$v['old_end_repeat']+0;                    //点击完成次数
                    $data['database'][$k]['date_time'] = $v['date_time'];                                          //日期
                }
            }else if($user == 1){
                //次数、新用户
                foreach($data['count_arr']['data'] as $k=>$v){
                    $data['database'][$k]['sumgetwx'] = $v['new_sumgetwx_repeat']+0;        //获取微信次数
                    $data['database'][$k]['getwx'] = $v['new_getwx_repeat']+0;              //成功获取微信次数
                    $data['database'][$k]['complet'] = $v['new_complet_repeat']+0;          //连接次数(微信认证次数)
                    $data['database'][$k]['follow'] = $v['new_follow_repeat']+0;            //成功关注次数
                    $data['database'][$k]['end'] = $v['new_end_repeat']+0;                  //点击完成次数
                    $data['database'][$k]['date_time'] = $v['date_time'];                   //日期
                }
            }else{
                //次数、老用户
                foreach($data['count_arr']['data'] as $k=>$v){
                    $data['database'][$k]['sumgetwx'] = $v['old_sumgetwx_repeat']+0;        //获取微信次数
                    $data['database'][$k]['getwx'] = $v['old_getwx_repeat']+0;              //成功获取微信次数
                    $data['database'][$k]['complet'] = $v['old_complet_repeat']+0;          //连接次数(微信认证次数)
                    $data['database'][$k]['follow'] = $v['old_follow_repeat']+0;            //成功关注次数
                    $data['database'][$k]['end'] = $v['old_end_repeat']+0;                  //点击完成次数
                    $data['database'][$k]['date_time'] = $v['date_time'];                   //日期
                }
            }
        }else{
            if($user == 0){
                //人数、全部用户
                foreach($data['count_arr']['data'] as $k=>$v){
                    $data['database'][$k]['sumgetwx'] = $v['new_sumgetwx_only']+$v['old_sumgetwx_only']+0;         //获取微信次数
                    $data['database'][$k]['getwx'] = $v['new_getwx_only']+$v['old_getwx_only']+0;                  //成功获取微信次数
                    $data['database'][$k]['complet'] = $v['new_complet_only']+$v['old_complet_only']+0;            //连接次数(微信认证次数)
                    $data['database'][$k]['follow'] = $v['new_follow_only']+$v['old_follow_only']+0;               //成功关注次数
                    $data['database'][$k]['end'] = $v['new_end_only']+$v['old_end_only']+0;                        //点击完成次数
                    $data['database'][$k]['date_time'] = $v['date_time'];                                          //日期
                }
            }else if($user == 1){
                //人数、新用户
                foreach($data['count_arr']['data'] as $k=>$v){
                    $data['database'][$k]['sumgetwx'] = $v['new_sumgetwx_only']+0;          //获取微信次数
                    $data['database'][$k]['getwx'] = $v['new_getwx_only']+0;                //成功获取微信次数
                    $data['database'][$k]['complet'] = $v['new_complet_only']+0;            //连接次数(微信认证次数)
                    $data['database'][$k]['follow'] = $v['new_follow_only']+0;              //成功关注次数
                    $data['database'][$k]['end'] = $v['new_end_only']+0;                    //点击完成次数
                    $data['database'][$k]['date_time'] = $v['date_time'];                   //日期
                }
            }else{
                //人数、老用户
                foreach($data['count_arr']['data'] as $k=>$v){
                    $data['database'][$k]['sumgetwx'] = $v['old_sumgetwx_only']+0;          //获取微信次数
                    $data['database'][$k]['getwx'] = $v['old_getwx_only']+0;                //获取微信次数
                    $data['database'][$k]['complet'] = $v['old_complet_only']+0;            //连接次数(微信认证次数)
                    $data['database'][$k]['follow'] = $v['old_follow_only']+0;              //成功关注次数
                    $data['database'][$k]['end'] = $v['old_end_only']+0;                    //点击完成次数
                    $data['database'][$k]['date_time'] = $v['date_time'];                   //日期
                }
            }
        }
        unset($data['count_arr']);
        return $data;
    }
}