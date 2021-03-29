<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/5/27
 * Time: 13:51
 */
namespace App\Models\Order;
use App\Lib\WeChat\Wechat;
use App\Models\CommonModel;
use App\Models\Count\FansLogModel;
use App\Models\Count\OrderTaskModel;
use App\Models\Count\TaskSummaryModel;
use App\Models\Store\StoreWxModel;
use App\Models\Store\TaskStoreSummaryModel;
use App\Models\User\UserModel;
use App\Models\Wechat\WxInfoModel;
use App\Services\Impl\Receive\ReceiveServicesImpl;
use App\Services\Impl\Wechat\WechatServicesImpl;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;


class OrderModel extends CommonModel{

    protected $table = 'y_order';

    protected $primaryKey = 'id';

    public $timestamps = false;

    /**
     * 订单列表
     * @param $start_date       开始时间
     * @param $end_date         结束时间
     * @param $wx_name          微信name
     * @param $order_status     订单状态
     * @return mixed            arr
     */
    public static function getOrderList($start_date,$end_date,$wx_name,$order_status,$uid,$page,$pagesize){
        $where[] = array('order_type','=',1);
        if($wx_name){
            $wx = WxModel::select('id')->where('wx_name','=',$wx_name)->first();
            if($wx)
                $wx_id = $wx->id;
        }
        if($start_date)
            $where[] = array( 'create_time', '>=',$start_date);
        if($end_date)
            $where[] = array( 'create_time', '<=', $end_date.' 23:59:59');
        if(isset($wx_id))
            $where[] = array( 'o_wx_id','=',$wx_id);
        if($order_status)
            $where[] = array( 'order_status', '=', $order_status);
        if($uid){
            $where[] = array('o_uid','=',$uid);
        }else{
            $where[] = array('o_uid','>',0);
        }
        $model = OrderModel::select('wx_name','create_time','o_total_fans','o_per_price','order_status','order_id','work_id','order_level',DB::raw('CASE WHEN order_status = 5 THEN 5 WHEN order_status = 1 THEN 6
        WHEN order_status = 2 THEN 7 WHEN order_status = 4 THEN 8 WHEN order_status = 3 THEN 9 END AS order_status'))
            ->where($where)
            ->orderBy('order_status','asc')
            ->orderBy('create_time','desc');
        $orderdata=self::getPages($model, $page, $pagesize);
        return $orderdata;
    }

    /**
     * 获取微信列表
     * @param $uid              销售系统id
     * @return mixed
     */
    public static function getWxList($uid){
        if($uid){
            $where[] = array('o_uid','=',$uid);
        }else{
            $where[] = array('o_uid','>',0);
        }
        $model = OrderModel::select('o_wx_id','wx_name')
                                ->where($where)
                                ->groupBy('o_wx_id')
                                ->get();
        return $model;
    }

    public static function getIsOrder($workId) {
        $res = OrderModel::where(array('work_id'=>$workId))->exists();
        return $res;
    }

    public static function getUpOrderStat($id,$stat,$new,$user_id,$arr){
        $isset=OrderModel::where(array('work_id'=>$id))->first();
        $level = OrderModel::where('o_wx_id',$arr['wx_id'])->where('order_status',1)->first();
        if($level){
            $order_level = $level->order_level;
        }else{
            $order_level = 0 ;
        }
        if($new==3){
            //工单通过
            $inert=array(
                'o_wx_id'=>$arr['wx_id'],
                'work_id'=>$arr['id'],
                'o_total_fans'=>$arr['w_total_fans'],
                'o_least_fans'=>$arr['w_least_fans'],
                'o_advis_fans'=>$arr['w_advis_fans'],
                'o_max_fans'=>$arr['w_max_fans'],
                'o_per_price'=>$arr['w_per_price'],
                'o_start_date'=>$arr['w_start_date'],
                'o_end_date'=>$arr['w_end_date'],
                'o_start_time'=>$arr['w_start_time'],
                'o_end_time'=>$arr['w_end_time'],
                'o_uid'=>$arr['user_id'],
                'order_status'=>5,
                'create_time'=>date('Y-m-d H:i:s',time()),
                'o_user_money'=>$arr['w_user_money'],
                'wx_name'=>$arr['wx_name'],
                'order_level'=>$order_level,
                'device_type'=>$arr['device_type'],
            );
            //工单第一次通过
            $stooges = Array(
                //'oid' => "123",
                'ghname' => $arr['wx_name'],
                'ghid' => $arr['ghid'],
                'sname' => $arr['default_shopname'],
                'sid' => $arr['default_shopid'],
                'appid' => $arr['appid'],
                'secretkey' => $arr['secretkey'],
                'area' => "不限",//写死
                'ssid' => "wifi",//写死
                'qrcode_url' => config('config.BUSS_URL')."/storages/Wxqrcode/".$arr['qrcode_url'],
                'head_url' => config('config.BUSS_URL')."/storages/Wx/".$arr['head_img'],
                );
            $new = serialize($stooges);
            //print_r($new);
            $inert['content']=$new;
                
            if($isset==''){
                $rest['orderid']=OrderModel::insertGetId($inert);
                $rest['workid']=$arr['id'];
                return $rest;
            }else{
                //工单第二次通过
                unset($inert['o_wx_id']);
                unset($inert['create_time']);
                unset($inert['o_total_fans']);
                unset($inert['o_per_price']);
                unset($inert['o_user_money']);
                unset($inert['order_level']);
                $inert['order_status']=2;
                OrderModel::where('order_id', $isset->order_id)->update($inert);
                
                $where = array(
                    ['order_id','=',$isset->order_id],
                    ['task_status','<>',3]
                );
                
                $list = TaskModel::where($where)->get()->toArray();
                //print_r($list);
                foreach ($list as $key => $value) {
                    Redis::hdel($value['buss_id'],$value['order_id']);
                }
                $rtn = TaskModel::where($where)->update(['task_status'=>2]);
                $rest['orderid']=$isset->order_id;
                $rest['workid']=$arr['id'];
                return $rest;
            }

        }elseif($isset!=''&&$new==1){
            //通过之后修改,需要暂停全部操作
            $stat['order_status']=2;
            OrderModel::where('id', $isset->order_id)->update($stat);
        }else{
            //驳回无操作
        }
    }

    public static function getOlderPrice($userid) {
        $map['o_uid']=$userid;
        $map[]=['order_status','<',5];
        $count= OrderModel::where($map)->count();
        $sum= OrderModel::where($map)->sum('o_per_price');
        if($count!=0&&$sum!=0){
            return round($sum/$count,2);
        } else {
            return 0;
        }

    }

    /***
     * 获取订单某个字段的集合
     * @param $select
     * @param $where
     * @return null
     */
    static public function getwxid($where,$select){

        $idArray = OrderModel::where($where)->select($select)->get()->toArray();
        return $idArray?$idArray:null;
    }

    /**
     * 获取微信id 和ghid
     * @param $appid  appid
     * @return array
     */
    public static function getInfo($appid){
        $wx_info = WxModel::select('id','ghid')->where('appid','=',$appid)->first();
        if($wx_info){
            $wx_info = $wx_info->toArray();
            return $wx_info;
        }else{
            return $wx_info = array();
        }
    }

    /**
     * 关闭订单
     * @param $wx_id
     * @return bool
     */
    public static function closeOrder($wx_id){
        $where = array(
            ['order_status','<>',3],
            ['order_status','<>',4],
            ['o_wx_id','=',$wx_id]
        );
        $order_id = OrderModel::select('order_id')->where($where)->get()->toArray();
        if($order_id){
            $buss_id = TaskModel::select('buss_id','order_id')->whereIn('order_id',$order_id)->where('task_status','=',1)->get()->toArray();
            $data = ReceiveServicesImpl::getDelTask($buss_id);
            $rtn = OrderModel::where($where)->update(['order_status'=>3]);
            if($rtn && $data){
                return $order_id;
            }else{
                return false;
            }
        }else{
            return false;
        }

    }

    static public function sellCount($start_date,$end_date,$wx_name,$uid,$page,$pagesize){
        if($start_date){
            $where[] = array('date','>=',$start_date.' 00:00:01');
            $condition[] = array('date_time','>=',$start_date);
        }else{
            $where[] = array('date','>=',date('Y-m-d',strtotime('-7days')).' 00:00:01');
            $condition[] = array('date_time','>=',date('Y-m-d',strtotime('-7days')));
        }
        if($end_date) {
            $where[] = array('date', '<=', $end_date.' 23:59:59');
            $condition[] = array('date_time', '<=', $end_date);
        }else{
            $where[] = array('date', '<=', date('Y-m-d',time()).' 23:59:59');
            $condition[] = array('date_time', '<=', date('Y-m-d',time()));
        }
        if($wx_name){
            $parameter[] = array('wx_name','=',$wx_name);
        }else{
            $parameter[] = array('wx_name','<>',' ');
        }
        $oid = TaskSummaryModel::leftJoin('y_order','y_task_summary.order_id','=','y_order.order_id')->select('y_order.order_id')->where('o_uid','=',$uid)->groupBy('order_id')->get()->toArray();
//        $order_id = FansLogModel::select('oid')->where($where)->whereIn('oid',$oid)->groupBy('oid')->get()->toArray();
        $wx_list = OrderModel::select('wx_name')->whereIn('order_id',$oid)->groupBy('wx_name')->get()->toArray();
        $order_count = OrderModel::select('order_id','wx_name',DB::raw('avg(o_per_price) as price'))->whereIn('order_id',$oid)->where($parameter)->groupBy('wx_name')->get()->toArray();
        $count = count($order_count);
//        $order_page = OrderModel::leftJoin('y_task_summary','y_task_summary.order_id','=','y_order.order_id')->select('y_order.order_id','wx_name',DB::raw('avg(o_per_price) as price'))->whereIn('y_order.order_id',$oid)->where($parameter)->where($condition)->groupBy('wx_name');
        $order_page = OrderModel::select('order_id','wx_name',DB::raw('avg(o_per_price) as price'))->whereIn('order_id',$oid)->where($parameter)->groupBy('wx_name');
        $order_arr = self::getPages($order_page,$page,$pagesize,$count);
        if($order_arr['data']){
            $data = TaskSummaryModel::leftJoin('y_order','y_task_summary.order_id','=','y_order.order_id')->select('y_task_summary.order_id','o_per_price','wx_name','date_time',DB::raw('sum(new_follow_repeat+old_follow_repeat) as follow'),
                DB::raw('sum(new_unfollow_repeat+old_unfollow_repeat) as unfollow'))->where($condition)->whereIn('y_task_summary.order_id',$oid)
                ->orderBy('date_time','desc')->groupBy('order_id')->groupBy('date_time')->get()->toArray();
            $avg_price = TaskSummaryModel::leftJoin('y_order','y_task_summary.order_id','=','y_order.order_id')->select('y_task_summary.order_id',DB::raw('avg(o_per_price) as price'),'wx_name','date_time')->where($condition)->whereIn('y_task_summary.order_id',$oid)
                ->groupBy('wx_name')->groupBy('date_time')->get()->toArray();
            $arr['list'] = $order_arr['data'];
            $arr['data'] = $data;
            $arr['wx_list'] = $wx_list;
            $arr['avg_price'] = $avg_price;
            $arr['count'] = $order_arr['count'];
            return $arr;
        }else{
            return false;
        }
    }
    //检查订单是否过期
    static public function orderDecide(){
        $order_list = OrderModel::select('order_id','o_end_date')->where('order_status','<',3)->get()->toArray();
        foreach ($order_list as $k=>$v) {
            if(substr($v['o_end_date'],0,10) < date('Y-m-d',time())){
                $order_id[] = $v['order_id'];
            }
        }
        if(empty($order_id)) die('没有相关订单');
        $task = OrderTaskModel::select('order_id','buss_id')->where('task_status',1)->whereIn('order_id',$order_id)->get()->toArray();
        foreach($order_id as $k=>$v){
            foreach($task as $kk=>$vv){
                if($v == $vv['order_id']){
                    $redis[$v][] = $vv['buss_id'];
                }
            }
        }
        //修改订单状态—关闭状态
        OrderModel::whereIn('order_id',$order_id)->update(['order_status'=>3]);
        //修改任务状态—暂停状态
        OrderTaskModel::where('task_status',1)->whereIn('order_id',$order_id)->update(['task_status'=>2]);
        //删除redis数据
        foreach($redis as $k=>$v){
            foreach($redis[$k] as $kk=>$vv){
                Redis::hdel($vv,$k);
            }
        }
    }
    static public function agentSale($start_date,$end_date,$wx_name,$uid,$page,$pagesize){
        if($start_date){
            $where[] = array('date','>=',$start_date.' 00:00:01');
            $condition[] = array('date_time','>=',$start_date);
        }else{
            $where[] = array('date','>=',date('Y-m-d',strtotime('-7days')).' 00:00:01');
            $condition[] = array('date_time','>=',date('Y-m-d',strtotime('-7days')));
        }
        if($end_date) {
            $where[] = array('date', '<=', $end_date.' 23:59:59');
            $condition[] = array('date_time', '<=', $end_date);
        }else{
            $where[] = array('date', '<=', date('Y-m-d',time()).' 23:59:59');
            $condition[] = array('date_time', '<=', date('Y-m-d',time()));
        }
        if($wx_name){
            $parameter[] = array('wx_name','=',$wx_name);
        }else{
            $parameter[] = array('wx_name','<>',' ');
        }
        if($uid){
            $users = UserModel::select('id')->where('id','=',$uid)->orWhere('agent_id','=',$uid)->groupBy('id')->get()->toArray();
            $oid = TaskSummaryModel::leftJoin('y_order','y_task_summary.order_id','=','y_order.order_id')->select('y_order.order_id')->whereIn('o_uid',$users)->groupBy('order_id')->get()->toArray();
//        $order_id = FansLogModel::select('oid')->where($where)->whereIn('oid',$oid)->groupBy('oid')->get()->toArray();
            $wx_list = OrderModel::select('wx_name')->whereIn('order_id',$oid)->groupBy('wx_name')->get()->toArray();
            $order_count = OrderModel::select('order_id','wx_name',DB::raw('avg(o_per_price) as price'))->whereIn('order_id',$oid)->where($parameter)->groupBy('wx_name')->get()->toArray();
            $count = count($order_count);
//        $order_page = OrderModel::leftJoin('y_task_summary','y_task_summary.order_id','=','y_order.order_id')->select('y_order.order_id','wx_name',DB::raw('avg(o_per_price) as price'))->whereIn('y_order.order_id',$oid)->where($parameter)->where($condition)->groupBy('wx_name');
            $order_page = OrderModel::select('order_id','wx_name',DB::raw('avg(o_per_price) as price'))->whereIn('order_id',$oid)->where($parameter)->groupBy('wx_name');
            $order_arr = self::getPages($order_page,$page,$pagesize,$count);
            if($order_arr['data']){
                $data = TaskSummaryModel::leftJoin('y_order','y_task_summary.order_id','=','y_order.order_id')->select('y_task_summary.order_id','o_per_price','wx_name','date_time',DB::raw('sum(new_follow_repeat+old_follow_repeat) as follow'),
                    DB::raw('sum(new_unfollow_repeat+old_unfollow_repeat) as unfollow'))->where($condition)->whereIn('y_task_summary.order_id',$oid)
                    ->orderBy('date_time','desc')->groupBy('order_id')->groupBy('date_time')->get()->toArray();
                $avg_price = TaskSummaryModel::leftJoin('y_order','y_task_summary.order_id','=','y_order.order_id')->select('y_task_summary.order_id',DB::raw('avg(o_per_price) as price'),'wx_name','date_time')->where($condition)->whereIn('y_task_summary.order_id',$oid)
                    ->groupBy('wx_name')->groupBy('date_time')->get()->toArray();
                $arr['list'] = $order_arr['data'];
                $arr['data'] = $data;
                $arr['wx_list'] = $wx_list;
                $arr['avg_price'] = $avg_price;
                $arr['count'] = $order_arr['count'];
                return $arr;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * 美业订单列表
     */
    public static function storeOrderList($wx_id,$page,$pagesize){
        $where[] = array('order_type','=',2);
        if($wx_id)
            $where[] = array('o_wx_id','=',$wx_id);
        $data = OrderModel::select('order_id','wx_name','store_tags','order_status')->where($where)->orderBy('order_id','desc');
        $list = self::getPages($data,$page,$pagesize);
        if($list['data']){
            $wx_list = OrderModel::select('o_wx_id','wx_name')->where('order_type','=',2)->groupBy('o_wx_id')->get()->toArray();
            foreach ($list['data'] as $k=>$v){
                $order_id[] = $v['order_id'];
            }
            $sub_total = TaskStoreSummaryModel::select('order_id',DB::raw('sum(new_follow_repeat+old_follow_repeat) as total_fans'),DB::raw('sum(new_unfollow_repeat+old_unfollow_repeat) as un_subscribe'))->whereIn('order_id',$order_id)->where('date_time','<>',date('Y-m-d'))->groupBy('order_id')->get()->toArray();
            foreach ($list['data'] as $k=>$v){
                $list['data'][$k]['total_fans_old'] = 0;
                $list['data'][$k]['un_subscribe_old'] = 0;
                if(!empty($sub_total)){
                    foreach ($sub_total as $kk=>$vv){
                        if($v['order_id'] == $vv['order_id'])
                            $list['data'][$k]['total_fans_old'] = $vv['total_fans'];
                            $list['data'][$k]['un_subscribe_old'] = $vv['un_subscribe'];
                    }
                }
            }
            $arr['data'] = $list['data'];
            $arr['count'] = $list['count'];
            $arr['wx_list'] = $wx_list;
            return $arr;
        }
        return false;
    }

    /**
     * 美业新增订单
     */
    public static function storeOrderAdd($wxName,$tags,$select_brand){
        if($wxName){
            $wx_store = StoreWxModel::select('wx_id')->where('wx_name','=',$wxName)->first();
            $wx_id = '';
            if($wx_store)
                $wx_id = $wx_store->wx_id;
            if($wx_id){
                $wx_info = WxInfoModel::select('id','wx_name','ghid','appid','default_shopname','default_shopid','secretkey','qrcode_url','head_img')->where('id','=',$wx_id)->first();

                if($wx_info){
                    $wx_info = $wx_info->toArray();
                    $wx_name = $wx_info['wx_name'];
                    $token= WechatServicesImpl::getToken($wx_info['id']);
                    if($token){
                        $wx = new Wechat();
                        $wx->access_token=$token;
                        $secretkey=$wx->add_device($wx_info['default_shopid'],'wifi',false);
                        if(!$secretkey)   return false;
                    }else{
                        return false;
                    }
                    if( empty($wx_info['default_shopname']) || empty($wx_info['default_shopid'])){
                        return false;
                    }
                    if($select_brand){
                        foreach ($select_brand as $k=>$v){
                            $barr = explode('/',$v);
                            $redis[] = $barr[1];
                        }
                    }
                    $stooges = Array(
                        //'oid' => "123",
                        'ghname' => $wx_info['wx_name'],
                        'ghid' => $wx_info['ghid'],
                        'sname' => $wx_info['default_shopname'],
                        'sid' => $wx_info['default_shopid'],
                        'appid' => $wx_info['appid'],
                        'secretkey' => $secretkey,
                        'area' => "不限",//写死
                        'ssid' => "wifi",//写死
                        'qrcode_url' => config('config.BUSS_URL')."/storages/Wxqrcode/".$wx_info['qrcode_url'],
                        'head_url' => config('config.BUSS_URL')."/storages/Wx/".$wx_info['head_img'],
                    );
                    $new = serialize($stooges);
                    $data = OrderModel::insertGetId(['o_wx_id'=>$wx_id,'wx_name'=>$wx_name,'content'=>$new,'order_type'=>2,'store_tags'=>$tags,'store_tags_id'=>implode(',',$select_brand),'order_status'=>1,'create_time'=>date('Y-m-d H:i:s')]);
                    if($data){
                        foreach ($redis as $k=>$v){
                            $arr = array(
                                "total_fans"=> '99999999',
                                "date_fans"=> '99999999',
                                "start_date"=> "1970-01-01",
                                "end_date"=> "2222-12-30",
                                "start_time"=> "00:01",
                                "end_time"=> "23:59",
                                "check_status"=> '1',
                                "user_type"=> "0",
                                "device_type"=> '0',
                                "is_hot_area"=> '1',
                                "hot_area"=> '',
                                "isprecision"=> '2',
                                "fans_tag"=> '',
                                "ghid"=> $wx_info['ghid'],
                                "isattribute"=> '2',
                                "is_sex"=> '0',
                                "content"=> $new,
                                "price"=> '1',
                                "alreadynum"=> $data,
                                "date_total_fans"=> "99999999"
                            );
                            Redis::hset('brand'.$v,$data,json_encode($arr));
                        }
                        return $data;
                    }
                }
            }
        }
        return false;
    }
    /**
     * 美业订单状态修改
     */
    public static function changeStatus($oid){
        if($oid){
            $data = false;
            $order = OrderModel::select('order_status','store_tags_id','o_wx_id')->where('order_id','=',$oid)->first();
            if($order){
                $status = $order->order_status;
                $store_tags_id = $order->store_tags_id;
                $wx_id = $order->o_wx_id;
                $store_tags = explode(',',$store_tags_id);
                if($status == 1){
                    foreach ($store_tags as $k=>$v){
                        $arr = explode('/',$v);
                        Redis::hdel('brand'.$arr[1],$oid);
                    }
                    $data = OrderModel::where('order_id','=',$oid)->update(['order_status'=>2]);
                }
                if($status == 2){
                    $wx_info = WxInfoModel::select('id','wx_name','ghid','appid','default_shopname','default_shopid','secretkey','qrcode_url','head_img')->where('id','=',$wx_id)->first();
                    if($wx_info){
                        $wx_info = $wx_info->toArray();
                        $token= WechatServicesImpl::getToken($wx_info['id']);
                        if($token){
                            $wx = new Wechat();
                            $wx->access_token=$token;
                            $secretkey=$wx->add_device($wx_info['default_shopid'],'wifi',false);
                            if(!$secretkey)   return false;
                        }else{
                            return false;
                        }
                        if( empty($wx_info['default_shopname']) || empty($wx_info['default_shopid'])){
                            return false;
                        }
                        if($store_tags){
                            foreach ($store_tags as $k=>$v){
                                $barr = explode('/',$v);
                                $redis[] = $barr[1];
                            }
                        }
                        $stooges = Array(
                            //'oid' => "123",
                            'ghname' => $wx_info['wx_name'],
                            'ghid' => $wx_info['ghid'],
                            'sname' => $wx_info['default_shopname'],
                            'sid' => $wx_info['default_shopid'],
                            'appid' => $wx_info['appid'],
                            'secretkey' => $secretkey,
                            'area' => "不限",//写死
                            'ssid' => "wifi",//写死
                            'qrcode_url' => config('config.BUSS_URL')."/storages/Wxqrcode/".$wx_info['qrcode_url'],
                            'head_url' => config('config.BUSS_URL')."/storages/Wx/".$wx_info['head_img'],
                        );
                        $new = serialize($stooges);
                        foreach ($redis as $k=>$v){
                            $arr = array(
                                "total_fans"=> '99999999',
                                "date_fans"=> '99999999',
                                "start_date"=> "1970-01-01",
                                "end_date"=> "2222-12-30",
                                "start_time"=> "00:01",
                                "end_time"=> "23:59",
                                "check_status"=> '1',
                                "user_type"=> "0",
                                "device_type"=> '0',
                                "is_hot_area"=> '1',
                                "hot_area"=> '',
                                "isprecision"=> '2',
                                "fans_tag"=> '',
                                "ghid"=> $wx_info['ghid'],
                                "isattribute"=> '2',
                                "is_sex"=> '0',
                                "content"=> $new,
                                "price"=> '1',
                                "alreadynum"=> $data,
                                "date_total_fans"=> "99999999"
                            );
                            Redis::hset('brand'.$v,$oid,json_encode($arr));
                        }
                    }
                    $data = OrderModel::where('order_id','=',$oid)->update(['order_status'=>1]);
                }
                return $data;
            }
        }
        return false;
    }
}