<?php
/**
 * Created by PhpStorm.
 * User: li wei
 * Date: 2017/5/15
 * Time: 下午8:38
 */
namespace App\Models\User;

use App\Models\CommonModel;
use App\Models\Count\OrderModel;
use App\Models\Count\TaskSummaryModel;
use App\Models\Order\WOrderModel;
use App\Models\Report\WxReportModel;
use App\User;
use Illuminate\Support\Facades\DB;

class UserModel extends CommonModel{

    protected $table = 'user';

    protected $primaryKey = 'id';

    public $timestamps = false;

    /**
     * 获取用户信息
     * @param $userId   用户id
     * @return null
     */
    static public function getUserInfo($userId)
    {
        $model = UserModel::select('username','user_mail','nick_name','password','create_time')
                                ->leftJoin('user_info','user.id','=','user_info.uid')
                                ->where('id', '=', $userId)
                                ->first();
        return $model?$model->toArray():null;
    }

    /**
     * @param $username
     * @return null
     */
    static public function getUserByUsername($username){
        $model = UserModel::where('username', '=', $username)->get()->first();
        return $model?$model->toArray():null;
    }

    /**
     * 代理登陆
     * @param $username
     * @return null
     */
    static public function getUserByAgentUsername($username){
        $model = UserModel::where('username', '=', $username)->where('type', '=', 2)->get()->first();
        return $model?$model->toArray():null;
    }

    /**
     * 获取运营系统销售列表
     */
    static public function getSaleList($page,$pagesize,$sale){
        if($sale){
            $where = array(
                ['nick_name','like','%'.$sale.'%']
            );
        }else{
            $where = array(
                ['uid','>',0]
            );
        }
        $user = UserModel::select('id')->get()->toArray();
        $money = OrderModel::leftJoin('y_task_summary','y_order.order_id','=','y_task_summary.order_id')
                    ->select('o_uid','y_order.order_id','o_per_price',DB::raw('sum(new_follow_repeat) as new_follow'),DB::raw('sum(old_follow_repeat) as old_follow'))
                    ->whereIn('o_uid',$user)->groupBy('y_order.order_id')->get()->toArray();
        $user_count = UserModel::leftJoin('user_info','user.id','=','user_info.uid')
                    ->leftJoin('y_wx_report','user_info.uid','=','y_wx_report.user_id')
                    ->select('user.id','username','nick_name','user.status','ti_money',DB::raw('count(y_wx_report.id) as report'))
                    ->whereIn('user.id',$user)->where($where)->where('user.type','=',1)->groupBy('user.id')->get()->toArray();
        $count = count($user_count);
        $user_arr = UserModel::leftJoin('user_info','user.id','=','user_info.uid')
                    ->leftJoin('y_wx_report','user_info.uid','=','y_wx_report.user_id')
                    ->select('user.id','username','nick_name','user.status','ti_money',DB::raw('count(y_wx_report.id) as report'))
                    ->whereIn('user.id',$user)->where($where)->where('user.type','=',1)->groupBy('user.id');
        $user_list = self::getPages($user_arr,$page,$pagesize,$count);
        $auth = WxReportModel::select('user_id',DB::raw('count(id) as auth'))->where('status','=',3)->orWhere('status','=',4)->whereIn('user_id',$user)->groupBy('user_id')->get()->toArray();
        $report = WxReportModel::select('user_id',DB::raw('count(id) as report'))->where('status','=','4')->whereIn('user_id',$user)->groupBy('user_id')->get()->toArray();
        $work = WOrderModel::select('user_id',DB::raw('count(id) as work'))->whereIn('user_id',$user)->groupBy('user_id')->get()->toArray();
        $order = OrderModel::select('o_uid',DB::raw('count(order_id) as orders'))->whereIn('o_uid',$user)->groupBy('o_uid')->get()->toArray();
        foreach($user_list['data'] as $k=>$v){
            $user_list['data'][$k]['auth'] = 0;
            $user_list['data'][$k]['report_success'] = 0;
            $user_list['data'][$k]['work'] = 0;
            $user_list['data'][$k]['order'] = 0;
            foreach($auth as $kk=>$vv){
                if($v['id'] == $vv['user_id']){
                    $user_list['data'][$k]['auth'] = $vv['auth'];
                }
            }
            foreach($report as $kk=>$vv){
                if($v['id'] == $vv['user_id']){
                    $user_list['data'][$k]['report_success'] = $vv['report'];
                }
            }
            foreach($work as $kk=>$vv){
                if($v['id'] == $vv['user_id']){
                    $user_list['data'][$k]['work'] = $vv['work'];
                }
            }
            foreach($order as $kk=>$vv){
                if($v['id'] == $vv['o_uid']){
                    $user_list['data'][$k]['order'] = $vv['orders'];
                }
            }
        }
        foreach($user_list['data'] as $k=>$v){
            $user_list['data'][$k]['money'] = 0;
            foreach($money as $kk=>$vv){
                if($v['id'] == $vv['o_uid']){
                    $user_list['data'][$k]['money'] += ($vv['new_follow']+$vv['old_follow']+0)*$vv['o_per_price'];
                }
            }
        }
        $arr['data'] = $user_list['data'];
        $arr['count'] = $user_list['count'];
        return $arr;
    }

