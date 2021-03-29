<?php
namespace App\Services;

interface ReportServices{

    /** 获取报备列表
     * @param $start_date 开始日期
     * @param $end_date 结束日期
     * @param $wx_id 微信id
     * @param $report_status 报备状态
     * @param $user_id 用户id
     * @return mixed
     */
    public static function getReportList($start_date,$end_date,$wx_id,$report_status,$user_id,$page,$pagesize);

    /** 获取报备微信列表
     * @param $where
     * @return mixed
     */
    public static function getWxList($where);
}