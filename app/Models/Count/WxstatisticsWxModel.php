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
use App\Models\Count\WxstatisticsWxModel;

class WxstatisticsWxModel extends CommonModel{

    protected $table='y_wxstatistics_wx';

    protected $primaryKey = 'id';

    public $timestamps = false;
    
    static public function getWxstatisticsWxList($order,$date_time){
        //分页判断
        if(!empty($date_time)){
            $model = WxstatisticsWxModel::select('y_wxstatistics_wx.id','y_wxstatistics_wx.follow_repeat','y_wxstatistics_wx.flowing_water','y_wxstatistics_wx.new_fans','y_wxstatistics_wx.new_fans_water','y_wxstatistics_wx.flowing_fans_water','y_wxstatistics_wx.wx_id','wx_info.wx_name')
            ->rightJoin('wx_info','y_wxstatistics_wx.wx_id','=','wx_info.id')
            ->where('y_wxstatistics_wx.date_time','=',$date_time)
            ->where('wx_info.id','=',$order['xid']);
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

    static public function addWxstatisticsWx($data){
        //分页判断
        if(!empty($data)){
            $model = WxstatisticsWxModel::insert($data);

            if($model){
                return $model;
            }
            return null;
        }else{
            return null;
        }
        // var_dump($data_wx_total);die;
        
    }

    static public function updateWxstatisticsWx($array) {
        $id = $array['id'];
        unset($array['id']);
        $model = WxstatisticsWxModel::where('id', $id)->update($array);
        if($model){
            return $model;
        }
    }
    
}