<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/11/4
 * Time: 14:07
 */
namespace App\Http\Controllers\Store;
use App\Http\Controllers\Controller;
use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Services\Impl\Store\StoreBrandServicesImpl;

class StoreBrandController extends Controller{
    public function storeBrandList(){
        $brand = isset($_GET['brand'])?$_GET['brand']:'';
        $page = isset($_GET['page']) ? $_GET['page'] : '';
        $pagesize = isset($_GET['pagesize']) ? $_GET['pagesize'] : '';
        $data = StoreBrandServicesImpl::storeBrandList($brand,$page,$pagesize);
        return ApiSuccessWrapper::success($data);
    }
}