<?php

namespace App\Services\Impl\Receive;

use App\Models\Order\UserModel;
use App\Models\Order\WxInfoModel;
use App\Models\Order\WxReportModel;
use App\Models\Order\SceneModel;
use App\Models\Order\UserOrderModel;
use App\Models\Count\FansInfoModel;
use App\Services\UserServices;
use Illuminate\Support\Facades\Redis;
use App\Models\Count\CompletLogModel;
use App\Models\Count\FansLogModel;
use App\Models\Count\GetWxLogModel;
use App\Models\Count\UpsubLogModel;



class ReceiveServices2Impl implements UserServices
{
    /**
     * 获取取号信息
     * @param $userId
     * @return null
     */
    static public function getWxInfo($bid,$mac)
    {
        date_default_timezone_set('PRC');  
        
        $array=Redis::hgetall($bid); 
        //print_r($array);
        $arraykey= array_keys($array);
        
        $endarray=array();;
        
        foreach ($arraykey as $value) {
            $orderinfo= json_decode($array[$value],TRUE);

            $orderinfo['oid']=$value;
            $orderinfo['bid']=$bid;
            //判断订单是否在涨粉
            if(self::getIsActivity($orderinfo)){
                

                if($orderinfo['isprecision']==1||$orderinfo['isattribute']==1){
                    $case=1;    
                } else {
                    $case=2;
                }
                //判断是否需要属性订单或者精准投放
                switch ($case) {
                    case 1:
                        
                        $num=self::getIsSpecial($orderinfo,$mac);
                        break;

                    default:
                        $num=$orderinfo['alreadynum'];
                        break;
                }
            } else {
                $num=0;
            }
            $dmxy= unserialize($orderinfo['content']);
            //print_r($dmxy);
            $list=array(
                "ssid" => "youfentong",
                "oid" => $value,
                "ghname" => $dmxy['ghname'],
                "ghid" => $dmxy['ghid'],
                "sname" => $dmxy['sname'],
                "sid" => $dmxy['sid'],
                "appid" => $dmxy['appid'],
                "secretkey" => $dmxy['secretkey'],
                "portal_type" => "0",
                "portal_text" => null,
                "awifi_imgcode" => null,
                "price" => $orderinfo['price'],
                "type" => 0,
                "description" => "",
                "head_img" => $dmxy['qrcode_url']
            );
            $endarray[]=array(
                    'orderid'=>$value,
                    'num'=>$num,
                    'list'=>$list
                );
        }
        
        $endarray2=self::getBubbleSort($endarray);
        $rest='';
        //print_r($endarray2);
        if(isset($endarray2[0]['list'])){
            $rest=array($endarray2[0]['list']);
        }
        
        return $rest;
    }

    /**
     * 模拟渠道信息
     * @param $userId
     * @return null
     */
    static public function setChannelInfo()
    {
        $data20001=array(
            'alreadynum'=>10,                 //后端已经存在的分数
            'total_fans'=>1000,               //总涨粉
            'date_fans'=>500,                 //日涨粉
            'isprecision'=>1,                 //是否精准投放 1需要,2不需要
            'isattribute'=>1,                 //是否属性订单 1是,2不是
            'start_date'=>'2017-04-03',       //投放开始时间
            'end_date'=>'2017-09-03',         //投放结束时间
            'start_time'=>'08:50',            //投放开始时间段
            'end_time'=>'21:50',              //投放结束时间段
            'fans_tag'=>'杭州,宁波,嘉兴',       //粉丝标签
            'check_status'=>2,                //满足其一还是全部满足 1.满足其一2.满足全部
            'is_hot_area'=>1,                 //是否满足热点区域     1.满足,2不满足
            'is_sex'=>1,                      //是否男女     0：全部  1：男 2：女
            'ghid'=>'gh_ea8b2185809d',        //微信公众号ID
            'price'=>'0.7',                   //单价
            'content'=>'a:10:{s:3:"oid";s:3:"731";s:6:"ghname";s:12:"游途旅行";s:4:"ghid";s:15:"gh_c3f777c16873";s:5:"sname";s:21:"世纪金源大饭店";s:3:"sid";s:7:"4133188";s:5:"appid";s:18:"wxacd9cd4673c88dce";s:9:"secretkey";s:32:"4e38dd78ec643e3d6c845a9ccf16e939";s:4:"area";s:8:",不限,";s:4:"ssid";s:4:"ssid";s:10:"qrcode_url";s:75:"http://user.weifentong.com.cn/Uploads/Wxqrcode/2017-02-16/1312335713685.jpg";}'          
        );
        
        $data20001 = json_encode($data20001,TRUE);
        $biddata=array(
            '20001'=>$data20001
        );
        
        Redis::hmset(1001, $biddata);

        print_r(Redis::hgetall(1001));
        return 12344;
    }
    
