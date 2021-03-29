<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/11/3
 * Time: 11:02
 */
namespace App\Http\Controllers\Store;
use App\Http\Controllers\Controller;
use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Services\Impl\Store\StoreOrderServicesImpl;

class StoreOrderController extends Controller
{
    /**
     * 美业订单列表
     */
    public function storeOrderList()
    {
        $wx_id = isset($_GET['wx_id']) ? $_GET['wx_id'] : '';
        $page = isset($_GET['page']) ? $_GET['page'] : '';
        $pagesize = isset($_GET['pagesize']) ? $_GET['pagesize'] : '';
        $data = StoreOrderServicesImpl::storeOrderList($wx_id, $page, $pagesize);
        return ApiSuccessWrapper::success($data);
    }

    /**
     * 美业授权微信
     */
    public function storeOrderAddWx()
    {
        $data = StoreOrderServicesImpl::storeOrderAddWx();
        return ApiSuccessWrapper::success($data);
    }

    /**
     * 美业新增订单
     */
    public function storeOrderAdd()
    {
        $arr = json_decode(file_get_contents("php://input"), TRUE);
        $wx_id = isset($arr['gzh']) ? $arr['gzh'] : '';
        $tags = isset($arr['tags']) ? $arr['tags'] : '';
        $select_brand = isset($arr['select_brand'])?json_decode($arr['select_brand'],true):'';
        $data = StoreOrderServicesImpl::storeOrderAdd($wx_id, $tags,$select_brand);
        return ApiSuccessWrapper::success($data);
    }

    /**
     * 美业订单状态修改
     */
    public function changeStatus(){
        $oid = isset($_GET['oid'])?$_GET['oid']:'';
        $data = StoreOrderServicesImpl::changeStatus($oid);
        return ApiSuccessWrapper::success($data);
    }
}