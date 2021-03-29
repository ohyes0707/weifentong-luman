<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/5/27
 * Time: 14:18
 */
namespace App\Services;
interface OrderServices{

    /**
     * 获取订单列表
     * @param $start_date
     * @param $end_date
     * @param $wx_id
     * @param $order_status
     * @param $uid
     * @return mixed
     */
    static function getOrderList($start_date,$end_date,$wx_id,$order_status,$uid,$page,$pagesize);
    /**
     * 获取渠道列表
     */
//    static function getBussList();

    /**
     * 获取指定销售下的公众号
     * @param $uid    用户id
     * @return mixed
     */
//    static function getWxList($uid);
}