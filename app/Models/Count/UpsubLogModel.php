<?php

namespace App\Models\Count;

use App\Models\CommonModel;

class UpsubLogModel extends CommonModel{

    protected $table;

    protected $primaryKey = 'id';

    public $timestamps = false;

    public function __construct() {
        $this->table='y_upsub_log';
    }
    /**
     * 获取第三方平台配置信息
     */
    static public function getAddsubLog($date){
        $array=array(
            'mac'=>$date['mac'],
            'bid'=>$date['bid'],
            'bmac'=>$date['bmac'],
            'order_id'=>$date['order_id'],
            'openid'=>$date['openid'],
            'isold' => $date['isold'],
            'date'=>date("Y-m-d H:i:s"),
        );
        $model = UpsubLogModel::insert($array);
        //return $model?$model->toArray():null;
    }

}