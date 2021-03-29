<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/6/6
 * Time: 14:56
 */
namespace App\Models\Buss;
use App\Models\CommonModel;

class TaskModel extends CommonModel{

    protected $table = 'y_order_task';

    protected $primaryKey = 'id';

    public $timestamps = false;

    static public function setBussList($order_id){
        $where = array(
            ['task_status','!=','3']
        );
        if($order_id){
            $where['order_id'] = $order_id;
        }

        $data = TaskModel::select('buss_id','task_status','plan_fans','day_fans','parent_id','weight_value','user_type')
                            ->where($where)
                            ->get();
        return $data;
    }

    /**
     * 暂停任务
     * @param $order_id
     * @return mixed
     */
    static public function closeTask($order_id){
        $rtn = TaskModel::where('task_status','<>',3)->whereIn('order_id',$order_id)->update(['task_status'=>2]);
        return $rtn;
    }

    /**
     * 获取父级渠道的ID
     * @param $order_id
     * @return mixed
     */
    static public function getParentId($orderid,$bid) {
        $request=TaskModel::where('order_id','=',$orderid)->where('buss_id','=',$bid)->get()->first();
        return $request?$request->toArray()['parent_id']:null;
    }
    
    /**
     * 这个渠道下的所有任务
     * @param $order_id
     * @return mixed
     */
    static public function getBussAllTask($bussid) {
        $request=TaskModel::where('buss_id','=',$bussid)->where('task_status','=',1)->get();
        return $request?$request->toArray():null;
    }
}
