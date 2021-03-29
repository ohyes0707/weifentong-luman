<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/6/30
 * Time: 15:28
 */
namespace App\Http\Controllers\Receive;
use App\Http\Controllers\Controller;
use App\Services\Impl\Receive\ReceiveServicesImpl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class SearchController extends Controller{

    /**
     * 检查是否关注
     * @return array
     */
    public function checkSub(){
        $openid = isset($_GET['openid'])?$_GET['openid']:'';
        $oid = isset($_GET['oid'])?$_GET['oid']:'';
        $bid = isset($_GET['bid'])?$_GET['bid']:'';
        $data = ReceiveServicesImpl::checkSub($openid,$oid,$bid);
        return $data;
    }
    
    public function orderSearch(Request $request) {
        $bid = $request ->input('bid');
        $oid = $request ->input('oid');
        $order = Redis::hgetall($bid); 
        return isset($order[$oid])?$order[$oid]:'';
    }

    /**
     * 云袋通知接口
     */
    public function YUNDAI_notice(){
        $arr = json_decode(file_get_contents("php://input"), TRUE);
        $YD_status = $arr['YD_status'];
        $workinfo = json_decode($arr['workinfo'],true);
        $content = unserialize($arr['content']);
        $order_id = $arr['order_id'];
        $data = ReceiveServicesImpl::YUNDAI_notice($YD_status,$workinfo,$content,$order_id);
        return $data;
    }
    /**
     * 云袋接口（检查是否关注、查询今日剩余可涨粉数）
     */
    public function YUNDAI_Search(){
        $openid = isset($_GET['openid'])?$_GET['openid']:'';
        $oid = isset($_GET['oid'])?$_GET['oid']:'';
        $bid = isset($_GET['bid'])?$_GET['bid']:'';
        $fans = ReceiveServicesImpl::YUNDAI_Search($openid,$oid,$bid);
        if(!$openid || !$oid || !$bid){
            $arr = array(
                'error'=> -1,
                'msg'=>'缺少必要参数',
                'data'=>null,
            );
            $fans = json_encode($arr);
        }
        return $fans;
    }

}