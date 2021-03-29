<?php
namespace App\Http\Controllers\Operate;

use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Services\Impl\Admin\AdminServicesImpl;
use App\Services\Impl\User\UserServicesImpl;
use App\Http\Controllers\Controller;
class UserController extends Controller
{
    /**
     * 运营登录接口
     * @method POST
     * @param username
     * @param password
     * @return Blooen
     */
    public function doOperateLogin(){
        $username = $_POST['username'];
        $password = $_POST['password'];
        $res = UserServicesImpl::doOperateLogin($username,$password);
        return ApiSuccessWrapper::success($res);
    }

    public function getAdminInfo(){
        $userId = isset($_GET['userid'])?$_GET['userid']:'';
        $res = AdminServicesImpl::getAdminInfo($userId);
        return ApiSuccessWrapper::success($res);
    }

    /**
     * 管理员列表
     */
    public function managerlist(){

        $res = AdminServicesImpl::managerlist();
        return ApiSuccessWrapper::success($res);
    }
    /**
     * 新增管理员
     */
    public function addUser(){
        $res = AdminServicesImpl::addUser();
        return ApiSuccessWrapper::success($res);
    }
    /**
     * 编辑管理员
     */
    public function editUser(){

        $res = AdminServicesImpl::editUser();
        return ApiSuccessWrapper::success($res);
    }

    /**
     *  增删改查
     */
    public function setmanagerList(){

        $res = AdminServicesImpl::setmanagerList();
        return ApiSuccessWrapper::success($res);

    }

    /**
     *  角色列表
     */
    public function roleList(){

        $res = AdminServicesImpl::roleList();
        return ApiSuccessWrapper::success($res);
    }

    /**
     *  编辑角色
     */
    public  function  editRole(){

        $res = AdminServicesImpl::editRole();
        return ApiSuccessWrapper::success($res);

    }

    public function addRole(){

        $res = AdminServicesImpl::addRole();
        return ApiSuccessWrapper::success($res);
    }

    /**
     * 获取左视图权限
     */
    public function getleftView(){
        $res = AdminServicesImpl::getleftView();
        return ApiSuccessWrapper::success($res);
    }



}
