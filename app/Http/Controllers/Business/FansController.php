<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/8/1
 * Time: 16:07
 */
namespace App\Http\Controllers\Business;
use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Services\Impl\Business\FansServicesImpl;
use App\Services\Impl\Count\RevenueSummaryServicesImpl;
use Laravel\Lumen\Routing\Controller;

class FansController extends Controller{
    public function fansEarn(){
        $start_date = isset($_GET['start_date'])?$_GET['start_date']:'';
        $end_date = isset($_GET['end_date'])?$_GET['end_date']:'';
        $page = isset($_GET['page'])?$_GET['page']:1;
        $pagesize = isset($_GET['pagesize'])?$_GET['pagesize']:10;
        $buss = isset($_GET['buss'])?$_GET['buss']:1;
        $data = FansServicesImpl::fansEarn($start_date,$end_date,$page,$pagesize,$buss);
        return ApiSuccessWrapper::success($data);
    }

    public function fansEarn_child(){
        $start_date = isset($_GET['start_date'])?$_GET['start_date']:'';
        $end_date = isset($_GET['end_date'])?$_GET['end_date']:'';
        $page = isset($_GET['page'])?$_GET['page']:1;
        $pagesize = isset($_GET['pagesize'])?$_GET['pagesize']:10;
        $buss = isset($_GET['buss'])?$_GET['buss']:227;
        $data = FansServicesImpl::fansEarn_child($start_date,$end_date,$page,$pagesize,$buss);
        return ApiSuccessWrapper::success($data);
    }
}