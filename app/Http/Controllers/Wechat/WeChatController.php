<?php
/**
 * Created by PhpStorm.
 * User: luojia
 * Date: 2017/6/7
 * Time: 14:15
 */

namespace App\Http\Controllers\Wechat;
use App\Http\Controllers\Controller;
use App\Lib\WeChat\Third;
use App\Lib\WeChat\Wechat;
use App\Lib\Data\Dandan;
use Illuminate\Support\Facades\Redis;
use App\Services\Impl\Wechat\WechatServicesImpl;
use App\Services\Impl\Order\BussServicesImpl;
use App\Services\Impl\Dandan\DandanServicesImpl;
use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Lib\HttpUtils\Tools;
use App\Lib\HttpUtils\Net;
use Illuminate\Support\Facades\Config;

class WeChatController extends Controller {

    const TEXT_URL = 'http://api.yft.com/';

    public function testWx(){
        $_SESSION['test'] = 11;
//        print_R($_SESSION);
        Redis::set('test1',18368860668);
        Redis::expire('test1', 10);
//        $user = Redis::get('test1');
//        var_dump($user);
    }


    /**
     * @param rid 报备id
     * 返回添加授权地址
     */
    public function add_auth(){

        $rid = isset($_SESSION['rid'])?$_SESSION['rid']:'';
        if($rid){
            $add_auth_rid = isset($_SESSION['add_auth_'.$rid])?$_SESSION['add_auth_'.$rid]:'';
            $add_auth_rid_time = isset($_SESSION['add_auth_'.$rid.'_time'])?$_SESSION['add_auth_'.$rid.'_time']:'';
//            if($add_auth_rid&&time()<$add_auth_rid_time){
//                $this-> alert_back('5分钟内请勿频繁授权!');
//            }
            $_SESSION['add_auth_'.$rid] = $rid;
            $_SESSION['add_auth_'.$rid.'_time'] = time()+300;

            $platform_id = 0;
            $third = new Third(array('platform_id' =>$platform_id,'rid'=>$rid));//记得这里不需要传授权返回地址
            $third->getAuthRedirect();
        }else{
            die('rid empty');
        }
    }



    //授权着陆页
    public function auth_back()
    {

        $config = unserialize($_GET['conf']);
        $queryauthcode = $_GET['auth_code'];
        WechatServicesImpl::auth_back($queryauthcode,$config);

    }

    //    //设置好门店,id之后对,保存数据库 http://user.airopera.com/index.php/WxAuth/set_default
    /**
     * wx_id:314
    shop_id:3987069
    shop_name:西溪天堂商业街
     */
    public function set_default()
    {

        $wx_id = (int)$_GET['wxid'];
        $shop_id = (int)$_GET['shopid'];
        $shop_name = $_GET['shopname'];
        $result = WechatServicesImpl::set_default($wx_id,$shop_id,$shop_name);
        if(isset($result) && $result>0){
            $wxChat = new WechatServicesImpl();
            $res = $wxChat->setShopPage($shop_id,$wx_id);
            if($res && $res==true){
                $re['data'] = 1;
                return ApiSuccessWrapper::success($re);
            }
        }else{
            // 门店写入失败,重复写入
            $re['data'] = 9999;
            return ApiSuccessWrapper::success($re);
        }
    }

//    // 获取的是门店地址
//
    public function get_wifishop()
    {

        $_SESSION['wxid'] = $_GET['wxid'];
        $wxService = new WechatServicesImpl();
        $list = $wxService->get_shop();
        if($list){
            return ApiSuccessWrapper::success($list);
        }else{
            return ApiSuccessWrapper::success('null');
        }

    }


    public function authRedirect(){
        $_SESSION['url']=$_GET['url'];
        $_SESSION['rid']=$_GET['rid'];
        header("Content-type: text/html; charset=utf-8");
        $url  = config('config.BUSS_URL');
        echo "<meta http-equiv='refresh' content='0; url=$url/Wechat/add_auth?'>";

    }



