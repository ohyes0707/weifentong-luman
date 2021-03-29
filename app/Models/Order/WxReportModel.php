<?php

namespace App\Models\Order;

use App\Models\CommonModel;
use Illuminate\Support\Facades\DB;

class WxReportModel extends CommonModel{

    protected $table = 'y_wx_report';

    protected $primaryKey = 'id';

    public $timestamps = false;
    
    protected $status=3;   //3是通过验证的状态

    
    /** 
    * 获取报备成功列表
    *@param $keyword            搜索微信关键字
    * @return array 
    */    
    static public function getWxNumberName($userid)
    {
//        if ($keyword!=null&&$keyword!=0) {
//            $map[] = array('wx_name','like', '%' . $keyword . '%');
//        }
//        $map['status']=3;
        $map[]=array('status','>=', '3');
        if ($userid!=null&&$userid!=0) {
            $map['user_id']=$userid;
            $model = WxReportModel::select('id','wx_name','y_wx_report.wx_id','num','gdnum')
                    ->leftjoin(DB::raw('(SELECT COUNT(order_id) as num , o_wx_id  FROM y_order WHERE order_status = 1 or order_status = 2 or order_status = 5 GROUP BY o_wx_id) as hhd'),'hhd.o_wx_id','=','y_wx_report.wx_id')
                    ->leftjoin(DB::raw('(SELECT COUNT(id) as gdnum , wx_id  FROM y_work_order WHERE w_status = 1 GROUP BY wx_id) as work'),'work.wx_id','=','y_wx_report.wx_id')
                    ->where($map)->orderBy('id', 'DESC') ->get()->toArray();
        }else{
            $model = WxReportModel::select('id','wx_name','wx_id')->where($map)->groupBy('wx_name')->orderBy('id', 'DESC') ->get()->toArray();
        }
        
        //$data=self::getPages($model, 1);
        return $model?$model:null;
    }

}