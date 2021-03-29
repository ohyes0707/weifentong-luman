<?php

namespace App\Models\Count;
use App\Lib\WeChat\Third;
use App\Models\Buss\BussModel;
use App\Lib\WeChat\Wechat;
use App\Models\CommonModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class MonBackModel extends CommonModel{

    protected $table='y_money_log_backups';

    protected $primaryKey = 'id';

    public $timestamps = false;

    static public function addMon($data){
        //分页判断
        if(!empty($data)){
            $model = MonBackModel::insert($data);

        return $model;
        }
    }
}