    /***
     * 商家主页
     */
    public function shangjia()
    {
        $now_url = Tools::get_url();
        $shangjia_url = Config::get('config.SHANGJIA_URL');
        $length = strlen($shangjia_url);
        $param = substr($now_url,$length);
        $_GET['extend'] = isset($_GET['extend'])?$_GET['extend']:'';
        if(strpos($_GET['extend'],'{')!==false){
            $bid = (int)$this->_cut('{','}',$_GET['extend']);
        }else{
            $extend = base64_decode($_GET['extend']);
            parse_str($extend,$arr);
            $bid = isset($arr['channelid'])?$arr['channelid']:'';
            if(!$bid){
                $extend_tmp = explode('&',$extend);
                $extend = $extend_tmp[0];
                $extend_tmp = explode('=',$extend);
                $extend = @$extend_tmp[1];
                $bid = (int)$this->_cut('{','}',$extend);
            }
        }
        $_GET['bssid'] = isset($_GET['bssid'])?$_GET['bssid']:'';
        if($bid>0)
        {
            //cookie('wft_bid',$bid,86400);
        }/*else if(cookie('wft_bid')!=''){
            $bid = cookie('wft_bid');
        }*/else if($_GET['bssid']!=''){
            $bssid=Net::get_mac($_GET['bssid']);
            if($bssid == '')$bssid=$_GET['bssid'];
            if($bssid!=''){
                $deviceinfo = BussServicesImpl::getDeviceInfo($bssid);
                $bid  = !empty($deviceinfo)?$deviceinfo['bid']:0;
            }
        }
        if($bid>0)
        {
            $shangjia_url_arr = BussServicesImpl::getBussInfo($bid);
            $shangjia_url = !empty($shangjia_url_arr)?$shangjia_url_arr['shangjia_url']:'';
            if($shangjia_url!=''){
                if(strpos($shangjia_url,'?')!==false)
                {
                    $param[0]='&';
                }
                $shangjia_url.=$param;
                header("Location:$shangjia_url");
            }
        }
        exit("默认商家主页");
    }


    public function addShopInfo(){
        $rid = isset($_GET['rid'])?$_GET['rid']:'';
        $ghname = isset($_GET['ghname'])?$_GET['ghname']:'';
        $shopName = isset($_GET['shopname'])?$_GET['shopname']:'';
        $shopid = isset($_GET['shopid'])?$_GET['shopid']:'';
        $ssid = isset($_GET['ssid'])?$_GET['ssid']:'';
        $appId = isset($_GET['appid'])?$_GET['appid']:'';
        $secretKey = isset($_GET['secretkey'])?$_GET['secretkey']:'';
        $ghid = isset($_GET['ghid'])?$_GET['ghid']:'';
        if( $rid && $ghname && $shopName && $ssid && $secretKey && $ghid && strlen($appId) == 18){
            $wxService = new WechatServicesImpl();
            $res = $wxService::addShopInfo($rid,$ghname,$shopName,$shopid,$ssid,$secretKey,$ghid,$appId);
            return ApiSuccessWrapper::success($res);
        }else{
            return ApiSuccessWrapper::fail(1002);
        }

    }

    //定时任务处理重发机制记录表
    public function rsend_timer_task(){
        // $rsend = M('rsend_event');
        //$now_time = time();
        // $rsend_list = $rsend->where('id>0')->select();
        $rsend_list = Dandan::rsend_select();
        if(!empty($rsend_list)){
            foreach ($rsend_list as $key => $value) {
                    // \Data\Dandanz::rsend_note($value);
                    $rsend_note = Dandan::rsend_note($value);
            }
        }
    }

    //定时任务处理重发机制记录表
    public function task_complete(){
        $redisarray['mac'] = 12121;
        $redisarray['order_id'] = 121;
        $redisarray['bmac'] = 12121;
        $redisarray['bid'] = 312312;
        $openid = 32234;
        $orderinfo = '{"total_fans":10000,"date_fans":10000,"start_date":"2017-09-19","end_date":"2017-10-31","start_time":"00:00","end_time":"23:55","check_status":2,"user_type":"0","is_hot_area":1,"isprecision":2,"fans_tag":null,"ghid":"gh_65ac839a9333","isattribute":1,"is_sex":2,"content":"a:9:{s:6:\"ghname\";s:12:\"\u4e00\u65e5\u5341\u6761\";s:4:\"ghid\";s:15:\"gh_65ac839a9333\";s:5:\"sname\";s:31:\"\u676d\u5dde\u667a\u6167\u4ea7\u4e1a\u521b\u4e1a\u56edB\u5ea7\";s:3:\"sid\";i:3550491;s:5:\"appid\";s:18:\"wxfe6bfec65cb590eb\";s:9:\"secretkey\";s:32:\"9d6950e1955e26d8cefaf8a54660fdb5\";s:4:\"area\";s:6:\"\u4e0d\u9650\";s:4:\"ssid\";s:4:\"wifi\";s:10:\"qrcode_url\";s:75:\"http:\/\/user.weifentong.com.cn\/Uploads\/Wxqrcode\/2017-02-16\/1556576151156.jpg\";}","price":0.6,"alreadynum":0}';
        Dandan::task_complete($redisarray,$openid,$orderinfo);
    }


    /**
     * 当时任务处理商家主页设置
     */
    public function checkShopPage(){
         $wechat = new WechatServicesImpl();
         return $wechat->checkShopPage();
    }


    



}