<?php

namespace App\Models\Group;

use App\Models\Group\CommonModel;

class WOrderModel extends CommonModel{

    protected $table = 'y_work_order';

    protected $primaryKey = 'id';

    public $timestamps = false;

    
    static public function getWOrderList($startDate,$endDate,$stat,$gzh,$page,$pageSize)
    {
        if($startDate==0){$startDate='2009-10-10';}
        if($endDate==0){$endDate=date("Y-m-d H:i:s");}
        $map[] = array('commit_time','>',  $startDate);
        $map[] = array('commit_time','<',  $endDate);
        
        if($stat>0)
            $map['w_status']=$stat;

        if($gzh>0)
            $map['wx_id']=$gzh;
        $model = WOrderModel::where($map);
        $data=self::getPages($model, $page,$pageSize);
        return $data?$data:null;
    }

    static public function getWorkOrderInfo($workId)
    {
        $model = WOrderModel::where('id', '=', $workId)->first();
        return $model?$model->toArray():null;
    }
    
    static public function getUpWOrderStat($id,$stat,$new)
    {
        $map=array(
            'id'=>$id,
            'w_status'=>$stat,
        );
        $model = WOrderModel::where($map)->update(array('w_status' => $new));
        return true;
    }
}