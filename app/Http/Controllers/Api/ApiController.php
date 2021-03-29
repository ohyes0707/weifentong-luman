<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/8/1
 * Time: 16:07
 */
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Lib\HttpUtils\Net;
use App\Lib\HttpUtils\Tools;
use App\Models\ThirdData\AiKuaiSubLogModel;
use App\Services\Impl\Store\StoreBrandServicesImpl;
use Illuminate\Support\Facades\DB;
use App\Lib\WeChat\Wechat;
use App\Services\Impl\Wechat\WechatServicesImpl;
use App\Models\Count\OrderModel;
use App\Models\Count\FansLogModel;
use Illuminate\Support\Facades\Redis;
use App\Models\Admin\AdminLogModel;
use Illuminate\Support\Facades\Config;
use App\Models\Wechat\WxInfoModel;
use App\Models\Buss\BussModel;

class ApiController extends Controller{

    /***
     * 顺巴查询当日实时涨粉数接口
     */
    public function getTodayFans(){
        $bid = isset($_GET['bid'])?$_GET['bid']:'';
        $result = array('error'=>0,'msg'=>'ok','data'=>null);
        if($bid){
            $where[] = array('id','=',$bid);
            $buss_obj =  BussModel::select('pbid')->where($where)->first();
            if($buss_obj){
                $buss = $buss_obj->toArray();
                if( $buss['pbid']==2 || $bid==2){
                    $now_fans = Redis::hget(date('Ymd'),'sum--'.$bid.'-3');
                    $now_fans = $now_fans?$now_fans:0;
                    $result = array('error'=>0,'msg'=>'ok','data'=>array('bid'=>(int)$bid,'fans'=>(int)$now_fans));
                }else{
                    $result = array('error'=>-3,'msg'=>'bid不合法','data'=>null);
                }
            }else{
                $result = array('error'=>-2,'msg'=>'bid不存在','data'=>null);
            }
        }else{
            $result = array('error'=>-1,'msg'=>'缺少必要参数bid','data'=>null);
        }
        echo json_encode($result);
    }

    /***
     * 打标签接口
     */
    public function batchTag(){
        $ghid = isset($_GET['ghid'])?$_GET['ghid']:'gh_b10776512024';
        $openid = isset($_GET['openid'])?$_GET['openid']:'';
        $result = array('error_code'=>-1,'msg'=>'没有该公众号信息');
        if( $ghid && $openid ){
            $tag_code_arr = array(  //定义ghid对应标签id
                'gh_b10776512024'=>113,
            );
            $select = array('id');
            $where = array('ghid'=>$ghid);
            $idArray = WxInfoModel::getWxInfoOne($select,$where);
            if($idArray['id']){
                $config['debug'] = false;
                $weObj = new Wechat($config);//微信类
                $weObj->access_token=WechatServicesImpl::getToken($idArray['id']);
                $openid_list = array($openid);
                $res = $weObj->batchtag($openid_list,$tag_code_arr[$ghid]);
                if($res){
                    $result = array('error_code'=>0,'msg'=>'打标签成功');
                }else{
                    $result = array('error_code'=>-3,'msg'=>'打标签失败');
                }
            }
        }else{
            $result = array('error_code'=>-2,'msg'=>'缺少openid或ghid');
        }
        echo json_encode($result);
    }

    /***
     * 美业门店中转接口
     */
    public function store_redirect(){
        $mac = isset($_GET['mac'])?$_GET['mac']:'';
        $bmac = isset($_GET['bmac'])?Net::get_mac($_GET['bmac']):'';
        $bssid = isset($_GET['bssid'])?$_GET['bssid']:'';
        $result = array('error'=>0,'message'=>'');
        if($mac&&$bmac){
            $res = StoreBrandServicesImpl::getStoreByBmac($bmac);
            if($res){
                $brand_portal = $res['brand_portal']?base64_encode(Config::get('config.OPERATE_URL').str_replace('\\','/',$res['brand_portal'])):'';
                $portal_url = Config::get('config.RES_URL').'/meiye/rzing.html?bid='.$res['bid'].'&brand_id='.$res['brand_id'].'&store_id='.$res['store_id'].'&mac='.$mac.'&bmac='.$bmac.'&bssid='.$bssid.'&brand_name='.$res['brand_name'].'&brand_portal='.$brand_portal;
                header("Location:$portal_url");
            }else{
                $result = array('error'=>-2,'message'=>'未找到设备绑定的门店信息');
            }
        }else{
            $result = array('error'=>-1,'message'=>'缺少必要参数mac或bmac');
        }
        echo json_encode($result);
    }

    public function admin_log()
    {
        $username = isset($_GET['username'])?$_GET['username']:'';
        $userid = isset($_GET['userid'])?$_GET['userid']:'';
        $useragent = isset($_GET['useragent'])?$_GET['useragent']:'';
        $message = isset($_GET['message'])?$_GET['message']:'';
        $userip = isset($_GET['userip'])?$_GET['userip']:'';
        $result = array('error'=>0,'msg'=>'');
        if($username&&$userid&&$useragent&&$message&&$userip){
            $data['operator'] = $username;
            $data['operator_id'] = $userid;
            $data['ip'] = $userip;
            $data['agent'] = $useragent;//$_SERVER['HTTP_USER_AGENT'];
            $data['datetime'] = date('Y-m-d H:i:s',time());
            if(is_string($message))$message = var_export($message,TRUE);
            $data['action'] = $message;
            $res = AdminLogModel::insert($data);
            if($res){
                $result = array('error'=>0,'msg'=>'ok');
            }else{
                $result = array('error'=>-1,'msg'=>'database error');
            }
        }else{
            $result = array('error'=>-1,'msg'=>'缺少参数');
        }
        echo json_encode($result);
    }


