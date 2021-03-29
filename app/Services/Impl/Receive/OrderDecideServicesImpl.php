<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/10/11
 * Time: 9:27
 */
namespace App\Services\Impl\Receive;
use App\Models\Order\OrderModel;

class OrderDecideServicesImpl{
    //检查订单是否过期
    static public function orderDecide(){
        OrderModel::orderDecide();
    }
}