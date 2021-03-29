<?php

namespace App\Models\Count;
use App\Models\CommonModel;
use Illuminate\Support\Facades\Redis;


class OrderModel extends CommonModel{

    protected $table = 'y_order';

    protected $primaryKey = 'order_id';

    public $timestamps = false;

    /**
     * 订单列表
     * @param $start_date       开始时间
     */
    public static function getWxId($orderid){
        $flight = OrderModel::find($orderid);
        return $flight['o_wx_id'];
    }
}