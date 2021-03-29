<?php

namespace App\Models\Count;

use App\Models\CommonModel;

class CompletLogModel extends CommonModel{

    protected $table;

    protected $primaryKey = 'id';

    public $timestamps = false;

    public function __construct() {
        $this->table='y_complet_log';
    }

    static public function getCompletLog($date){
        $array=array(
            'mac'=>$date['mac'],
            'bid'=>$date['bid'],
            'bmac'=>$date['bmac'],
            'order_id'=>$date['order_id'],
            'openid'=>$date['openid'],
            'date'=>date("Y-m-d H:i:s"),
            'isContact'=>$date['isContact'],
            'isold' => $date['isold'],
        );
        $model = CompletLogModel::insert($array);
       // return $model?$model->toArray():null;
    }
}