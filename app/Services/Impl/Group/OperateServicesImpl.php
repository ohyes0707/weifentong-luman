<?php

namespace App\Services\Impl\Group;

use App\Models\Group\WOrderModel;
use App\Services\UserServices;

class OperateServicesImpl
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
     * 获取工单列表
     */
    static public function getWOrderList($startDate,$endDate,$stat,$gzh,$page,$pageSize){
        return WOrderModel::getWOrderList($startDate,$endDate,$stat,$gzh,$page,$pageSize);
    }    
    
    /**
     * 获取工单信息
     */
    static public function getWorkOrderInfo($workId){
        return WOrderModel::getWorkOrderInfo($workId);
    }    
    
    /**
     * 修改工单状态
     */
    static public function getUpWOrderStat($id,$stat,$new){
        return WOrderModel::getUpWOrderStat($id,$stat,$new);
    }    

}
