<?php

namespace App\Models\Count;
use App\Lib\WeChat\Third;
use App\Models\Buss\BussModel;
use App\Lib\WeChat\Wechat;
use App\Models\CommonModel;
use App\Services\Impl\Wechat\WechatServicesImpl;
use App\Services\Impl\Wechat\WeChatReportServicesImpl;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class WxstatisticsModel extends CommonModel{

    protected $table='y_wxstatistics';

    protected $primaryKey = 'id';

    public $timestamps = false;
    
    static public function getWxstatisticsList($date_time){
        //分页判断
        if(!empty($date_time)){
            $model = WxstatisticsModel::select('id','follow_repeat','flowing_water','new_fans','new_fans_water','flowing_fans_water')
            ->where('date_time','=',$date_time);
            // ->get()
            // ->first()
            // ->toArray()
            $data_wx= self::getPages($model,1,1);

            if(empty($data_wx['data'][0])){
                return null;
            }
            return $data_wx['data'][0];
        }else{
            return null;
        }
        // var_dump($data_wx_total);die;
        
    }

    static public function addWxstatistics($data){
        //分页判断
        if(!empty($data)){
            $model = WxstatisticsModel::insert($data);

            if($model){
                return $model;
            }
            return null;
        }else{
            return null;
        }
        // var_dump($data_wx_total);die;
        
    }

    static public function updateWxstatistics($array) {
        $id = $array['id'];
        unset($array['id']);

        $model = WxstatisticsModel::where('id', $id)->update($array);
        if($model){
            return $model;
        }
    }
    
}