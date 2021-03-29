<?php

namespace App\Models\Count;
use App\Lib\WeChat\Third;
use App\Models\Buss\BussModel;
use App\Lib\WeChat\Wechat;
use App\Models\CommonModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class BackfillModel extends CommonModel{

    protected $table='y_backfill';

    protected $primaryKey = 'id';

    public $timestamps = false;
    
    //取出存在门店的日期
    static public function getBackFill($where,$list){
        $date_time = $where['date_time'];
        $wx_id = $list['wx_id'];
        // var_dump($date_time);die;
        $data_bf = BackfillModel::select('y_backfill.datetime',DB::raw('SUM(IFNULL(y_backfill.number,0)) as sum_number'))
        ->where('y_backfill.datetime','=',$date_time)
        ->where('y_backfill.wx_id','=',$wx_id)
        ->groupBy('y_backfill.wx_id')
        ->get()
        ->toArray();
        // var_dump($data_bf);
        return $data_bf;
    }

    static public function BackEdit($data){
        //分页判断
        if(!empty($data)){
            $model = BackfillModel::insert($data);

        return $model;
        }
    }

    static public function Backhold($data,$where){

        $date_time = $where['date_time'];
        $wx_id = $where['wx_id'];
        $bid = $data['bid'];
        //分页判断
        if(!empty($data)){
            $data_bf = BackfillModel::select('y_backfill.number','y_backfill.hold')
            ->where('y_backfill.datetime','=',$date_time)
            ->where('y_backfill.wx_id','=',$wx_id)
            ->where('y_backfill.bid','=',$bid)
            ->get()
            ->toArray();

            return $data_bf;
        }
    }
    
}