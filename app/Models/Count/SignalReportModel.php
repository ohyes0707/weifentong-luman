<?php

namespace App\Models\Count;

use App\Models\CommonModel;

class WeChatReportCountModel extends CommonModel{

    protected $table='y_order';

    protected $primaryKey = 'order_id';

    public $timestamps = false;
    
    static public function getReportList($date){
        $model= TaskSummaryModel::select('buss_id','username')
                ->leftJoin('bussiness','bussiness.id','=','y_task_summary.buss_id')
                ->where($where)
                ->groupBy('y_task_summary.buss_id');
        $data= self::getPages($model,10);
        if(count($data)>0){
            $newarray=$data['data'];
            foreach ($newarray as $key => $value) {
                if($value['buss_id']!=''){
                    $buss_id[]=$value['buss_id'];
                }
            }
            $retuen['count']=$data['count'];
            $retuen['buss_id']=$buss_id;
            $retuen['data']=$newarray;
            return $retuen;
        }
        return null;
    }
    
}