    /**
     * 模拟用户信息
     * @param $userId
     * @return null
     */
    static public function setUserInfo()
    {
        $data20001=array(
            'city'=>'杭州',                     //城市
            'sex'=>1,                         //性别
            'bid'=>100,                        //渠道ID
            'receivenum'=>11,                  //取号次数
            'connectnum'=>6,                   //连接次数
            'follownum'=>4,                    //关注次数
            'finishnum'=>3,                    //点击完成次数
            'umfollownum'=>2,                  //取关次数
            'ghid'=>'',       //关注的公众号ghid
        );
        print_r($data20001);
        Redis::hmset('amcluojia520', $data20001);

        //print_r(Redis::get('04021FEFC597'));
        return 12344;
    }
    
    /**
     * 判断是否继续涨粉
     * @param $userId
     * @return null
     */
    static public function getIsActivity($orderinfo)
    {
        //print_r($orderinfo);
        $nowdate=date('Y-m-d');
        $nowtime=date('H:i');
        $dateday= date('Ymd');
        $orderkey='oid'.$orderinfo['oid'];
        $orderid=100000+$orderinfo['oid'];
        $bid=100000+$orderinfo['bid'];
        $mac=$orderid.$bid.'3';
        //print_r($nowdate);
        $total_fans = Redis::exists($orderkey)? Redis::get($orderkey):0;    //需要统计获取   Redis::get('oid'.)
        $date_fans = Redis::hexists($dateday,$mac)? Redis::hget($dateday,$mac):0;     //需要统计获取   Redis::hget('oid'.,'')
        $pass=FALSE;
        if($orderinfo['total_fans']<=$total_fans||$orderinfo['date_fans']<=$date_fans)
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
        
        //判断是否是新用户
        switch (Redis::exists($mac)) {
                    case 0:
                        return $orderinfo['alreadynum'];
                    default:
                        $userinfo= Redis::hgetall($mac);
                        return  self::getCountNum($orderinfo['alreadynum'],self::getOldUserNum($orderinfo, $userinfo));
                }
    }
    
    static public function getOldUserNum($orderinfo,$userinfo) 
    {
        //老用户判断是否关注
        switch (strpos($userinfo['ghid'], $orderinfo['ghid'])) {
                    case 0:
                        //是否需啊精准投放
                        return self::getIsPrecision($orderinfo,$userinfo) ;

                    default:
                        return 0;
                }
    }
    
    static public function getIsPrecision($orderinfo,$userinfo) 
    {
        //是否需啊精准投放
        switch ($orderinfo['isprecision']) {
                    case 1:
                        //判断是老用户之后需要判断是满足其一还是满足其二
                        return self::getAddOldUserNum($orderinfo,$userinfo) ;

                    default:

                        //不需要经过精准投放
                        return self::getIsComplete($orderinfo,$userinfo) ;
                }
    }
    
    static public function getAddOldUserNum($orderinfo,$userinfo) 
    {
//            'receivenum'=>5,                  //取号次数
//            'connectnum'=>4,                  //连接次数
//            'follownum'=>3,                   //关注次数
//            'finishnum'=>2,                   //点击完成次数
//             'umfollownum'=>1,                 //取关次数   
        //print_r($userinfo);

        $num1=self::getDivideNum($userinfo['connectnum'], $userinfo['receivenum']);
        $num2=self::getDivideNum($userinfo['follownum'], $userinfo['connectnum']);
        $num3=self::getDivideNum($userinfo['finishnum'], $userinfo['follownum']);
        $num4=1 - self::getDivideNum($userinfo['umfollownum'], $userinfo['follownum']);
        $num4=20*($num1*0.2+$num2*0.3+$num3*0.1+$num4*0.4);
        
        return self::getCountNum($num4,self::getIsComplete($orderinfo,$userinfo));
    }
    