    /**
     * 销售添加
     */
    static public function saleAdd($tel,$name,$price){
        $data = UserModel::select()->where('username','=',$tel)->first();
        if($data){
            return false;
        }else{
            $add_arr = array(
                'username'=>$tel,
                'password'=>md5('123456'.time()),
                'create_time'=>time(),
                'status'=>1,
                'ti_money'=>$price
            );
            $user = UserModel::insert($add_arr);
            if($user){
                $user_id = UserModel::select('id')->where('username','=',$tel)->first()->toArray();
                $user_info = UserInfoModel::insert([
                    'uid'=>$user_id['id'],
                    'nick_name'=>$name
                ]);
            }
            if($user_info)
                return true;
            return false;
        }
    }

    /**
     * 销售编辑
     */
    static public function saleEdit($uid,$token,$tel,$name,$price,$pwd,$oem){
        if($uid){
            $user = UserModel::leftJoin('user_info','user.id','=','user_info.uid')->select('id','username','nick_name','ti_money','create_time','user.type','oem_ok')->where('id','=',$uid)->first();
            if($token){
                if($user){
                    $insert = false;
                    $update = false;
                    $user_arr = $user->toArray();
                    if($tel != $user_arr['username'])
                        $user_insert['username'] = $tel;
                    if($oem != $user_arr['oem_ok'])
                        $user_insert['oem_ok'] = $oem;
                    if($name != $user_arr['nick_name'])
                        $userinfo_insert['nick_name'] = $name;
                    if($price != $user_arr['ti_money'])
                        $user_insert['ti_money'] = $price;
                    if($pwd)
                        $user_insert['password'] = md5('123456'.$user['create_time']);
                    if(isset($user_insert))
                        $insert = UserModel::where('id','=',$uid)->update($user_insert);
                    if(isset($userinfo_insert))
                        $update = UserInfoModel::where('uid','=',$uid)->update($userinfo_insert);
                    if($insert || $update)
                        return true;
                }
                return false;
            }else{
                if($user)
                    $user_info = $user->toArray();
                return $user_info;
            }
        }else{
            return false;
        }
    }

