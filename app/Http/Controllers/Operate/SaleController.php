<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/8/23
 * Time: 9:53
 */
namespace App\Http\Controllers\Operate;
use App\Http\Controllers\Controller;
use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Services\Impl\User\UserServicesImpl;

class SaleController extends Controller{
    public function getSaleList(){
        $page = isset($_GET['page'])?$_GET['page']:1;
        $pagesize = isset($_GET['pagesize'])?$_GET['pagesize']:10;
        $sale = isset($_GET['sale'])?$_GET['sale']:'';
        $data = UserServicesImpl::getSaleList($page,$pagesize,$sale);
        return ApiSuccessWrapper::success($data);
    }

    public function saleAdd(){
        $tel = isset($_GET['mobile'])?$_GET['mobile']:"";
        $name = isset($_GET['name'])?$_GET['name']:'';
        $price = isset($_GET['minprice'])?$_GET['minprice']:'';
        $data = UserServicesImpl::saleAdd($tel,$name,$price);
        return ApiSuccessWrapper::success($data);
    }

    public function saleEdit(){
        $uid = isset($_GET['uid'])?$_GET['uid']:'';
        $token = isset($_GET['_token'])?$_GET['_token']:"";
        $tel = isset($_GET['mobile'])?$_GET['mobile']:'';
        $name = isset($_GET['name'])?$_GET['name']:'';
        $price = isset($_GET['minprice'])?$_GET['minprice']:'';
        $pwd = isset($_GET['password'])?$_GET['password']:'';
        $oem = isset($_GET['oem'])?$_GET['oem']:0;
        $data = UserServicesImpl::saleEdit($uid,$token,$tel,$name,$price,$pwd,$oem);
        return ApiSuccessWrapper::success($data);
    }

    public function saleForm(){
        $uid = isset($_GET['uid'])?$_GET['uid']:'';
        $start_date = isset($_GET['start_date'])?$_GET['start_date']:'';
        $end_date = isset($_GET['end_date'])?$_GET['end_date']:'';
        $page = isset($_GET['page'])?$_GET['page']:1;
        $pagesize = isset($_GET['pagesize'])?$_GET['pagesize']:10;
        $data = UserServicesImpl::saleForm($uid,$start_date,$end_date,$page,$pagesize);
        return ApiSuccessWrapper::success($data);
    }

    public function saleStatus(){
        $uid = isset($_GET['uid'])?$_GET['uid']:'';
        $data = UserServicesImpl::saleStatus($uid);
        return ApiSuccessWrapper::success($data);
    }

    public function saleDel(){
        $uid = isset($_GET['uid'])?$_GET['uid']:'';
        $data = UserServicesImpl::saleDel($uid);
        return ApiSuccessWrapper::success($data);
    }

    public function startAll(){
        $uid = isset($_GET['uid'])?$_GET['uid']:'';
        $data = UserServicesImpl::startAll($uid);
        return ApiSuccessWrapper::success($data);
    }

    public function endAll(){
        $uid = isset($_GET['uid'])?$_GET['uid']:'';
        $data = UserServicesImpl::endAll($uid);
        return ApiSuccessWrapper::success($data);
    }

    public function delAll(){
        $uid = isset($_GET['uid'])?$_GET['uid']:'';
        $data = UserServicesImpl::delAll($uid);
        return ApiSuccessWrapper::success($data);
    }
}