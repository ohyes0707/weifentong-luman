<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/10/30
 * Time: 13:36
 */
namespace App\Http\Controllers\Operate;
use App\Http\Controllers\Controller;
use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Services\Impl\Wechat\WechatServicesImpl;

class WechatController extends Controller{
    public function wechatList(){
        $ghid = isset($_GET['ghid'])?$_GET['ghid']:'';
        $page = isset($_GET['page'])?$_GET['page']:1;
        $pagesize = isset($_GET['pagesize'])?$_GET['pagesize']:'';
        $data = WechatServicesImpl::wechatList($ghid,$page,$pagesize);
        return ApiSuccessWrapper::success($data);
    }
}