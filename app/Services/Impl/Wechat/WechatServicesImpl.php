<?php

namespace App\Services\Impl\Wechat;

use App\Services\CommonServices;
use App\Services\WechatServices;
use App\Models\Wechat\PlatFormModel;
use App\Models\Wechat\WxInfoModel;
use App\Models\Report\WxReportModel;
use App\Lib\WeChat\Third;
use App\Lib\WeChat\Wechat;
use App\Services\Impl\Order\OrderServicesImpl;
use Illuminate\Support\Facades\Config;
use App\Models\Agent\AgentModel;
use App\Models\Order\OrderModel;



class WechatServicesImpl extends CommonServices implements WechatServices
{

    const FAILED_AUTH = 0;//微信授权失败状态
    const SUCCESS_AUTH = 1;//微信授权成功状态
    const REPORT_AUTH_SUCCESS = 3; //报备授权成功状态
    const REPORT_AUTH_FAILED = 2;//报备授权失败状态
    const REPORT_SUCCESS = 4;//报备成功状态
    const TEXT_URL = 'http://lumen5.4.com/';
    public  $wxObj;


    /**
     * 获取第三方平台配置信息
     * @param $platform_id
     * @return null
     */
    static public function getPlatFormInfo($platform_id){
        return PlatFormModel::getPlatFormInfo($platform_id);
    }

    /**
     * 取消微信授权
     * @param $auth_appid
     * @param $platform_id
     * @return null
     */
    static public function calcelAuth($auth_appid,$plat_id){
        $where = array('appid' => $auth_appid,'plat_id'=>$plat_id);
        $data = array('status' => self::FAILED_AUTH);
        $result = WxInfoModel::updateWxInfo($where,$data);
        if($result !== false){

            $ismeiye = WxInfoModel::where('appid',$auth_appid)->select('type')->first()->type;

            if($ismeiye==2)
            {
                $where_wx = array('appid'=>$auth_appid,'status'=>2);
                $res = WxInfoModel::getSuccessAuthReport($where_wx);
                if(!empty($res)){
                    $data_wx = array('status'=>3);
                    $where_wx = array('appid'=>$auth_appid,'status'=>2);
                    WxInfoModel::updateReportOrWhere($where_wx,$data_wx);
                }
            }else{
                //改变报备状态
                $where_wx = array('appid'=>$auth_appid,'status'=>self::REPORT_AUTH_SUCCESS);
                $or_where_wx = array('appid'=>$auth_appid,'status'=>self::REPORT_SUCCESS);
                $res = WxReportModel::getSuccessAuthReport($where_wx,$or_where_wx);
                if(!empty($res)){
                    $data_wx = array('status'=>self::REPORT_AUTH_FAILED);
                    WxReportModel::updateReportOrWhere($where_wx,$or_where_wx,$data_wx);
                }
            }
            //改变订单状态
            //改变工单状态
            OrderServicesImpl::closeTask($auth_appid);
        }
    }


    /***
     * 获取微信token服务
     * @param $wx_id 微信di
     * @return null
     */
    static public function getToken($wx_id)
    {

        $select = array('access_token','expires_in','refresh_token','appid','plat_id');
        $where = array('id'=>$wx_id);
        $list = WxInfoModel::getWxInfoOne($select,$where);




        if($list!=null)
        {

            if(time()<$list['expires_in'])return $list['access_token'];

            $third =new Third(array('platform_id'=>$list['plat_id']));

            $data = $third->refresh_authorizer_token($list['appid'],$list['refresh_token']);

            if($data)
            {
                self::setToken($wx_id,$data['authorizer_access_token'],$data['expires_in'],$data['authorizer_refresh_token']);
                return $data['authorizer_access_token'];
            }
        }
        return null;
    }

    /****
     * 设置微信token
     * @param $wx_id
     * @param $token
     * @param $expire
     * @param string $refresh_token
     * @return mixed
     */
    static public function setToken($wx_id,$token,$expire,$refresh_token = '')
    {
        $data['access_token'] = $token;
        $data['expires_in'] = time()+$expire-((int)($expire/12));
        if($refresh_token != '')$data['refresh_token'] = $refresh_token;
        $where = array('id'=>$wx_id);
        return WxInfoModel::updateWxInfo($where,$data);
    }

