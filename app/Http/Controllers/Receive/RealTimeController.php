<?php
namespace App\Http\Controllers\Receive;

use App\Http\Controllers\Controller;
use App\Services\Impl\Receive\RealTimeServicesImpl;
use App\Lib\HttpUtils\ApiSuccessWrapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class RealTimeController {
    
    public function getSumPlatform() 
    {   
        header('Access-Control-Allow-Origin:'.Config::get('config.OPERATE_URL'));
        header("Cache-Control:no-cache,must-revalidate,no-store");
        header("Pragma:no-cache");
        header("Expires:-1");
        $RealTimeServicesImpl=new RealTimeServicesImpl();
        $data= $RealTimeServicesImpl->getSumPlatform();
        return ApiSuccessWrapper::success($data);
    }
    
    public function getSumDesc(Request $request)
    {
        header('Access-Control-Allow-Origin:'.Config::get('config.OPERATE_URL'));
        header("Cache-Control:no-cache,must-revalidate,no-store");
        header("Pragma:no-cache");
        header("Expires:-1");
        $array=array(
            'pbid'=>$request->input('pbid'),
            'bussid'=>$request->input('bussid'),
        );
        $RealTimeServicesImpl=new RealTimeServicesImpl();
        $data= $RealTimeServicesImpl->getUpDownBuss($array);
        return ApiSuccessWrapper::success($data);
    }
}
