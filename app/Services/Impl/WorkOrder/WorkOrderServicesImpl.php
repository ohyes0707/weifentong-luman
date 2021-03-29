<?php

namespace App\Services\Impl\WorkOrder;

use App\Models\Order\WOrderModel;
use App\Models\Order\WxReportModel;
use App\Models\Order\SceneModel;
use App\Models\Order\DataWifiModel;
use App\Models\Order\WOrderLogModel;
use App\Models\Order\OrderModel;
use App\Models\Count\TaskSummaryModel;
use App\Services\WorkOrderServices;
use App\Lib\WeChat\Wechat;
use App\Services\Impl\Wechat\WechatServicesImpl;
use App\Models\Order\WxModel;
use App\Models\User\UserModel;
//use Illuminate\Support\Facades\Log;

class WorkOrderServicesImpl implements WorkOrderServices
{
    /**
     * 获取工单列表
     * @param $startDate       开始时间
     * @param $endDate         结束时间
     * @param $stat            工单状态
     * @param $gzh             微信公众号ID
     * @param $page            页码
     * @param $pageSize        一页条数
     * @param $userid          用户id
     * @param $fanstate        订单涨粉状态
     * @return array
     */
    static public function getWOrderList($startDate,$endDate,$stat,$gzh,$page,$pageSize,$userid,$fanstate){
        $arr = WOrderModel::getWOrderList($startDate,$endDate,$stat,$gzh,$page,$pageSize,$userid,$fanstate);
        if($userid!=0){
            $model['data'] = TaskSummaryModel::getCount($arr['data'],$startDate,$endDate);
        }else{
            //$model['data'] = $arr['data'];
            $model['data'] = TaskSummaryModel::getCount($arr['data'],$startDate,$endDate);
        }
        
        $model['count']=$arr['count'];
        return $model;
    }    
    
    /**
     * 获取工单信息
     * @param $workId       工单ID
     * @return array
     */
    static public function getWorkOrderInfo($workId){
        $model=WOrderModel::getWorkOrderInfo($workId);
        $model['isorder']=OrderModel::getIsOrder($workId);
        return $model;
    }    
    
    /**
     * 获取工单信息
     * @param $workId       工单ID
     * @return array
     */
    static public function getWOrderTwoInfo($workId){
        $data['now']= WOrderModel::getWorkOrderInfo($workId);
        $data['old']= WOrderLogModel::getWorkOrderLogInfo($workId);
        return $data;
    }    
    
    /**
     * 修改工单状态
     * @param $id          工单ID
     * @param $stat        工单现在状态
     * @param $new         工单需要修改成的状态
     * @param $user_id     用户id
     * @return array
     */
    static public function getUpWOrderStat($id,$stat,$new,$user_id){
        
        //判断是否存在工单信息并修改
        $upnum=WOrderModel::getUpWOrderStat($id,$stat,$new,$user_id);
        //工单状态是否有修改
        if($upnum==1){
            $arr= WOrderModel::select('wx_info.secretkey','y_work_order.id','y_work_order.wx_id','y_work_order.w_total_fans','y_work_order.w_least_fans','y_work_order.w_advis_fans','y_work_order.w_max_fans','y_work_order.w_per_price','y_work_order.w_start_date','y_work_order.w_end_date','y_work_order.w_start_time','y_work_order.w_end_time','y_work_order.w_user_money','y_work_order.wx_name','y_work_order.user_id','wx_info.ghid','wx_info.appid','wx_info.default_shopid','wx_info.default_shopname','wx_info.head_img','wx_info.qrcode_url','y_work_order.device_type')
                            ->leftJoin('wx_info','y_work_order.wx_id','=','wx_info.id')
                            ->where('y_work_order.id', $id)->first()->toArray();
            $token= WechatServicesImpl::getToken($arr['wx_id']);
            //Log::info('token: '.$token);
            if($token){
                $wx = new Wechat();
                $wx->access_token=$token;
                $arr['secretkey']=$wx->add_device($arr['default_shopid'],'wifi',false);
                if(!$arr['secretkey'])   return false;
            }else{
                return false;
            }
            if( empty($arr['default_shopname']) || empty($arr['default_shopid'])){
                return false;
            }
            //print_r($token);
            //$arr['qrcode_url']= WxModel::get_qrcode($arr['wx_id']);
            //工单影响订单操作
            return OrderModel::getUpOrderStat($id,$stat,$new,$user_id,$arr);
        }
        
        return 0;
    }    

    
    /** 
    * 获取报备成功列表
    *@param $userid            
    * @return array 
    */    
    static public function getWxNumberName($userid)
    {
        return WxReportModel::getWxNumberName($userid);
    }
    
    /** 
    * 获取场景列表
    * @return array 
    */    
    static public function getSceneList()
    {
        return SceneModel::getSceneList();
    }
    
    /** 
    * 获取省份城市
    * @return array 
    */  
    static public function getLogList()
    {
        return WOrderLogModel::getLogList();
    }
    
    /** 
    * 获取单价
    * @return array 
    */  
    static public function getOlderPrice($userid)
    {
        return UserModel::getUserTiMoney($userid);
        //return OrderModel::getOlderPrice($userid);
    }
    
    /** 
    * 获取单价
    * @return array 
    */  
    static public function getReferencePrice($userid)
    {
        return OrderModel::getOlderPrice($userid);
    }
    
    /** 
    * 获取单价
    * @return array 
    */  
    static public function getShopName()
    {
        return WOrderModel::getShopName();
    }
}
