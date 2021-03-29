<?php

namespace App\Services\Impl\Receive;

use App\Models\Count\OrderTaskModel;
use App\Models\Count\TaskSummaryModel;
use App\Models\Order\OrderModel;
use App\Models\Order\TaskModel;
use App\Models\Count\FansInfoModel;
use App\Models\Order\WOrderModel;
use App\Models\Wechat\WxInfoModel;
use App\Services\UserServices;
use Illuminate\Support\Facades\Redis;
use App\Models\Count\CompletLogModel;
use App\Models\Count\FansLogModel;
use App\Models\Count\GetWxLogModel;
use App\Models\Count\UpsubLogModel;
use App\Models\Count\UnsubEventModel;
use App\Services\Impl\Common\TakeNumServicesImpl;
use App\Lib\Data\Yundai;
use App\Services\Impl\Common\WriteBehaviorServicesImpl;
use App\Services\Impl\Common\OldPlatformServicesImpl;
use App\Lib\HttpUtils\Net;
use App\Lib\Data\Smschanzor;


class ReceiveServicesImpl implements UserServices
{
    static public $buss_area;
    static public $bid_sex;
    static public $bid_device_type;

    /**
     * 获取取号信息
     * @param $userId
     * @return null
     */
    static public function getWxInfo($bid, $mac, $ordernum, $sex, $device_type, $brand_id=null, $store_id=null)
    {
//        date_default_timezone_set('PRC');  
        //获取所有的订单
        $array = Redis::hgetall($bid); 
        $endarray = array();
        if(OldPlatformServicesImpl::getIsOldPlatform($bid)){
            $isold = 1;
        } else {
            $isold = 2;
        }
        self::$buss_area = Redis::hget('bidredis',$bid);
        self::$bid_sex = $sex;
        self::$bid_device_type = $device_type;
        
        if($brand_id!='')
        {
            $brandOidArray =  Redis::hgetall('brand'.$brand_id);
            foreach ($brandOidArray as $key => $value) {
                $orderinfo= json_decode($value,TRUE);
                $orderinfo['oid'] = $key;
                $orderinfo['alreadynum'] = 1000000-$key;
                $orderinfo['bid'] = $bid;
                //判断订单是否已关注
                $num=self::getIsSpecial($orderinfo,$mac);
                if($num>0){          
                    $endarray[]=array(
                            'orderid'=>$key,
                            'num'=>$orderinfo['alreadynum'],
                            'list' => TakeNumServicesImpl:: getSerializeArray($orderinfo)
                    );
                }
            }
        }

        foreach ($array as $key => $value) {
            $orderinfo= json_decode($value,TRUE);
            $orderinfo['oid'] = $key;
            $orderinfo['alreadynum'] = $orderinfo['alreadynum']+1;
            $orderinfo['bid'] = $bid;
            switch ($key) {
                case '153':
                        if($isold == 2){
                            //$num = TakeNumServicesImpl::getIsFollow($yundai['list']['ghid'], Redis::hget($mac,'ghid'),$orderinfo['alreadynum']);
                            if($orderinfo['alreadynum']>0){          
                                $endarray[]=array(
                                                'orderid'=>$key,
                                                'num'=>$orderinfo['alreadynum'],
                                                //'list' => TakeNumServicesImpl:: getYunDaiArray($mac)
                                            );
                            }
                        }
                    break;
                case '155':
                        if($isold == 2){
                            if($orderinfo['alreadynum']>0){          
                                $endarray[]=array(
                                    'orderid'=>$key,
                                    'num'=>$orderinfo['alreadynum'],
                                    //'list' => TakeNumServicesImpl:: getOldPlatformArray($mac,$bid)
                                );
                            }
                        }
                    break;
                case '211':
                            if($orderinfo['alreadynum']>0){          
                                $endarray[]=array(
                                    'orderid'=>$key,
                                    'num'=>$orderinfo['alreadynum'],
                                    //'list' => TakeNumServicesImpl:: getOldPlatformArray($mac,$bid)
                                );
                                
                            }
                    break;
                default:
                        //判断订单是否在涨粉
                        if(self::getIsActivity($orderinfo))
                        {
                            $num=self::getIsSpecial($orderinfo,$mac);
                            if($num>0){          
                                $endarray[]=array(
                                        'orderid'=>$key,
                                        'num'=>$orderinfo['alreadynum'],
                                        'list' => TakeNumServicesImpl:: getSerializeArray($orderinfo)
                                );
                            }
                        } 
                    break;
            }

        }
        
        //排序
        $oldlist=self::getBubbleSort($endarray);
        $rest = null;
        if(count($oldlist)>0){
            $rest = TakeNumServicesImpl::getOnePublicNum($oldlist,$bid,$mac,$ordernum);
        }
        //$rest=isset($oldlist[0]['list'])?array($oldlist[0]['list']):'';
        return $rest;
    }

//    /**
//     * 模拟渠道信息
//     * @param $userId
//     * @return null
//     */
//    static public function setChannelInfo()
//    {
//        $data20001=array(
//            'alreadynum'=>10,                 //后端已经存在的分数
//            'total_fans'=>1000,               //总涨粉
//            'date_fans'=>500,                 //日涨粉
//            'isprecision'=>1,                 //是否精准投放 1需要,2不需要
//            'isattribute'=>1,                 //是否属性订单 1是,2不是
//            'start_date'=>'2017-04-03',       //投放开始时间
//            'end_date'=>'2017-09-03',         //投放结束时间
//            'start_time'=>'08:50',            //投放开始时间段
//            'end_time'=>'21:50',              //投放结束时间段
//            'fans_tag'=>'杭州,宁波,嘉兴',       //粉丝标签
//            'check_status'=>2,                //满足其一还是全部满足 1.满足其一2.满足全部
//            'is_hot_area'=>1,                 //是否满足热点区域     1.满足,2不满足
//            'is_sex'=>1,                      //是否男女     0：全部  1：男 2：女
//            'ghid'=>'gh_ea8b2185809d',        //微信公众号ID
//            'price'=>'0.7',                   //单价
//            'content'=>'a:10:{s:3:"oid";s:3:"731";s:6:"ghname";s:12:"游途旅行";s:4:"ghid";s:15:"gh_c3f777c16873";s:5:"sname";s:21:"世纪金源大饭店";s:3:"sid";s:7:"4133188";s:5:"appid";s:18:"wxacd9cd4673c88dce";s:9:"secretkey";s:32:"4e38dd78ec643e3d6c845a9ccf16e939";s:4:"area";s:8:",不限,";s:4:"ssid";s:4:"ssid";s:10:"qrcode_url";s:75:"http://user.weifentong.com.cn/Uploads/Wxqrcode/2017-02-16/1312335713685.jpg";}'          
//        );
//        
//        $data20001 = json_encode($data20001,TRUE);
//        $biddata=array(
//            '20001'=>$data20001
//        );
//        
//        Redis::hmset(1001, $biddata);
//
//        return 12344;
//    }
    
//    /**
//     * 模拟用户信息
//     * @param $userId
//     * @return null
//     */
//    static public function setUserInfo()
//    {
//        $data20001=array(
//            'city'=>'杭州',                     //城市
//            'sex'=>1,                         //性别
//            'bid'=>100,                        //渠道ID
//            'receivenum'=>11,                  //取号次数
//            'connectnum'=>6,                   //连接次数
//            'follownum'=>4,                    //关注次数
//            'finishnum'=>3,                    //点击完成次数
//            'umfollownum'=>2,                  //取关次数
//            'ghid'=>'',       //关注的公众号ghid
//        );
//        Redis::hmset('amcluojia520', $data20001);
//
//        return 12344;
//    }
    
