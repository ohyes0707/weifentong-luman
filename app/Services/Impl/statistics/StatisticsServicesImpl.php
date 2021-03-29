<?php
namespace App\Services\Impl\statistics;

use App\Services\CommonServices;
use App\Models\Count\TaskSummaryModel;

class StatisticsServicesImpl extends CommonServices{

    /**
     * 报表订单数据 以单个公众号 下单时间 总涨粉 已涨粉 取关 取关率
     */
    public static function getorderdata($getdade){

        return TaskSummaryModel::getorderinfo($getdade);

    }

    /**
     * 报表渠道数据   父渠道(子渠道) 日期 总涨分量 已涨分量 取关量 取关率
     */
    public static  function getchanneldata($getdate){



        if($getdate['bussid']==""){
            return TaskSummaryModel::getchanneldata($getdate);
        }else
        {
            return TaskSummaryModel::getonechanneldata($getdate);

        }
    }

    /**
     * 报表平台数据  日期 总涨分量 已涨分量 取关量 取关率
     */
    public static  function getplatformdata($getdade){

        return TaskSummaryModel::getplatformdata($getdade);

    }


    public static function getPictureData($data){

        return TaskSummaryModel::getPictureData($data);
    }





}



