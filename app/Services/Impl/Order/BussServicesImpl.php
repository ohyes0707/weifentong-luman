<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/5/27
 * Time: 14:14
 */
namespace App\Services\Impl\Order;
use App\Models\Buss\BussModel;
use App\Models\Order\CountModel;
use App\Models\Order\TaskModel;
use App\Services\OrderServices;
use App\Models\Buss\BussInfoModel;
use App\Models\Buss\DeviceInfoModel;

class BussServicesImpl implements OrderServices{
    static public function getOrderList($start_date,$end_date,$wx_id,$order_status,$uid,$page,$pagesize)
    {
        // TODO: Implement getOrderList() method.
    }

    /**
     * 获取渠道列表
     * @return mixed
     */
    static public function getBussList(){
        return BussModel::getBussList();
    }

    /**
     * 获取订单已选择的渠道
     * @param $order_id 订单id
     * @return mixed
     */
    static public function setBussList($order_id){
        $buss_list = TaskModel::setBussList($order_id);
        if($buss_list){
            $buss_arr = CountModel::getCount($buss_list,'','');
            foreach($buss_arr as $k=>$v){
                $parent_id[$v['parent_id']]['parent_id'] = $v['parent_id'];
            }
            foreach($parent_id as $k=>$v){
                $parent_id[$k]['total_fans'] = 0;
                $parent_id[$k]['subscribe_today'] = 0;
                $parent_id[$k]['un_subscribe_today'] = 0;
                $parent_id[$k]['un_subscribe'] = 0;
                foreach($buss_arr as $kk=>$vv){
                    if($v['parent_id'] == $vv['parent_id']){
                        $parent_id[$k]['total_fans'] += $vv['total_fans'];
                        $parent_id[$k]['subscribe_today'] += $vv['subscribe_today'];
                        $parent_id[$k]['un_subscribe_today'] += $vv['un_subscribe_today'];
                        $parent_id[$k]['un_subscribe'] += $vv['un_subscribe'];
                    }
                }
            }
            $buss_arr['parent_total'] = $parent_id;
            return $buss_arr;
        }else{
            return false;
        }
    }

    /****
     * @param $device_code
     * @return mixed
     */
    static public function getDeviceInfo($device_code){
        return DeviceInfoModel::getDeviceInfo($device_code);
    }

    /****
     * @param $bid
     * @return mixed
     */
    static public function getBussInfo($bid){
        return BussInfoModel::getBussInfo($bid);
    }

    /**
     * 渠道redis数据
     */
    static public function buss_redis(){
        $data = BussInfoModel::buss_redis();
        return $data;
    }

    /**
     * 获取订单信息
     */
    static public function get_order_task($wx_info_id_val){
        $data = TaskModel::get_order_task($wx_info_id_val);
        return $data;
    }
}