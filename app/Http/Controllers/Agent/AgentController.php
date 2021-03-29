<?php

namespace App\Http\Controllers\Agent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Impl\Agent\AgentServicesImpl;
use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Lib\WeChat\Third;
use App\Services\Impl\Wechat\WechatServicesImpl;

class AgentController extends Controller
{

    /**
     * @return array  代理列表
     */
    public function  managerAgent()
    {

        $data = $_REQUEST;
        return AgentServicesImpl::agentList($data);

    }

    /**
     * @return mixed 增加代理
     */
    public static function addAgent()
    {

        $data = $_GET;
        return AgentServicesImpl::addUser($data);

    }


    /**
     * @return mixed 编辑
     */
    public static function editAgent()
    {

        return AgentServicesImpl::editUser();

    }

    /**
     * @return mixed 设置子代理 的删 开启 关闭
     */
    public static function setAgentList(){


        return AgentServicesImpl::setAgentList();
    }

    /**
     *  子代理列表
     */
    public  static function sonAgentList(){

        $data = $_REQUEST;
        return AgentServicesImpl::sonAgentList($data);

    }

    /**
     *  子代理分析
     */
    public static function analyseSonAgent(){

        $data = $_REQUEST;
        return AgentServicesImpl::analyseSonAgent($data);
    }


    /**
     * @param rid 报备id
     * 返回添加授权地址    http://apitext.youfentong.com/agent/auth_redirect?rid=319&url=http://agenttext.youfentong.com/index.php/agent/report/reportlist
     */
    public function auth_redirect(){

        $rid = isset($_SESSION['agent_rid'])?$_SESSION['agent_rid']:'';
        if($rid){
//            $add_auth_rid = isset($_SESSION['add_agentAuth_'.$rid])?$_SESSION['add_agentAuth_'.$rid]:'';
//            $add_auth_rid_time = isset($_SESSION['add_agentAuth_'.$rid.'_time'])?$_SESSION['add_agentAuth_'.$rid.'_time']:'';
//            if($add_auth_rid&&time()<$add_auth_rid_time){
//                $this-> alert_back('5分钟内请勿频繁授权!');
//            }
//            $_SESSION['add_agentAuth_'.$rid] = $rid;
//            $_SESSION['add_agentAuth_'.$rid.'_time'] = time()+300;

            $platform_id = 0;
            $third = new Third(array('platform_id' =>$platform_id,'rid'=>$rid,'isAgent'=>1));//记得这里不需要传授权返回地址

            $third->getAuthRedirect();
        }else{
            die('rid empty');
        }
    }

    public function  add_auth(){
        $_SESSION['agent_rid']=$_REQUEST['rid'];
        header("Content-type: text/html; charset=utf-8");
        $url  = config('config.BUSS_URL');
        echo "<meta http-equiv='refresh' content='0; url=$url/agent/auth_redirect?'>";

    }



    //授权着陆页
    public function auth_back()
    {

        $config = unserialize($_GET['conf']);
        $queryauthcode = $_GET['auth_code'];
        WechatServicesImpl::auth_back($queryauthcode,$config);

    }

    public function getShopInfo(){

        $wxid =  WechatServicesImpl::get_wxid($_REQUEST['rid']);
        $_SESSION['agentwxid'] = $wxid;
        $wxService = new WechatServicesImpl();
        $list = $wxService->get_shop('','',2);
        if($list){
            $result['list'] = $list;
            $result['agentwxid'] = $wxid;
            return ApiSuccessWrapper::success($result);
        }else{
            return ApiSuccessWrapper::success('null');
        }

    }



    public function set_default(){

        $wx_id = (int)$_GET['wxid'];
        $shop_id = (int)$_GET['shopid'];
        $shop_name = $_GET['shopname'];
        $result = WechatServicesImpl::set_default($wx_id,$shop_id,$shop_name,true);
        if(isset($result) && $result>0){
            $wxChat = new WechatServicesImpl();
            $res = $wxChat->setShopPage($shop_id,$wx_id);
            if($res && $res==true){
                $re['data'] = 1;
                return ApiSuccessWrapper::success($re);
            }
        }else{
            $re['data'] = 9999;
            return ApiSuccessWrapper::success($re);
        }
    }

}