    //授权着陆页
    static public function auth_back($queryauthcode, $confarray)
    {

        $platform_id = $confarray['platform_id'];
        $isAgent = isset($confarray['isAgent'])?$confarray['isAgent']:0;

        $rid = $confarray['rid']; //y_wx_report 的 id

        //TODO:域名检查
        $third = new Third(array('platform_id' => $platform_id, 'rid' => $rid));

        $result = $third->queryAuthInfo($queryauthcode);

        $result = $result['authorization_info'];

        $wx_info = $third->getWxInfo($result['authorizer_appid']);

        if ($result && $wx_info) {

            $decideResult = WxReportModel::decideReport($wx_info,$isAgent);
            if($decideResult == false){  //http://operatetest.youfentong.com/index.php/operate/report/reportlist
                if($isAgent ==1){ // 代理
                    header('Location:'.Config::get('config.AGENTE_URL').'/index.php/agent/report/reportlist?action=error');
                    exit();
                }else{
                    header('Location:'.Config::get('config.OPERATE_URL').'/index.php/operate/report/reportlist?action=error');
                    exit();
                }

            }

            if (!$third->check_priv($wx_info['authorization_info']['func_info']))//必须的权限
            {

                $third->setPreauthCode('', 0);
                if($isAgent ==1) {
                    header('Location:' . Config::get('config.AGENTE_URL') . '/index.php/agent/report/reportlist?action=limit');
                    exit();
                }else{
                    header('Location:' . Config::get('config.OPERATE_URL') . '/index.php/operate/report/reportlist?action=limit');
                    exit();

                }

            }
            $wxmodel = new WxInfoModel();
            $id = $wxmodel->add_wx($wx_info['authorizer_info']['user_name'], $platform_id); //wx_id;

            if ($id > 0) {
                //保存公众号token
                self::setToken($id, $result['authorizer_access_token'], $result['expires_in'], $result['authorizer_refresh_token']);
                //公众号信息
                 $wxmodel->save_info($wx_info);
                 WxReportModel::changeReport($id, $wx_info,$isAgent);
                 $third->setPreauthCode('',0);
                if($isAgent ==1) {
                    header('Location:'.Config::get('config.AGENTE_URL').'/index.php/agent/report/reportlist?action=open&wxid='.$id);
                    exit();
                }else{
                    header('Location:'.Config::get('config.OPERATE_URL').'/index.php/operate/report/reportlist?action=open&wxid='.$id);
                    exit();

                }



            }

        }
    }

    public function check_wxobj($ismeiye)
    {

        $this->wxObj = new Wechat();
        if(isset($ismeiye) && $ismeiye==1){
            $wxid= $_SESSION['meiyewxid'];
        }elseif(isset($ismeiye) && $ismeiye==2){
            $wxid= $_SESSION['agentwxid'];
        }else{
            $wxid= $_SESSION['wxid'];
        }
        $this->wxObj->access_token = $this->getToken($wxid);
        return true;
    }


    public function get_shop($shop_id = 0,$page_size = 20,$ismeiye=null)
    {


        $this->check_wxobj($ismeiye);

        $page = 1;
        $all_page = 1;
        $result = array();
        $field = 'shop_id';//查找的字段
        $find_val = 0;
        if(is_array($shop_id)){
            $field = $shop_id[0];
            $find_val = $shop_id[1];
        }

        $data = $this->wxObj->wifiShopList($page,$page_size);
        if($data == false)return null;
        $all_page = $data['pagecount'];
        for($page = 1;$page <= $all_page;$page++)
        {
            if($page != 1)$data = $this->wxObj->wifiShopList($page,$page_size);
            if($find_val === 0 && $data!=NULL)
            {
                $result = array_merge_recursive($result,$data['records']);
            }else{
                foreach ($data['records'] as $key => $value) {
                    if($value[$field] == $find_val)
                    {
                        return $value;
                    }
                }
            }
        }
        return $result;

    }

    /**
     *  设置默认门店
     */
    static public function set_default($wx_id, $shop_id, $shop_name)
    {
        $where = array('id' => $wx_id, 'status' => 1);
        $token = self::getToken($wx_id);
        $wx = new Wechat();
        $wx->access_token = $token;
        $secretkey = $wx->add_device($shop_id, 'wifi', false);
        $data = array('default_shopid' => $shop_id, 'default_shopname' => $shop_name, 'ssid'=>'wifi', 'secretkey' => $secretkey);

        $result = WxInfoModel::updateWxInfo($where, $data);

        return $result;
    }



//   设置商家主页

    public function setShopPage($shop_id,$wx_id,$ismeiye=null){
        $_SESSION['wxid'] = $wx_id;

        $this->check_wxobj($ismeiye);

        $shangjia_url = config('config.SHANGJIA_URL');

        $homepage = $this->wxObj->setHomepage($shop_id,1,$shangjia_url);
        $finishpage = $this->wxObj->setFinshpage($shop_id,$shangjia_url);

        if($homepage && $finishpage){
            return true;
        }else{
            return false;
        }

    }


