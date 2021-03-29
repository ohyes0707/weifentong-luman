<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/8/29
 * Time: 9:53
 */
namespace App\Http\Controllers\Count;
use App\Http\Controllers\Controller;
use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Services\Impl\Count\SaleSummaryServicesImpl;

class SaleFormController extends Controller{
    public function saleStatistics(){
        $start_date = isset($_GET['start_date'])?$_GET['start_date']:'';
        $end_date = isset($_GET['end_date'])?$_GET['end_date']:'';
        $page = isset($_GET['page'])?$_GET['page']:1;
        $pagesize = isset($_GET['pagesize'])?$_GET['pagesize']:10;
        $sales = isset($_GET['sales'])?$_GET['sales']:'';
        $data = SaleSummaryServicesImpl::saleStatistics($start_date,$end_date,$page,$pagesize,$sales);
        return ApiSuccessWrapper::success($data);
    }
}