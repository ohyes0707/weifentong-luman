<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/7/10
 * Time: 14:05
 */
namespace App\Services\Impl\Count;
use App\Models\Business\SettlementModel;
use App\Models\Business\WithdrawalsModel;
use App\Services\CommonServices;

class WithdrawalsServicesImpl extends CommonServices{

    public static function getDepositlist($array){
        if(empty($array['buss_id'])){
            $array['buss_id'] = 0;
        }
        $array['buss_id'] = SettlementModel::getBussId($array['buss_id']);
        $initialize= WithdrawalsServicesImpl::getArray($array);
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
        
        $where['buss_id'] = $buss_id['id_list'];

        if(!empty($status)){
            $where['status'] = $status;
        }
        
        if(empty($where['buss_id'])){
            return false;
        }

        $where['create_date']= WithdrawalsServicesImpl::getWhereArray($initialize,'create_date');
        $data = SettlementModel::getParentBuss($where,$page,$pagesize);
        // var_dump($buss_id);die;
        if(strstr($buss_id['id_list'],",")){
            $data['name_list'] = $buss_id['name_list'];
        }else{
            $data['name_list'][] = $buss_id;
        }
        
        return $data;
    }

    public static function getWithdrawLook($array){
        //查看
        $data = WithdrawalsModel::getWithdrawLook($array);
        return $data;
    }

    public static function getLook($array){
        //查看
        $data = WithdrawalsModel::getLook($array);
        //审核通过扣钱
        if($data){
            $deduct_money = SettlementModel::deduct_money($array);
        }
        return $data;
    }

    public static function getReject($array){
        //查看
        $data = WithdrawalsModel::getReject($array);
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