<?php
namespace App\Http\Controllers\Buss;
use App\Http\Controllers\Controller;
use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Services\Impl\Buss\SonBussTaskServicesImpl;
use Illuminate\Http\Request;


class SonBussTaskController extends Controller
{

    /**
     * @return array  获取当前的商家的buss列表
     */
    public  function  CurrentTask()
    {
        $data = $_GET;
        $data = SonBussTaskServicesImpl::CurrentTask($data);
        return ApiSuccessWrapper::success($data);

    }

    /**
     * 获取历史任务列表
     */
    public  function historyTaskList(){

        $data = $_GET;
        $data = SonBussTaskServicesImpl::historyTaskList($data);
        return ApiSuccessWrapper::success($data);

    }


    /**
     *  子商户统计
     */

    public function sonBussList(){

        $data = $_GET;
        $result = SonBussTaskServicesImpl::sonBussList($data);
        return ApiSuccessWrapper::success($result);

    }

    /**
     *
     */
    public  function  refuseReport(){

        $data = $_GET;
        $result = SonBussTaskServicesImpl::refuseReport($data);

        $array = array('data'=>$result);

        echo json_encode($array);


    }

    /**
     *编辑渠道一口价更新redis的price
     */
    public function upRedisBussPrice(Request $request) {
        $bussid = $request->input("bussid");
        $price = $request->input("price");
        $result = SonBussTaskServicesImpl::upRedisBussPrice($bussid,$price);
        return ApiSuccessWrapper::success($result);
    }
}


