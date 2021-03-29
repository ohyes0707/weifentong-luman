<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Services\Impl\Business\SettlementServicesImpl;
use Illuminate\Http\Request;
use App\Lib\HttpUtils\ApiSuccessWrapper;

class SettlementController extends Controller{
    /*父商户结算管理*/
    public function getParentBuss(Request $request) {
        $array=array(
            'buss_id' =>$request->input('buss_id'),
            'startdate'=>$request->input('startdate'),
            'enddate'=>$request->input('enddate'),
            'page'=>$request->input('page'),
            'pagesize'=>$request->input('pagesize')
        );
        // var_dump($termarray);die;
        $data=SettlementServicesImpl::ParentBuss($array);
        return ApiSuccessWrapper::success($data);
    }

    /*提现*/
    public function getWithdrawDeposit(Request $request) {
        $type = $request->input('type');
        if(!empty($type)){
            if($type == 1){
                $array=array(
                    'tixian_type' =>0,
                    'create_date'=>date("Y-m-d H:i:s"),
                    'status'=>4,
                    'bid' =>$request->input('bid'),
                    'real_name'=>$request->input('payee'),
                    'tixian_account'=>$request->input('account'),
                    'op_money'=>$request->input('amount'),
                );
            }elseif ($type == 2) {
                $array=array(
                    'tixian_type' =>1,
                    'create_date'=>date("Y-m-d H:i:s"),
                    'status'=>4,
                    'bid' =>$request->input('bid'),
                    'real_name'=>$request->input('payee'),
                    'opening_bank'=>$request->input('bank'),
                    'tixian_account'=>$request->input('cardnumber'),
                    'op_money'=>$request->input('amount')
                );
            }
        }
        $data=SettlementServicesImpl::WithdrawDeposit($array);
        return ApiSuccessWrapper::success($data);
    }

    /*总额*/
    public function getParentSum(Request $request) {
        $array=array(
            'buss_id' =>$request->input('buss_id'),
        );
        
        $data=SettlementServicesImpl::getParentSum($array);
        return ApiSuccessWrapper::success($data);
    }

    /*提现查看*/
    public function getWithdrawLook(Request $request) {
        $array=array(
            'buss_id' =>$request->input('buss_id'),
            'lid'=>$request->input('lid'),
            'op_money'=>$request->input('op_money'),
            'sbid'=>$request->input('sbid'),
        );
        // var_dump($termarray);die;
        $data=SettlementServicesImpl::getWithdrawLook($array);
        return ApiSuccessWrapper::success($data);
    }

    /*子商户审核通过*/
    public function getLook(Request $request) {
        $array=array(
            'buss_id' =>$request->input('buss_id'),
            'lid'=>$request->input('lid'),
            'op_money'=>$request->input('op_money'),
            'sbid'=>$request->input('sbid'),
        );
        $data=SettlementServicesImpl::getLook($array);
        return ApiSuccessWrapper::success($data);
    }

    /*子商户审核驳回*/
    public function getReject(Request $request) {
        $array=array(
            'buss_id' =>$request->input('buss_id'),
            'lid'=>$request->input('lid'),
            'reject' =>$request->input('reject'),
        );
        // var_dump($termarray);die;
        $data=SettlementServicesImpl::getReject($array);
        return ApiSuccessWrapper::success($data);
    }
    
    /*子商户结算管理*/
    public function getSonBuss(Request $request) {
        $array=array(
            'buss_id' =>$request->input('buss_id'),
            'startdate'=>$request->input('startdate'),
            'enddate'=>$request->input('enddate'),
            'page'=>$request->input('page'),
            'pagesize'=>$request->input('pagesize'),
            'status'=>$request->input('status'),
            'buss_one'=>$request->input('buss_one'),
        );
        // var_dump($termarray);die;
        $data=SettlementServicesImpl::getSonBuss($array);
        return ApiSuccessWrapper::success($data);
    }

    /*商户信息*/
    public function getBussInfo(){
        $userId = isset($_GET['userid'])?$_GET['userid']:'';
        $res = SettlementServicesImpl::getBussInfo($userId);
        return ApiSuccessWrapper::success($res);
    }
}