<?php

namespace App\Http\Controllers\Count;

use App\Http\Controllers\Controller;
use App\Services\Impl\Count\WithdrawalsServicesImpl;
use Illuminate\Http\Request;
use App\Lib\HttpUtils\ApiSuccessWrapper;

class WithdrawalsController extends Controller{
    /*报备系统提现列表*/
    public function getDepositlist(Request $request) {
        $array=array(
            'buss_id' =>$request->input('buss_id'),
            'startdate'=>$request->input('startdate'),
            'enddate'=>$request->input('enddate'),
            'page'=>$request->input('page'),
            'pagesize'=>$request->input('pagesize'),
            'status'=>$request->input('status'),
            'buss_one'=>$request->input('buss_one'),
        );
        $data=WithdrawalsServicesImpl::getDepositlist($array);
        return ApiSuccessWrapper::success($data);
    }

    /*提现查看*/
    public function getWithdrawLook(Request $request) {
        $array=array(
            'buss_id' =>$request->input('buss_id'),
            'lid'=>$request->input('lid'),
        );
        // var_dump($termarray);die;
        $data=WithdrawalsServicesImpl::getWithdrawLook($array);
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
        // var_dump($termarray);die;
        $data=WithdrawalsServicesImpl::getLook($array);
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
        $data=WithdrawalsServicesImpl::getReject($array);
        return ApiSuccessWrapper::success($data);
    }

}