    //判断是否满足其一还是全部满足
    static public function getIsComplete($orderinfo,$userinfo) 
    {
        
        if($orderinfo['check_status']==1){
            if($orderinfo['is_hot_area']==1||strpos(',,,'.$userinfo['city'], $orderinfo['fans_tag'])||$orderinfo['fans_tag']==''){
                return self::getCountNum(5, self::getIsSex($orderinfo,$userinfo));
            } else {
                return 0;
            }
        } else {
            if($orderinfo['is_hot_area']==1&&stripos( ',,,'.$orderinfo['fans_tag'],$userinfo['city'])>0||$orderinfo['is_hot_area']==1&&$orderinfo['fans_tag']==''){
                return self::getCountNum(10, self::getIsSex($orderinfo,$userinfo));
            } else {
                echo $orderinfo['is_hot_area'];
                return 0;
            }
        }
    }
    
    //判断是否满足性别
    static public function getIsSex($orderinfo,$userinfo) 
    {
        if($orderinfo['is_sex']==0){
            return 5;
        } elseif ($orderinfo['is_sex']==$userinfo['sex']) {
            return 5;
        } else {
            return 0;
        }
    }

    
    static public function getBubbleSort($numbers) 
    {
        
        $cnt = count($numbers);
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
            return $number1+$number2;
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
    
    /**
     * 从数据库中导入到redis用户信息
     * @param $userId
     * @return null
     */
    static public function getDbUserInfo()
    {
        //可以根据时间来获取一批ID,然后取出导入,直到结束
        $addfans=FansInfoModel::getDbUserInfo();
        //print_r($addfans);
        
        foreach ($addfans as $value) {
            //查看是否
            switch (Redis::exists($value['mac'])) {
                case 0:
                    self::getAddUserInfo($value);
                    break;

                default:
                    $behavior=1;
                    self::getUpUserInfo($value['mac'],$behavior);
                    break;
            }
            $userinfo= Redis::hgetall($value['mac']);
            print_r($userinfo);
        }
        die();
        //print_r(Redis::get('04021FEFC597'));
        return 12344;
    }
    
    /**
     * 添加redis的用户数据
     * @param $userId
     * @return null
     */
    static public function getAddUserInfo($userinfo)
    {
        
        $need=array(
                        'city'=>$userinfo['city'],        //城市
                        'sex'=>$userinfo['sex'],          //性别
//                        'bid'=>100,                     //渠道ID
                        'receivenum'=>0,                  //取号次数
                        'connectnum'=>0,                  //连接次数
                        'follownum'=>0,                   //关注次数
                        'finishnum'=>0,                   //点击完成次数
                        'umfollownum'=>0,                 //取关次数
                        'ghid'=>$userinfo['ghid'],        //关注的公众号ghid
                    );
        //$date= json_decode($need,TRUE);
        Redis::hmset($userinfo['mac'],$need);
        //
        if($userinfo['date']>'2005-01-01'){
            Redis::hincrby($userinfo['mac'],'follownum',1);
        }
        if($userinfo['un_date']>'2005-01-01'){
            Redis::hincrby($userinfo['mac'],'umfollownum',1);
        }
        
    }

    /**
     * 更新redis的用户数据
     * @param $userId
     * @return null
     */
    static public function getUpUserInfo($mac,$behavior)
    {
        if(!Redis::exists($mac)){
            $need=array(
                            'city'=>'',        //城市
                            'sex'=>'',          //性别
    //                        'bid'=>100,                     //渠道ID
                            'receivenum'=>0,                  //取号次数
                            'connectnum'=>0,                  //连接次数
                            'follownum'=>0,                   //关注次数
                            'finishnum'=>0,                   //点击完成次数
                            'umfollownum'=>0,                 //取关次数
                            'ghid'=>'',        //关注的公众号ghid
                        );
            Redis::hmset($mac,$need);
        }
        
        
        switch ($behavior) {
                case 1:
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
                    );
                    $addredis= json_encode($addredis,TRUE);
                    Redis::set($date['openid'],$addredis);
                    Redis::expire($date['openid'],600);
                    UpsubLogModel::getAddsubLog($date);
                    break;
                case 3:
                    //点击关注只是插入数据,从redis取得bid和orderid,Mac

                    
                    //需要把关注的公众号id插入进去,城市和性别,写入KEY为mac的用户数据,关注ghid
                    if(!strpos(Redis::hget($date['mac'], 'ghid'), $date['ghid']))
                    {
                        $nowghid=Redis::hget($date['mac'],'ghid').','.$date['ghid'];
                        Redis::hset($date['mac'],'ghid',$nowghid);
                    }

                    Redis::hset($date['mac'],'city',$date['city']);
                    Redis::hset($date['mac'],'sex',$date['sex']);
                    //记录今天关注的openid
                    $datetime= date('Ymd');
                    Redis::set($datetime.'openid',$date['openid']); 
                    FansLogModel::getAddFansLog($date);
                    break;
                case 4:
                    //只是插入数据
                    CompletLogModel::getCompletLog($date);
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
        switch (Redis::hexists($date,$mac)) {
                case 0:
                    Redis::hset($date, $mac, 1);  
                    break;
                default:
                    Redis::hincrby($date, $mac, 1);  
                    break;
        }
        
//        $orderkey='oid'.$orderid;
//        if($behavior==3){
//            if(Redis::exists($orderkey)){
//                Redis::set($orderkey, 1);
//            } else {
//                Redis::incr($orderkey);
//            }
//        }
        
    }
    
    
    /**
     * 统计用户行为
     * @param $userId
     * @return null
     */
    static public function getFansBehavior($date)
    {
        $behavior=$date['behavior'];
        
        switch ($behavior) {
            case 3:
                    if(!Redis::exists($date['openid'])){
                        die();
                    }
                    $addredis=Redis::get($date['openid']);
                    $addredis= json_decode($addredis,TRUE);
                    $date['order_id']=$addredis['order_id'];
                    $date['bid']=$addredis['bid'];
                    $date['mac']=$addredis['mac'];
                    $addlog= self::getAddUserInfoLog($behavior,$date);
                break;
            case 5:
                    $addlog= self::getAddUserInfoLog($behavior,$date);
                    $date['order_id']=$addlog['oid'];
                    $date['bid']=$addlog['bid'];
                    $date['mac']=$addlog['mac'];
                    $addqgcount= self::getqgCountFans($date);
                break;
            case 6:
                    $date['order_id']=null;
                break;
            default:
                $addlog= self::getAddUserInfoLog($behavior,$date);
                    
                break;
        }
        if($behavior==3){
            //统计用户的行为
            $newcount= self::getnewCountFans($date['mac'],$behavior,$date['order_id'],$date['bid'],$date['sex']);
        } else {
            //统计用户的行为
            $newcount= self::getnewCountFans($date['mac'],$behavior,$date['order_id'],$date['bid']);
        }

        
        //修改redis的用户信息
        $count= self::getUpUserInfo($date['mac'],$behavior);
        //统计用户的行为
        $hhd=self::getCountFans($date['order_id'],$date['bid'],$behavior);
        //记录今天用户的行为
        $doit=self::getIsDo($date['mac'],$behavior);
    }
    
    
    /**
     * 删除任务
     * @param $userId
     * @return null
     */
    static public function getDelTask($date)
    {
        foreach ($date as $key => $value) {
            Redis::hdel($value['buss_id'],$value['order_id']);
        }
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
        if (strpos(',,,'.$do, $behavior)) {
            //今天有这个行为    
            return TRUE;
        }
        //今天,没有行为
        return FALSE;
    }
    
    
    /**
     * 判断是否新老用户
     * @param $userId
     * @return null
     */
    static public function getIsOld($mac)
    {
        $userinfo= Redis::hgetall($mac);
        if(!isset($userinfo['follownum'])){

        }elseif($userinfo['follownum']==0){
            
        } else {
            return TRUE;
        }
        return FALSE;
        
        
    }
    
    
    /**
     * 记录今天用户干了的事
     * @param $userId
     * @return null
     */
    static public function getIsDo($mac,$behavior)
    {
        $datetime=date('Ymd');
        $key=$datetime.'mac';
        $do= Redis::hget($key,$mac);
        if($do==''){
            Redis::hset($key,$mac,$behavior);
        }elseif (strpos(',,,'.$do, $behavior)) {

        } else {
            $newdo=$do.$behavior;
            Redis::hset($key,$mac,$newdo);
        }
    }
    
    static public function getnewCountFans($mac,$behavior,$orderid=null,$bid,$sex=null){
            $datetime=date('Ymd');
            
            //日期加上渠道号加上订单号加上行为
            $weld=$orderid.'-'.$bid.'-'.$behavior;
            //新老用户,重复不重复,在这里判断
            if(self::getIsOld($mac)){
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
    static public function getqgCountFans($date) {
        $datetime=date('Ymd');
        if(Redis::exists($datetime.'openid',$date['openid'])){
            $orderid=$date['order_id'];
            $bid=$date['bid'];
            $weld='nqg-'.$orderid.'-'.$bid;
            Redis::hIncrBy($datetime, $weld, 1); 
        }
    }
}
