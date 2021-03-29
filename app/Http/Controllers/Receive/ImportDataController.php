<?php

namespace App\Http\Controllers\Receive;

use App\Http\Controllers\Controller;
use App\Services\Impl\Receive\WriteServicesImpl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Lib\HttpUtils\Net;
use App\Lib\Data\Smschanzor;
class ImportDataController extends Controller{

    //检测ip是否在白名单内
    static public function checkUserIp(){
        $ip_allow_list = array('122.224.205.69','122.224.205.67');//白名单ip列表
        $user_ip = Net::user_ip();
        if(!in_array($user_ip,$ip_allow_list)) die('Ip '.$user_ip.' Permission denied');
    }

    public function getAddTaskData() {
        WriteServicesImpl::getAddTaskData();
    }
    
    public function getTestData() {
        WriteServicesImpl::getTestData();
    }
    
    public function getAddmoney(){
    	//结算钱
    	WriteServicesImpl::getAddmoney();
    }
        
    //导入redis数据
    public function getAddUserinfo(){
    	$key = WriteServicesImpl::getAddUserinfo();
        return $key;
    }
    
    //删除redis数据id初始值
    public function getDelUserinfoid(){
    	$key = WriteServicesImpl::getDelUserinfoid();
        return $key;
    }
    
    //删除redis数据
    public function getDelUserinfo(){
    	$key = WriteServicesImpl::getDelUserinfo();
        return $key;
    }
    
    public function getData(Request $request) {
        $key = $request->input('key');
            print_r(Redis::get($key));
    }
    
    public function getHData(Request $request) {
        $key = $request->input('key');
            print_r(Redis::hgetall($key));
    }
    
    
    public function delData(Request $request) {
        self::checkUserIp();
        $key = $request->input('key');
            print_r(Redis::del($key));
    }
    
    public function setData(Request $request) {
        self::checkUserIp();
        $key = $request->input('key');
        $val = $request->input('val');
        Redis::set($key, $val);
        print_r(Redis::get($key));
    }
    
    public function setHashData(Request $request) {
        self::checkUserIp();
        $key = $request->input('key');
        $field = $request->input('field');
        $val = $request->input('val');
        Redis::hset($key, $field, $val);
        print_r(Redis::hgetall($key));
    }
    
    
    public function delHashData(Request $request) {
        self::checkUserIp();
        $key = $request->input('key');
        $field = $request->input('field');
        Redis::hdel($key, $field);
        print_r(Redis::hgetall($key));
    }

    public function checkSystemFans(){ //10分钟请求一次
        $date = date('Ymd');
        if(Redis::hexists($date,'sum---3')){
            $total_fans_old = 0;
            $total_fans_now = Redis::hget($date,'sum---3');
            if(Redis::exists($date.'total_fans')) $total_fans_old = Redis::get($date.'total_fans');
            if($total_fans_now < $total_fans_old+15){
                echo '警告';
                Smschanzor::sendmsg(1);
            }else{
                echo '正常';
            }
            Redis::set($date.'total_fans', $total_fans_now);
        }else{
            die('no fans now');
        }
    }
}