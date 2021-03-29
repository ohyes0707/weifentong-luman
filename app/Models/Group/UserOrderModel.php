<?php

namespace App\Models\Group;

use App\Models\Group\CommonModel;

class UserOrderModel extends CommonModel{

    protected $table = 'user_order';

    protected $primaryKey = 'id';

    public $timestamps = false;

    
    static public function getWxNumberInfo()
    {
//        if ($keyword!=null) {
//            $map[] = array('wx_name','like', '%' . $keyword . '%');
//        }
        $model = UserOrderModel::select('user_order.id','order_info.back_money','task.now_fans')
                ->leftJoin('order_info', 'user_order.id', '=', 'order_info.oid')
                ->leftJoin('task', 'user_order.id', '=', 'task.id');
        $data= self::getPages($model, 1, 10);
        return $data->$model;
    }

}