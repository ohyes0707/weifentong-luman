<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/11/4
 * Time: 15:59
 */
namespace App\Http\Controllers\Store;
use App\Http\Controllers\Controller;
use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Services\Impl\Store\StoreShopServicesImpl;

class StoreShopController extends Controller{
    public function storeShopList(){
        $brand = isset($_GET['brand'])?$_GET['brand']:'';
        $page = isset($_GET['page'])?$_GET['page']:'';
        $pagesize = isset($_GET['pagesize'])?$_GET['pagesize']:'';
        $data = StoreShopServicesImpl::storeShopList($brand,$page,$pagesize);
        return ApiSuccessWrapper::success($data);
    }

    public function getAreaBrand(){
        $data = StoreShopServicesImpl::getAreaBrand();
        return ApiSuccessWrapper::success($data);
    }

    public function storeShopAdd(){
        $mac = isset($_GET['mac'])?$_GET['mac']:'';
        $area = isset($_GET['area'])?$_GET['area']:"";
        $brand = isset($_GET['brand'])?$_GET['brand']:'';
        $shop = isset($_GET['shop'])?$_GET['shop']:'';
        $data = StoreShopServicesImpl::storeShopAdd($mac,$area,$brand,$shop);
        return ApiSuccessWrapper::success($data);
    }
}