<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/9/1
 * Time: 14:04
 */
namespace App\Http\Controllers\Count;
use App\Http\Controllers\Controller;
use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Services\Impl\Count\OrderSummaryServicesImpl;

class OrderSummaryController extends Controller{
    public function orderForm(){
        $page = isset($_GET['page'])?$_GET['page']:1;
        $pagesize = isset($_GET['pagesize'])?$_GET['pagesize']:10;
        $date = isset($_GET['date'])?$_GET['date']:date('Y-m-d',time());
        $data = OrderSummaryServicesImpl::orderForm($date,$page,$pagesize);
        return ApiSuccessWrapper::success($data);
    }
}