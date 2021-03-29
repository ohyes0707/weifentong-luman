<?php

namespace App\Models\Count;

use App\Models\CommonModel;

class FansInfoModel extends CommonModel{

    protected $table;

    protected $primaryKey = 'id';

    public $timestamps = false;

    public function __construct() {
        $this->table='fans_log_10';
    }
    
    /**
     * 获取第三方平台配置信息
     */
    static public function getDbUserInfo($x){
        //$this->table='fans_log_1';
        $where[]=array('id', '<', $x*50);
        $where[]=array('id', '>', ($x-1)*50);
        $model = FansInfoModel::where($where)->get();
        return $model?$model->toArray():null;
    }

}