    /**
     * 判断是否继续涨粉
     * @param $userId
     * @return null
     */
    static public function getIsActivity($orderinfo)
    {
        $nowdate=date('Y-m-d');
        $nowtime=date('H:i');
        $dateday= date('Ymd');
        $orderkey='tot-'.$orderinfo['oid'].'-'.$orderinfo['bid'];
        $mac='sum-'.$orderinfo['oid'].'-'.$orderinfo['bid'].'-'.'3';
        $total_fans = Redis::exists($orderkey)? Redis::get($orderkey):0;    //需要统计获取   Redis::get('oid'.)
        $date_fans = Redis::hexists($dateday,$mac)? Redis::hget($dateday,$mac):0;     //需要统计获取   Redis::hget('oid'.,'')
        
        //
        $date_total_fanskey='sum-'.$orderinfo['oid'].'--'.'3';
        $date_total_fans = Redis::hexists($dateday,$date_total_fanskey)? Redis::hget($dateday,$date_total_fanskey):0;     //需要统计获取   Redis::hget('oid'.,'')
        $pass=FALSE;
        if(!isset($orderinfo['date_total_fans'])){
            $orderinfo['date_total_fans'] = 999999;
        }
        if($orderinfo['total_fans']<=$total_fans||$orderinfo['date_fans']<=$date_fans||$orderinfo['date_total_fans']<=$date_total_fans)
        {
            
        }elseif ($nowdate<$orderinfo['start_date']||$nowdate>$orderinfo['end_date']) {

        }elseif ($nowtime<$orderinfo['start_time']||$nowtime>$orderinfo['end_time']) {
            if ($orderinfo['end_time']<$orderinfo['start_time']) {
                if($nowtime>$orderinfo['start_time']||$nowtime<$orderinfo['end_time']){
                        $pass=TRUE;
                }
            }
        } else {
            $pass=TRUE;
        }
        
        return $pass;
    }

 //   static public function getIsPrecision($orderinfo,$mac) 
    static public function getIsSpecial($orderinfo,$mac) 
    {
        
        $key=1;
        if(!Redis::exists($mac)||Redis::hget($mac, 'follownum')==0){
            $key=0;
        }
        //判断是否是新用户
        switch ($key) {
                    case 0:
                        if($orderinfo['user_type']==0||$orderinfo['user_type']==1){
                            $userinfo= Redis::hgetall($mac);
                            return  self::getCountNum($orderinfo['alreadynum'],self::getOldUserNum($orderinfo, $userinfo));
                            //return $orderinfo['alreadynum'];
                        }
                        return 0;
                    default:
                        if($orderinfo['user_type']==1){
                            return 0;
                        }
                        //if($orderinfo['user_type']==2){
                            $userinfo= Redis::hgetall($mac);
                            return  self::getCountNum($orderinfo['alreadynum'],self::getOldUserNum($orderinfo, $userinfo));
                        //}
                }
    }
    
