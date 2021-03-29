<?php
namespace App\Http\Controllers\Business;

use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Services\Impl\User\UserServicesImpl;
use App\Http\Controllers\Controller;
class UserController extends Controller
{
    /**
     * 商家登录接口
     * @method POST
     * @param username
     * @param password
     * @return Blooen
     */
    public function doBussLogin(){
        $username = $_POST['username'];
        $password = $_POST['password'];
        $res = UserServicesImpl::doBussLogin($username,$password);
        return ApiSuccessWrapper::success($res);
    }

    /**
     * 获取用户信息接口
     * @return array
     */
    public function getUserInfo(){
        $userId = isset($_GET['userid'])?$_GET['userid']:'';
        $data = UserServicesImpl::getUserInfo($userId);
        return ApiSuccessWrapper::success($data);
    }
}
