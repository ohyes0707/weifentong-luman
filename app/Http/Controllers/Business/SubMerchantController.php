<?php
namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Services\Impl\Count\SubMerchantServicesImpl;
use Illuminate\Http\Request;

class SubMerchantController extends Controller{
 
    public function getSearchSubTaskData(Request $request) {
        $array=array(
            'buss_id'=>$request->input('buss_id'),
            'parent_id'=>$request->input('parent_id'),
            'page'=>$request->input('page'),
            'pagesize'=>$request->input('pagesize')
        );
        $data=SubMerchantServicesImpl::getSearchSubTaskData($array);
        return ApiSuccessWrapper::success($data);
    }
    
    public function getSubShopReport(Request $request) {
        $array=array(
            'buss_id'=>$request->input('bussid'),
            'startdate'=>$request->input('startdate'),
            'enddate'=>$request->input('enddate'),
            'page'=>$request->input('page'),
            'parent_id'=>$request->input('parentid'),
            'pagesize'=>$request->input('pagesize')
        );
        $data=SubMerchantServicesImpl::getSubShopReport($array);
        return ApiSuccessWrapper::success($data);
    }
    
    public function getHistoryFans(Request $request) {
        $array=array(
            'buss_id'=>$request->input('bussid'),
            'wx_id'=>$request->input('gzh'),
            'startdate'=>$request->input('startdate'),
            'enddate'=>$request->input('enddate'),
            'page'=>$request->input('page'),
            'pagesize'=>$request->input('pagesize')
        );
        $data=SubMerchantServicesImpl::getHistoryFans($array);
        return ApiSuccessWrapper::success($data);
    }
}