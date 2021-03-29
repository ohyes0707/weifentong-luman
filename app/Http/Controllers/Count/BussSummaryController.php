<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/7/10
 * Time: 14:02
 */
namespace App\Http\Controllers\Count;
use App\Http\Controllers\Controller;
use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Services\Impl\Count\BussSummaryServicesImpl;

class BussSummaryController extends Controller{
    public function bussCount(){
        $status = isset($_GET['status'])?$_GET['status']:1;
        $user = isset($_GET['user'])?$_GET['user']:0;
        $page = isset($_GET['page'])?$_GET['page']:1;
        $pagesize = isset($_GET['pagesize'])?$_GET['pagesize']:10;
        $start_date = isset($_GET['start_date'])?$_GET['start_date']:'';
        $end_date = isset($_GET['end_date'])?$_GET['end_date']:'';
        $buss = isset($_GET['buss'])?$_GET['buss']:'';
        $data = BussSummaryServicesImpl::bussCount($start_date,$end_date,$status,$user,$page,$pagesize,$buss);
        return ApiSuccessWrapper::success($data);
    }
    public function bussCount_detail(){
        $child = isset($_GET['child'])?$_GET['child']:'';
        $user = isset($_GET['user'])?$_GET['user']:0;
        $page = isset($_GET['page'])?$_GET['page']:1;
        $pagesize = isset($_GET['pagesize'])?$_GET['pagesize']:10;
        $start_date = isset($_GET['start_date'])?$_GET['start_date']:'';
        $end_date = isset($_GET['end_date'])?$_GET['end_date']:'';
        $buss = isset($_GET['buss'])?$_GET['buss']:'';
        $data = BussSummaryServicesImpl::bussCount_detail($start_date,$end_date,$user,$page,$pagesize,$buss,$child);
        return ApiSuccessWrapper::success($data);
    }
}