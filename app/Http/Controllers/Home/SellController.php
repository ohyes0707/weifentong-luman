<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/6/12
 * Time: 9:46
 */
namespace App\Http\Controllers\Home;
use App\Http\Controllers\Controller;
use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Services\Impl\Order\OrderServicesImpl;
use App\Services\Impl\Sell\SellServicesImpl;

class SellController extends Controller{
    public function sellCount(){
        $start_date = isset($_GET['start_date'])?$_GET['start_date']:'';
        $end_date = isset($_GET['end_date'])?$_GET['end_date']:'';
        $wx_name = isset($_GET['wx_name'])?$_GET['wx_name']:'';
        $uid = isset($_GET['uid'])?$_GET['uid']:'';
        $page = isset($_GET['page'])?$_GET['page']:1;
        $pagesize = isset($_GET['pagesize'])?$_GET['pagesize']:10;
        $data = SellServicesImpl::sellCount($start_date,$end_date,$wx_name,$uid,$page,$pagesize);
        return ApiSuccessWrapper::success($data);
    }
}