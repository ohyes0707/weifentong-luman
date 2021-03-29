<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/7/10
 * Time: 14:05
 */
namespace App\Services\Impl\Count;
use App\Models\Count\WeChatReportModel;
use App\Models\Count\WeChatOrderModel;
use App\Models\Count\WxstatisticsModel;
use App\Services\CommonServices;

class WeChatReportServicesImpl extends CommonServices{

    public static function WeChatReportCount($array){
        set_time_limit(0);
        $initialize= WeChatReportServicesImpl::getArray($array);
        // $usertype=$initialize['usertype'];
        $pagesize=$initialize['pagesize'];
        $page=$initialize['page'];
        unset($initialize['usertype']);
        unset($initialize['page']);
        unset($initialize['pagesize']);
        $where= WeChatReportServicesImpl::getWhereArray($initialize,'date_time');
        $data = WeChatReportModel::getReportList($where,$page,$pagesize);

        if(count($data['data'])>0){
            $newarray=$data['data'];
            foreach ($newarray as $key => $value) {
                if($value['order_id_str']!=''){
                    //先查看Wxstatistics表里是否存储数据
                    $data_wx_one = WxstatisticsModel::getWxstatisticsList($value['date_time']);
                    if(!empty($data_wx_one)){
                        $data_wx = $data_wx_one;
                    }else{
                        //查询微信数据
                        $data_wx = WeChatOrderModel::getOrdertList($value['order_id_str'],$value['date_time']);
                        $add_data = $data_wx+$value;
                        //流水增幅
                        $data_wx['flowing_fans_water'] = round($add_data['flowing_water']-$add_data['new_fans_water'],2);
                        //日期
                        $add['date_time'] = $add_data['date_time'];
                        //成功关注
                        $add['follow_repeat'] = $add_data['follow_repeat'];
                        //流水
                        $add['flowing_water'] = round($add_data['flowing_water'],2);
                        //微信关注
                        $add['new_fans'] = $add_data['new_fans'];
                        //微信关注流水
                        $add['new_fans_water'] = round($add_data['new_fans_water'],2);
                        //流水增幅
                        $add['flowing_fans_water'] = $data_wx['flowing_fans_water'];

                        //对Wxstatistics表里的数据微信数据进行更新
                        if(!empty($data_wx_one)){
                            $add['id'] = $data_wx_one['id'];
                            //更新
                            $data_wx_one_up = WxstatisticsModel::updateWxstatistics($add);
                            unset($add);
                        }else{
                            //插入
                            $data_wx_one_add = WxstatisticsModel::addWxstatistics($add);
                        }
                    }
                    if(strstr($value['order_id_str'],',')){
                        $value['color'] = 'blue';
                    }
                    
                    $data_total[] = $data_wx+$value;
                }
            }

            $data['data'] = $data_total;
            // $retuen['orderid']=$orderid;
            $retuen = $data;
        }else{
            $retuen = $data;
        }

        return $retuen;
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