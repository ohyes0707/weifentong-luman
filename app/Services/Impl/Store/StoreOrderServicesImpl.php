<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/11/3
 * Time: 11:16
 */
namespace App\Services\Impl\Store;
use App\Models\Order\OrderModel;
use App\Models\Store\StoreModel;
use App\Models\Store\StoreWxModel;
use App\Services\CommonServices;
use Illuminate\Support\Facades\Redis;

class StoreOrderServicesImpl extends CommonServices{
    /**
     * 美业订单列表
     */
    public static function storeOrderList($wx_id,$page,$pagesize){
        $data = OrderModel::storeOrderList($wx_id,$page,$pagesize);
        if($data){
            foreach($data['data'] as $k=>$v){
                $arr = explode(',',$v['store_tags']);
                foreach($arr as $kk=>$vv){
                    $list = explode('/',$vv);
                    if(isset($data['data'][$k]['area'])){
                        $data['data'][$k]['area'] .= ','.$list[0];
                        $data['data'][$k]['brand'] .= ','.$list[1];
                    }else{
                        $data['data'][$k]['area'] = $list[0];
                        $data['data'][$k]['brand'] = $list[1];
                    }
                }
                $data['data'][$k]['sub_today'] = Redis::hget(date('Ymd'),'sum-'.$v['order_id'].'--3')+0;
                $data['data'][$k]['unsub_today'] = Redis::hget(date('Ymd'),'sum-'.$v['order_id'].'--5')+0;
            }
            return $data;
        }
        return false;
    }

    /**
     * 美业授权微信
     */
    public static function storeOrderAddWx(){
        $wx_list = StoreWxModel::storeOrderAddWx();
        $data = StoreModel::getStoreList();
        if($data && $wx_list){
            $data['wx_list'] = $wx_list;
            return $data;
        }
        return false;
    }

    /**
     * 美业新增订单
     */
    public static function storeOrderAdd($wx_id,$tags,$select_brand){
        if($tags){
            return OrderModel::storeOrderAdd($wx_id,$tags,$select_brand);
        }else{
            return false;
        }
    }
    /**
     * 美业订单状态修改
     */
    public static function changeStatus($oid){
        return OrderModel::changeStatus($oid);
    }
}