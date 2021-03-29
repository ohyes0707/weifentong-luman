<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Services\Impl\Report\ReportServicesImpl;


class ReportController extends Controller
{
    /**
     * 获取条件获取报备信息
     * @method GET
     * @return array
     */
    public function getReportList()
    {
        $start_date = isset($_GET['start_date'])?$_GET['start_date']:'';
        $end_date = isset($_GET['end_date'])?$_GET['end_date']:'';
        $wx_name = isset($_GET['wx_name'])?$_GET['wx_name']:'';
        $user_name = isset($_GET['user_name'])?$_GET['user_name']:'';
        $report_status = isset($_GET['report_status'])?$_GET['report_status']:'';
        $userid = isset($_GET['userid'])?$_GET['userid']:'';
        $page = isset($_GET['page'])?$_GET['page']:1;
        $pagesize = isset($_GET['pagesize'])?$_GET['pagesize']:10;
        $data['report_list'] = ReportServicesImpl::getReportList($start_date,$end_date,$wx_name,$user_name,$report_status,$userid,$page,$pagesize);
        $where = $userid?array('user_id'=>$userid):array();
        $data['wx_list'] = ReportServicesImpl::getWxList($where);
        return ApiSuccessWrapper::success($data);
    }


    /** 根据微信号获取报备信息
     * @return array
     */
    public function getReportByWxname(){
        $wx_name = $_GET['wx_name'];
        $data = array('data'=>'参数错误');
        if($wx_name){
            $data = ReportServicesImpl::getReportByWxname($wx_name);
            return ApiSuccessWrapper::success($data);
            exit;
        }
        return ApiSuccessWrapper::success($data);
    }



}
