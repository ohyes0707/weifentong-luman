<?php

namespace App\Services\Impl\Order;

use App\Models\Order\UserModel;
use App\Models\Order\WxInfoModel;
use App\Models\Order\WxReportModel;
use App\Models\Order\SceneModel;
use App\Models\Order\UserOrderModel;
use App\Services\UserServices;

class UserServicesImpl implements UserServices
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
    static public function doUserLogin($username,$password){
        $userinfo = UserModel::getUserByUsername($username);
        $data['is_login'] = false;
        if(count($userinfo)==1){
            if(!$userinfo[0]['status']){
                return $data;
            }
            if ($userinfo[0]['password'] == md5($password . $userinfo[0]['create_time'])) {
                $data = $userinfo[0];
                $data['is_login'] = true;
                return $data;
            }
            return $data;
        }
        return $data;
    }    
    /**
     * 获取公众号信息
     */
    static public function getWxNumberName($keyword)
    {
        return WxReportModel::getWxNumberName($keyword);
    }
    
    /**
     * 获取场景
     */
    static public function getSceneList()
    {
        return SceneModel::getSceneList();
    }
    
    /**
     * 获取公众号详细信息
     */
    static public function getWxNumberInfo()
    {
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : 0;
        $model = new UserOrderModel();
        $query =$model::select('user_order.id','order_info.back_money','task.now_fans')
                ->leftJoin('order_info', 'user_order.id', '=', 'order_info.oid')
                ->leftJoin('task', 'user_order.id', '=', 'task.id');
        //获取分页数据
        $data = $model->getPages($query, 2, 10);
        
        
        return $data;
        //return UserOrderModel::getWxNumberInfo();

    }
}
