<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/5/22
 * Time: 11:45
 */
namespace App\Http\Controllers\Operate;
use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Http\Controllers\Controller;
use App\Services\Impl\Admin\AdminServicesImpl;
use App\Services\Impl\Business\FansServicesImpl;
use App\Services\Impl\Order\OrderServicesImpl;
use Illuminate\Support\Facades\Redis;

class OrderController extends Controller{
    /**
     * 获取订单列表
     * @method GET
     * @return array
     */
    public function getOrderList(){
        $start_date = isset($_GET['start_date'])?$_GET['start_date']:'';
        $end_date = isset($_GET['end_date'])?$_GET['end_date']:'';
        $wx_name = isset($_GET['wx_name'])?$_GET['wx_name']:'';
        $order_status = isset($_GET['order_status'])?$_GET['order_status']:'';
        $uid = isset($_GET['uid'])?$_GET['uid']:'';
        $page = isset($_GET['page'])?$_GET['page']:1;
        $pagesize = isset($_GET['pagesize'])?$_GET['pagesize']:10;
        $data = OrderServicesImpl::getOrderList($start_date,$end_date,$wx_name,$order_status,$uid,$page,$pagesize);
        $wx_list = OrderServicesImpl::getWxList($uid,$start_date,$end_date,$order_status);
        if($data){
            $data['wx_list'] = $wx_list;
            return ApiSuccessWrapper::success($data);
        }else{
            $arr['wx_list'] = $wx_list;
            return ApiSuccessWrapper::success($arr);
        }

    }

    /**
     * 设置redis
     * @return array
     */
    public function setRedis(){
        $arr = json_decode(file_get_contents("php://input"),TRUE);
        $list = $arr['redis'];
        $data = OrderServicesImpl::setRedis($list);
        return ApiSuccessWrapper::success($data);
    }

    /**
     * 关闭订单任务
     * @return array
     */
    public function closeTask(){
        $appid = $_GET['appid'];
        $data = OrderServicesImpl::closeTask($appid);
        return ApiSuccessWrapper::success($data);
    }

    /**
     * 订单查询
     */
    public function orderSearch(){
        $page = isset($_GET['page'])?$_GET['page']:1;
        $pagesize = isset($_GET['pagesize'])?$_GET['pagesize']:10;
        $date = isset($_GET['date'])?$_GET['date']:date('Y-m-d',time());
        $data = OrderServicesImpl::orderSearch($date,$page,$pagesize);
        return ApiSuccessWrapper::success($data);
    }

    /**
     * 订单粉丝详情
     */
    public function orderFans(){
        $order_id = isset($_GET['order_id'])?$_GET['order_id']:'';
        $start_date = isset($_GET['start_date'])?$_GET['start_date']:'';
        $end_date = isset($_GET['end_date'])?$_GET['end_date']:'';
        $data = FansServicesImpl::orderFans($order_id,$start_date,$end_date);
        return ApiSuccessWrapper::success($data);
    }

    /**
     * 添加订单操作日志
     */
    public function orderLogAdd(){
        $arr = json_decode(file_get_contents("php://input"),TRUE);
        $username = isset($arr['username'])?$arr['username']:'';
        $userid = isset($arr['userid'])?$arr['userid']:'';
        $useragent = isset($arr['useragent'])?$arr['useragent']:'';
        $message = isset($arr['message'])?$arr['message']:'';
        $userip = isset($arr['ip'])?$arr['ip']:'';
        $order_id = isset($arr['order_id'])?$arr['order_id']:'';
        $data = AdminServicesImpl::orderLogAdd($username,$userid,$useragent,$message,$userip,$order_id);
        return ApiSuccessWrapper::success($data);
    }

    /**
     * 订单操作日志列表
     */
    public function orderLogs(){
        $order_id = isset($_GET['order_id'])?$_GET['order_id']:'';
        $uid = isset($_GET['uid'])?$_GET['uid']:'';
        $start_date = isset($_GET['start_date'])?$_GET['start_date']:'';
        $end_date = isset($_GET['end_date'])?$_GET['end_date']:'';
        $page = isset($_GET['page'])?$_GET['page']:1;
        $pagesize = isset($_GET['pagesize'])?$_GET['pagesize']:10;
        $data = AdminServicesImpl::orderLog($order_id,$uid,$start_date,$end_date,$page,$pagesize);
        return ApiSuccessWrapper::success($data);
    }
}