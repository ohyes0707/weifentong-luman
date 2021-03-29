<?php
namespace App\Services\Impl\Common;

use App\Lib\Data\Yundai;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Lib\HttpUtils\HttpRequest;
use App\Models\Count\OrderModel;
use App\Models\Buss\BussModel;
/**
 * 主要记录用户行为
 */

class WriteBehaviorServicesImpl {
    //返回我们的订单号
    static public function getPlatformOid($array) {
        switch ($array['type']) {
            case '3':
                $yudai_array=array(
                    'bid'=>$array['bid'],
                    'third_oid'=>$array['order_id'],
                    'oid'=>153,
                    'mac'=>$array['mac'],
                    'openid'=>$array['openid'],
                    'status'=>'',
                    'bmac'=>'',
                );
                DB::table("up_sub_yundai")->insert($yudai_array);  
                return 153;
                
            case '4':
                //调取老平台的立即连接接口
                $parameter=array(
                    'bid'=>$array['bid'],
                    'oid'=>$array['order_id'],
                    'openid'=>$array['openid'],
                    'mac'=>$array['mac'],
                    'bmac'=>$array['bmac'],
                    'ext1'=>'',
                );
                HttpRequest::getApiServices('api', 'up_subscribe', 'GET', $parameter);
                return 155;
            case '5':
                $orderInfo = Redis::get($array['mac'].'51login');
                if($orderInfo == ''){
                    die();
                }
                $orderArr = json_decode($orderInfo, TRUE);
                //调取山腾的立即连接接口
                $parameter=array(
                    'businessid'=>'5017-21',
                    'openId'=>$array['openid'],
                    'stamac'=>$array['mac'],
                    'appid'=>isset($orderArr['appid'])?$orderArr['appid']:'',
                    'token'=>time(),
                    'extend'=>isset($array['extend'])?$array['extend']:'',
                    'tid'=> isset($array['tid'])?$array['tid']:'',
                );
                $url = 'http://wx.51login.cn/s.stp';
                HttpRequest::getApiServices('', '', 'GET', $parameter,$url);
                return 211;
            default:
                return $array['order_id'];
        }
    }
    
    //点击完成返回我们的订单号
    static public function getEndPlatformOid($array) {
        switch ($array['type']) {
            case '3':
                return 153;
            case '4':
                return 155;
            case '5':
                return 211;
            default:
                return $array['order_id'];
        }
    }
    
    //平台对粉丝的统计
    static public function setFollowRedisCount($userinfo,$isxin) 
    {
        $datetime=date('Ymd');
        //订单总涨粉
        $orderkey='tot-'.$userinfo['oid'].'-'.$userinfo['bid'];
        Redis::incr($orderkey);
        
        $weld=$userinfo['oid'].'-'.$userinfo['bid'].'-3';

        //新用户插入数据
        $key=$isxin.'-'.$weld;
        Redis::hIncrBy($datetime, $key.'-1', 1); 
        Redis::hIncrBy($datetime, $key.'-2', 1); 
        
        //未知用户性别
        Redis::hIncrBy($datetime, 'sex-'.$weld.'-1-0', 1);
    }
    
    
//    //平台对云袋用户关注公众号的修改
//    static public function setYundaiRedisFollow($userinfo) 
//    {
//        $ghid = Redis::hget('yundai',$userinfo['third_oid']);
//        $userghid = Redis::hget($userinfo['mac'],'ghid');
//        if($ghid!=null&&strpos($userghid,$ghid) === false){
//            Redis::hset($userinfo['mac'],'ghid',$userghid.','.$ghid);
//        }
//    }
    
   //爱快-乾联立即连接特殊处理
    static public function getPlatformBid($array) {
        switch ($array['bid']) {
            case 344:
                $map['order_id'] = $array['order_id'];
                if($array['type']==4){
                        $map['order_id'] = 155;
                    }
                //$model = M('task');
                $order_info = OrderModel::select(['o_per_price','content'])->where($map)->first();
                if($order_info){
                    $order_info = $order_info->toArray();
                    $one_price = BussModel::where("id",'=',$array['bid'])->first()->one_price;
                    $content = unserialize($order_info['content']);
                    $data['oid'] = $array['order_id'];
                    $data['mac'] = $array['mac'];;
                    $data['bmac'] = $array['bmac'];
                    $data['openid'] = $array['openid'];
                    $data['price'] = $one_price;//$order_info['order_price'];
                    $data['ghname'] = $content['ghname'];
                    $data['appid'] = $content['appid'];
                    //$data['create_time'] = date('Y-m-d H:i:s',time());
                    if($array['type']==4){
                        $old = json_decode(Redis::hget('oldoid',$array['order_id']),TRUE);
                        $data['appid'] = $old['appid'];
                        $data['ghname'] = $old['ghname'];
                        $data['oid'] = $old['oid'];
                    }
                    DB::table("ai_kuai_sublog")->insert($data);  
                }
                
                break;
            case 346:
                if(isset($array['ext1'])&&$array['ext1']!=''){
                    Redis::set(md5('bihu_'.$array['mac']),$array['ext1']);
                    Redis::Expire(md5('bihu_'.$array['mac']),300);
                }
                break;
            default:
                break;
        }
    }
    
    //爱快特殊记录表数据
    static public function setFollowBid($array) {
        switch ($array['bid']) {
            case 344:
                    $oid = $array['order_id'];
                    $where_ak = array();
                    $where_ak['openid'] = $array['openid'];
                    //$where_ak['oid'] = $array['order_id'];
                    $where_ak['mac'] = $array['mac'];
                    $ak_id = DB::table("ai_kuai_sublog")->where($where_ak)->orderBy('id', 'DESC')->first();
                    if($ak_id){
                        $where_id['id'] = $ak_id->id;
                        $data_ak['subscribe'] = 1;
                        $data_ak['subscribe_time'] = date('Y-m-d H:i:s',time());
                        DB::table("ai_kuai_sublog")->where($where_id)->update($data_ak);
                    }
                break;
            default:
                break;
        }
    }
}
