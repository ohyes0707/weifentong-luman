<?php

namespace App\Models\ThirdData;

use App\Models\CommonModel;

class UpSubYunDaiModel extends CommonModel{

    protected $table = 'up_sub_yundai';

    protected $primaryKey = 'id';

    public $timestamps = false;

    /***
     * 获取相关立即连接记录
     * @param $where
     * @return null
     */
    static public function getUpSubInfo($where){
        $model = UpSubYunDaiModel::where($where)
            ->get()->first();
        return $model?$model->toArray():null;

    }

    /***
     * 删除相关立即连接记录
     * @param $where
     * @return mixed
     */
    static public function delUpSubInfo($where){
        $res = UpSubYunDaiModel::where($where)
            ->delete();
        return $res;

    }

    /***
     * 获取相关立即连接记录集合
     * @param $where
     */
    static public function getUpSubInfoList($where){
        $res = UpSubYunDaiModel::where($where)
            ->orderBy('id','desc')->get();
        return $res->toArray();
    }

}