<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/7/5
 * Time: 15:36
 */
namespace App\Http\Controllers\Operate;
use App\Http\Controllers\Controller;
use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Services\Impl\Level\LevelServicesImpl;

class LevelController extends Controller{
    public function getLevelList(){
        $buss_f = isset($_GET['buss_f'])?$_GET['buss_f']:'';
        $buss_c = isset($_GET['buss_c'])?$_GET['buss_c']:'';
        $page = isset($_GET['page'])?$_GET['page']:1;
        $pagesize = isset($_GET['pagesize'])?$_GET['pagesize']:15;
        $data = LevelServicesImpl::getLevelList($page,$pagesize,$buss_f,$buss_c);
        return ApiSuccessWrapper::success($data);
    }
    public function setLevel(){
        $json = isset($_GET['list'])?$_GET['list']:'';
        $list = json_decode($json,true);
        $data = LevelServicesImpl::setLevel($list);
        return ApiSuccessWrapper::success($data);
    }
//    public function setLevel(){
//        $buss_id = isset($_GET['buss_id'])?$_GET['buss_id']:'';
//        $order_id = isset($_GET['order_id'])?$_GET['order_id']:'';
//        $level = isset($_GET['level'])?$_GET['level']:0;
//        $data = LevelServicesImpl::setLevel($buss_id,$order_id,$level);
//        return ApiSuccessWrapper::success($data);
//    }
}