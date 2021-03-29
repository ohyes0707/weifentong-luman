<?php

namespace App\Services\Impl\User;

use App\Models\User\UserModel;
use App\Models\User\AdminModel;
use App\Models\Buss\BussModel;
use App\Services\CommonServices;
use App\Services\UserServices;

class UserServicesImpl extends CommonServices implements UserServices
{
    /**
     * 获取用户信息
     * @param $userId
     * @return null
     */
    static public function getUserInfo($userId)
    {
        return UserModel::getUserInfo($userId);
    }

    /**
     * 检测用户登录
     * @param $username
     * @param $password
     * @return null
     */
    static public function doUserLogin($username,$password)
    {
        $userinfo = UserModel::getUserByUsername($username);
        $data['is_login'] = false;
        if ($userinfo && !$userinfo['status']) {
            return $data;
        }
        if ($userinfo && ($userinfo['password'] == md5($password . $userinfo['create_time']))) {
            $data = $userinfo;
            $data['is_login'] = true;
            return $data;
        }
        return $data;
    }

    /**
     * 检测运营登录
     * @param $username
     * @param $password
     * @return null
     */
    static public function doOperateLogin($username,$password)
    {
        $userinfo = AdminModel::getUserByUsername($username);
        $data['is_login'] = false;
        if ($userinfo && !$userinfo['status']) {
            return $data;
        }
        if ($userinfo && ($userinfo['password'] == md5($password . $userinfo['create_time']))) {
            $data = $userinfo;
            $data['is_login'] = true;
            return $data;
        }
        return $data;
    }

    /**
     * 检测商家登录
     * @param $username
     * @param $password
     * @return null
     */
    static public function doBussLogin($username,$password)
    {
        $userinfo = BussModel::getUserByUsername($username);
        $data['is_login'] = false;
        if ($userinfo && !$userinfo['status']) {
            return $data;
        }
        if ($userinfo && ($userinfo['password'] == md5($password . $userinfo['create_time']))) {
            $data = $userinfo;
            $data['is_login'] = true;
            return $data;
        }
        return $data;
    }

    /**
     * 检测代理登录
     * @param $username
     * @param $password
     * @return null
     */
    static public function doAgentLogin($username,$password)
    {
        $userinfo = UserModel::getUserByAgentUsername($username);
        $data['is_login'] = false;
        if ($userinfo && !$userinfo['status']) {
            return $data;
        }
        if ($userinfo && ($userinfo['password'] == md5($password . $userinfo['create_time']))) {
            $data = $userinfo;
            $data['is_login'] = true;
            return $data;
        }
        return $data;
    }

    /**
     * 获取运营系统销售列表
     */
    static public function getSaleList($page,$pagesize,$sale){
        $data = UserModel::getSaleList($page,$pagesize,$sale);
        return $data;
    }

    /**
     * 添加销售
     */
    static public function saleAdd($tel,$name,$price){
        $data = UserModel::saleAdd($tel,$name,$price);
        return $data;
    }

    /**
     * 销售编辑
     */
    static public function saleEdit($uid,$token,$tel,$name,$price,$pwd,$oem){
        $data = UserModel::saleEdit($uid,$token,$tel,$name,$price,$pwd,$oem);
        return $data;
    }

    /**
     * 销售报表
     */
    static public function saleForm($uid,$start_date,$end_date,$page,$pagesize){
        $data = UserModel::saleForm($uid,$start_date,$end_date,$page,$pagesize);
        if($data){
            foreach($data['data'] as $k=>$v){
                $data_list[] = array(
                    'date_time'=>$v['date_time'],
                    'follow'=>$v['new_follow']+$v['old_follow'],
                    'unfollow'=>$v['new_unfollow']+$v['old_unfollow'],
                    'cost'=>$v['price']*($v['new_follow']+$v['old_follow']),
                );
            }
            foreach($data['list'] as $k=>$v){
                $data['list'][$k]['follow'] = 0;
                $data['list'][$k]['unfollow'] = 0;
                $data['list'][$k]['cost'] = 0;
                foreach($data_list as $kk=>$vv){
                    if($v['date_time'] == $vv['date_time']){
                        $data['list'][$k]['follow'] += $vv['follow'];
                        $data['list'][$k]['unfollow'] += $vv['unfollow'];
                        $data['list'][$k]['cost'] += $vv['cost'];
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
    /**
     * 销售状态
     */
    static public function saleStatus($uid){
        $data = UserModel::saleStatus($uid);
        return $data;
    }
    /**
     * 销售删除
     */
    static public function saleDel($uid){
        $data = UserModel::saleDel($uid);
        return $data;
    }
    /**
     * 销售多选开启
     */
    static public function startAll($uid){
        $data = UserModel::startAll($uid);
        return $data;
    }
    /**
     * 销售多选禁用
     */
    static public function endAll($uid){
        $data = UserModel::endAll($uid);
        return $data;
    }
    /**
     * 销售多选删除
     */
    static public function delAll($uid){
        $data = UserModel::delAll($uid);
        return $data;
    }
    /**
     * 获取运营系统代理列表
     */
    static public function getAgentList($page,$pagesize,$sale){
        $data = UserModel::getAgentList($page,$pagesize,$sale);
        return $data;
    }
    /**
     * 运营系统代理列表-子代理
     */
    static public function subAgent($page,$pagesize,$uid){
        $data = UserModel::subAgent($page,$pagesize,$uid);
        return $data;
    }
}