    /**
     * 微信api判断关注接口
     */
    public function subscribe_wxapi(){
        $oid = isset($_GET['oid'])?$_GET['oid']:null;
        $bid = isset($_GET['bid'])?(int)$_GET['bid']:null;
        $openid = isset($_GET['openid'])?$_GET['openid']:null;
        $focus_type = isset($_GET['type'])?(int)$_GET['type']:null;
        $mac = isset($_GET['mac'])?$_GET['mac']:null;
        $result = array('error'=>0,'subscribe'=>0,'message'=>'ok');
        if($openid !='' && $oid >0 && $bid>0)
        {
            $mac =Net::get_mac($mac);
            $where[] = array('order_id','=',$oid);
            $order_list_obj =  OrderModel::select('o_wx_id','content')->where($where)->first();
            $order_list = $order_list_obj?$order_list_obj->toArray():null;
            if($order_list){
                $wx_id = $order_list['o_wx_id'];
                $content = unserialize($order_list['content']);
                $config['debug'] = false;
                $weObj = new Wechat($config);//微信类
                $weObj->access_token=WechatServicesImpl::getToken($wx_id);
                $tmp =$weObj->getUserInfo($openid);
                if($tmp['subscribe'] == 1){
                    if($mac&&!strpos($content['ghid'],'test')){
                        if(!strpos(','.Redis::hget($mac, 'ghid'), $content['ghid']))
                        {
                            $nowghid=Redis::hget($mac,'ghid').','.$content['ghid'];
                            Redis::hset($mac,'ghid',$nowghid);
                        }
                    }
                    $result['subscribe']=1;
                }else{
                    //$where_s[] = array('oid','=',$oid);
                    $where_s[] = array('bid','=',$bid);
                    $where_s[] = array('openid','=',$openid);
                    $list_obj =FansLogModel::select('id')->where($where_s)->first();
                    if($list_obj){
                        if($mac&&!strpos($content['ghid'],'test')){
                            if(!strpos(','.Redis::hget($mac, 'ghid'), $content['ghid']))
                            {
                                $nowghid=Redis::hget($mac,'ghid').','.$content['ghid'];
                                Redis::hset($mac,'ghid',$nowghid);
                            }
                        }
                        $result['subscribe']=1;
                    }
                }
            }else{
                $url = 'http://api.weifentong.com.cn/index.php/Api/subscribe_wxapi?bid='.$bid.'&oid='.$oid.'&openid='.$openid;
                $res = $this->http_get($url);
                $result  = json_decode($res,true);
                //$result = array('error'=>-2,'subscribe'=>0,'message'=>'订单不存在');
            }
        }else{
            $result = array('error'=>-1,'subscribe'=>0,'message'=>'参数错误');
        }
        Tools::jsonreturn($result);
    }

    /****
     * 爱快查询立即连接记录接口
     * 接口验证方式
     */
    public function get_upsubscribe(){
        $result = array('status'=>0,'msg'=>'','date'=>null);
        $param = array();
        $param['date'] = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
        $param['page'] = isset($_GET['page']) ? $_GET['page'] : 1;
        $param['pagesize'] = isset($_GET['pagesize']) ? $_GET['pagesize'] : 20;
        $param['tm'] = isset($_GET['tm']) ? $_GET['tm'] : '';
        $sign_param = isset($_GET['sign']) ? $_GET['sign'] : '';
        $bid = 344;
        $key = md5($bid);
        $sign = $this->make_sign($param,$key);
        if($sign==$sign_param){
            $start_size = ($param['page']-1)*$param['pagesize'];
            $where[] = array('create_time','>=',$param['date'].' 00:00:00');
            $where[] = array('create_time','<=',$param['date'].' 23:59:59');
            $total_count_obj = AiKuaiSubLogModel::select(DB::raw('count(id) as total_count'))->where($where)->first();
            $total_count_arr = $total_count_obj?$total_count_obj->toArray():0;
            $total_count = $total_count_arr['total_count'];
            $list = null;
            $total_page = 0;
            if($total_count){
                $list_obj = AiKuaiSubLogModel::select('oid','mac','bmac','openid','subscribe','price','ghname','appid','subscribe_time')->where($where)
                    ->offset(($param['page'] - 1) * $param['pagesize'])
                    ->limit($param['pagesize'])->get();
                $list = $list_obj?$list_obj->toArray():null;
                $total_page = ceil($total_count/$param['pagesize']);
            }
            if($list){
                $date_arr['list'] = $list;
                $date_arr['total_page'] = $total_page;
                $date_arr['total_count'] = $total_count;
                $date_arr['now_page'] = $param['page'];
                $result['status'] = 1;
                $result['msg'] ='成功';
                $result['date'] = $date_arr;
            }else{
                $result['status'] = -2;
                $result['msg'] ='databese null';
            }
        }else{
            $result['status'] = -1;
            $result['msg'] ='没有权限';
        }
        Tools::jsonreturn($result);
    }

    private function http_get($url){
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }

    /**
     * @param $data
     * @return string
     */
    private function make_sign($data,$key){
        ksort($data);
        $paramString =  $this->parseUrlParamString($data);
        //if(self::DEBUG) $this->logger('paramString=>'.var_export($paramString,TRUE));
        $paramString .= $key;
        //if(self::DEBUG) $this->logger('paramString=>'.var_export($paramString,TRUE));
        return hash("sha256", $paramString);
    }

    /***
     * 拼接参数成字符串
     * @param $param
     * @return string
     */
    private function parseUrlParamString($param){
        $arg = "";
        if($param) {
            foreach ($param as $key => $val) {
                $arg .= $key . "=" . $val;
            }
        }
        return $arg;
    }
}