    /**
     * 销售报表
     */
    static public function saleForm($uid,$start_date,$end_date,$page,$pagesize){
        if($uid){
            $order_id = \App\Models\Order\OrderModel::select('order_id')->where('o_uid','=',$uid)->get()->toArray();
            $order_price = \App\Models\Order\OrderModel::select('o_per_price','order_id')->where('o_uid','=',$uid)->get()->toArray();
            $where = array(
                ['date_time','>=',$start_date],
                ['date_time','<=',$end_date],
            );
            if($order_id){
                $data = TaskSummaryModel::select(DB::raw('sum(new_follow_repeat) as new_follow'),DB::raw('sum(old_follow_repeat) as old_follow'),
                            DB::raw('sum(new_unfollow_repeat) as new_unfollow'),DB::raw('sum(old_unfollow_repeat) as old_unfollow'),'date_time','order_id')
                            ->where($where)
                            ->whereIn('order_id',$order_id)
                            ->groupBy('order_id')
                            ->groupBy('date_time')
                            ->get()->toArray();
                $time_count = TaskSummaryModel::select('date_time')->whereIn('order_id',$order_id)->where($where)->groupBy('date_time')->orderBy('date_time','desc')->get()->toArray();
                $count = count($time_count);
                $time = TaskSummaryModel::select('date_time')->whereIn('order_id',$order_id)->where($where)->groupBy('date_time')->orderBy('date_time','desc');
                $time_page = self::getPages($time,$page,$pagesize,$count);
                foreach($data as $k=>$v){
                    foreach($order_price as $kk=>$vv){
                        if($v['order_id'] == $vv['order_id']){
                            $data[$k]['price'] = $vv['o_per_price'];
                        }
                    }
                }
                $arr['data'] = $data;
                $arr['list'] = $time_page['data'];
                $arr['count'] = $time_page['count'];
                return $arr;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    /**
     * 销售状态
     */
    static public function saleStatus($uid){
        $data = UserModel::select('id','status')->where('id','=',$uid)->first();
        if($data){
            if($data->status == 1){
                $rtn = UserModel::where('id','=',$uid)->update(['status'=>0]);
            }else{
                $rtn = UserModel::where('id','=',$uid)->update(['status'=>1]);
            }
            return $rtn;
        }else{
            return false;
        }
    }
    /**
     * 销售删除
     */
    static public function saleDel($uid){
        $user = UserModel::where('id','=',$uid)->delete();
        $userinfo = UserInfoModel::where('uid','=',$uid)->delete();
        return $user.$userinfo;
    }
    /**
     * 销售多选开启
     */
    static public function startAll($uid){
        if($uid){
            $arr = explode(',',$uid);
            $data = UserModel::whereIn('id',$arr)->where('status','<>','1')->update(['status'=>1]);
            return $data;
        }else{
            return false;
        }
    }
    /**
     * 销售多选禁用
     */
    static public function endAll($uid){
        if($uid){
            $arr = explode(',',$uid);
            $data = UserModel::whereIn('id',$arr)->where('status','<>','0')->update(['status'=>0]);
            return $data;
        }else{
            return false;
        }
    }
    /**
     * 销售多选删除
     */
    static public function delAll($uid){
        if($uid){
            $arr = explode(',',$uid);
            $user = UserModel::whereIn('id',$arr)->delete();
            $userinfo = UserInfoModel::whereIn('uid',$arr)->delete();
            return $user.$userinfo;
        }else{
            return false;
        }
    }
    
    /**
     * 获取用户单价
     * @param $userId   用户id
     * @return null
     */
    static public function getUserTiMoney($userId)
    {
        $model = UserModel::where('id', '=', $userId)->first();
        return $model?$model->toArray()['ti_money']:null;
    }
    /**
     * 获取运营系统代理列表
     */
    static public function getAgentList($page,$pagesize,$sale){
        //代理条件
        $condition = array(['user.type','=',2]);
        if($sale){
            $where = array(
                ['nick_name','like','%'.$sale.'%']
            );
        }else{
            $where = array(
                ['uid','>',0]
            );
        }
        $user = UserModel::select('id')->where($condition)->where('agent_id','=',0)->get()->toArray();
        if($user){
            $money = OrderModel::leftJoin('y_task_summary','y_order.order_id','=','y_task_summary.order_id')
                ->select('o_uid','y_order.order_id','o_per_price',DB::raw('sum(new_follow_repeat) as new_follow'),DB::raw('sum(old_follow_repeat) as old_follow'))
                ->whereIn('o_uid',$user)->groupBy('y_order.order_id')->get()->toArray();
            $user_count = UserModel::leftJoin('user_info','user.id','=','user_info.uid')
                ->leftJoin('y_wx_report','user_info.uid','=','y_wx_report.user_id')
                ->select('user.id','username','nick_name','user.status','ti_money',DB::raw('count(y_wx_report.id) as report'))
                ->whereIn('user.id',$user)->where($where)->where($condition)->groupBy('user.id')->get()->toArray();
            $count = count($user_count);
            $user_arr = UserModel::leftJoin('user_info','user.id','=','user_info.uid')
                ->leftJoin('y_wx_report','user_info.uid','=','y_wx_report.user_id')
                ->select('user.id','username','nick_name','user.status','ti_money',DB::raw('count(y_wx_report.id) as report'))
                ->whereIn('user.id',$user)->where($where)->where($condition)->groupBy('user.id');
            $user_list = self::getPages($user_arr,$page,$pagesize,$count);
            $auth = WxReportModel::select('user_id',DB::raw('count(id) as auth'))->where('status','=',3)->orWhere('status','=',4)->whereIn('user_id',$user)->groupBy('user_id')->get()->toArray();
            $report = WxReportModel::select('user_id',DB::raw('count(id) as report'))->where('status','=','4')->whereIn('user_id',$user)->groupBy('user_id')->get()->toArray();
            $work = WOrderModel::select('user_id',DB::raw('count(id) as work'))->whereIn('user_id',$user)->groupBy('user_id')->get()->toArray();
            $order = OrderModel::select('o_uid',DB::raw('count(order_id) as orders'))->whereIn('o_uid',$user)->groupBy('o_uid')->get()->toArray();
            foreach($user_list['data'] as $k=>$v){
                $user_list['data'][$k]['auth'] = 0;
                $user_list['data'][$k]['report_success'] = 0;
                $user_list['data'][$k]['work'] = 0;
                $user_list['data'][$k]['order'] = 0;
                foreach($auth as $kk=>$vv){
                    if($v['id'] == $vv['user_id']){
                        $user_list['data'][$k]['auth'] = $vv['auth'];
                    }
                }
                foreach($report as $kk=>$vv){
                    if($v['id'] == $vv['user_id']){
                        $user_list['data'][$k]['report_success'] = $vv['report'];
                    }
                }
                foreach($work as $kk=>$vv){
                    if($v['id'] == $vv['user_id']){
                        $user_list['data'][$k]['work'] = $vv['work'];
                    }
                }
                foreach($order as $kk=>$vv){
                    if($v['id'] == $vv['o_uid']){
                        $user_list['data'][$k]['order'] = $vv['orders'];
                    }
                }
            }
            foreach($user_list['data'] as $k=>$v){
                $user_list['data'][$k]['money'] = 0;
                foreach($money as $kk=>$vv){
                    if($v['id'] == $vv['o_uid']){
                        $user_list['data'][$k]['money'] += ($vv['new_follow']+$vv['old_follow']+0)*$vv['o_per_price'];
                    }
                }
            }
            $arr['data'] = $user_list['data'];
            $arr['count'] = $user_list['count'];
            return $arr;
        }else{
            return false;
        }
    }
    /**
     * 运营系统代理列表-子代理
     */
    static public function subAgent($page,$pagesize,$uid){
        if($uid){
            //代理条件
            $condition = array(['user.type','=',2]);
            $where = array(['user.agent_id','=',$uid]);
            $user = UserModel::select('id')->where($condition)->where('agent_id','=',$uid)->get()->toArray();
            if($user){
                $money = OrderModel::leftJoin('y_task_summary','y_order.order_id','=','y_task_summary.order_id')
                    ->select('o_uid','y_order.order_id','o_per_price',DB::raw('sum(new_follow_repeat) as new_follow'),DB::raw('sum(old_follow_repeat) as old_follow'))
                    ->whereIn('o_uid',$user)->groupBy('y_order.order_id')->get()->toArray();
                $user_count = UserModel::leftJoin('user_info','user.id','=','user_info.uid')
                    ->leftJoin('y_wx_report','user_info.uid','=','y_wx_report.user_id')
                    ->select('user.id','username','nick_name','user.status','ti_money',DB::raw('count(y_wx_report.id) as report'))
                    ->whereIn('user.id',$user)->where($where)->where($condition)->groupBy('user.id')->get()->toArray();
                $count = count($user_count);
                $user_arr = UserModel::leftJoin('user_info','user.id','=','user_info.uid')
                    ->leftJoin('y_wx_report','user_info.uid','=','y_wx_report.user_id')
                    ->select('user.id','username','nick_name','user.status','ti_money',DB::raw('count(y_wx_report.id) as report'))
                    ->whereIn('user.id',$user)->where($where)->where($condition)->groupBy('user.id');
                $user_list = self::getPages($user_arr,$page,$pagesize,$count);
                $auth = WxReportModel::select('user_id',DB::raw('count(id) as auth'))->where('status','=',3)->orWhere('status','=',4)->whereIn('user_id',$user)->groupBy('user_id')->get()->toArray();
                $report = WxReportModel::select('user_id',DB::raw('count(id) as report'))->where('status','=','4')->whereIn('user_id',$user)->groupBy('user_id')->get()->toArray();
                $work = WOrderModel::select('user_id',DB::raw('count(id) as work'))->whereIn('user_id',$user)->groupBy('user_id')->get()->toArray();
                $order = OrderModel::select('o_uid',DB::raw('count(order_id) as orders'))->whereIn('o_uid',$user)->groupBy('o_uid')->get()->toArray();
                foreach($user_list['data'] as $k=>$v){
                    $user_list['data'][$k]['auth'] = 0;
                    $user_list['data'][$k]['report_success'] = 0;
                    $user_list['data'][$k]['work'] = 0;
                    $user_list['data'][$k]['order'] = 0;
                    foreach($auth as $kk=>$vv){
                        if($v['id'] == $vv['user_id']){
                            $user_list['data'][$k]['auth'] = $vv['auth'];
                        }
                    }
                    foreach($report as $kk=>$vv){
                        if($v['id'] == $vv['user_id']){
                            $user_list['data'][$k]['report_success'] = $vv['report'];
                        }
                    }
                    foreach($work as $kk=>$vv){
                        if($v['id'] == $vv['user_id']){
                            $user_list['data'][$k]['work'] = $vv['work'];
                        }
                    }
                    foreach($order as $kk=>$vv){
                        if($v['id'] == $vv['o_uid']){
                            $user_list['data'][$k]['order'] = $vv['orders'];
                        }
                    }
                }
                foreach($user_list['data'] as $k=>$v){
                    $user_list['data'][$k]['money'] = 0;
                    foreach($money as $kk=>$vv){
                        if($v['id'] == $vv['o_uid']){
                            $user_list['data'][$k]['money'] += ($vv['new_follow']+$vv['old_follow']+0)*$vv['o_per_price'];
                        }
                    }
                }
                $arr['data'] = $user_list['data'];
                $arr['count'] = $user_list['count'];
                return $arr;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * 代理添加
     */
    static public function add_agent($arr){
        $username = UserModel::select('id')->where('username', '=', $arr['username'])->get()->first();

        if($username){
            if($username->toArray()['id']){
                return null;
            }
        }
        $model = UserModel::insertGetId($arr);
        return $model?$model:null;
        
        
    }
}