    static public function getOldUserNum($orderinfo,$userinfo) 
    {
        
        $userghid = isset($userinfo['ghid'])?$userinfo['ghid']:'';
        $orderghid = isset($orderinfo['ghid'])?$orderinfo['ghid']:'';
        //老用户判断是否关注
        if(stripos($userghid, $orderghid)!==FALSE){
            return 0;
        } else {
            return self::getIsComplete($orderinfo,$userinfo) ;
        }
    }
    
//    static public function getIsPrecision($orderinfo,$userinfo) 
//    {
//        //是否需啊精准投放
//        switch ($orderinfo['isprecision']) {
//                    case 1:
//                        //需要经过精准投放
//                        return self::getAddOldUserNum($orderinfo,$userinfo) ;
//
//                    default:
//
//                        //不需要经过精准投放
//                        return self::getIsComplete($orderinfo,$userinfo) ;
//                }
//    }
//    
//    static public function getAddOldUserNum($orderinfo,$userinfo) 
//    {
//        $num1=self::getDivideNum($userinfo['connectnum'], $userinfo['receivenum']);
//        $num2=self::getDivideNum($userinfo['follownum'], $userinfo['connectnum']);
//        $num3=self::getDivideNum($userinfo['finishnum'], $userinfo['follownum']);
//        $num4=1 - self::getDivideNum($userinfo['umfollownum'], $userinfo['follownum']);
//        $num4=20*($num1*0.2+$num2*0.3+$num3*0.1+$num4*0.4);
//        
//        return self::getCountNum($num4,self::getIsComplete($orderinfo,$userinfo));
//    }
    
    //判断是否满足其一还是全部满足
    static public function getIsComplete($orderinfo,$userinfo) 
    {
        if($orderinfo['check_status']==1){
            if(self::getIsArea($orderinfo,$userinfo)>=1){
                return self::getCountNum(5, self::getIsSex($orderinfo,$userinfo));
            } else {
                return 0;
            }
        } else {
            if(self::getIsArea($orderinfo,$userinfo)>=2){
                return self::getCountNum(10, self::getIsSex($orderinfo,$userinfo));
            } else {
                return 0;
            }
        }
    }
    
    //判断是否满足热点区域和粉丝标签
    static public function getIsArea($orderinfo,$userinfo) 
    {
        $key = 0;
        $userprovince = isset($userinfo['province'])?$userinfo['province']:'';
        $usercity = isset($userinfo['city'])?$userinfo['city']:'';
        $bidprovince = isset($userinfo['bid_province'])?$userinfo['bid_province']:'';
        $bidcity= isset($userinfo['bid_city'])?$userinfo['bid_city']:'';
        $orderhot_area = isset($orderinfo['hot_area'])?$orderinfo['hot_area']:'';
        $orderfans_tag = isset($orderinfo['fans_tag'])?$orderinfo['fans_tag']:'';
        //满足热点区域
        if(TakeNumServicesImpl::area_compare($orderhot_area, $orderinfo['bid'], $bidprovince, $bidcity, self::$buss_area)== TRUE||$orderhot_area==''){
            $key++;
        }
        //满足粉丝标签
        if(TakeNumServicesImpl::area_compare($orderfans_tag, $orderinfo['bid'], $userprovince, $usercity, self::$buss_area)== TRUE||$orderfans_tag==''){  
            $key++;
        }
        return $key;
    }

    //判断是否满足性别
    static public function getIsSex($orderinfo,$userinfo) 
    {
        
        if(!isset($userinfo['sex'])){
            $userinfo['sex']=0;
        }
        if(self::$bid_sex!=''||self::$bid_sex>0){
            $userinfo['sex']=self::$bid_sex;
        }
        if($orderinfo['is_sex']==0){
            return self::getCountNum(10, self::getIsDeviceType($orderinfo,$userinfo));
        } elseif ($orderinfo['is_sex']==$userinfo['sex']) {
            return self::getCountNum(10, self::getIsDeviceType($orderinfo,$userinfo));
        } else {            
            return 0;
        }
    }

    /**
     * 判断是否满足设备类型
     */
    static public function getIsDeviceType($orderinfo,$userinfo) 
    {
        if(!isset($orderinfo['device_type'])||$orderinfo['device_type']==''||$orderinfo['device_type']==0||self::$bid_device_type==$orderinfo['device_type']){
            return 5;
        }
        return 0;
    }
    
    static public function getBubbleSort($numbers) 
    {
        
        $cnt = count($numbers);
        if($cnt<=0){
            return $numbers;
        }
           for ($i = 0; $i < $cnt; $i++) {
               for ($j = 0; $j < $cnt - $i - 1; $j++) {
                   if ($numbers[$j]['num'] < $numbers[$j + 1]['num']) {
                       $temp = $numbers[$j];
                       $numbers[$j] = $numbers[$j + 1];
                       $numbers[$j + 1] = $temp;
                   }
               }
           }

          return $numbers;
    }
    
    static public function getCountNum($number1,$number2) 
    {
        if($number2==0){
            return 0;
        } else {
            return $number1;
            //return $number1+$number2;
        }
    }
    
