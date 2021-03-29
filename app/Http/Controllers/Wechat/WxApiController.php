<?php
/**
 * Created by PhpStorm.
 * User: luojia
 * Date: 2017/6/7
 * Time: 13:27
 */
namespace App\Http\Controllers\Wechat;
use App\Http\Controllers\Controller;
//use App\Lib\Data\BiHu;
//use App\Lib\Data\Dandan;
//use App\Lib\Data\Hcforward;
use App\Lib\HttpUtils\Net;
use App\Lib\WeChat\Third;
use App\Services\Impl\Wechat\WechatServicesImpl;
use App\Lib\WeChat\Wechat;
use App\Models\Wechat\WxInfoModel;
use App\Http\Controllers\Receive\PublicNumController;
use App\Services\Impl\Receive\ReceiveServicesImpl;
use Illuminate\Support\Facades\Redis;
use App\Services\Impl\Common\OldPlatformServicesImpl;
//use Illuminate\Support\Facades\Log;
use App\Services\Impl\Buss\BussPassServicesImpl;

class WxApiController extends Controller {
    private $pass_mode = false;//授权模式
    /**
     * 第三方平台接收授权事件
     */
    public function authNotify()
    {
        $platform_id = 0;
        $rid = 0;
        if(isset($_GET['platform_id'])){
            $platform_id = $_GET['platform_id'];
        }
        if(isset($_GET['rid'])){
            $rid = $_GET['rid'];
        }
        $third = new Third(array('platform_id' =>$platform_id,'rid'=>$rid));
        //$third->log('auth_notify platform_id='.$platform_id.' rid='.$rid);
        $type = $third ->getRev()->getRevType();
        switch ($type) {
            case $third::EVENT_VERIFY_TICKET:
                //$third->log('test_ok');
                $third->setComponentTicket();
                break;
            case $third::EVENT_AUTHORIZED:
                $auth_appid = $third->getAuthAppid();
                //操作任务表，更改任务状态
                //D('Task')->temp_restart($auth_appid,$platform_id);
                break;
            case $third::EVENT_UPDATEAUTH:
                //根据你是否重新取了授权码来发更新，应该重置一下
                $auth_code = $third->_receive['AuthorizationCode'];
                $auth_appid = $third->getAuthAppid();
                $result = $third ->queryAuthInfo($auth_code);
                $result = $result['authorization_info'];
                $wx_info = $third->getWxInfo($auth_appid);
                if(!$third->check_priv($wx_info['authorization_info']['func_info']))//必须的权限
                {
                    //防止用户临时更改权限
                    WechatServicesImpl::calcelAuth($auth_appid,$platform_id);
                }else{
                    //D('Task')->temp_restart($auth_appid,$platform_id);
                }
                break;
            case $third::EVENT_CANCEL_AUTH:
                $auth_appid = $third->getAuthAppid();
                WechatServicesImpl::calcelAuth($auth_appid,$platform_id);
                break;
            default:
                break;
        }
        echo 'success';
    }


    //普通事件接收
    public function event_notify()
    {

        $post_str = file_get_contents("php://input");

        //Tools::NewLog($_GET);
        //Tools::NewLog($post_str);

        $platform_id = 0;
        if(isset($_GET['platform_id'])){
            $platform_id = $_GET['platform_id'];
        }
        $third = new Third(array('platform_id' => $platform_id));
        $config = $third->get_cur_plat_conf();


        $config['debug'] = false;

        $weObj = new Wechat($config);//微信类

        $weObj->valid();

        $type =$weObj->getRev()->getRevType();

        $weObj->encrypt_type='';//回复不加密
        $ghid = $weObj->getRevTo();


        switch($type) {
            case $weObj::MSGTYPE_EVENT:
                if($this->pass_mode && $third->_receive['AppId'] == 'wx570bc396a51b8ff8')
                {
                    $weObj->text($event['event'].'from_callback')->reply();
                }else{

                    $this->handle_event($weObj,$ghid);
                }
                break;
            default:
                break;
        }
//        echo 'success';
        //$runtime = microtime(TRUE)-$GLOBALS['_beginTime'];
        //if($runtime>3){
            //$event = $weObj->getRevEvent();
            //$openid = $weObj->getRevFrom();
//            \Think\Log::write($runtime.$event['event'].$openid,\Think\Log::INFO);
        //}

    }


