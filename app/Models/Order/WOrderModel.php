<?php

namespace App\Models\Order;

use App\Models\CommonModel;
use Illuminate\Support\Facades\DB;

class WOrderModel extends CommonModel{

    protected $table = 'y_work_order';

    protected $primaryKey = 'id';

    public $timestamps = false;

    /**
     * 工单
     * @param $startDate       开始时间
     * @param $endDate         结束时间
     * @param $stat            工单状态
     * @param $gzh             微信公众号ID
     * @param $page            页码
     * @param $pageSize        一页条数
     * @param $userid        用户id
     * @return array
     */
    static public function getWOrderList($startDate,$endDate,$stat,$gzh,$page,$pageSize,$userid,$fanstate)
    {
        if($startDate==0){$startDate='2009-10-10';}
        if($endDate==0){$endDate=date("Y-m-d H:i:s");}
        if($userid!=0){$map['user_id']=$userid;}
        if($fanstate!=0){$map['order_status']=$fanstate;}
        $map[] = array('commit_time','>',  $startDate);
        $map[] = array('commit_time','<',  date('Y-m-d',strtotime('+1 day',strtotime($endDate))));
        
        if($stat>0)
            $map['w_status']=$stat;

        if($gzh>0)
        {
            if($userid!=0){$map['report_id']=$gzh;}
            else {$map['wx_id']=$gzh;}
        }

        $model = WOrderModel::select('y_work_order.id','w_total_fans','order_id','commit_time','commit_time','y_work_order.wx_name','order_status','w_per_price','create_time','w_start_date','w_end_date','nick_name','y_work_order.device_type',
                DB::raw('CASE WHEN w_status = 1 THEN 1 WHEN w_status = 3 THEN 2
        WHEN w_status = 2 THEN 3  END AS w_status')
                )
                ->where($map)
                ->leftJoin('y_order', 'y_order.work_id', '=', 'y_work_order.id')
                ->leftJoin('user_info', 'user_info.uid', '=', 'y_work_order.user_id')
                ->orderBy('w_status', 'asc')
                ->orderBy('commit_time', 'desc')
                ->orderBy('y_work_order.id', 'desc');
        $data=self::getPages($model, $page,$pageSize);
        return $data?$data:null;
    }

    
    /**
     * 工单
     * @param $workId       工单ID
     * @return array
     */
    static public function getWorkOrderInfo($workId)
    {
        $model = WOrderModel::where('id', '=', $workId)->first();
        return $model?$model->toArray():null;
    }
    
    /**
     * 工单
     * @param $id          工单ID
     * @param $stat        工单现在状态
     * @param $new         工单需要修改成的状态
     * @return array
     */
    static public function getUpWOrderStat($id,$stat,$new,$user_id)
    {
        $map=array(
            'id'=>$id,
            'w_status'=>$stat,
        );
        if($user_id!=0){$map['user_id']=$user_id;}
        $model = WOrderModel::where($map)->update(array('w_status' => $new));
        
        return $model;
    }
    
    static public function getShopName() 
    {
        $model= WOrderModel::select('user_id','nick_name')
                ->leftJoin('user_info', 'user_info.uid', '=', 'y_work_order.user_id')
                ->groupby('user_id')
                ->get()
                ->toArray();
        return $model;
    }
}