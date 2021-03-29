<?php

namespace App\Services\Impl\Common;

use App\Lib\HttpUtils\HttpRequest;
use App\Models\Buss\BussModel;
use Illuminate\Support\Facades\Redis;
use App\Models\Count\FansLogModel;

class OldPlatformServicesImpl {
    
    //发送关注数据给老平台
    static public function getSendFollowOld($array) 
    {
        $addredis=Redis::get($array['openid']);
        $addredis= json_decode($addredis,TRUE);
        $array['oid']=$addredis['order_id'];
        $array['bid']=$addredis['bid'];
        $array['mac']=$addredis['mac'];
        $array['bmac']=$addredis['bmac'];
        if(self::getIsOldPlatform($array['bid'])){
            HttpRequest::getApiServices('newApi', 'receive_follow_new', 'GET', $array);
        }
        
    }
    
    //发送点击完成数据给老平台
    static public function getSendFinish($array) 
    {
        $FansLog = FansLogModel::select()->where('openid',$array['openid'])->orderBy('id', 'DESC') ->first() ->toArray();
        if(self::getIsOldPlatform($FansLog['bid'])){
            HttpRequest::getApiServices('newApi', 'receive_finish_new', 'GET', $FansLog);
        }
    }
    
    //发送取关数据给老平台
    static public function getSendUnFollow($array) 
    {
        $FansLog = FansLogModel::select()->where('openid',$array['openid'])->orderBy('id', 'DESC') ->first() ->toArray();
        if(self::getIsOldPlatform($FansLog['bid'])){
            HttpRequest::getApiServices('newApi', 'receive_off_new', 'GET', $FansLog);
        }
    }
    
    //判断是否是老平台的渠道
    static public function getIsOldPlatform($bid) 
    {
        $where = array(
            'id'=>$bid,
            'belong_to'=>0
        );
        $model = BussModel::where($where)->get()->first();
        return $model?TRUE:FALSE;
    }
}
