<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/7/10
 * Time: 14:05
 */
namespace App\Models\Business;
use App\Models\CommonModel;
use App\Services\Impl\Business\SettlementServicesImpl;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class WithdrawalsModel extends CommonModel{
    protected $table='buss_tixian';

    protected $primaryKey = 'id';

    public $timestamps = false;
    
    static public function getWithdrawalsBuss($where){
        //分页判断
        $bid = $where['buss_id'];
        
        $Withdrawals= WithdrawalsModel::select(DB::raw('SUM(op_money) as op_money'))
        ->where('bid','=',$bid)
        ->where('status','=','4')
        ->get()
        ->first()
        ->toArray();

        if(!empty($Withdrawals['op_money'])){
            return $Withdrawals;
        }
        return null;
    }

    static public function setWithdrawDeposit($array){
        $logic=WithdrawalsModel::insert($array);
        return $logic?TRUE:FALSE;
    }

    static public function getLook($array){
        if(empty($array['lid'])){
            return false;
        }
        $id = $array['lid'];
        $logic=WithdrawalsModel::where('id', $id)
        ->update(array('status' => 1));

        return $logic?TRUE:FALSE;
    }

    static public function getReject($array){
        if(empty($array['reject'])){
            return false;
        }
        $id = $array['lid'];
        $reject = $array['reject'];
        $logic=WithdrawalsModel::where('id', $id)
        ->update(array('reason' => $reject,'status' => 2));
        return $logic?TRUE:FALSE;
    }
    

    static public function getWithdrawLook($where){
        //分页判断
        $id = $where['lid'];
        
        $WithdrawLook= WithdrawalsModel::select('buss_tixian.id','buss_tixian.bid','buss_tixian.create_date','buss_tixian.op_money','buss_tixian.status','buss_tixian.reason','buss_tixian.real_name','buss_tixian.tixian_account','buss_tixian.tixian_type','buss_tixian.opening_bank','buss_info.nick_name as username','bussiness.pbid')
        ->rightJoin('bussiness','bussiness.id','=','buss_tixian.bid')
        ->rightJoin('buss_info','bussiness.id','=','buss_info.bid')
        ->where('buss_tixian.id','=',$id)
        ->get()
        ->first()
        ->toArray();
        if(!empty($WithdrawLook)){
            return $WithdrawLook;
        }
        return null;
    }
    

}