    static public function getDivideNum($number1,$number2) 
    {
        if($number1==0||$number1==''){
            return 0;
        } elseif($number2==0||$number2=='') {
            return 1;
        } else {
            if(round($number1/$number2,3)>1){
                return 1;
            }
            return round($number1/$number2,3);
        }
    }
    
//    /**
//     * 从数据库中导入到redis用户信息
//     * @param $userId
//     * @return null
//     */
//    static public function getDbUserInfo()
//    {
//            Redis::select(1);
//            $key= Redis::get('hhd');
//            Redis::incr('hhd');
//            //可以根据时间来获取一批ID,然后取出导入,直到结束
//            $addfans=FansInfoModel::getDbUserInfo($key);
//            foreach ($addfans as $value) {
//                //查看是否
//                switch (Redis::exists($value['mac'])) {
//                    case 0:
//                        self::getAddUserInfo($value);
//                        break;
//
//                    default:
//                        $ghid=$value['ghid'];
//                        $redisghid= Redis::hget($value['mac'], 'ghid');
//                        $behavior=3;  
//                        self::getUpUserInfo($value['mac'],$behavior);
//
//                        if($value['un_date']>'1970-01-01'){
//                            $behavior=5;
//                            self::getUpUserInfo($value['mac'],$behavior);
//                            Redis::hset($value['mac'], 'ghid', str_replace($ghid,'' , $redisghid));
//                            break;
//                        }
//                        if(!strpos(',,,,'.$redisghid, $ghid)){
//                            $newvalue=$redisghid.','.$ghid;
//                            Redis::hset($value['mac'], 'ghid',$newvalue);
//                        }
//                    break;
//                }
//                $userinfo= Redis::hgetall($value['mac']);
//            
//        }
//        return 123445;
//    }
    
//    /**
//     * 添加redis的用户数据
//     * @param $userId
//     * @return null
//     */
//    static public function getAddUserInfo($userinfo)
//    {
//        
//        $need=array(
//                        'city'=>$userinfo['city'],        //城市
//                        'sex'=>$userinfo['sex'],          //性别
////                        'bid'=>100,                     //渠道ID
//                        'receivenum'=>0,                  //取号次数
//                        'connectnum'=>0,                  //连接次数
//                        'follownum'=>0,                   //关注次数
//                        'finishnum'=>0,                   //点击完成次数
//                        'umfollownum'=>0,                 //取关次数
//                        'ghid'=>$userinfo['ghid'],        //关注的公众号ghid
//                    );
//        //$need['city']= ReceiveServicesImpl::getIsShanghai($need['city']);
//        $need['province']= $need['province'];
//        if($userinfo['un_date']>'1970-01-01'){
//            $need['umfollownum']=1;
//            $need['ghid']='';
//        }
//        $need['follownum']=1;
//        //$date= json_decode($need,TRUE);
//        Redis::hmset($userinfo['mac'],$need);
//    }

    /**
     * 更新redis的用户数据
     * @param $userId
     * @return null
     */
    static public function getUpUserInfo($mac,$behavior)
    {
        switch ($behavior) {
                case 1:
                    Redis::hset($mac,'receivenum',1);
                    Redis::hincrby($mac,'receivenum',1);
                    break;
                case 2:
                    Redis::hincrby($mac,'connectnum',1);
                    break;
                case 3:
                    Redis::hincrby($mac,'follownum',1);
                    break;
                case 4:
                    Redis::hincrby($mac,'finishnum',1);
                    break;
                case 5:
                    Redis::hincrby($mac,'umfollownum',1);
                    break;
                default:
                    break;
        }
        
    }
    
    /**
     * 插入用户数据log
     * @param $userId
     * @return null
     */
    static public function getAddUserInfoLog($behavior,$date)
    {
        if($behavior!=5){
            //判断是否是老用户
           if(ReceiveServicesImpl::getIsOld($date['mac'], $behavior)){
               //是老用户人就是1
               $date['isold']=1;
           } else {
               //不是老用户人就是2
               $date['isold']=2;
           }
        }
        switch ($behavior) {
                case 1:
                    //取号只是插入数据
                    GetWxLogModel::getAddWxLog($date);
                    break;
                case 2:
                    //立即连接插入数据,还需要写一个redis
                    $addredis=array(
                        'bid'=>$date['bid'],
                        'order_id'=>$date['order_id'],
                        'mac'=>$date['mac'],
                        'bmac'=>$date['bmac'],
                    );
                    if(isset($date['store_id'])&&$date['store_id']!=''){
                        $addredis['store_id'] = $date['store_id'];
                        $addredis['brand_id'] = $date['brand_id'];
                    }
                    $addredis= json_encode($addredis,TRUE);
                    Redis::set($date['openid'],$addredis);
                    Redis::expire($date['openid'],600);
                    UpsubLogModel::getAddsubLog($date);
                    break;
                case 3:
                    //点击关注只是插入数据,从redis取得bid和orderid,Mac

                    
                    //需要把关注的公众号id插入进去,城市和性别,写入KEY为mac的用户数据,关注ghid
                    if(!strpos(','.Redis::hget($date['mac'], 'ghid'), $date['ghid']))
                    {
                        $nowghid=Redis::hget($date['mac'],'ghid').','.$date['ghid'];
                        Redis::hset($date['mac'],'ghid',$nowghid);
                    }

                    //判断是否是上海的
                    //$date['city'] = ReceiveServicesImpl::getIsShanghai($date['city']);
                    //把城市和性别插入进去
                    $redisProvince = Redis::hget($date['mac'], 'province');
                    
                    if($redisProvince=='' || $redisProvince=='未知省份' || $date['province']!='未知省份')
                    {
                        $redisarr = array(
                            'province'=>$date['province'],
                            'city'=>$date['city'],
                            'sex'=>$date['sex'],
                        );
                        Redis::hmset($date['mac'],$redisarr);
                    }

                    //记录今天关注的openid
                    $datetime= date('Ymd');
                    $pd=Redis::hget($date['mac'],'follownum');
                    $isold=2;
                    if($pd<=1||$pd==''){
                        $isold=1;
                    }
                    Redis::hset($datetime.'openid',$date['openid'],$isold); 
                    $date['bid_province'] = Redis::hget($date['mac'],'bid_province');
                    $date['bid_city'] = Redis::hget($date['mac'],'bid_city');
                    FansLogModel::getAddFansLog($date);
                    break;
                case 4:
                    //只是插入数据
                    CompletLogModel::getCompletLog($date);
                    break;
                case 5:
                    //更新数据   通过ghid
                    return FansLogModel::getUpFansLog($date);
                case 6:
                    break;
                default:
                    break;
        }
        
    }
    
