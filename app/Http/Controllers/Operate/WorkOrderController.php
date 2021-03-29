<?php

namespace App\Http\Controllers\Operate;

use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Lib\HttpUtils\SignUtils;
use App\Services\Impl\WorkOrder\WorkOrderServicesImpl;
use App\Http\Controllers\Controller;

class WorkOrderController extends Controller
{


    /**
     * 获取工单列表
     * @method POST
     * @param $startDate
     * @param $endDate
     * @return Blooen
     */
    public function getWOrderList()
    {
        $startDate = isset($_GET['startdate'])&&$_GET['startdate']!='' ? $_GET['startdate'] : 0;
        $endDate = isset($_GET['enddate'])&&$_GET['enddate']!='' ? $_GET['enddate'] : 0;
        $stat = isset($_GET['stat'])&&$_GET['stat']!='' ? $_GET['stat'] : 0;
        $gzh = isset($_GET['gzh'])&&$_GET['gzh']!='' ? $_GET['gzh'] : 0;
        $page = isset($_GET['page'])&&$_GET['page']!='' ? $_GET['page'] : 1;
        $pageSize = isset($_GET['pagesize'])&&$_GET['pagesize']!='' ? $_GET['pagesize'] : 10;
        $userid = isset($_GET['userid'])&&$_GET['userid']!='' ? $_GET['userid'] : 0;
        $fanstate = isset($_GET['fanstate'])&&$_GET['fanstate']!='' ? $_GET['fanstate'] : 0;
        $data = WorkOrderServicesImpl::getWOrderList($startDate,$endDate,$stat,$gzh,$page,$pageSize,$userid,$fanstate);
        return ApiSuccessWrapper::success($data);
    }

    /**
     * 工单信息
     * @return array
     */
    public function getWOrderInfo()
    {
        $workId = isset($_GET['workid']) ? $_GET['workid'] : 0;
        $data = WorkOrderServicesImpl::getWorkOrderInfo($workId);
        return ApiSuccessWrapper::success($data);
    }
    
    /**
     * 修改工单状态
     * @return array
     */
    public function getUpWOrderStat()
    {
        $id = isset($_GET['id']) ? $_GET['id'] : 0;
        $stat = isset($_GET['stat']) ? $_GET['stat'] : 0;
        $new = isset($_GET['new']) ? $_GET['new'] : 0;
        $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : 0;
        $data = WorkOrderServicesImpl::getUpWOrderStat($id,$stat,$new,$user_id);
        return ApiSuccessWrapper::success($data);
    }
    
        /**
     * 获取场景
     * @method GET
     * @return array
     */
    public function getSceneList()
    {
        //$scene_list = M('scene')->field('id,scene_name')->select();
        $data = WorkOrderServicesImpl::getSceneList();
        return ApiSuccessWrapper::success($data);
    }
    
    /**
     * 获取公众号名称
     * @method GET
     * @return array
     */
    public function getWxNumberName()
    {
        $userid = isset($_GET['userid']) ? $_GET['userid'] : 0;
        $data = WorkOrderServicesImpl::getWxNumberName($userid);
        return ApiSuccessWrapper::success($data);
    }
    
    /**
     * 添加工单时获取公众号名称,场景信息
     * @method GET
     * @return array
     */
    public function getWorkOrder()
    {
        $userid = isset($_GET['userid']) ? $_GET['userid'] : 0;
        $data['wxname'] = WorkOrderServicesImpl::getWxNumberName($userid);
        $data['scene'] = WorkOrderServicesImpl::getSceneList();
        $data['olderprice'] = WorkOrderServicesImpl::getOlderPrice($userid);
        $data['referenceprice'] = WorkOrderServicesImpl::getReferencePrice($userid);
        return ApiSuccessWrapper::success($data);
    }
    
    /**
     * 获取城市省份
     * @method GET
     * @return array
     */
    public function getAllCity()
    {
        $data = WorkOrderServicesImpl::getAllCity();
        return ApiSuccessWrapper::success($data);
    }
    
    /**
     * 获取工单日志
     * @method GET
     * @return array
     */
    public function getWOrderTwoInfo()
    {
        $workId = isset($_GET['workid']) ? $_GET['workid'] : 0;
        $data = WorkOrderServicesImpl::getWOrderTwoInfo($workId);
        return ApiSuccessWrapper::success($data);
    }
    
    public function getShopName() {
        $data = WorkOrderServicesImpl::getShopName();
        return ApiSuccessWrapper::success($data);
    }
}

