<?php

namespace App\Models\Count;

use App\Models\CommonModel;

class GetWxLogModel extends CommonModel{
    
    protected $table;

    protected $primaryKey = 'id';

    public $timestamps = false;

    public function __construct() {
        $this->table='y_getwx_log';
    }
    /**
     * 添加取号数据
     */
    static public function getAddWxLog($date){
        $array=array(
            'mac' => $date['mac'],
            'bid' => $date['bid'],
            'bmac' => $date['bmac'],
            'order_id' => $date['order_id'],
            'isold' => $date['isold'],
            'date' => date("Y-m-d H:i:s"),
        );
        $model = GetWxLogModel::insert($array);
        //return $model?$model->toArray():null;
    }

    static public function getInitialId() {
        $date = date("Y-m-d",strtotime("-1 day"));
        
        $where[] =array('date','>=',$date.' 00:00:00'); 
        $where[] =array('date','<=',$date.' 23:59:59'); 
        $model = GetWxLogModel::where($where)->orderBy('id','desc')->first()->toArray();
        $model2 = GetWxLogModel::where($where)->orderBy('id','asc')->first()->toArray();
        $id = array(
            'start' => floor($model2['id']/1000),
            'end' => ceil($model['id']/1000),
        );
        return $id;
    }
}