    /**
     * 统计涨粉数量
     * @param $userId
     * @return null
     */
    static public function getCountFans($orderid,$bid,$behavior)
    {
        //日期加上渠道号加上订单号加上状态
        $date= date('Ymd');
        $mac='sum-'.$orderid.'-'.$bid.'-'.$behavior;
        
        switch ($behavior) {
            case 6:
                break;
            default:
                Redis::hincrby($date, $mac, 1);
                break;
        }
        Redis::hincrby($date, 'sum-'.$orderid.'--'.$behavior, 1);
        Redis::hincrby($date, 'sum--'.$bid.'-'.$behavior, 1);
        if($behavior!=6){
            Redis::hincrby($date, 'sum---'.$behavior, 1);
        }
        
        
        if($behavior==3){
                $orderkey='tot-'.$orderid.'-'.$bid;
                Redis::incr($orderkey);
        }
        
    }
    
    
    /**
     * 统计用户行为
     * @param $userId
     * @return null
     */
    static public function getFansBehavior($date)
    {
        $datetime=date('Ymd');
        if(isset($date['mac'])){
            $date['mac'] = Net::get_mac($date['mac']);
        }
        $behavior=$date['behavior'];
        switch ($behavior) {
            case 3:
                    if(!Redis::exists($date['openid'])||Redis::hexists($datetime.'openid',$date['openid'])){
                        die();
                    } else {
                        Redis::hset($datetime.'openid',$date['openid'],3); 
                    }
                    $addredis=Redis::get($date['openid']);
                    $addredis= json_decode($addredis,TRUE);
                    $date['order_id']=$addredis['order_id'];
                    $date['bid']=$addredis['bid'];
                    $date['mac']=$addredis['mac'];
                    $date['bmac']= isset($addredis['bmac'])?$addredis['bmac']:'';
                    $date['store_id']= isset($addredis['store_id'])?$addredis['store_id']:'';
                    WriteBehaviorServicesImpl::setFollowBid($date);
                    $addlog= self::getAddUserInfoLog($behavior,$date);
                break;
            case 5:
                    $addlog= self::getAddUserInfoLog($behavior,$date);
                    if($addlog === FALSE){
                        die(1);
                        break;
                    }
                    $addunsublog = UnsubEventModel::getAddUnsubEventLog($addlog);
                    $date['order_id']=$addlog['oid'];
                    $date['bid']=$addlog['bid'];
                    $date['mac']=$addlog['mac'];
                    if($addlog['store_id']!=''){
                        $storedate = $date;
                        $storedate['bid'] = 'sid'.$addlog['store_id'];
                        $addqgcount= self::getqgCountFans($storedate);
                        
                    }
                    
                    $addqgcount= self::getqgCountFans($date);
                break;
            case 6:
                    Redis::hset($date['mac'],'bid_province',$date['bid_province']);
                    Redis::hset($date['mac'],'bid_city',$date['bid_city']);
                    $date['order_id']=null;
                break;
            default:
                    $addlog= self::getAddUserInfoLog($behavior,$date);
                break;
        }
        if($behavior==3){
            if($date['sex']!=1&&$date['sex']!=2){
                $date['sex']=0;
            }
            //统计用户的行为
            $newcount= self::getnewCountFans($date['mac'],$behavior,$date['order_id'],$date['bid'],$date['sex']);
            if(isset($date['store_id'])&&$date['store_id']!=''){
                $newcount= self::getnewCountFans($date['mac'],$behavior,$date['order_id'],'sid'.$date['store_id'],$date['sex']);
            }
        } else {
            //统计用户的行为
            $newcount= self::getnewCountFans($date['mac'],$behavior,$date['order_id'],$date['bid']);
            if(isset($date['store_id'])&&$date['store_id']!=''){
                $newcount= self::getnewCountFans($date['mac'],$behavior,$date['order_id'],'sid'.$date['store_id']);
            }
        }

        
//        //修改redis的用户信息
        $count= self::getUpUserInfo($date['mac'],$behavior);
        //统计用户的行为
        $hhd=self::getCountFans($date['order_id'],$date['bid'],$behavior);
        if($behavior==3){
            self::overOrder($date['openid']);
        }
        //记录今天用户的行为
        //$doit=self::getIsDo($date['mac'],$behavior);
    }
    
    
    /**
     * 删除任务
     * @param $userId
     * @return null
     */
    static public function getDelTask($date)
    {
        foreach ($date as $key => $value) {
            if(Redis::hexists($value['buss_id'],$value['order_id'])){
                Redis::hdel($value['buss_id'],$value['order_id']);
            }
        }
    }