    private function handle_event($weObj,$ghid)
    {

        $event=$weObj->getRevEvent();  //获取事件类型

        //Tools::NewLog($event);

        switch($event['event'])
        {
            //取关
            case $weObj::EVENT_UNSUBSCRIBE:

                $openid = $_GET['openid'];

                $select = array('id');
                $where = array('ghid'=>$ghid);
                //Tools::NewLog($ghid);
                $idArray = WxInfoModel::getWxInfoOne($select,$where);

                $weObj->access_token=WechatServicesImpl::getToken($idArray['id']);

                //Tools::NewLog($weObj->access_token);


                $tmp =$weObj->getUserInfo($openid);
                //Tools::NewLog($tmp);
                $array = array(
                    'openid' => $openid,
                    'behavior' => 5,
                    'ghid' => $ghid
                );
                ReceiveServicesImpl::getFansBehavior($array);
                OldPlatformServicesImpl::getSendUnFollow($array);
                break;
            //关注
            case $weObj::EVENT_SUBSCRIBE:
                $openid = $_GET['openid'];
                //Log::info(date('Y-m-d H:i:s',time()).'-openid:'.$openid);
                $redisjson = Redis::get($_GET['openid']);  //用openid查询立即连接数据
                $redisarray = json_decode($redisjson,TRUE);
                if( !isset($redisarray['order_id']) || $redisarray['order_id']<=0 ) die;//没有的openid结束
                if(isset($redisarray['brand_id'])&&Redis::hExists('brandoid', $redisarray['order_id'])){
                    $orderjson = Redis::hget('brand'.$redisarray['brand_id'],$redisarray['order_id']);//查询订单
                }else{
                    $orderjson = Redis::hget($redisarray['bid'],$redisarray['order_id']);//查询订单
                }
                $orderinfo = json_decode($orderjson,TRUE);
                $dayfans = Redis::hget(date('Ymd'),'sum-'.$redisarray['order_id'].'-'.$redisarray['bid'].'-'.'3');//日涨粉
                $sumfans = Redis::get('tot-'.$redisarray['order_id'].'-'.$redisarray['bid']);
                if($orderinfo['date_fans']<=$dayfans||$orderinfo['total_fans']<=$sumfans)
                {
                    //终止程序;
                    die();
                }

                $select = array('id');
                $where = array('ghid'=>$ghid);

                $idArray = WxInfoModel::getWxInfoOne($select,$where);
                $weObj->access_token=WechatServicesImpl::getToken($idArray['id']);
                $tmp =$weObj->getUserInfo($openid);
                $array = array(
                    'openid' => $openid,
                    'behavior' => 3,
                    'city' => !empty($tmp['city'])?$tmp['city']:'未知城市',
                    'nickname' => Net::remove_emoji($tmp['nickname']),
                    'province' =>  !empty($tmp['province'])?$tmp['province']:'未知省份',
                    'sex' => isset($tmp['sex'])?$tmp['sex']:0,
                    'ghid' => $ghid,
                    //'province'=>$tmp['province']
                );
                ReceiveServicesImpl::getFansBehavior($array);
                OldPlatformServicesImpl::getSendFollowOld($array);
                //判断订单涨粉状态
                ReceiveServicesImpl::overOrder($openid);

                //渠道放行
                BussPassServicesImpl::getPassBuss($redisarray,$openid,$orderinfo);

                //蛋蛋赚关注通知
/*                if($redisarray['bid']==264){
                    Dandan::task_complete($redisarray,$openid,$orderinfo);
                }*/

                //寰创关注通知
/*                if($redisarray['bid']==266){
                    // Tools::NewLog('redisarray:'.json_encode($redisarray));
                    // Tools::NewLog('openid:'.json_encode($openid));
                    // Tools::NewLog('orderinfo:'.json_encode($orderinfo));
                    Hcforward::success_set($redisarray,$openid,$orderinfo,true);
                }*/

/*                if($redisarray['bid'] == 348){//极路由放行 后期需整合
                    $token = Redis::exists($redisarray['mac'].'_token')?Redis::get($redisarray['mac'].'_token'):'';
                    $url_jly = 'http://wifidog.hiwifi.com/share.php?m=portal&a=thirdcallback&param_usermac='.$redisarray['mac'].'&param_devmac='.$redisarray['bmac'].'&token='.$token.'&openid='.$openid.'&isnew=1';
                    file_get_contents($url_jly);
                }*/

                if($ghid=='gh_43dfb34d7dea'){ //更成都 打标签
                    $openid_list = array($openid);
                    $weObj->batchtag($openid_list,102);
                }

                if($ghid=='gh_b10776512024'){ //更杭州 打标签
                    $openid_list = array($openid);
                    $weObj->batchtag($openid_list,103);
                }

                //壁虎放行通知
                /*if($redisarray['bid']==346){
                    $mac = $redisarray['mac'];
                    $md5_mac = md5('bihu_'.$mac);
                    if(Redis::exists($md5_mac)){ //用户未访问
                        $tid = $appid;
                        $ext1 = Redis::get($md5_mac);
                        $umac = strtolower($redisarray['mac']);
                        $dmac = strtolower($redisarray['bmac']);
                        $bihu = new BiHu($tid,$umac,$dmac,$openid,$ext1);
                        $bihu->pass_authorize();
                    }
                }*/

                //壁虎自己设备放行 放行接口后期需整合成类
/*                $allow_bid_array = array(951,952,953,978,979);
                if(in_array($redisarray['bid'],$allow_bid_array)){
                    $bh_obj = new Su2Pass_BH($redisarray['mac'],$redisarray['bmac']);
                    $bh_obj->pass_authorize();
                }

                //数芳设备放行 放行接口后期需整合成类
                $allow_bid_Su2 = array(969,970,971,972,973,976,977,980);
                if(in_array($redisarray['bid'],$allow_bid_Su2)){
                    $su2_obj = new Su2Pass($redisarray['mac'],$redisarray['bmac'],$redisarray['bid']);
                    $su2_obj->pass_authorize();
                }*/
                
                break;
            default:
                break;
        }
    }

}