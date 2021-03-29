<?php

namespace App\Models\Count;
use App\Lib\WeChat\Third;
use App\Models\Buss\BussModel;
use App\Lib\WeChat\Wechat;
use App\Models\CommonModel;
use App\Services\Impl\Wechat\WechatServicesImpl;
use App\Services\Impl\Wechat\WeChatReportServicesImpl;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class WeChatOrderModel extends CommonModel{

    protected $table='y_order';

    protected $primaryKey = 'order_id';

    public $timestamps = false;
    
    static public function getOrdertList($where,$date_time){
        //分页判断
        if(strpos($where, ',')){
            $where_list = explode(',', $where);
            foreach ($where_list as $kl => $vl) {
                $data_wx[] = WeChatOrderModel::getOrdertwhere($vl,$date_time);
            }
            foreach ($data_wx as $kx => $vx) {
                $kd = $vx['xid'];
                $data_wx_total[$kd] = $vx;
            }
            unset($data_wx);
            $data_wx = array();
            $data_wx['new_fans'] = 0;
            $data_wx['new_fans_water'] = 0;
            $data_wx['oid'] = '';
            foreach ($data_wx_total as $kt => $vt) {
                $data_wx['oid'] = $data_wx['oid'].','.$vt['oid'];
                $data_wx['xid'] = $vt['xid'];
                $data_wx['oprice'] = $vt['oprice'];
                $data_wx['shopid'] = $vt['shopid'];
                $data_wx['begin_date'] = $vt['begin_date'];
                $data_wx['end_date'] = $vt['end_date'];
                $data_wx['new_fans'] =$data_wx['new_fans'] + $vt['new_fans'];
                $data_wx['new_fans_water'] =$data_wx['new_fans_water'] + $vt['new_fans_water'];
            }
            return $data_wx;
        }else{
            $data_wx = WeChatOrderModel::getOrdertwhere($where,$date_time);
            return $data_wx;
        }
        
    }

    static public function getOrdertwhere($where,$date_time){
        $data_model= WeChatOrderModel::select('y_order.order_id AS oid','y_order.o_wx_id AS xid','y_order.o_per_price AS oprice','wx_info.default_shopid as shopid')
        ->rightJoin('wx_info','y_order.o_wx_id','=','wx_info.id')
        ->where('y_order.order_id',$where);
        // ->get()
        // ->first()
        // ->toArray();
        $data_wx_o= self::getPages($data_model,1,1);
        if(isset($data_wx_o['data'][0])){
            $data_wx = $data_wx_o['data'][0];
        }else {
            return 0;
        }

        if($data_wx){
            $data_wx['begin_date'] = $date_time;
            $data_wx['end_date'] = $date_time;
            //获取微信号token
            $options['access_token'] = WechatServicesImpl::getToken($data_wx['xid']);
            
            $Wechat = new Wechat($options);
            /*查询微信数据*/
            $data_wx_sum = $Wechat->getShopWifi($data_wx['begin_date'],$data_wx['end_date'],$data_wx['shopid']);
            /*获取新增数据*/
            if(empty($data_wx_sum['data'])){
                $data_wx['new_fans'] = 0;
                $data_wx['new_fans_water'] = 0;
            }else{
                $data_wx['new_fans'] = $data_wx_sum['data'][0]['new_fans'];
                $data_wx['new_fans_water'] = $data_wx_sum['data'][0]['new_fans']*$data_wx['oprice'];  
            }

            // var_dump($data_wx);
            return $data_wx;
        }

        return null;
    }

    static public function getWxinfotwhere($where,$page,$pagesize,$order_wxname = 0){
        if(isset($where['date_time']['excel'])){
            $excel = $where['date_time']['excel'];
            unset($where['date_time']['excel']);
        }
        $o_start_date = $where['date_time'][0][2];
        $o_end_date = $where['date_time'][1][2];

        if (!empty($where['wx_id'])) {
            /*有微信id的情况*/
            $where_xid = $where['wx_id'];
            $model= WeChatOrderModel::select(DB::raw('GROUP_CONCAT(y_order.order_id) as order_id_str'),'y_order.o_wx_id AS xid','wx_info.default_shopid as shopid','wx_info.wx_name',DB::raw('MIN(y_order.o_per_price) as price'))
            ->leftJoin('wx_info','y_order.o_wx_id','=','wx_info.id')
            ->where('y_order.o_wx_id',$where_xid)
            ->groupBy('y_order.o_wx_id');
        }else{
            /*无微信id的情况*/
            $model= WeChatOrderModel::select(DB::raw('GROUP_CONCAT(y_order.order_id) as order_id_str'),'y_order.o_wx_id AS xid','wx_info.default_shopid as shopid','wx_info.wx_name',DB::raw('MIN(y_order.o_per_price) as price'))
            ->leftJoin('wx_info','y_order.o_wx_id','=','wx_info.id')
            ->where(function ($query) use($o_start_date) {
                $query->where('y_order.o_start_date','<',$o_start_date)->where('y_order.o_end_date','>',$o_start_date);
            })
            ->orWhere(function ($query) use($o_end_date) {
                $query->where('y_order.o_start_date','<',$o_end_date)->where('y_order.o_end_date','>',$o_end_date);
            })
            ->groupBy('y_order.o_wx_id');
        }
        $count = count($model->get()->toArray());
        if(isset($excel)){
            if($excel==1){
                $pagesize = $count;
            }
        }
        if($order_wxname){
            $pagesize = $count;
        }
        
        $data= self::getPages($model,$page,$pagesize,$count);
        if($data){
            
            return $data;
        }
        return null;
    }
    
}