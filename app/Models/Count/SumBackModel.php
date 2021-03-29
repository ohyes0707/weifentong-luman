<?php

namespace App\Models\Count;
use App\Lib\WeChat\Third;
use App\Models\Buss\BussModel;
use App\Lib\WeChat\Wechat;
use App\Models\CommonModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class SumBackModel extends CommonModel{

    protected $table='y_task_summary_backups';

    protected $primaryKey = 'id';

    public $timestamps = false;

    static public function addSum($data){
        //分页判断
        if(!empty($data)){
            $model = SumBackModel::insert($data);

        return $model;
        }
    }
}