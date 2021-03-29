<?php

namespace App\Http\Controllers\Operate;
use App\Http\Controllers\Controller;
use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Services\Impl\statistics\StatisticsServicesImpl;


class AnalyzeController extends Controller{

    /**
     *  获取报表数据
     */
    public function  getReportData(){


        $action = $_GET['action'];

        if($action == 'order')
        {
            $getdate = $_GET;
            // 订单数据
            $data = statisticsServicesImpl::getorderdata($getdate);
            return ApiSuccessWrapper::success($data);

        }elseif($action == 'channel')
        {
            $getdate = $_GET;
            $data = statisticsServicesImpl::getchanneldata($getdate);
            return ApiSuccessWrapper::success($data);


        }else if($action == 'platform'){


            // 平台数据
            $getdate = $_GET;
            $data = statisticsServicesImpl::getplatformdata($getdate);
            return  ApiSuccessWrapper::success($data);
        }



   }

    /**
     * @return 报表的图片数据
     */
    public function  getPictureData()
    {
        $data = $_GET;
        $data = statisticsServicesImpl::getPictureData($data);
        return  ApiSuccessWrapper::success($data);

    }




}
