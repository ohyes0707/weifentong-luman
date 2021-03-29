<?php

namespace App\Http\Controllers\Count;

use App\Http\Controllers\Controller;
use App\Services\Impl\Count\SignalReportServicesImpl;
use Illuminate\Http\Request;
use App\Lib\HttpUtils\ApiSuccessWrapper;

class SignalReportController extends Controller{
    /*以公众号为维度微信关注报表*/
    public function getPublicSignalReport(Request $request) {
        $array=array(
            // 'gzh'=>$request->input('gzh'),
            'excel'=>$request->input('excel'),
            'wx_id'=>$request->input('wx_id'),
            'startdate'=>$request->input('startdate'),
            'enddate'=>$request->input('enddate'),
            'page'=>$request->input('page'),
            'pagesize'=>$request->input('pagesize')
        );
        $data=SignalReportServicesImpl::SignalReportCount($array);
        return ApiSuccessWrapper::success($data);
    }

}