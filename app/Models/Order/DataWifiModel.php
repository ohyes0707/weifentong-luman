<?php

namespace App\Models\Order;

use App\Models\CommonModel;

class DataWifiModel extends CommonModel{

    protected $table = 'data_iwifi';

    protected $primaryKey = 'id';

    public $timestamps = false;
    
    
    /** 
    * 获取所有的省份以及省份下的城市,不应用在系统中,为了生成插件js所生成的方法
    * @return array 
    */  
    public static function getAllCity(){
        //area_level 为0的,都是省份
        $where=array("area_level"=>0);
         $data=DataWifiModel::select()->where($where)->get()->toArray();

         $allcity=array();
         foreach ($data as $key => $data1) {
             //86为插件所需
            $allcity[86][]=array(
                        'code' => $data1['id'],
                        'address' => $data1['name']
                    );
            //area_level 1为城市的,pid是城市的code,筛选出该省份下的所有城市
            $wherepid=array("area_level"=>1,"pid"=>$data1['id']);
            $datacity=DataWifiModel::select()->where($wherepid)->get()->toArray();
            
            foreach ($datacity as $key2 => $value2) {      
                $allcity[$data1['id']][$value2['id']]=$value2['name'];
            }
         }
         print_r(json_encode($allcity,TRUE));
         die();
         return $data;
    }

    /**
     * 订单报表
     */
    static public function orderForm($data){
        foreach($data as $k=>$v){
            if(empty($v['hot_area'])){
                $data[$k]['hot_area'] = '全国';
            }
        }
        return $data;
    }
    /**
     * 订单查询
     */
    static public function orderSearch($data){
        foreach($data as $k=>$v){
            if(empty($v['hot_area'])){
                $data[$k]['hot_area'] = '全国';
            }
        }
        return $data;
    }
}