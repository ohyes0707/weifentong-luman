<?php

namespace App\Http\Controllers\Count;

use App\Http\Controllers\Controller;
use App\Services\Impl\Count\WeChatReportServicesImpl;
use Illuminate\Http\Request;
use App\Lib\HttpUtils\ApiSuccessWrapper;

class WeChatReportController extends Controller{
    /*以订单为维度微信关注报表*/
    public function getPlatformReport(Request $request) {
        $array=array(
            'excel'=>$request->input('excel'),
            // 'usertype'=>$request->input('user'),
            'startdate'=>$request->input('startdate'),
            'enddate'=>$request->input('enddate'),
            'page'=>$request->input('page'),
            'pagesize'=>$request->input('pagesize')
        );
        $data=WeChatReportServicesImpl::WeChatReportCount($array);
        return ApiSuccessWrapper::success($data);
    }

}