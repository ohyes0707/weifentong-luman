<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/11/6
 * Time: 16:35
 */
namespace App\Http\Controllers\Store;
use App\Http\Controllers\Controller;
use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Services\Impl\Store\StoreMacServicesImpl;

class StoreMacController extends Controller{
    public function macList(){
        $mac = isset($_GET['mac'])?$_GET['mac']:'';
        $area = isset($_GET['area'])?$_GET['area']:'';
        $brand = isset($_GET['brand'])?$_GET['brand']:'';
        $store = isset($_GET['store'])?$_GET['store']:'';
        $page= isset($_GET['page'])?$_GET['page']:'1';
        $pagesize= isset($_GET['pagesize'])?$_GET['pagesize']:10;
        $data = StoreMacServicesImpl::macList($mac,$area,$brand,$store,$page,$pagesize);
        return ApiSuccessWrapper::success($data);
    }
}