<?php
namespace App\Services;


interface WorkOrderServices{
    /**
     * 获取工单列表
     * @param $startDate
     * @param $endDate
     * @param $stat
     * @param $gzh
     * @param $page
     * @param $pageSize
     * @param $userid
     * @return array
     */
    static public function getWOrderList($startDate,$endDate,$stat,$gzh,$page,$pageSize,$userid,$fanstate);
    
    
    /**
     * 获取工单信息
     * @param $workId
     * @return array
     */
    static public function getWorkOrderInfo($workId);
    
    /**
     * 修改工单状态
     * @param $id
     * @param $stat
     * @param $new
     * @param $$user_id
     * @return array
     */
    static public function getUpWOrderStat($id,$stat,$new,$user_id);
    
    
    /**
     * 获取公众号信息
     * @param $keyword
     * @return array
     */
    static public function getWxNumberName($keyword);
    
    /**
     * 获取场景
     * @return array
     */
    static public function getSceneList();
}