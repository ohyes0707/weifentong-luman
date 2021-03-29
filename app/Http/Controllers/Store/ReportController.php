<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Services\Impl\Store\ReportServicesImpl;
use App\Lib\WeChat\Third;
use App\Services\Impl\Wechat\WechatServicesImpl;




class ReportController extends Controller
{
    /**
     * 获取条件获取报备信息
     * @method GET
     * @return array
     */
    public function getReportList()
    {

        $data = $_REQUEST;
        return  ReportServicesImpl::getReportList($data);


    }

    /**
     * 新增报备
     */
    public function addreport(){

        $data = $_REQUEST;
        return  ReportServicesImpl::addreport($data);

    }

    /**
     *  美业收授权
     */
    public function add_auth(){

        $rid = isset($_SESSION['store_rid'])?$_SESSION['store_rid']:'';
        if($rid){
            $add_auth_rid = isset($_SESSION['store_auth_'.$rid])?$_SESSION['store_auth_'.$rid]:'';
            $add_auth_rid_time = isset($_SESSION['store_auth_'.$rid.'_time'])?$_SESSION['store_auth_'.$rid.'_time']:'';
//            if($add_auth_rid&&time()<$add_auth_rid_time){
//                $this-> alert_back('5分钟内请勿频繁授权!');
//            }
            $_SESSION['store_auth_'.$rid] = $rid;
            $_SESSION['store_auth_'.$rid.'_time'] = time()+300;

            $platform_id = 0;
            $third = new Third(array('platform_id' =>$platform_id,'rid'=>$rid,'isStore'=>1));//记得这里不需要传授权返回地址
            $third->getAuthRedirect();
        }else{
            die('rid empty');
        }


    }


    public function auth_redirect(){

        $_SESSION['store_rid']=$_GET['rid'];
        header("Content-type: text/html; charset=utf-8");
        $url  = config('config.BUSS_URL');
        echo "<meta http-equiv='refresh' content='0; url=$url/store/add_auth?'>";

    }



    /**
     *  美业授权返回
     */
    public function auth_back(){

        $config = unserialize($_GET['conf']);
        $queryauthcode = $_GET['auth_code'];
        ReportServicesImpl::auth_back($queryauthcode,$config);
    }


    public function getShopInfo(){

        $wxid =  ReportServicesImpl::get_wxid($_REQUEST['rid']);
        $_SESSION['meiyewxid'] = $wxid;
        $wxService = new WechatServicesImpl();
        $list = $wxService->get_shop('','',1);
        if($list){
            $result['list'] = $list;
            $result['meiyewxid'] = $wxid;
            return ApiSuccessWrapper::success($result);
        }else{
            return ApiSuccessWrapper::success('null');
        }

    }


}