    /**
     *  查询商家主页  OrderModel
     */
    public function checkShopPage(){
        header("Content-type: text/html; charset=utf-8");

        $where = array('order_status'=>1);
        $select= 'o_wx_id';
        $wxidArray = OrderModel::getwxid($where,$select);
        $this->wxObj = new Wechat();
        $shangjia_url = config('config.SHANGJIA_URL'); //'SHANGJIA_URL' => 'http://api.youfentong.com/Wechat/shangjia/v1.0',
        $wxidArray =  AgentModel::object_array($wxidArray);

//        $wxidArray=array(['o_wx_id' => '427'],['o_wx_id' => '428'],['o_wx_id' => '429']);
        foreach($wxidArray as $key=>$value){

            $access_token = $this->getToken($value['o_wx_id']);


            if($access_token != ''){
                    $this->wxObj->access_token = $access_token;
                    $shopid = WxInfoModel::getWxInfoOne('default_shopid',array('id'=>$value['o_wx_id']));
                    $Homepage = $this->wxObj->wifiShopGet($shopid['default_shopid']);
                    if($Homepage['homepage_url'] !=$shangjia_url){
                        $this->wxObj->setHomepage($shopid['default_shopid'],1,$shangjia_url);
                    }
                    if($Homepage['finishpage_url'] !=$shangjia_url){
                        $this->wxObj->setFinshpage($shopid['default_shopid'],$shangjia_url);
                    }

                }
        }


    }




    public function getHomepage($shop_id){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $data['shop_id'] = (int)$shop_id;
        $result = $this->http_post(self::WIFI_URL_PREFIX.self::GETHOMEPAGE.'access_token='.$this->access_token,self::json_encode($data));
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
    }




    static function alert_to($content,$to)
    {
        echo "<script>alert('".$content."');location.href='$to';</script>";
        exit();
    }

    /***
     * @param $rid
     * @param $ghname
     * @param $shopName
     * @param $ssid
     * @param $secretKey
     * @param $ghid
     * @param $appId
     * @return null
     */
    static function addShopInfo($rid,$ghname,$shopName,$shopid,$ssid,$secretKey,$ghid,$appId){
        $select = array('id');
        $where = array('ghid'=>$ghid,'plat_id'=>0);
        $wxinfo = WxInfoModel::getWxInfoOne($select,$where);
        $data = array('wx_name' => $ghname,
            'default_shopname' => $shopName,
            'default_shopid' => $shopid,
            'ssid' => $ssid,
            'secretkey' => $secretKey,
            'ghid' => $ghid,
            'appid' => $appId,
            'status'=>2
        );
        if($wxinfo){//存在微信信息 更新
            $where = array('id' => $wxinfo['id']);
            $res = WxInfoModel::updateWxInfo($where,$data);
        }else{//不存在新增
            $res = WxInfoModel::saveWxInfo($data);
            $wxinfo['id'] = $res;
        }
        $where2 = array('id'=>$rid);
        $data2 = array('wx_id'=>$wxinfo['id'],'appid'=>$appId,'ghid'=>$ghid,'status'=>3);
        WxReportModel::updateReport($where2,$data2);
        return $res;
    }

    /**
     * 运营系统公众号列表
     */
    static public function wechatList($ghid,$page,$pagesize){
        $data = WxInfoModel::wechatList($ghid,$page,$pagesize);
        if(isset($data['data'])){
            foreach($data['data'] as $k=>$v){
                switch($v['service_type']){
                    case -1 :
                        $data['data'][$k]['service_type'] = '未知';
                        break;
                    case 2:
                        $data['data'][$k]['service_type'] = '服务号';
                        break;
                    default:
                        $data['data'][$k]['service_type'] = '订阅号';
                        break;
                }
                switch($v['verify_type']){
                    case -1 :
                        $data['data'][$k]['verify_type'] = '未认证';
                        break;
                    default:
                        $data['data'][$k]['verify_type'] = '认证';
                        break;
                }
                switch($v['status']){
                    case 1 :
                        $data['data'][$k]['status'] = '授权';
                        break;
                    default:
                        $data['data'][$k]['status'] = '未授权';
                        break;
                }
            }
        }
        return $data;
    }

    public static function get_wxid($rid){
        return WxReportModel::get_wxid($rid);
    }

}
