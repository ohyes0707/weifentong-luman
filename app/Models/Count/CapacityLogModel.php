<?php

namespace App\Models\Count;

use App\Models\CommonModel;
use Illuminate\Support\Facades\DB;

class CapacityLogModel extends CommonModel{

    protected $table = 'y_capacity_norm';

    protected $primaryKey = 'id';

    public $timestamps = false;

    /**
     * 获取省份
     */
    static public function getBidProvince($operat_query){
        $field = array(
            'pid',
            'boy_num',
            'girl_num',
            'province_name',
            'city_name',
            DB::raw('sum(capacity_num) as capacity_num')
        );
        if(isset($operat_query['keycode'])&&$operat_query['keycode']!=''){
            $model = CapacityLogModel::select($field)
                    ->where('area_id','<=',35)
                    ->where('province_name','like','%'.$operat_query['keycode'].'%')
                    ->groupBy('pid')
                    ->get();
        } else {
            $model = CapacityLogModel::select($field)
                    ->where('area_id','<=',35)
                    ->groupBy('pid')
                    ->get();
        }
        return $model?$model->toArray():null;
    }

    /**
     * 获取城市
     */
    static public function getBidCity(){
        $model = CapacityLogModel::select()
                ->get();
        //print_r($model->toArray());
        return $model?$model->toArray():null;
    }
    
    /**
     * 获取城市
     */
    static public function getBidSonCity($operat_query){
        $model = CapacityLogModel::select()
                ->where('city_name','like','%'.$operat_query['keycode'].'%')
                ->get()
                ->first();
        //print_r($model->toArray());
        return $model?$model->toArray():null;
    }
    
    /**
     * 获取城市
     */
    static public function getIsPro($keycode){
        $model = CapacityLogModel::select()
                ->where('province_name','like','%'.$keycode.'%')
                ->get()
                ->first();
        //print_r($model->toArray());
        return $model?TRUE:FALSE;
    }
}