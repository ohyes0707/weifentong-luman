<?php

namespace App\Models\Order;

use App\Models\CommonModel;

class WOrderLogModel extends CommonModel{

    protected $table = 'y_work_order_log';

    protected $primaryKey = 'id';

    public $timestamps = false;

    
    static public function getWorkOrderLogInfo($workId)
    {
        $model = WOrderLogModel::where('work_id', '=', $workId)->orderBy('id', 'desc')->first();
        return $model?$model->toArray():null;
    }
}