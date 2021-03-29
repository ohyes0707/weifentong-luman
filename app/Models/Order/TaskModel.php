<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/6/5
 * Time: 18:10
 */
namespace App\Models\Order;
use App\Models\Buss\BussInfoModel;
use App\Models\CommonModel;
use App\Lib\HttpUtils\HttpRequest;
use App\Models\Count\FansLogModel;
use App\Models\Count\TaskSummaryModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class TaskModel extends CommonModel{
    protected $table = 'y_order_task';

    protected $primaryKey = 'id';

    public $timestamps = false;

    /**
     * 添加渠道任务
     * @param $arr
     * @return bool
     */
    public static function addTask($arr){

        $where = array(
            ['order_id','=',$_GET['order_id']],
            ['task_status','<>',3]
        );
        $list = TaskModel::where($where)->get()->toArray();
        
        $pause= TaskModel::zd($arr['db_insert']);
        if($pause==0){
            $upwhere = array(
                ['order_id','=',$_GET['order_id']]
            );
            OrderModel::where($upwhere)->update(['order_status'=>2]);
        } else {
            $upwhere = array(
                ['order_id','=',$_GET['order_id']]
            );
            OrderModel::where($upwhere)->update(['order_status'=>1]);
        }
        if($list){
            HttpRequest::getApiServices('user','getDelTask','POST',$list);
            $rtn = TaskModel::where($where)->update(['task_status'=>3]);
            if($rtn){
                //存入数据库
                $data = TaskModel::insert($arr['db_insert']);
                return $data;
            }else{
                return false;
            }
        }else{
            $data = TaskModel::insert($arr['db_insert']);
            return $data;
        }

        
    }

    public static function setTaskStatus($order_id,$sta){
        $where = array(
            ['order_id','=',$order_id],
            ['task_status','<>','2']
        );
        return TaskModel::where($where)->update(['task_status'=>$sta]);
    }
    
    public static function zd($param){
        $num=0;
        foreach ($param as $value) {
            if($value['task_status']==1){
                $num=$num+1;
            }
        }
        return $num;
    }

    /**
     * 获取优先级列表
     */
    public static function getLevelList($page,$pagesize,$buss_f,$buss_c){
        if($buss_f){
            $buss_carr = TaskModel::leftJoin('buss_info','y_order_task.buss_id','=','buss_info.bid')->select('buss_id','nick_name')->where('parent_id',$buss_f)->where('task_status',1)->orderBy('buss_id','asc')->groupBy('buss_id')->get()->toArray();
            if($buss_c){
                $where[] = array('buss_id','=',$buss_c);
            }else{
                $where[] = array('parent_id','=',$buss_f);
            }
        }else{
            $buss_list = TaskModel::select('parent_id')->where('task_status','=',1)->groupBy('parent_id')->get()->toArray();
            $buss_name = BussInfoModel::select('nick_name','bid')->whereIn('bid',$buss_list)->get()->toArray();
            $rtn['buss'] = $buss_name;
            return $rtn;
        }
        $where[] = array('task_status','=',1);
        if($buss_f){
            if($buss_c){
                $buss_arr = TaskModel::leftJoin('buss_info','y_order_task.buss_id','=','buss_info.bid')->select('buss_id','nick_name')->where('task_status',1)->where('bid','=',$buss_c)->groupBy('bid');
            }else{
                $buss_arr = TaskModel::leftJoin('buss_info','y_order_task.buss_id','=','buss_info.bid')->select('buss_id','nick_name')->where('task_status',1)->where('parent_id','=',$buss_f)->groupBy('bid');
            }
        }
        $task = TaskModel::select('level','buss_id','order_id')->where($where)->orderBy('buss_id','desc')->groupBy('buss_id')->groupBy('order_id')->get()->toArray();
//        $task_arr = TaskModel::select('level','buss_id','order_id')->where($where)->orderBy('buss_id','desc')->groupBy('buss_id')->groupBy('order_id')->get()->toArray();
        $b_count = count($buss_arr->get()->toArray());
        $data = self::getPages($buss_arr,$page,$pagesize,$b_count);
        if($task){
            foreach($task as $k=>$v){
                $order[$v['order_id']] = $v['order_id'];
                $buss[$v['buss_id']] = $v['buss_id'];
            }
            $wx = OrderModel::select('wx_name','order_id')->whereIn('order_id',$order)->where('order_status','=',1)->groupBy('order_id')->get()->toArray();
            $buss = BussInfoModel::select('nick_name','bid')->whereIn('bid',$buss)->get()->toArray();
            foreach($task as $k=>$v){
                $task[$k]['wx_name'] = '';
                $task[$k]['buss_name'] = '';
                foreach($wx as $kk=>$vv){
                    if($v['order_id']==$vv['order_id'])
                        $task[$k]['wx_name'] = $vv['wx_name'];
                }
                foreach($buss as $kk=>$vv){
                    if($v['buss_id']==$vv['bid'])
                        $task[$k]['buss_name'] = $vv['nick_name'];
                }
            }
            foreach($data['data'] as $k=>$v){
                foreach($task as $kk=>$vv){
                    if($v['nick_name'] == $vv['buss_name']){
                        $arr[$v['nick_name']][] = $vv;
                        unset($task[$kk]);
                    }
                }
            }
        }
        $buss_list = TaskModel::select('parent_id')->where('task_status','=',1)->groupBy('parent_id')->get()->toArray();
        $buss_name = BussInfoModel::select('nick_name','bid')->whereIn('bid',$buss_list)->get()->toArray();
        $rtn['data'] = $arr;
        $rtn['buss'] = $buss_name;
        $rtn['count'] = $data['count'];
        $rtn['buss_c'] = $buss_carr;
        return $rtn;
    }
    static public function setLevel($list){
        //修改数据库中优先级（循环修改）
//        foreach($list as $k=>$v){
//            foreach($list[$k] as $kk=>$vv){
//                TaskModel::where('buss_id',$k)->where('order_id',$vv)->update(['level'=>$kk+1]);
//            }
//        }
        //修改数据库中优先级
        $str = '';
        foreach($list as $k=>$v){
            foreach($list[$k] as $kk=>$vv){
                $id = TaskModel::select('id')->where('order_id',$vv)->where('buss_id',$k)->where('task_status',1)->first();
                if($id)
                    $tid = $id->id;
                $arr[] = array(
                    'id'=>$tid,
                    'level'=>$kk+1
                );
            }
        }
        foreach($arr as $k=>$v){
            if($str){
                $str .= ",(".$v['id'].",".$v['level'].")";
            }else{
                $str = "(".$v['id'].",".$v['level'].")";
            }
        }
        DB::insert("insert into y_order_task (id,level) values ".$str." on duplicate key update level=values(level)");
        //修改redis中的优先级
        foreach($list as $k=>$v){
            foreach($list[$k] as $kk=>$vv){
                $redis = Redis::hget($k,$vv);
                $redis_arr = json_decode($redis,true);
                $redis_arr['alreadynum'] = $kk+1;
                Redis::hset($k,$vv,json_encode($redis_arr));
            }
        }
        return true;
    }
//    public static function getLevelList($page,$pagesize,$buss_name,$wx_name){
////        if($buss_f){
////            if($buss_c){
////                $where[] = array('buss_id','=',$buss_c);
////            }else{
////                $where[] = array('parent_id','=',$buss_f);
////            }
////        }
//        if($buss_name){
//            $buss_arr = BussInfoModel::select('bid')->where('nick_name','=',$buss_name)->first();
//            if($buss_arr)
//                $buss_id = $buss_arr->bid;
//        }
//        if($wx_name){
//            $wx = WxModel::select('id')->where('wx_name','=',$wx_name)->first();
//            if($wx)
//                $wx_id = $wx->id;
//        }
//        if(isset($buss_id))
//            $where[] = array('buss_id','=',$buss_id);
//        if(isset($wx_id)){
//            $order_id = OrderModel::select('order_id')->where('o_wx_id','=',$wx_id)->get()->toArray();
//        }
//        $where[] = array('task_status','=',1);
//        if(isset($wx_id)){
//            $task = TaskModel::select('level','buss_id','order_id')->where($where)->whereIn('order_id',$order_id)->orderBy('buss_id','desc')->groupBy('buss_id')->groupBy('order_id');
//            $task_arr = TaskModel::select('level','buss_id','order_id')->where($where)->whereIn('order_id',$order_id)->orderBy('buss_id','desc')->groupBy('buss_id')->groupBy('order_id')->get()->toArray();
//            $t_count = count($task_arr);
//        }else{
//            $task = TaskModel::select('level','buss_id','order_id')->where($where)->orderBy('buss_id','desc')->groupBy('buss_id')->groupBy('order_id');
//            $task_arr = TaskModel::select('level','buss_id','order_id')->where($where)->orderBy('buss_id','desc')->groupBy('buss_id')->groupBy('order_id')->get()->toArray();
//            $t_count = count($task_arr);
//        }
//        $data = self::getPages($task,$page,$pagesize,$t_count);
//        if($data['data']){
//            foreach($data['data'] as $k=>$v){
//                $order[$v['order_id']] = $v['order_id'];
//                $buss[$v['buss_id']] = $v['buss_id'];
//            }
//            $wx = OrderModel::select('wx_name','order_id')->whereIn('order_id',$order)->where('order_status','=',1)->groupBy('order_id')->get()->toArray();
//            $buss = BussInfoModel::select('nick_name','bid')->whereIn('bid',$buss)->get()->toArray();
//            foreach($data['data'] as $k=>$v){
//                $data['data'][$k]['wx_name'] = '';
//                $data['data'][$k]['buss_name'] = '';
//                foreach($wx as $kk=>$vv){
//                    if($v['order_id']==$vv['order_id'])
//                        $data['data'][$k]['wx_name'] = $vv['wx_name'];
//                }
//                foreach($buss as $kk=>$vv){
//                    if($v['buss_id']==$vv['bid'])
//                        $data['data'][$k]['buss_name'] = $vv['nick_name'];
//                }
//            }
//            foreach($data['data'] as $k=>$v){
//                foreach($buss as $kk=>$vv){
//                    if($vv['nick_name']==$v['buss_name'])
//                        $arr[$v['buss_name']][] = $data['data'][$k];
//                }
//            }
//            $data['data'] = $arr;
//        }
//        $condition = array(
//            ['order_status','<>',2],
//            ['order_status','<>',5]
//        );
//        $wx_name = OrderModel::select('wx_name','order_id','o_wx_id')->where($condition)->groupBy('wx_name')->get()->toArray();
//        $buss_list = TaskModel::select('buss_id')->where('task_status','=',1)->get()->toArray();
//        $buss_name = BussInfoModel::select('nick_name','bid')->whereIn('bid',$buss_list)->get()->toArray();
//        $data['wx_name'] = $wx_name;
//        $data['buss_name'] = $buss_name;
//        return $data;
//    }

//    static public function setLevel($buss_id,$order_id,$level){
//        if($buss_id == '' || $order_id == ''){
//            return false;
//        }else{
//            $condition = array(
//                ['buss_id','=',$buss_id],
//                ['order_id','=',$order_id]
//            );
//            $data = TaskModel::where($condition)->update(['level'=>$level]);
//            return $data;
//        }
//    }

    static public function setBussList($order_id){
        $where = array(
            ['task_status','!=','3']
        );
        if($order_id){
            $where['order_id'] = $order_id;
        }

        $data = TaskModel::select('buss_id','task_status','plan_fans','day_fans','parent_id','weight_value','user_type','order_id','one_price','order_day_fans')
            ->where($where)
            ->groupBy('buss_id')
            ->get()->toArray();
        return $data;
    }

    /**
     * 暂停任务
     * @param $order_id
     * @return mixed
     */
    static public function closeTask($order_id){
        $rtn = TaskModel::where('task_status','<>',3)->whereIn('order_id',$order_id)->update(['task_status'=>2]);
        return $rtn;
    }

    /**
     * 订单报表
     */
    static public function orderForm($date,$page,$pagesize){
        $now = date('Ymd',time());
        if($date){
            $where[] = array('date','>=',$date.' 00:00:01');
            $where[] = array('date','<=',$date.' 23:59:59');
            $condition[] = array('date_time','=',$date);
        }
        $order_id = FansLogModel::select('oid')->where($where)->groupBy('oid')->get()->toArray();
        if($order_id){
            $order_page = OrderModel::select('order_id','work_id','wx_name')->whereIn('order_id',$order_id)->orderBy('create_time','desc');
            $order_arr = self::getPages($order_page,$page,$pagesize);
            $order_data = TaskModel::select('order_id','buss_id','hot_area','sex','day_fans')->whereIn('order_id',$order_id)->where('task_status','<',3)->get()->toArray();
            if($date == date('Y-m-d',time())){
                $buss_ids = TaskModel::select('order_id','buss_id')->whereIn('order_id',$order_id)->groupBy('order_id')->groupBy('buss_id')->get()->toArray();
                foreach($buss_ids as $k=>$v){
                    $buss_id[$v['order_id']][] = $v['buss_id'];
                }
                foreach($buss_id as $k=>$v){
                    $fans = 0;
                    foreach($v as $kk=>$vv){
                        $fans += Redis::hget($now,'new-'.$k.'-'.$vv.'-3-1')+Redis::hget($now,'old-'.$k.'-'.$vv.'-3-1');
                    }
                    $day_up_fans[] = array(
                        'order_id'=>$k,
                        'follow'=>$fans
                    );
                }
            }else{
                $day_up_fans = TaskSummaryModel::select('order_id',DB::raw('sum(new_follow_repeat + old_follow_repeat) as follow'))->where($condition)
                            ->whereIn('order_id',$order_id)->groupBy('order_id')->get()->toArray();
            }
            foreach($order_data as $k=>$v){
                if(isset($order_list[$v['order_id']])){
                    $order_list[$v['order_id']]['day_fans'] += $v['day_fans'];
                }else{
                    $order_list[$v['order_id']] = array(
                        'order_id'=>$v['order_id'],
                        'hot_area'=>$v['hot_area'],
                        'sex'=>$v['sex'],
                        'day_fans'=>$v['day_fans']
                    );
                }
            }
            foreach($order_arr['data'] as $k=>$v){
                foreach($order_list as $kk=>$vv){
                    if($v['order_id'] == $vv['order_id']){
                        $order_arr['data'][$k]['hot_area'] = $vv['hot_area'];
                        $order_arr['data'][$k]['sex'] = $vv['sex'];
                        $order_arr['data'][$k]['day_fans'] = $vv['day_fans'];
                    }
                }
            }
            foreach($order_arr['data'] as $k=>$v){
                $order_arr['data'][$k]['follow'] = 0;
                foreach($day_up_fans as $kk=>$vv){
                    if($v['order_id'] == $vv['order_id']){
                        $order_arr['data'][$k]['follow'] = $vv['follow'];
                    }
                }
            }
            $arr['data'] = $order_arr['data'];
            $arr['count'] = $order_arr['count'];
            return $arr;
        }else{
            return false;
        }
    }
    /**
     * 订单查询
     */
    static public function orderSearch($date,$page,$pagesize){
        if($date){
//            $where[] = array('o_start_date','<=',$date.' 00:00:01');
            $where[] = array('o_end_date','>=',$date.' 23:59:59');
            $condition[] = array('date_time','=',$date);
        }
        $order_id = OrderModel::select('order_id')->where($where)->where('order_status','=',1)->get()->toArray();
        if($order_id){
            $order_page = OrderModel::select('order_id','work_id','wx_name')->whereIn('order_id',$order_id);
            $order_arr = self::getPages($order_page,$page,$pagesize);
            $order_data = TaskModel::select('order_id','buss_id','hot_area','sex',DB::raw('sum(day_fans) as day_fans'))->whereIn('order_id',$order_id)->where('task_status','<',3)->groupBy('order_id')->get()->toArray();
            foreach($order_data as $k=>$v){
                if(isset($order_list[$v['order_id']])){
                    $order_list[$v['order_id']]['day_fans'] += $v['day_fans'];
                }else{
                    $order_list[$v['order_id']] = array(
                        'order_id'=>$v['order_id'],
                        'hot_area'=>$v['hot_area'],
                        'sex'=>$v['sex'],
                        'day_fans'=>$v['day_fans']
                    );
                }
            }
            foreach($order_arr['data'] as $k=>$v){
                $order_arr['data'][$k]['hot_area'] = '';
                $order_arr['data'][$k]['sex'] = 0;
                $order_arr['data'][$k]['day_fans'] = 0;
                foreach($order_list as $kk=>$vv){
                    if($v['order_id'] == $vv['order_id']){
                        $order_arr['data'][$k]['hot_area'] = $vv['hot_area'];
                        $order_arr['data'][$k]['sex'] = $vv['sex'];
                        $order_arr['data'][$k]['day_fans'] = $vv['day_fans'];
                    }
                }
            }
            $arr['data'] = $order_arr['data'];
            $arr['count'] = $order_arr['count'];
            return $arr;
        }else{
            return false;
        }
    }
    
    static public function getOrderTaskAreaList($operat_query) {
        $field=array(
            'buss_id','day_fans','buss_area'
        );
        $where[]=['o_start_date','<=',$operat_query['time']];
        $where[]=['o_end_date','>=',$operat_query['time']];
        $where[]=['task_status','=',1];
        if(isset($operat_query['sex'])&&$operat_query['sex']>0){
            $where[]=['sex','=',$operat_query['sex']];
        }
        $model = TaskModel::select($field)
                ->leftJoin('buss_info','y_order_task.buss_id','=','buss_info.bid')
                ->where($where)
                ->get();
        return $model?$model->toArray():null;
    }
    
    static public function getAreaData($array) {
        $newarray = array();
        foreach ($array as $key => $value) {
            $newarray['全国'] = isset($newarray['全国'])?$newarray['全国']:0;
            $newarray['全国'] = $newarray['全国'] + $value['day_fans'];
            if($value['buss_area'] == ''){
                continue;
            }
            $cityarray = explode(',', $value['buss_area']);
            foreach ($cityarray as $key2 => $value2) {
                if(count(explode('/', $value2))>1){
                    $value2 = explode('/', $value2)[1];
                }
                $newarray[$value2] = isset($newarray[$value2])?$newarray[$value2]:0;
                $newarray[$value2] = $newarray[$value2] + $value['day_fans']/count($cityarray);
                continue;
            }
        }
        return $newarray;
    }
    
    static public function getCapacityDescList($bid_province,$bid_city,$areadata,$operat_query) {
        $newarray=array(
            
        );
        foreach ($bid_province as $key => $value) {
            $newarray[$key]=$value;
            $newarray[$key]['db_capacity_num']=isset($areadata[$value['province_name']])?$areadata[$value['province_name']]:0;
            //print_r($newarray);
            foreach ($bid_city as $key2 => $value2) {
                if($value2['province_name'] == $value['province_name']&&$value2['city_name']!=''){
                    $newarray[$key]['list'][$key2] = $value2;
                    $newarray[$key]['list'][$key2]['db_capacity_num'] = isset($areadata[$value2['city_name']])?$areadata[$value2['city_name']]:0;
                    $newarray[$key]['db_capacity_num'] = $newarray[$key]['db_capacity_num']+$newarray[$key]['list'][$key2]['db_capacity_num'];
                }
            }
        
            if($newarray[0]['province_name'] == '全国'&& $value['province_name']!='全国'){
                $newarray[0]['db_capacity_num']= $areadata['全国'];
            }
        }

        return $newarray;
    }

    /**
     * 根据商户id获取订单列表
     * @param $wx_info_id_val
     * @return data
     */
    static public function get_order_task($wx_info_id_val)
    {

        $request = TaskModel::select('y_order_task.id', 'y_order.wx_name', 'y_order.content')
            ->where('y_order_task.task_status', '=', 1)
            ->where('y_order_task.buss_id', '=', $wx_info_id_val)
            ->where('y_order.order_id', '<>', 155)
            ->rightJoin('y_order', 'y_order_task.order_id', '=', 'y_order.order_id')
            ->get();

        return $request ? $request->toArray() : null;
    }
    
    /**
     * 单价
     * @param $order_id
     * @param $bid
     * @return mixed
     */
    static public function getOnePrice($orderid,$bid) {
        $request= TaskModel::where(['order_id'=>$orderid,'buss_id'=>$bid])->get()->first();
        return $request?$request->toArray()['one_price']:null;
    }
}