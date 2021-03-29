<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/9/1
 * Time: 14:05
 */
namespace App\Services\Impl\Count;
use App\Models\Order\DataWifiModel;
use App\Models\Order\TaskModel;
use App\Services\CommonServices;

class OrderSummaryServicesImpl extends CommonServices{
    static public function orderForm($date,$page,$pagesize){
        $data = TaskModel::orderForm($date,$page,$pagesize);
        if($data['data']){
            $area = DataWifiModel::orderForm($data['data']);
            foreach($area as $k=>$v){
                if($v['sex'] == 1){
                    $area[$k]['sex'] = '男';
                }elseif($v['sex'] == 2){
                    $area[$k]['sex'] = '女';
                }else{
                    $area[$k]['sex'] = '不限';
                }
            }
            $data['data'] = $area;
            return $data;
        }else{
            return false;
        }
    }
}