<?php

namespace App\Http\Controllers\Receive;
use App\Http\Controllers\Controller;
use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Services\Impl\Receive\ReceiveServicesImpl;
use App\Services\Impl\Common\WriteBehaviorServicesImpl;
use Illuminate\Http\Request;
use App\Services\Impl\Common\TakeNumServicesImpl;
use App\Services\Impl\Common\OldPlatformServicesImpl;
use App\Lib\HttpUtils\Net;
use Illuminate\Support\Facades\Redis;

class PublicNumController extends Controller{
    /**
     * 取号逻辑
     */
    public function getWxInfo(Request $request){
        $callback = $request->input('callback');
        $bid = $request->input('bid');
        $mac = $request->input('mac');
        $bmac = $request->input('bmac');
        $sex = $request->input('sex');
        $device_type = $request->input('device_type');
        //要取几个号
        $ordernum = $request->input('num');
        //门店id
        $store_id = $request->input('store_id');
        //品牌id
        $brand_id = $request->input('brand_id');
        //判断是否继续取号
        $mac = TakeNumServicesImpl::getMac($bid, $mac);
        $mac = Net::get_mac($mac);
        if($bmac == ''){
            $bmac=$mac;
        }
        TakeNumServicesImpl::getIsContinue($mac);
        TakeNumServicesImpl::getBidCity($bid);
        //记录取号次数
        $array=array(
            'behavior' => 6,
            'bid' => $bid,
            'mac' => $mac,
            'bmac' => $bmac,
            'store_id' => $store_id,
            //转成我们的省份
            'bid_province'=> isset(TakeNumServicesImpl::$area['province'])?TakeNumServicesImpl::$area['province']:'',
            //转成我们的城市
            'bid_city'=> isset(TakeNumServicesImpl::$area['city'])?TakeNumServicesImpl::$area['city']:'',
        );    
        if($request->input('is_count')!=2){
            $count = ReceiveServicesImpl::getFansBehavior($array);
        }
        //开启取号逻辑
        $data = ReceiveServicesImpl::getWxInfo($bid,$mac,$ordernum,$sex,$device_type, $brand_id, $store_id);
        if(!isset($data[0])||$data[0]['secretkey']==''){
            $ruserinfo = Redis::hgetall($mac);
            if(!isset($ruserinfo['follownum'])||$ruserinfo['follownum']<1)
            {
                //清除这个key
                Redis::del($mac);
            } 
            $result  = array(
                'error' => 40008,
            );
            //return $result;
            if($callback == null){
                return json_encode($result);
            } else {
                return $callback.'('.json_encode($result).')';
            }
        }else{
            $result  = array(
                'error' => 0
            );
            if($request->input('is_count')!=2){
                switch ($bid) {
                    case 264:
                        $array['behavior']=1;
                        $array['order_id'] = TakeNumServicesImpl::getPlatformOid($data[0]['oid'], $data[0]['type']);

                        $count = ReceiveServicesImpl::getFansBehavior($array);
                        $result['list'] = TakeNumServicesImpl::getDanDan($data, $bid, $mac, $bmac);
                        break;
                    case 266:
                        $rediskey = md5($mac.'gfrom');
                        if($ordernum!=''&&$ordernum>0){
                            Redis::set($rediskey ,$ordernum);
                        } else {
                            Redis::set($rediskey ,1);
                        }
                        Redis::expire($rediskey ,180);
                        $result['list'] = $data;
                        //取号是否成功纪录
                        $array['behavior']=1;
                        $array['order_id'] = TakeNumServicesImpl::getPlatformOid($data[0]['oid'], $data[0]['type']);
                        $count = ReceiveServicesImpl::getFansBehavior($array);
                        break;
                    default:
                        $result['list'] = $data;
                        //取号是否成功纪录
                        $array['behavior']=1;
                        $array['order_id'] = TakeNumServicesImpl::getPlatformOid($data[0]['oid'], $data[0]['type']);
                        $count = ReceiveServicesImpl::getFansBehavior($array);
                        break;
                }
            }
            if($callback == null){
                return json_encode($result);
            } else {
                return $callback.'('.json_encode($result).')';
            }
        }
        
    }
    
