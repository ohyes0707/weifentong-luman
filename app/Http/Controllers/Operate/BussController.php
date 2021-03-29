<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/5/23
 * Time: 10:28
 */
namespace App\Http\Controllers\Operate;
use App\Http\Controllers\Controller;
use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Services\Impl\Order\BussServicesImpl;

class BussController extends Controller{
    /**
     * 获取渠道列表
     */
    public function getBussList(){
        $data = BussServicesImpl::getBussList();
        return ApiSuccessWrapper::success($data);
    }

    /**
     * 获取订单已选择的渠道
     * @return array
     */
    public function setBussList(){
        $order_id = isset($_GET['order_id'])?$_GET['order_id']:'';
        $data = BussServicesImpl::setBussList($order_id);
        return ApiSuccessWrapper::success($data);
    }

    /**
     * 渠道redis数据
     */
    public function buss_redis(){
        $data = BussServicesImpl::buss_redis();
        return ApiSuccessWrapper::success($data);
    }
}