    /**
     * 检查是否关注
     * @param $openid
     * @return array
     */
    static public function checkSub($openid,$oid,$bid)
    {
        $data = FansLogModel::checkSub($openid,$oid,$bid);
        return $data;
    }
    
    
    /**
     * 判断是否重复
     * @param $userId
     * @return null
     */
    static public function getIsRepeat($mac,$behavior)
    {
        $datetime=date('Ymd');
        $key=$datetime.'mac';
        $do= Redis::hget($key,$mac);
        if (strpos(',,,'.$do, "$behavior")) {
            //今天有这个行为    
            return TRUE;
        }
        $newdo=$do.$behavior;
        Redis::hset($key,$mac,$newdo);
        //今天,没有行为
        return FALSE;
    }
    
    
    /**
     * 判断是否新老用户
     * @param $userId
     * @return null
     */
    static public function getIsOld($mac,$behavior)
    {
        $userinfo= Redis::hgetall($mac);
        if(!isset($userinfo['follownum'])){

        }elseif($userinfo['follownum']==0){
            
        } else {
            if($behavior==4&&$userinfo['follownum']==1||$behavior==5&&$userinfo['follownum']==1){
                return FALSE;
            }
            return TRUE;
        }
        return FALSE;
        
        
    }
    
    
//    /**
//     * 记录今天用户干了的事
//     * @param $userId
//     * @return null
//     */
//    static public function getIsDo($mac,$behavior)
//    {
//        $datetime=date('Ymd');
//        $key=$datetime.'mac';
//        $do= Redis::hget($key,$mac);
//        if($do==''){
//            Redis::hset($key,$mac,$behavior);
//        }elseif (strpos(',,,'.$do, "$behavior")) {
//
//        } else {
//            $newdo=$do.$behavior;
//            Redis::hset($key,$mac,$newdo);
//        }
//    }
    
    static public function getnewCountFans($mac,$behavior,$orderid=null,$bid,$sex=0){
            $datetime=date('Ymd');
            
            //日期加上渠道号加上订单号加上行为
            $weld=$orderid.'-'.$bid.'-'.$behavior;
            //新老用户,重复不重复,在这里判断
            if(self::getIsOld($mac,$behavior)){
                //重复的统计
                $key='old-'.$weld;
                Redis::hIncrBy($datetime, $key.'-1', 1); 
                //老用户
                if(!self::getIsRepeat($mac, $behavior)){
                    Redis::hIncrBy($datetime, $key.'-2', 1); 
                }
                
                if($behavior==3){
                    $key2='sex-'.$weld;
                    Redis::hIncrBy($datetime, $key2.'-1-'.$sex, 1); 
                }
            } else {
                //重复的统计
                $key='new-'.$weld;
                Redis::hIncrBy($datetime, $key.'-1', 1); 
                //新用户
                if(!self::getIsRepeat($mac, $behavior)){
                    Redis::hIncrBy($datetime, $key.'-2', 1); 
                }
                
                if($behavior==3){
                    $key2='sex-'.$weld;
                    Redis::hIncrBy($datetime, $key2.'-2-'.$sex, 1); 
                }
            }
    }
    
    //取关统计
    static public function getqgCountFans($date) {
        $datetime=date('Ymd');
        $isold=Redis::hget($datetime.'openid',$date['openid']);
        if($isold>0){
            $orderid=$date['order_id'];
            $bid=$date['bid'];
            $weld='nqg-'.$orderid.'-'.$bid.'-'.$isold;
            Redis::hIncrBy($datetime, $weld, 1); 
        }
    }
    
    //通过Mac判断是否关注了
    static public function getIsFollownum($mac,$ghid) {
        $macghid= Redis::hget($mac,'ghid');
        if(strpos('..'.$macghid, $ghid)){
            return TRUE;
        }
        return FALSE;
    }
    
