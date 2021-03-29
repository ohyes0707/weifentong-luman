<?php

namespace App\Http\Controllers\Count;

use App\Http\Controllers\Controller;
use App\Services\Impl\Count\BackfillServicesImpl;
use Illuminate\Http\Request;
use App\Lib\HttpUtils\ApiSuccessWrapper;

class BackfillController extends Controller{
    /*门店回填列表*/
    public function getBackFill(Request $request) {
        $array=array(
            'startdate'=>$request->input('startdate'),
            'enddate'=>$request->input('enddate'),
            'page'=>$request->input('page'),
            'pagesize'=>$request->input('pagesize'),
            'status'=>$request->input('status'),
        );
        $data=BackfillServicesImpl::getBackFill($array);
        return ApiSuccessWrapper::success($data);
    }

    /*回填渠道查询*/
    public function getBackEdit(Request $request) {
        $array=array(
            'datetime' =>$request->input('datetime'),
        );
        // var_dump($termarray);die;
        $data=BackfillServicesImpl::getBackEdit($array);
        return ApiSuccessWrapper::success($data);
    }

    /*回填数据添加*/
    public function BackEdit(Request $request) {
        $array=array(
            'wx_id'=>$request->input('wx_id'),
            'number'=>$request->input('number'),
            'datetime' =>$request->input('datetime'),
            'bid'=>$request->input('bid'),
            'hold'=>$request->input('hold'),
        );
        // var_dump($array);die;
        // 插入到backfiff表
        $data=BackfillServicesImpl::BackEdit($array);
        if($data){
            // 备份到y_task_summary_backups表 // 更新y_task_summary表
            $data_sum=BackfillServicesImpl::getOrder($array);
            // var_dump($data_sum);die;
            // 备份到y_money_log_backups表    // 更新y_money_log表
            $data_mon=BackfillServicesImpl::getOrder_backups($array);
            
            
        }
        
        
        return ApiSuccessWrapper::success($data);
    }

}