    /**
     * 模拟渠道信息
     */
    public function setChannelInfo(){
        $data = ReceiveServicesImpl::setChannelInfo();
        return ApiSuccessWrapper::success($data);
    }
    
    /**
     * 模拟用户信息
     */
    public function setUserInfo(){
        $data = ReceiveServicesImpl::setUserInfo();
        return ApiSuccessWrapper::success($data);
    }
    
    /**
     * 从数据库中导入到redis用户信息
     */
    public function getDbUserInfo(){
        
        $data = ReceiveServicesImpl::getDbUserInfo();
        return ApiSuccessWrapper::success($data);
    }
    
    /**
     * 统计涨粉数量
     */
    public function getCountFans(){
        $data = ReceiveServicesImpl::getCountFans();
        return ApiSuccessWrapper::success($data);
    }
    
    
    /**
     * 接口
     */
    public function getFansBehavior(Request $request){
        
        
        
//        //用户取号统计
//        $array=array(
//            'mac'=>'test12345678',
//            'bid'=>1,
//            'bmac'=>2,
//            'order_id'=>3,
//            'behavior'=>1,
//        );
        
//        //用户立即链接统计
//        $array=array(
//            'mac'=>'test12345678',
//            'bid'=>1,
//            'bmac'=>2,
//            'order_id'=>3,
//            'openid'=>'wetjoisndgjklsjakjfslasjkfd',
//            'behavior'=>2,
//        );
        
//        //用户关注统计
//        $array=array(
//            'openid'=>'wetjoisndgjklsjakjfslasjkfd',
//            'behavior'=>3,
//            'city'=>'绍兴',
//            'sex'=>1,
//            'ghid'=>'fsfasf'
//        );
        
//        //用户完成统计
//        $array=array(
//            'openid'=>'wetjoisndgjklsjakjfslasjkfd',
//            'mac'=>'test12345678',
//            'bid'=>1,
//            'bmac'=>2,
//            'order_id'=>3,
//            'behavior'=>4,
//        );

//        //用户取消关注统计
//        $array=array(
//            'openid'=>'wetjoisndgjklsjakjfslasjkfd',
//            'behavior'=>5,
//            'ghid'=>'fsfasf'
//        );
        $array=$request->all();
        
        //参数验证
        $array['behavior'] = TakeNumServicesImpl::getDefaultCompare($array);
        if($array['behavior'] == 0){
            echo '{ 40008 : "参数不全" }';
            die();
        }
        
        //从老平台取号去要发送数据给老平台
        if($array['behavior']==2){
            $array['mac'] = Net::get_mac($array['mac']);
            WriteBehaviorServicesImpl::getPlatformBid($array);
            if($request->input('type')>0){
                $array['order_id']= WriteBehaviorServicesImpl::getPlatformOid($array);
            }
        }

        if($request->input('behavior')==4&&$request->input('type')==4){
            OldPlatformServicesImpl::getSendFinish($array);
            if($request->input('type')>0){
                $array['order_id']= WriteBehaviorServicesImpl::getEndPlatformOid($array);
            }
        } 
        if($request->input('behavior')==3){
            if(!isset($array['province'])||$array['province']==''){
                $array['province']='未知省份';
            }
            if(!isset($array['city'])||$array['city']==''){
                $array['city']='未知城市';
            }

        }
        $data = ReceiveServicesImpl::getFansBehavior($array);
        return ApiSuccessWrapper::success($data);
    }
    
    public function getDelTask() {
        $array = json_decode(file_get_contents("php://input"),TRUE);
        $data = ReceiveServicesImpl::getDelTask($array);
        return ApiSuccessWrapper::success($array);
    }

    public function getIsFollownum(Request $request) {
        $ghid = $request->input('ghid');
        $mac = $request->input('mac');
        $data = ReceiveServicesImpl::getIsFollownum($mac,$ghid);
        return ApiSuccessWrapper::success($data);
    }

    public function getIsFollownumOpenid(Request $request) {
        $openid = $request->input('openid');
        $olderid = $request->input('oid');
        $mac = $request->input('mac');
        $type = $request->input('type');
        $bid = $request->input('bid');
        $data = ReceiveServicesImpl::getIsFollownumOpenid($openid,$olderid,$type,$mac,$bid);
        return ApiSuccessWrapper::success($data);
    }
}