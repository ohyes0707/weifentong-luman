<?php
namespace App\Services\Impl\Buss;


use App\Services\CommonServices;
use App\Models\Buss\BussTaskModel;
use App\Models\Buss\TaskModel;
use Illuminate\Support\Facades\Redis;


class SonBussTaskServicesImpl extends CommonServices
{

   /*
    *  获取当前的商家的任务列表
    */
    static public function  CurrentTask($bussid)
    {
       return  BussTaskModel::CurrentTask($bussid);

    }
    /**
     *  获取历史任务
     */

    static  public  function historyTaskList($data){

        return  BussTaskModel::historyTaskList($data);


    }

    static public function sonBussList($data){

        return  BussTaskModel::sonBussList($data);

    }

    /**  决绝任务
     * @param $data
     * @return array
     */
    static  public  function refuseReport($data){

        return  BussTaskModel::refuseReport($data);


    }
    
    /**  编辑渠道一口价更新redis的price
     * @param $data
     * @return array
     */
    static  public  function upRedisBussPrice($bussid,$price){
        
        $alltask = TaskModel::getBussAllTask($bussid);
        $allredistask = Redis::hgetall($bussid);
        foreach ($alltask as $key => $value) {
            if($value['one_price']>0){
                
            } else {
                if(isset($allredistask[$value['order_id']])){
                    $orderinfo = json_decode($allredistask[$value['order_id']], true);
                    $orderinfo['price'] = $price;
                    Redis::hset($bussid, $value['order_id'], json_encode($orderinfo)); 
                }
            }
        }
        return  1;

    }
}
