<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/6/16
 * Time: 11:11
 */
namespace App\Services\Impl\Api;
use App\Models\Api\HotelModel;
use App\Models\Api\FansModel;
use App\Models\Count\GetWxSummaryModel;
use App\Models\Count\WeChatReportModel;
use App\Services\CommonServices;
use Illuminate\Support\Facades\Redis;

class HotelServicesImpl extends CommonServices
{
    public static function get_ghid_fan($userMac)
    {
        return FansModel::get_ghid_fan($userMac);
    }

    public static function sum_total($mapp)
    {
        $pdate = $mapp['pdate'];
        $time = date('Y-m-d');

        if($pdate == $time){
            $time_Redis = date('Ymd');
            $bid = $mapp['bid'];
            $data['quhao'] = Redis::hget($time_Redis,"sum--$bid-6");
            $data['quhaochenggong'] = Redis::hget($time_Redis,"sum--$bid-1");
            $data['lijilianjie'] = Redis::hget($time_Redis,"sum--$bid-2");
            $data['guanzhu'] = Redis::hget($time_Redis,"sum--$bid-3");
        }else{
            //取号
            $getwx_total = GetWxSummaryModel::getwx_total($mapp);
            if($getwx_total){
                $data['quhao'] = $getwx_total['getwx_total'];
            }

            //成功取号
            $success_getwx_total = WeChatReportModel::success_getwx_total($mapp);
            if($success_getwx_total){
                $data['quhaochenggong'] = $success_getwx_total['success_getwx_total'];
                //立即连接
                $data['lijilianjie'] = $success_getwx_total['complet_total'];
            }
            
            //关注数据
            $sum_total = FansModel::sum_total($mapp);
            if($sum_total){
                $data['guanzhu'] = $sum_total['sum'];
            }
        }
        return $data;
    }

}