    //通过openid判断是否关注了,返回值大于0就是关注了
    static public function getIsFollownumOpenid($openid,$olderid,$type,$mac,$bid) {
        $array=array(
            'openid'=>$openid,
            'oid'=>$olderid
        );
        switch ($type) {
            case 3:
                $yundai_class = new Yundai();
                $res = $yundai_class->check_subscribe($olderid,$mac,$openid);
                if($res == 1){
                    $value = array(
                        'oid'=>153,
                        'bid'=>$bid
                    );
                    WriteBehaviorServicesImpl::setFollowRedisCount($value,2);
                }
                return $res;

            default:
                $macghid = FansLogModel::where($array)->get()->first();
                return $macghid ? 1 : 0;
        }
    }
    /**
     * 判断订单涨粉状态
     * @param $openid
     */
    static public function overOrder($openid){
        if(!Redis::exists($openid)){
            die();
        }
        $addredis=Redis::get($openid);
        $addredis= json_decode($addredis,TRUE);
        $order_id = $addredis['order_id'];
        $order_obj = OrderModel::select('o_total_fans','wx_name')->where('order_id','=',$order_id)->first();
        $total_fans = $order_obj->o_total_fans;
        $wx_name = $order_obj->wx_name;
        $where = array(
            ['order_id','=',$order_id],
//            ['task_status','=',1]
        );
        $buss_id = TaskModel::select('buss_id')->where($where)->groupBy('buss_id')->get()->toArray();
        if(count($buss_id)>0){
            $num = 0;
            foreach($buss_id as $k=>$v){
                $num += Redis::get('tot-'.$order_id.'-'.$v['buss_id']);
            }
            if($num >= $total_fans){
                OrderModel::where('order_id','=',$order_id)->update(['order_status'=>4]);
                TaskModel::where($where)->update(['task_status'=>2]);
                ReceiveServicesImpl::startOrder($order_id);
                foreach($buss_id as $k=>$v){
                    Redis::hdel($v['buss_id'],$order_id);
                }
                Smschanzor::sendmsg($wx_name);
            }
        }
    }
    
    //判断是否是上海
    static public function getIsShanghai($value) {
        if($value==null){return $value;}
        $shanghai='、、、浦东新区、徐汇区、长宁区、普陀区、闸北区、虹口区、杨浦区、黄浦区、卢湾区、静安区、宝山区、闵行区、嘉定区、金山区、松江区、青浦区、奉贤区、南汇区';
        $beijin='北京市辖东城、西城、海淀、朝阳、丰台、门头沟、石景山、房山、通州、顺义、昌平、大兴、怀柔、平谷、延庆、密云';
        $tianjin='。。和平。河西。河东。南开。红桥。河北。环城四区包括东丽。西青。津南。北辰。滨海新区包括塘沽。大港。汉沽。远郊区。宝坻。武清。郊县蓟县。宁河，静海';
        $chongqin='包括渝中区、江北区、渝北区、北碚区、巴南区、南岸区、沙坪坝区、大渡口区、九龙坡区、万州区、黔江区、涪陵区、合川区、南川区、綦江区、永川区、荣昌区、江津区、长寿区、潼南区、大足区、壁山区、铜梁区、开州区、梁平区、武隆区';
        $aomen='澳门花地玛堂区、花王堂区、望德堂区、风顺堂区、大堂区';
        $xianggang='香港中西区、东区、南区、湾仔区,九龙城区、观塘区、深水埗区、黄大仙区、油尖旺区,离岛区、葵青区、北区、西贡区、沙田区、大埔区、荃湾区、屯门区、元朗区';
        $taiwan='台湾台北市 • 新北市 • 台中市 • 台南市 • 高雄市 • 基隆市 • 新竹市 • 嘉义市• 宜兰县 • 桃园县 • 新竹县 • 苗栗县 • 彰化县 • 南投县 • 云林县 • 嘉义县 • 屏东县 • 台东县 • 花莲县 • 澎湖县 • 金门县 • 连江县';
        if(strpos($shanghai, $value)){
            return '上海';
        } elseif (strpos($beijin, $value)) {
            return '北京';
        } elseif (strpos($tianjin, $value)) {
            return '天津';
        } elseif (strpos($chongqin, $value)) {
            return '重庆';
        } elseif (strpos($aomen, $value)) {
            return '澳门';
        } elseif (strpos($xianggang, $value)) {
            return '香港';
        } elseif (strpos($taiwan, $value)) {
            return '台湾';
        }else {
            return $value;
        }
    }

