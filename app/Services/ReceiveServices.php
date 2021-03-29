<?php

namespace App\Services;
interface ReceiveServices{

    /** 取号逻辑开始实现
     * @param $bid 渠道ID
     * @param $mac 用户mac
     * @return array
     */
    static public function getWxInfo($bid,$mac);
    
    /**
     * 判断是否继续涨粉
     * @param $orderinfo    订单信息
     * @return null
     */
    static public function getIsActivity($orderinfo);

    /**
     * 判断是否精准投放
     * @param $orderinfo    订单信息
     * @param $mac          用户mac
     * @return null
     */
    static public function getIsPrecision($orderinfo,$mac);
    
    /**
     * 判断是否已经关注
     * @param $orderinfo    订单信息
     * @param $userinfo     用户信息
     * @return null
     */
    static public function getOldUserNum($orderinfo,$userinfo);
    
    /**
     * 计算精准投放的分数
     * @param $orderinfo    订单信息
     * @param $userinfo     用户信息
     * @return null
     */
    static public function getAddOldUserNum($orderinfo,$userinfo);
    
    /**
     * 根据分数排序
     * @param $numbers    渠道下的订单
     * @return null
     */
    static public function getBubbleSort($numbers);
}