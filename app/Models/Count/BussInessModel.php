<?php

namespace App\Models\Count;
use App\Models\CommonModel;
use Illuminate\Support\Facades\Redis;


class BussInessModel extends CommonModel{

    protected $table = 'bussiness';

    protected $primaryKey = 'id';

    public $timestamps = false;

    static public function getBussList($page) {
        //分页判断
        $model= BussInessModel::select()
                ->where('pbid','=',0)
                ->orderBy('id');
        $data= self::getPages($model,$page);
        foreach ($data['data'] as $value) {
            $pdid[]=$value['id'];
        }
        $return['count']=$data['count'];
        $return['pdid']=$pdid;
        $return['data']=$data['data'];
        return $return;
    }

    static public function getListBussdata($where) {
        return $where;
    }

    static public function getSonBussList() {
        $model= BussInessModel::select()
                ->where('pbid','=',0)
                ->orderBy('id');
        $data= self::getPages($model,1);
        return $data;
    }
    
    static public function getSonList($arr){
        $retuen['count']= BussInessModel::select()
                ->where('pbid','=',$arr['parent_id'])
                ->orwhere('id','=',$arr['parent_id'])
                ->count();
        $model= BussInessModel::select()
                ->where('pbid','=',$arr['parent_id'])
                ->orwhere('id','=',$arr['parent_id'])
                ->orderBy('id');
        $data= self::getGroupPages($model,$arr['page'],$arr['pagesize'],$retuen['count']);
            if(count($data)>0){
                $newarray=$data['data'];
                $buss_id=array();
                foreach ($newarray as $key => $value) {
                    if($value['id']!=''){
                        $buss_id[]=$value['id'];
                    }
                }
                $retuen['buss_id']=$buss_id;
                $retuen['data']=$newarray;
                //$retuen['count']=$newarray;
                return $retuen;
            }
       return null;
    }
    
    static public function getUpMoney($id,$money) {
        $array['money']=$money;
        BussInessModel::where('id', $id)->update($array);
    }
    
    static public function getBussMoney($id) {
        $model=BussInessModel::where('id', $id)->get()->first();
        return $model['money'];
    }
    
    static public function getPrice($id) {
        $model=BussInessModel::where('id', $id)->get()->first();
        return $model['cost_price']?$model['cost_price']:1;
    }
    
    
    static public function getPercent($id) {
        $model=BussInessModel::where('id', $id)->get()->first();
        return $model['reduce_percent']?$model['reduce_percent']:6;
    }

    /**
     * 获取父级渠道的ID
     * @param $order_id
     * @return mixed
     */
    static public function getParentId($orderid,$bid) {
        $request=BussInessModel::where('id','=',$bid)->get()->first();
        return $request?$request->toArray()['pbid']:null;
    }
}