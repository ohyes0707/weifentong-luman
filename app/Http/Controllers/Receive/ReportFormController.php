<?php

namespace App\Http\Controllers\Receive;

use App\Http\Controllers\Controller;
use App\Services\Impl\Receive\ReportServicesImpl;
use Illuminate\Http\Request;
use App\Lib\HttpUtils\ApiSuccessWrapper;

class ReportFormController extends Controller{
 
    public function getSearchWxTaskData(Request $request) {
        $array=array(
            'type'=>$request->input('usertype'),
            'startdate'=>$request->input('startdate'),
            'enddate'=>$request->input('enddate'),
            'wx_id'=>$request->input('wx_id'),
            'page'=>$request->input('page'),
            'pagesize'=>$request->input('pagesize')
            //'bid'=>$request->input('bid')
        );
        if($request->input('wxname')!=null){
            $array['wx_id']=ReportServicesImpl::getWxId_Name($request->input('wxname'));
        }
        $data=ReportServicesImpl::getSearchWxTaskData($array);
        return ApiSuccessWrapper::success($data);
    }
    
    public function getSearchWxBussTaskData(Request $request) {
        $array=array(
            'type'=>$request->input('usertype'),
            'startdate'=>$request->input('startdate'),
            'enddate'=>$request->input('enddate'),
            'buss_id'=>$request->input('buss_id'),
            'page'=>$request->input('page'),
            'pagesize'=>$request->input('pagesize'),
            'wx_id'=>$request->input('wx_id')
        );
        $data=ReportServicesImpl::getSearchWxBussTaskData($array);
        return ApiSuccessWrapper::success($data);
    }
    
    public function getSearchBussTaskData(Request $request) {
        $array=array(
            'type'=>$request->input('usertype'),
            'startdate'=>$request->input('startdate'),
            'enddate'=>$request->input('enddate'),
            'buss_id'=>$request->input('bussid'),
            'page'=>$request->input('page'),
            'pagesize'=>$request->input('pagesize'),
            //'bid'=>$request->input('bid')
        );
        $data=ReportServicesImpl::getSearchBussTaskData($array);
        return ApiSuccessWrapper::success($data);
    }
    
    public function getSearchBussWxTaskData(Request $request) {
        $array=array(
            'type'=>$request->input('usertype'),
            'startdate'=>$request->input('startdate'),
            'enddate'=>$request->input('enddate'),
            'buss_id'=>$request->input('bussid'),
            'parent_id'=>$request->input('parentid'),
            'wx_id'=>$request->input('wx_id'),
            'page'=>$request->input('page'),
            'pagesize'=>$request->input('pagesize'),
        );
        if($request->input('wxname')!=null){
            $array['wx_id']=ReportServicesImpl::getWxId_Name($request->input('wxname'));
        }
        $data=ReportServicesImpl::getSearchBussWxTaskData($array);
        return ApiSuccessWrapper::success($data);
    }
    
    public function getSearchOneBussTaskData(Request $request) {
        $array=array(
            'type'=>$request->input('usertype'),
            'startdate'=>$request->input('startdate'),
            'enddate'=>$request->input('enddate'),
            'buss_id'=>$request->input('buss_id'),
            'wx_id'=>$request->input('wx_id'),
            'parent_id'=>$request->input('parent_id'),
            'page'=>$request->input('page'),
            'pagesize'=>$request->input('pagesize'),
        );
        if($request->input('parentname')!=null){
            $array['parent_id']=ReportServicesImpl::getBussId_Name($request->input('parentname'));
        }
        $data=ReportServicesImpl::getSearchOneBussTaskData($array);
        return ApiSuccessWrapper::success($data);
    }
    
    
    public function getSearchSonBussWxTaskData(Request $request) {
        $array=array(
            'type'=>$request->input('usertype'),
            'startdate'=>$request->input('startdate'),
            'enddate'=>$request->input('enddate'),
            'buss_id'=>$request->input('bussid'),
            'page'=>$request->input('page'),
            'pagesize'=>$request->input('pagesize'),
        );
        $data=ReportServicesImpl::getSearchSonBussWxTaskData($array);
        return ApiSuccessWrapper::success($data);
    }
}