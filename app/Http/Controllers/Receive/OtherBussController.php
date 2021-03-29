<?php
namespace App\Http\Controllers\Receive;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Services\Impl\Receive\ReceiveServicesImpl;

class OtherBussController {
    
    public function setStFollow(Request $request) 
    {
        if($request->input('isnew')==1){
            $arr = array(
                'sex' => 0,
                'city' => '未知城市',
                'province' => '未知省份',
                'ghid' => $request->input('appid'),
                'behavior' => 3,
                'openid' => $request->input('openId'),
                'nickname' => '',
            );
            ReceiveServicesImpl::getFansBehavior($arr);
            $result = array("resultcode"=>"0","resultmsg"=>"ok");
            echo json_encode($result);
        }
    }
    
}
