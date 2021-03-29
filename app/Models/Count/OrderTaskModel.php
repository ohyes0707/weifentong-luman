<?php

namespace App\Models\Count;
use App\Models\CommonModel;
use Illuminate\Support\Facades\Redis;


class OrderTaskModel extends CommonModel{

    protected $table = 'y_order_task';

    protected $primaryKey = 'id';

    public $timestamps = false;

    static public function getListWxBussPbid($wxid) {
        $wherearray=array(
            'y_order.o_wx_id'=>$wxid
        );
        //分页判断
        $data= OrderTaskModel::select('pbid')
                ->leftJoin('y_order','y_order_task.order_id','=','y_order.order_id')
                ->where($wherearray)
                ->groupBy('pbid')
                ->get();
        if($data!=null){
            $newarray=$data->toArray();
            foreach ($newarray as $key => $value) {
                if($value['pbid']!=''){
                    $pdid[]=$value['pbid'];
                }
            }
            return $pdid;
        }
        return null;
    }
    
    static public function getListWxBussid($wxpddata) {
        //分页判断
        $data= OrderTaskModel::select('pbid','buss_id')
                ->whereIn('pbid',$wxpddata)
                ->get();
        if($data!=null){
            $newarray=$data->toArray();
            foreach ($newarray as $key => $value) {
                if($value['buss_id']!=''){
                    $buss_id[]=$value['buss_id'];
                }
            }
            $buss['bussid']=$buss_id;
            $buss['list']=$newarray;
            return $buss;
        }
        return null;
    }
}