    /**
     * 开启订单接口
     * @param $oid
     * @return bool
     */
    public static function startOrder($oid){
        $wx_name = OrderModel::select('wx_name')->where('order_id',$oid)->first()->wx_name;
        $order = OrderModel::select('order_id')->where('wx_name',$wx_name)->where('order_status',2)->first();
        if($order){
            $order_id = $order->order_id;
            $task = OrderTaskModel::where('order_id',$order_id)->where('task_status',2)->get()->toArray();
            //工单数据
            $work = WOrderModel::leftJoin('y_order','y_work_order.id','=','y_order.work_id')->where('y_order.order_id',$order_id)->first();

            if($work){
                $ghid = WxInfoModel::where('id','=',$work['wx_id'])->first()->ghid;
                //redis数据处理
                foreach($task as $k=>$v){
                    $redis[$v['buss_id']][$v['order_id']] = array(
                        'total_fans'=>$v['plan_fans']==''||$v['plan_fans']==0?$work['w_total_fans']:$v['plan_fans'],
                        'date_fans'=>$v['day_fans']==''||$v['day_fans']==0?$work['w_total_fans']:$v['day_fans'],
                        'start_date'=>$work['w_start_date'],
                        'end_date'=>$work['w_end_date'],
                        'start_time'=>$work['w_start_time'],
                        'end_time'=>$work['w_end_time'],
                        'check_status'=>$work['check_status'],
                        'user_type'=>$v['user_type'],
                        'is_hot_area'=>1,
                        'hot_area'=>$work['hot_area'],
                        'fans_tag'=>$work['fans_tag'],
                        'ghid'=>$ghid,
                        'is_sex'=>$work['sex'],
                        'content'=>$work['content'],
                        'price'=>$work['o_per_price'],
                        'alreadynum'=>$v['level'] == '' || $v['level'] == 0?$work['order_level']:$v['level'],
                    );
                    if(isset($work['isprecision']) && $work['isprecision']!=''){
                        $redis[$v['buss_id']][$v['order_id']]['isprecision'] = 1;
                    }else{
                        $redis[$v['buss_id']][$v['order_id']]['isprecision'] = 2;
                    }
                    if($work['sex']==0 && $work['hot_area'] != '' && $work['fans_tag'] == '' && $work['scene'] == ''){
                        $redis[$v['buss_id']][$v['order_id']]['isattribute'] = 2;
                    }elseif($work['sex']==0 && $work['hot_area'] == '' && $work['fans_tag'] == '' && $work['scene'] == ''){
                        $redis[$v['buss_id']][$v['order_id']]['isattribute'] = 2;
                    }else{
                        $redis[$v['buss_id']][$v['order_id']]['isattribute'] = 1;
                    }
                }
                //添加redis数据
                foreach($redis as $k=>$v){
                    foreach($v as $kk=>$vv){
                        Redis::hset($k,$kk,json_encode($vv));
                    }
                }
                //修改任务状态
                OrderTaskModel::where('order_id',$order_id)->where('task_status',2)->update(['task_status'=>1]);
                //设定任务默认优先级（订单优先级）
                $level = OrderModel::select('order_level')->where('order_id',$order_id)->first();
                if($level)
                    OrderTaskModel::where('order_id',$order_id)->where('task_status',1)->update(['level'=>$level->order_level]);
                //修改订单状态
                OrderModel::where('order_id',$order_id)->update(['order_status'=>1]);
            }
        }else{
            return false;
        }
    }

    /**
     * 云袋订单通知
     */
    public static function YUNDAI_notice($YD_status,$workinfo,$content,$order_id){
//        switch ($workinfo['sex']){
//            case 1:
//                $sex = '男';
//                break;
//            case 2:
//                $sex = '女';
//                break;
//            default :
//                $sex = '不限';
//        }
//        switch ($workinfo['device_type']){
//            case 1:
//                $device_type = 'ios';
//                break;
//            case 2:
//                $device_type = '安卓';
//                break;
//            default :
//                $device_type = '不限';
//        }
//        switch ($YD_status){
//            case 1:
//                $status = '开启';
//                break;
//            case 2:
//                $status = '暂停';
//                break;
//            default :
//                $status = '关闭';
//        }
        $list = array(
            'order_total_fans'=>$workinfo['w_total_fans'],
            'day_advis_fans'=>$workinfo['w_advis_fans'],
            'order_price'=>$workinfo['w_per_price'],
            'run_start_date'=>$workinfo['w_start_date'],
            'run_end_date'=>$workinfo['w_end_date'],
            'run_start_time'=>$workinfo['w_start_time'],
            'run_end_time'=>$workinfo['w_end_time'],
            'sex'=>$workinfo['sex'],
            'device_type'=>$workinfo['device_type'],
            'hot_area'=>$workinfo['hot_area'],
            'fans_tag'=>$workinfo['fans_tag'],
            'scene'=>$workinfo['scene'],
//            'check_status'=>$check_status,
            'status'=>(int)$YD_status,
            'ssid'=>$content['ssid'],
            'oid'=>(int)$order_id,
            'ghname'=>$content['ghname'],
            'ghid'=>$content['ghid'],
            'sname'=>$content['sname'],
            'sid'=>$content['sid'],
            'appid'=>$content['appid'],
            'secretkey'=>$content['secretkey'],
            'head_img'=>$content['head_url'],
        );
        return $list;
    }

    public static function YUNDAI_Search($openid,$oid,$bid){
        $data = ReceiveServicesImpl::checkSub($openid,$oid,$bid);
        $task = TaskModel::leftJoin('y_order','y_order_task.order_id','=','y_order.order_id')->select('order_day_fans','o_total_fans')->where('task_status','=',1)->first();
        $fans = TaskSummaryModel::orderFans($oid);
        if($task){
            $rest = 0;
            $task = $task->toArray();
            if($fans){
                $last = $task['o_total_fans'] - $fans;
                $buss_id = TaskModel::select('buss_id')->where('order_id','=',$oid)->groupBy('buss_id')->get()->toArray();
                $total = 0;
                if($buss_id){
                    foreach ($buss_id as $k=>$v){
                        $total += Redis::hget(date('Ymd'),'new-'.$oid.'-'.$v['buss_id'].'-3-1')+Redis::hget(date('Ymd'),'old-'.$oid.'-'.$v['buss_id'].'-3-1');
                    }
                }
                if($last > $task['order_day_fans']){
                    $rest = $task['order_day_fans'] - $total;
                }else{
                    $rest = $last - $total;
                }
            }else{
                $rest = $task['order_day_fans'];
            }
            $list = array(
                'error'=> 0,
                'msg'=>'ok',
                'data'=>array(
                    'subscribe'=>(int)$data['subscribe'],
                    'rest_fans'=>(int)$rest,
                ),
            );
            return json_encode($list);
        }
        return false;
    }
}
