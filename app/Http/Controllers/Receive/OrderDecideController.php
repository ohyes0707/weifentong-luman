<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/10/11
 * Time: 9:24
 */
namespace App\Http\Controllers\Receive;
use App\Http\Controllers\Controller;
use App\Services\Impl\Receive\OrderDecideServicesImpl;

class  OrderDecideController extends Controller{
    //检查订单是否过期
    public function orderDecide(){
        OrderDecideServicesImpl::orderDecide();
    }
}