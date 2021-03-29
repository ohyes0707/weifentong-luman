<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/10/23
 * Time: 10:20
 */
namespace App\Http\Controllers\Agent;
use App\Http\Controllers\Controller;
use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Services\Impl\Sell\SellServicesImpl;
use App\Services\Impl\User\UserServicesImpl;

class AgentListController extends Controller{
    /**
     * 运营系统代理列表
     */
    public function getAgentList(){
        $page = isset($_GET['page'])?$_GET['page']:1;
        $pagesize = isset($_GET['pagesize'])?$_GET['pagesize']:10;
        $sale = isset($_GET['sale'])?$_GET['sale']:'';
        $data = UserServicesImpl::getAgentList($page,$pagesize,$sale);
        return ApiSuccessWrapper::success($data);
    }

    /**
     * 运营系统代理列表-子代理
     */
    public function subAgent(){
        $page = isset($_GET['page'])?$_GET['page']:1;
        $pagesize = isset($_GET['pagesize'])?$_GET['pagesize']:10;
        $uid = isset($_GET['uid'])?$_GET['uid']:'';
        $data = UserServicesImpl::subAgent($page,$pagesize,$uid);
        return ApiSuccessWrapper::success($data);
    }
    /**
     * 代理系统销售统计
     */
    public function agentSale(){
        $start_date = isset($_GET['start_date'])?$_GET['start_date']:'';
        $end_date = isset($_GET['end_date'])?$_GET['end_date']:'';
        $wx_name = isset($_GET['wx_name'])?$_GET['wx_name']:'';
        $uid = isset($_GET['uid'])?$_GET['uid']:'';
        $page = isset($_GET['page'])?$_GET['page']:1;
        $pagesize = isset($_GET['pagesize'])?$_GET['pagesize']:10;
        $data = SellServicesImpl::agentSale($start_date,$end_date,$wx_name,$uid,$page,$pagesize);
        return ApiSuccessWrapper::success($data);
    }
}