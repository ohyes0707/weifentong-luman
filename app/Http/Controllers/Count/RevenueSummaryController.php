<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/7/20
 * Time: 10:59
 */
namespace App\Http\Controllers\Count;
use App\Http\Controllers\Controller;
use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Services\Impl\Count\RevenueSummaryServicesImpl;

class RevenueSummaryController extends Controller{
    public function revenueCountBuss(){
        $user = isset($_GET['user'])?$_GET['user']:0;
        $page = isset($_GET['page'])?$_GET['page']:1;
        $pagesize = isset($_GET['pagesize'])?$_GET['pagesize']:10;
        $newpage = isset($_GET['newpage'])?$_GET['newpage']:1;
        $newpagesize = isset($_GET['newpagesize'])?$_GET['newpagesize']:'';
        $start_date = isset($_GET['start_date'])?$_GET['start_date']:'';
        $end_date = isset($_GET['end_date'])?$_GET['end_date']:'';
        $buss = isset($_GET['buss'])?$_GET['buss']:'';
        $data = RevenueSummaryServicesImpl::revenueCountBuss($start_date,$end_date,$user,$page,$pagesize,$buss,$newpage,$newpagesize);
        return ApiSuccessWrapper::success($data);
    }
    public function revenueCountBussExcel(){
        $user = isset($_GET['user'])?$_GET['user']:0;
        $start_date = isset($_GET['start_date'])?$_GET['start_date']:'';
        $end_date = isset($_GET['end_date'])?$_GET['end_date']:'';
        $buss = isset($_GET['buss'])?$_GET['buss']:'';
        $data = RevenueSummaryServicesImpl::revenueCountBussExcel($start_date,$end_date,$user,$buss);
        return ApiSuccessWrapper::success($data);
    }

    public function revenueDetail_buss(){
        $bid = isset($_GET['bid'])?$_GET['bid']:'';
        $start_date = isset($_GET['start_date'])?$_GET['start_date']:'';
        $end_date = isset($_GET['end_date'])?$_GET['end_date']:'';
        $page = isset($_GET['page'])?$_GET['page']:1;
        $pagesize = isset($_GET['pagesize'])?$_GET['pagesize']:10;
        $wx_id = isset($_GET['wx_id'])?$_GET['wx_id']:'';
        $data = RevenueSummaryServicesImpl::revenueDetail_buss($bid,$start_date,$end_date,$page,$pagesize,$wx_id);
        return ApiSuccessWrapper::success($data);
    }

    public function revenueDetail_wechat(){
        $wid = isset($_GET['wid'])?$_GET['wid']:"";
        $bid = isset($_GET['bid'])?$_GET['bid']:'';
        $user = isset($_GET['user'])?$_GET['user']:'';
        $start_date = isset($_GET['start_date'])?$_GET['start_date']:'';
        $end_date = isset($_GET['end_date'])?$_GET['end_date']:'';
        $page = isset($_GET['page'])?$_GET['page']:1;
        $pagesize = isset($_GET['pagesize'])?$_GET['pagesize']:2;
        $data = RevenueSummaryServicesImpl::revenueDetail_wechat($wid,$user,$page,$pagesize,$start_date,$end_date,$bid);
        return ApiSuccessWrapper::success($data);
    }

    public function revenueDetail_wechatOne(){
        $wid = isset($_GET['wid'])?$_GET['wid']:"";
        $bid = isset($_GET['bid'])?$_GET['bid']:'';
        $user = isset($_GET['user'])?$_GET['user']:'';
        $start_date = isset($_GET['start_date'])?$_GET['start_date']:'';
        $end_date = isset($_GET['end_date'])?$_GET['end_date']:'';
        $page = isset($_GET['page'])?$_GET['page']:1;
        $pagesize = isset($_GET['pagesize'])?$_GET['pagesize']:2;
        $data = RevenueSummaryServicesImpl::revenueDetail_wechatOne($wid,$user,$page,$pagesize,$start_date,$end_date,$bid);
        return ApiSuccessWrapper::success($data);
    }
}