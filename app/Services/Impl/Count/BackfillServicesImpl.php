<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/7/10
 * Time: 14:05
 */
namespace App\Services\Impl\Count;
use App\Models\Count\BackfillModel;
use App\Models\Count\WeChatReportModel;
use App\Models\Count\SumBackModel;
use App\Models\Count\MoneyLogModel;
use App\Models\Count\MonBackModel;
use App\Services\CommonServices;

class BackfillServicesImpl extends CommonServices{

    public static function getBackFill($array){
        $initialize= BackfillServicesImpl::getArray($array);
        $status=$initialize['status'];
        $pagesize=$initialize['pagesize'];
        $page=$initialize['page'];
        unset($initialize['status']);
        unset($initialize['usertype']);
        unset($initialize['page']);
        unset($initialize['pagesize']);
        
        $where['date_time']= BackfillServicesImpl::getWhereArray($initialize,'date_time');
        $data = WeChatReportModel::getBackDate($where,$page,$pagesize);
        foreach ($data['data'] as $key => &$value) {
            $value['list'] = WeChatReportModel::getBackFilllist($value);
            $value['sum'] = 0;
            if(isset($value['list'])){
                foreach ($value['list'] as $k => &$v) {
                    $list = BackfillModel::getBackFill($value,$v);
                    if(!empty($list)){
                        $value['sum'] = $value['sum'] + $list[0]['sum_number'];
                        $v = $v+$list[0];
                    }
                }
                // var_dump($value['list']);die;
            }else{
                $value['list'] = array();
            }
            //状态筛选
            if($status == 1 && $value['sum'] != 0){
                unset($data['data'][$key]);
            }elseif ($status == 2 && $value['sum'] == 0) {
                unset($data['data'][$key]);
            }
            
        }
        
        return $data;
    }

    public static function getBackEdit($array){
        if(empty($array['datetime'])){
            return null;
        }
        
        $data = WeChatReportModel::getBackEdit($array);

        if(isset($data)){
            foreach ($data as $kb => &$vb) {
                $vb['buss'] = WeChatReportModel::getBuss($vb);
                $vb['sum'] = 0;
                if($vb['buss']){
                    foreach ($vb['buss'] as $key => $value) {
                        $data_buss = BackfillModel::Backhold($value,$vb);
                        if($data_buss){
                            $vb['sum'] = $vb['sum'] + $data_buss[0]['number'];
                            $vb['buss'][$key] = $value+$data_buss[0];
                        }
                    }
                }
            }
        }else{
            $value['list'] = array();
        }
        return $data;
    }

    //插入数据
    public static function BackEdit($array){
        if(empty($array['datetime'])){
            return null;
        }
        $data = BackfillModel::BackEdit($array);
        return $data;
    }

    //获取order信息 备份到y_task_summary_backups表 更新y_task_summary表
    public static function getOrder($array){
        if(empty($array['datetime'])){
            return null;
        }
        $data = WeChatReportModel::getOrder($array);
        
        if(!empty($data) && !empty($data['id']) && !empty($data['new_follow_repeat']) && !empty($data['new_nbg']) ){
            // 备份到y_task_summary_backups表 
            $array_add=array(
                'sum_id'=>$data['id'],
                'new_follow_repeat_backups'=>$data['new_follow_repeat'],
                'new_nbg_backups' =>$data['new_nbg'],
            );
            $sum_back = SumBackModel::addSum($array_add);

            // 更新y_task_summary表
            if($sum_back){
                $sum_id = $data['id'];
                $new_follow_repeat = $data['new_follow_repeat'] + $array['number'];
                $new_nbg = $data['new_nbg'] + $array['number'];
                $array_update=array(
                    'new_follow_repeat'=>$new_follow_repeat,
                    'new_nbg' =>$new_nbg,
                );
                $data_update = WeChatReportModel::updateSum($array_update,$sum_id);
                if($data_update){
                    return $data;
                }
            }
            
        }
        return '备份y_task_summary数据失败';
    }

    //获取order信息 // 备份到y_money_log_backups表    // 更新y_money_log表
    public static function getOrder_backups($array){
        if(empty($array['datetime'])){
            return null;
        }
        $data = MoneyLogModel::getOrder_backups($array);
        if(!empty($data) && !empty($data['id']) && !empty($data['num']) && !empty($data['newmoney']) && !empty($data['follow'])){
            // 备份到y_money_log_backups表 
            $array_add=array(
                'm_id'=>$data['id'],
                'num_backups'=>$data['num'],
                'newmoney_backups' =>$data['newmoney'],
                'follow_backups' =>$data['follow'],
            );
            $mon_back = MonBackModel::addMon($array_add);
            // 更新y_money_log表
            if($mon_back){
                $m_id = $data['id'];
                $follow = $data['follow'] + $array['number'];
                $num = $data['num'] + $follow*$data['price'];
                $newmoney = $num + $data['oldmoney'];

                $array_update=array(
                    'follow'=>$follow,
                    'num' =>$num,
                    'newmoney' =>$newmoney,
                );
                $data_update = MoneyLogModel::updateMon($array_update,$m_id);
                if($data_update){
                    return $data;
                }
            }
            
        }
        return '备份y_task_summary数据失败';
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
                    $newarray[$key]=empty($value)?date('Y-m-d'):$value;
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
                    $newarray[]=array($time,'<=',date("Y-m-d",strtotime("+1 day",strtotime($value))));
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