<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/7/4
 * Time: 16:00
 */
namespace App\Http\Controllers\Count;
use App\Http\Controllers\Controller;
use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Services\Impl\Count\PlatSummaryServicesImpl;

class PlatSummaryController extends Controller{
    public function platCount(){
        $status = isset($_GET['status'])?$_GET['status']:1;
        $user = isset($_GET['user'])?$_GET['user']:0;
        $page = isset($_GET['page'])?$_GET['page']:1;
        $pagesize = isset($_GET['pagesize'])?$_GET['pagesize']:10;
        $start_date = isset($_GET['start_date'])?$_GET['start_date']:'';
        $end_date = isset($_GET['end_date'])?$_GET['end_date']:'';
        $data = PlatSummaryServicesImpl::platCount($start_date,$end_date,$status,$user,$page,$pagesize);
        return ApiSuccessWrapper::success($data);
    }
}