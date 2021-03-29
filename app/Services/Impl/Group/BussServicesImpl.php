<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/5/23
 * Time: 10:30
 */
namespace App\Services\Impl\Group;
use App\Models\Group\BussModel;
use App\Services\OperateServices;

class BussServicesImpl implements OperateServices{
    static public function getOrderList($start_date,$end_date,$wx_id,$order_status,$uid)
    {
        // TODO: Implement getOrderList() method
    }
    static public function getBussList(){
        return BussModel::getBussList();
    }
}