<?php
/**
 * Created by PhpStorm.
 * User: xufang
 * Date: 2017/7/10
 * Time: 14:05
 */
namespace App\Services\Impl\Business;
use App\Models\Business\SettlementModel;
use App\Models\Business\WithdrawalsModel;
use App\Services\CommonServices;

class SettlementServicesImpl extends CommonServices{
    public static function ParentBuss($array){
        $initialize= SettlementServicesImpl::getArray($array);
        $buss_id=$initialize['buss_id'];
        $pagesize=$initialize['pagesize'];
        $page=$initialize['page'];
        unset($initialize['buss_id']);
        unset($initialize['usertype']);
        unset($initialize['page']);
        unset($initialize['pagesize']);
        if(empty($array['buss_id'])){
            return false;
        }
        $where['buss_id'] = $buss_id;
        $where['create_date']= SettlementServicesImpl::getWhereArray($initialize,'create_date');
        // var_dump($where);die;
        $data = SettlementModel::getParentBuss($where,$page,$pagesize);
        
        //总额
        $ParentSum = SettlementModel::getParentSum($where);
        
        if(!empty($ParentSum)){
            //提现
            $data['ParentSum'] = $ParentSum;
        }else{
            $data['ParentSum'] = 0;
        }
        //总额需要加上子商户的钱
        
        if($ParentSum['pbid'] == 0){
            $SonSum = SettlementModel::getSonSum($buss_id);
            $son_money = empty($SonSum['son_money']) ? 0 : $SonSum['son_money'];
            $data['ParentSum']['money'] = $data['ParentSum']['money'] + $son_money;
        }

        //正在提现金额
        $Withdrawals = WithdrawalsModel::getWithdrawalsBuss($where);
        if(!empty($Withdrawals)){
            //提现
            $data['Withdrawals'] = $Withdrawals['op_money'];
        }else{
            $data['Withdrawals'] = 0;
        }
        
        if(!empty($ParentSum) && !empty($Withdrawals)){
            //余额
            $data['balance'] = $data['ParentSum']['money'] - $data['Withdrawals'];
        }else{
            $data['balance'] = $data['ParentSum']['money'];
        }
        
        return $data;
    }

    public static function WithdrawDeposit($array){
        $where['buss_id'] = $array['bid'];
        $data = WithdrawalsModel::setWithdrawDeposit($array);
        //总额
        return $data;
    }

    public static function getParentSum($array){
        $where['buss_id'] = $array['buss_id'];
        //总额
        $ParentSum = SettlementModel::getParentSum($where);
        if(!empty($ParentSum)){
            //提现
            $data['ParentSum'] = $ParentSum;
        }else{
            $data['ParentSum'] = 0;
        }

        //正在提现金额
        $Withdrawals = WithdrawalsModel::getWithdrawalsBuss($where);
        if(!empty($Withdrawals)){
            //提现
            $data['Withdrawals'] = $Withdrawals['op_money'];
        }else{
            $data['Withdrawals'] = 0;
        }
        
        if(!empty($ParentSum) && !empty($Withdrawals)){
            //余额
            $data['balance'] = $data['ParentSum']['money'] - $data['Withdrawals'];
        }else{
            $data['balance'] = $data['ParentSum']['money'];
        }
        return $data;
    }

    /*提现查看*/
    public static function getWithdrawLook($array){
        $data = WithdrawalsModel::getWithdrawLook($array);
        return $data;
    }

    /*子商户审核通过*/
    public static function getLook($array){
        $data = WithdrawalsModel::getLook($array);
        //审核通过扣钱
        if($data){
            $deduct_money = SettlementModel::deduct_money($array);
        }
        return $data;
    }

    /*子商户审核驳回*/
    public static function getReject($array){
        $data = WithdrawalsModel::getReject($array);
        return $data;
    }
    
    /*商户信息*/
    public static function getBussInfo($userId){
        return SettlementModel::getBussInfo($userId);
    }

    //子商户结算
    public static function getSonBuss($array){
        if(empty($array['buss_id'])){
            return false;
        }
        
        $initialize= SettlementServicesImpl::getArray($array);
        $buss_one=$initialize['buss_one'];
        $status=$initialize['status'];
        $buss_id=$initialize['buss_id'];
        $pagesize=$initialize['pagesize'];
        $page=$initialize['page'];
        unset($initialize['buss_id']);
        unset($initialize['buss_one']);
        unset($initialize['status']);
        unset($initialize['usertype']);
        unset($initialize['page']);
        unset($initialize['pagesize']);
        $id_list = SettlementModel::getSonBussId($buss_id);
        if(!empty($buss_one)){
            $where['buss_id'] = $buss_one;
        }else{
            $where['buss_id'] = $id_list['id_list'];
        }

        if(!empty($status)){
            $where['status'] = $status;
        }
        
        if(empty($where['buss_id'])){
            return false;
        }
        $where['create_date']= SettlementServicesImpl::getWhereArray($initialize,'create_date');
        $data = SettlementModel::getParentBuss($where,$page,$pagesize);
        $data['name_list'] = $id_list['name_list'];
        return $data;
    }

    /*获取商户id*/
    public static function get_buss_id($channel){
        $data = SettlementModel::get_buss_id($channel);
        return $data;
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