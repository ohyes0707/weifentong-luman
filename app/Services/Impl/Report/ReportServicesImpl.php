<?php

namespace App\Services\Impl\Report;

use App\Models\Report\WxReportModel;
use App\Services\CommonServices;
use App\Services\ReportServices;

class ReportServicesImpl extends CommonServices
{
    /**
     * @param \App\Services\开始日期 $start_date
     * @param \App\Services\结束日期 $end_date
     * @param \App\Services\微信id $wx_name
     * @param \App\Services\报备状态 $report_status
     * @param \App\Services\用户id $userid
     * @param $page
     * @param $pagesize
     * @return array
     */
    public static function getReportList($start_date,$end_date,$wx_name,$user_name,$report_status,$userid,$page,$pagesize){
        $model = WxReportModel::getReportList($start_date,$end_date,$wx_name,$user_name,$report_status,$userid,$page,$pagesize);
        return $model;
    }

    /**
     * @param $where
     * @param $data
     * @return mixed
     */
    public static function updateReport($where,$data){
        $model = WxReportModel::updateReport($where,$data);
        return $model;
    }

    /**
     * 获取微信列表
     * @param $where
     * @return mixed
     */
    public static function getWxList($where){
        $model = WxReportModel::getWxList($where);
        return $model;
    }


    /**
     * @param $wx_name
     * @return mixed
     */
    public static function getReportByWxname($wx_name){
        $where = array('wx_name'=>$wx_name,'status'=>4);
        $date_time = date('Y-m-d H:i:s',strtotime("-15 day"));
        $where[] = array('create_time','>',$date_time);
        $model = WxReportModel::getReportByWxname($where);
        return $model;
    }
}
