<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/7/10
 * Time: 14:05
 */
namespace App\Services\Impl\Count;
use App\Models\Count\TaskSummaryModel;
use App\Models\Count\WeChatOrderModel;
use App\Models\Count\WeChatReportModel;
use App\Models\Count\WxstatisticsWxModel;
use App\Services\CommonServices;

class SignalReportServicesImpl extends CommonServices{
    public static function SignalReportCount($array){
        set_time_limit(120);
        $initialize= SignalReportServicesImpl::getArray($array);
        $pagesize=$initialize['pagesize'];
        $wx_id=$initialize['wx_id'];
        $page=$initialize['page'];
        unset($initialize['wx_id']);
        unset($initialize['page']);
        unset($initialize['pagesize']);
        $where['date_time']= SignalReportServicesImpl::getWhereArray($initialize,'date_time');
        if(!empty($wx_id)){
            $where['wx_id'] = $wx_id;
        }
        //根据微信号去查询订单号-重组订单数据
        $data_order = WeChatOrderModel::getWxinfotwhere($where,$page,$pagesize);

        //获取所有微信公众号
        $order_wxname = 1;
        $data_order_wxname = WeChatOrderModel::getWxinfotwhere($where,$page,$pagesize,$order_wxname);
        if(count($data_order['data'])>0){
            if(isset($where['date_time']['excel'])){
                $data_order['end'] = $data_order['count'];
            }else{
                if($data_order['count']<10){
                    $data_order['end'] = $data_order['count'];
                }elseif($data_order['count']>=10 ){
                    $data_order['end'] = 10;
                }
            }
            
        }
        
        if(isset($where['date_time']['excel'])){
            unset($where['date_time']['excel']);
        }

        if(isset($data_order)){
            $data_order_sum = array();
            //查询每个订单的微信数据
            foreach ($data_order['data'] as $ka => $va) {
                $where['order']  = $va;
                if(!empty($wx_id)){
                    $pagesize = 10;
                }else{
                    $pagesize = 7;
                }
                $data_order_sum[] = WeChatReportModel::getWxASumtwhere($where,$page,$pagesize);
            }

            $retuen['start'] = $data_order['start'];
            $retuen['end'] = $data_order['end'];
            $retuen['pageSize'] = $data_order['pageSize'];
            $retuen['count'] = $data_order['count'];
            $retuen['data'] = $data_order_sum;
            $retuen['wx_name'] = $data_order_wxname;
            return $retuen;
        }
        return null;
    }

        //常用参数赋初始值
    static public function getArray($array) {
        $newarray=array();
        foreach ($array as $key => $value) {
            switch ($key) {
                case 'startdate':
                    $newarray[$key]=empty($value)?date('Y-m-d',strtotime("-1 week")):$value;
                    break;
                case 'enddate':
                    $newarray[$key]=empty($value)?date('Y-m-d',strtotime("-1 day")):$value;
                    break;
                case 'page':
                    $newarray[$key]=empty($value)?1:$value;
                    break;
                case 'pagesize':
                    $newarray[$key]=empty($value)?10:$value;
                    break;
                default:
                    $newarray[$key]=$value;
                    break;
            }
        }
        return $newarray;
    }

    //构造where
    static public function getWhereArray($array,$time) {
        $newarray=array();
        foreach ($array as $key => $value) {
            switch ($key) {
                case 'startdate':
                    $newarray[]=array($time,'>=',$value);
                    break;
                case 'enddate':
                    $newarray[]=array($time,'<=',$value);
                    break;
                default:
                    if($value==null||$value==0){
                        
                    }else{
                        $newarray[$key]=$value;
                    }
                    //echo $key;
                    break;
            }
        }
        return $newarray;
    }
}