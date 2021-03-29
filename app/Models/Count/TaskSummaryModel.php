<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/7/4
 * Time: 16:29
 */
namespace App\Models\Count;

use App\Lib\WeChat\Third;
use App\Models\Buss\BussInfoModel;
use App\Models\Buss\BussModel;
use App\Models\CommonModel;
use App\Models\Order\OrderModel;
use App\Models\Order\TaskModel;
use App\Models\Order\WxModel;
use App\Models\User\UserInfoModel;
use App\Services\Impl\Wechat\WechatServicesImpl;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class TaskSummaryModel extends CommonModel
{
    protected $table = 'y_task_summary';

    protected $primaryKey = 'id';

    public $timestamps = false;

    //根据微信分组
    static public function getListWxid($where,$page,$pagesize) {
        $retuen['count']=TaskSummaryModel::select()
            ->where($where)
            ->where('wx_id','!=',null)
            ->count(DB::raw('DISTINCT wx_id'));
        //分页判断
        $model= TaskSummaryModel::select('wx_id','wx_name')
                ->leftJoin('wx_info','y_task_summary.wx_id','=','wx_info.id')
                ->where($where)
                ->where('wx_id','!=',null)
                ->orderBy('order_id', 'DESC')
                ->groupBy('wx_id');
        $data= self::getGroupPages($model,$page,$pagesize,$retuen['count']);
        if(count($data['data'])>0){
            $newarray=$data['data'];
            foreach ($newarray as $key => $value) {
                if($value['wx_id']!=''){
                    $wxid[]=$value['wx_id'];
                }
            }

            $retuen['wxid']=$wxid;
            $retuen['data']=$newarray;
            return $retuen;
        }
        return null;
    }
    
    //根据微信分组
    static public function getListWx($where=null) {
        if($where!=null){
            $model= TaskSummaryModel::select('wx_id','wx_name')
                    ->leftJoin('wx_info','y_task_summary.wx_id','=','wx_info.id')
                    ->where('buss_id','=',$where)
                    ->where('wx_id','!=',null)
                    ->groupBy('wx_id')
                    ->get()
                    ->toArray();
        }else{
            $model= TaskSummaryModel::select('wx_id','wx_name')
                    ->leftJoin('wx_info','y_task_summary.wx_id','=','wx_info.id')
                    ->where('wx_id','!=',null)
                    ->groupBy('wx_id')
                    ->get()
                    ->toArray();
        }

        if(count($model)>0){
            return $model;
        }
        return null;
    }
    //根据渠道分组
    static public function getListBussid($where,$page=1,$pagesize=10) {
        $parent_id=array();
        $retuen['count']=TaskSummaryModel::select()
            ->where($where)
            ->where('parent_id','!=',null)
            ->count(DB::raw('DISTINCT parent_id'));
        //分页判断
        $model= TaskSummaryModel::select('y_task_summary.parent_id','buss_info.nick_name as username')
                ->leftJoin('bussiness','bussiness.id','=','y_task_summary.parent_id')
                ->leftJoin('buss_info','bussiness.id','=','buss_info.bid')
                ->where($where)
                ->groupBy('y_task_summary.parent_id');
        $data= self::getGroupPages($model,$page,$pagesize,$retuen['count']);
        if(count($data)>0){
            $newarray=$data['data'];
            foreach ($newarray as $key => $value) {
                if($value['parent_id']!=''){
                    $parent_id[]=$value['parent_id'];
                }
            }
            $retuen['count']=$data['count'];
            $retuen['parent_id']=$parent_id;
            $retuen['data']=$newarray;
            return $retuen;
        }
        return null;
    }
    
    //根据渠道分组
    static public function getListBuss() {
        //分页判断
        $model= TaskSummaryModel::select('y_task_summary.parent_id','buss_info.nick_name as username')
                ->leftJoin('bussiness','bussiness.id','=','y_task_summary.parent_id')
                ->leftJoin('buss_info','bussiness.id','=','buss_info.bid')
                ->groupBy('y_task_summary.parent_id')
                ->get()
                ->toArray();
        return $model;
    }
    
    //根据渠道分组
    static public function getListBussSonid($where,$pagesize=10) {
        $buss_id=array();
        $retuen['count']=TaskSummaryModel::select()
            ->where('buss_id','!=',null)
            ->count(DB::raw('DISTINCT buss_id'));
        //分页判断
        $model= TaskSummaryModel::select('y_task_summary.buss_id','buss_info.nick_name as username')
                ->leftJoin('bussiness','bussiness.id','=','y_task_summary.buss_id')
                ->leftJoin('buss_info','bussiness.id','=','buss_info.bid')
                ->where($where)
                ->groupBy('y_task_summary.buss_id');
        $data= self::getGroupPages($model,1,$pagesize,$retuen['count']);
        if(count($data)>0){
            $newarray=$data['data'];
            foreach ($newarray as $key => $value) {
                if($value['buss_id']!=''){
                    $buss_id[]=$value['buss_id'];
                }
            }
            $retuen['buss_id']=$buss_id;
            $retuen['data']=$newarray;
            return $retuen;
        }
        return null;
    }
    static public function getListData($where,$wxdata=null) {
        //不用分页
        $selectarray=array(
            'wx_id',
            'date_time',
            DB::raw('sum(new_follow_repeat) as new_follow_repeat'),
            DB::raw('sum(old_follow_repeat) as old_follow_repeat'),
            DB::raw('sum(old_unfollow_repeat) as old_unfollow_repeat'),
            DB::raw('sum(new_unfollow_repeat) as new_unfollow_repeat'),
            DB::raw('sum(now_cancel_new) as now_cancel_new'),
            DB::raw('sum(now_cancel_old) as now_cancel_old')
        );
        if($wxdata==null){
            $data= TaskSummaryModel::select($selectarray)
                ->where($where)
                ->groupBy('date_time','wx_id')
                ->orderBy('date_time','desc')
                ->get();
        }else{
            $data= TaskSummaryModel::select($selectarray)
                ->where($where)
                ->whereIn('wx_id',$wxdata)
                ->groupBy('date_time','wx_id')
                ->orderBy('date_time','desc')
                ->get();
        }
        
        return $data?$data->toArray():null;
    }
    
    
    static public function getBussListData($where,$wxdata=null) {
        //不用分页
        $selectarray=array(
            'wx_id',
            'date_time',
            'parent_id',
            'buss_id',
            DB::raw('sum(new_follow_repeat) as new_follow_repeat'),
            DB::raw('sum(old_follow_repeat) as old_follow_repeat'),
            DB::raw('sum(old_unfollow_repeat) as old_unfollow_repeat'),
            DB::raw('sum(new_unfollow_repeat) as new_unfollow_repeat'),
            DB::raw('sum(now_cancel_new) as now_cancel_new'),
            DB::raw('sum(now_cancel_old) as now_cancel_old'),
            DB::raw('sum(now_cancel_new) as now_cancel_new'),
            DB::raw('sum(now_cancel_old) as now_cancel_old'),
        );
        $data= TaskSummaryModel::select($selectarray)
                ->where($where)
                ->whereIn('parent_id',$wxdata)
                ->groupBy('date_time','parent_id')
                ->orderBy('date_time','desc')
                ->get();
        return $data?$data->toArray():null;
    }
    
    static public function getBussOneListData($where,$wxdata=null) {
        //不用分页
        $selectarray=array(
            'wx_id',
            'date_time',
            'parent_id',
            'buss_id',
            DB::raw('sum(new_follow_repeat) as new_follow_repeat'),
            DB::raw('sum(old_follow_repeat) as old_follow_repeat'),
            DB::raw('sum(old_unfollow_repeat) as old_unfollow_repeat'),
            DB::raw('sum(new_unfollow_repeat) as new_unfollow_repeat'),
            DB::raw('sum(now_cancel_new) as now_cancel_new'),
            DB::raw('sum(now_cancel_old) as now_cancel_old'),
            DB::raw('sum(now_cancel_new) as now_cancel_new'),
            DB::raw('sum(now_cancel_old) as now_cancel_old'),
        );
        $data= TaskSummaryModel::select($selectarray)
                ->where($where)
                ->whereIn('buss_id',$wxdata)
                ->groupBy('date_time','buss_id')
                ->orderBy('date_time','desc')
                ->get();
        return $data?$data->toArray():null;
    }
    
    static public function getAddtaskSummary($date){
        $logic=TaskSummaryModel::insert($date);
        return $logic?TRUE:FALSE;
    }

    static public function getSearchtaskSummary($where)
    {
        $logic = TaskSummaryModel::where($where)->get()->first();
        return $logic ? TRUE : FALSE;
    }

    static public function getUptaskSummary($where, $date)
    {
        $logic = TaskSummaryModel::where($where)->update($date);
        return $logic;
    }

    //平台统计
    public static function platCount($start_date,$end_date,$page,$pagesize){
        if($start_date){
            $where[] = array('date_time','>=',$start_date);
        }else{
            $where[] = array('date_time','>=',date('Y-m-d',strtotime('-7days')));
        }
        if($end_date) {
            $where[] = array('date_time', '<=', $end_date);
        }else{
            $where[] = array('date_time', '<=', date('Y-m-d',time()));
        }
        $data = TaskSummaryModel::select(DB::raw('sum(new_getwx_only) as new_getwx_only'),DB::raw('sum(new_getwx_repeat) as new_getwx_repeat'),DB::raw('sum(old_getwx_only) as old_getwx_only'),
                                DB::raw('sum(old_getwx_repeat) as old_getwx_repeat'),DB::raw('sum(new_complet_only) as new_complet_only'),DB::raw('sum(new_complet_repeat) as new_complet_repeat'),
                                DB::raw('sum(old_complet_only) as old_complet_only'),DB::raw('sum(old_complet_repeat) as old_complet_repeat'),DB::raw('sum(new_follow_only) as new_follow_only'),
                                DB::raw('sum(new_follow_repeat) as new_follow_repeat'),DB::raw('sum(old_follow_only) as old_follow_only'),DB::raw('sum(old_follow_repeat) as old_follow_repeat'),
                                DB::raw('sum(new_end_only) as new_end_only'),DB::raw('sum(new_end_repeat) as new_end_repeat'),DB::raw('sum(old_end_only) as old_end_only'),
                                DB::raw('sum(old_end_repeat) as old_end_repeat'),'date_time')
                                ->where($where)
                                ->where('buss_id','<>',0)
                                ->groupBy('date_time')
                                ->orderBy('date_time','desc');
        $count = TaskSummaryModel::select('date_time')
                                ->where($where)
                                ->groupBy('date_time')
                                ->get()
                                ->toArray();
        $num = count($count);
        $count_data=self::getPages($data,$page,$pagesize,$num);
        $sumgetwx = GetwxSummaryModel::select(DB::raw('sum(old_sumgetwx_repeat) as old_sumgetwx_repeat'),DB::raw('sum(old_sumgetwx_only) as old_sumgetwx_only'),DB::raw('sum(new_sumgetwx_repeat) as new_sumgetwx_repeat')
                                          ,DB::raw('sum(new_sumgetwx_only) as new_sumgetwx_only'),'date_time')
                                        ->where($where)
                                        ->where('bid','<>',0)
                                        ->groupBy('date_time')
                                        ->orderBy('date_time','desc')
                                        ->get()
                                        ->toArray();
        foreach($count_data['data'] as $k=>$v){
            if(!empty($sumgetwx)){
                foreach($sumgetwx as $kk=>$vv){
                    if($v['date_time'] == $vv['date_time']){
                        $count_data['data'][$k] += $sumgetwx[$kk];
                        unset($sumgetwx[$kk]);
                    }
                }
            }else{
                $count_data['data'][$k] += array(
                    'old_sumgetwx_repeat' => 0,
                    'old_sumgetwx_only' => 0,
                    'new_sumgetwx_repeat' => 0,
                    'new_sumgetwx_only' => 0
                );
            }
        }
        foreach($count_data['data'] as $k=>$v){
            if(!isset($v['old_sumgetwx_repeat']))
                $count_data['data'][$k]['old_sumgetwx_repeat'] = 0;
            if(!isset($v['old_sumgetwx_only']))
                $count_data['data'][$k]['old_sumgetwx_only'] = 0;
            if(!isset($v['new_sumgetwx_repeat']))
                $count_data['data'][$k]['new_sumgetwx_repeat'] = 0;
            if(!isset($v['new_sumgetwx_only']))
                $count_data['data'][$k]['new_sumgetwx_only'] = 0;
        }
        $arr['count_arr'] = $count_data;
        $arr['count_num'] = $num;
        return $arr;
    }
    //渠道统计
    public static function bussCount($start_date,$end_date,$page,$pagesize,$buss_name){
        if($start_date){
            $where[] = array('date_time','>=',$start_date);
        }else{
            $where[] = array('date_time','>=',date('Y-m-d',strtotime('-1days')));
        }
        if($end_date) {
            $where[] = array('date_time', '<=', $end_date);
        }else{
            $where[] = array('date_time', '<=', date('Y-m-d',strtotime('-1days')));
        }
        if($buss_name){
            $id = BussInfoModel::select('bid')->where('nick_name','=',$buss_name)->first();
            if($id)
                $buss = $id->bid;
        }
        if(isset($buss)){
            $parameter[] = array('id','=',$buss);
        }else{
            $parameter[] = array('id','>',0);
        }
        $buss_id = GetWxSummaryModel::select('bid')->where($where)->where('bid','<>',0)->groupBy('bid')->get()->toArray();
        $pbid_arr = BussModel::select('id','pbid')->whereIn('id',$buss_id)->groupBy('id')->get()->toArray();
        foreach($pbid_arr as $k=>$v){
            if($v['pbid'] == 0){
                $pbid[$v['id']] = $v['id'];
            }else{
                $pbid[$v['pbid']] = $v['pbid'];
            }
        }
        $buss = BussModel::leftJoin('buss_info','buss_info.bid','=','bussiness.id')
                            ->select('id','username','if_child','nick_name','pbid')
                            ->where($parameter)
                            ->whereIn('id',$pbid);
        $buss_arr = BussModel::leftJoin('buss_info','buss_info.bid','=','bussiness.id')
                                ->select('id','username','if_child','nick_name','pbid')
                                ->whereIn('id',$pbid)
                                ->get()
                                ->toArray();
        $buss_data=self::getPages($buss, $page, $pagesize);
        $buss_list = BussModel::leftJoin('buss_info','buss_info.bid','=','bussiness.id')
                                    ->select('id','username','if_child','nick_name','pbid')
                                    ->whereIn('pbid',$pbid)
                                    ->whereIn('id',$buss_id)
                                    ->get()
                                    ->toArray();
        foreach($buss_data['data'] as $k=>$v){
            foreach($buss_list as $key=>$value){
                if($value['pbid']==$v['id'] && $v['if_child']!=0){
                    $buss_data['data'][$k][$v['username']][] = $value;
                }
            }
        }

        $data = TaskSummaryModel::select('buss_id','date_time',DB::raw('sum(new_getwx_only) as new_getwx_only'),DB::raw('sum(new_getwx_repeat) as new_getwx_repeat'),DB::raw('sum(old_getwx_only) as old_getwx_only'),
                                    DB::raw('sum(old_getwx_repeat) as old_getwx_repeat'),DB::raw('sum(new_complet_only) as new_complet_only'),DB::raw('sum(new_complet_repeat) as new_complet_repeat'),
                                    DB::raw('sum(old_complet_only) as old_complet_only'),DB::raw('sum(old_complet_repeat) as old_complet_repeat'),DB::raw('sum(new_follow_only) as new_follow_only'),
                                    DB::raw('sum(new_follow_repeat) as new_follow_repeat'),DB::raw('sum(old_follow_only) as old_follow_only'),DB::raw('sum(old_follow_repeat) as old_follow_repeat'),
                                    DB::raw('sum(new_end_only) as new_end_only'),DB::raw('sum(new_end_repeat) as new_end_repeat'),DB::raw('sum(old_end_only) as old_end_only'),DB::raw('sum(old_end_repeat) as old_end_repeat'))
                                    ->where($where)
                                    ->whereIn('buss_id',$buss_id)
                                    ->groupBy('buss_id')
                                    ->groupBy('date_time')
                                    ->orderBy('date_time','desc')
                                    ->get()
                                    ->toArray();
        $sumgetwx = GetwxSummaryModel::select('bid as buss_id',DB::raw('sum(old_sumgetwx_repeat) as old_sumgetwx_repeat'),DB::raw('sum(old_sumgetwx_only) as old_sumgetwx_only'),DB::raw('sum(new_sumgetwx_repeat) as new_sumgetwx_repeat')
                                        ,DB::raw('sum(new_sumgetwx_only) as new_sumgetwx_only'),'date_time')
                                        ->where($where)
                                        ->whereIn('bid',$buss_id)
                                        ->groupBy('bid')
                                        ->groupBy('date_time')
                                        ->orderBy('date_time','desc')
                                        ->get()
                                        ->toArray();
        foreach($sumgetwx as $k=>$v){
            $sumgetwx[$k]['new_getwx_only'] = 0;
            $sumgetwx[$k]['new_getwx_repeat'] = 0;
            $sumgetwx[$k]['old_getwx_only'] = 0;
            $sumgetwx[$k]['old_getwx_repeat'] = 0;
            $sumgetwx[$k]['new_complet_only'] = 0;
            $sumgetwx[$k]['new_complet_repeat'] = 0;
            $sumgetwx[$k]['old_complet_only'] = 0;
            $sumgetwx[$k]['old_complet_repeat'] = 0;
            $sumgetwx[$k]['new_follow_only'] = 0;
            $sumgetwx[$k]['new_follow_repeat'] = 0;
            $sumgetwx[$k]['old_follow_only'] = 0;
            $sumgetwx[$k]['old_follow_repeat'] = 0;
            $sumgetwx[$k]['new_end_only'] = 0;
            $sumgetwx[$k]['new_end_repeat'] = 0;
            $sumgetwx[$k]['old_end_only'] = 0;
            $sumgetwx[$k]['old_end_repeat'] = 0;
            if(!empty($data)){
                foreach($data as $kk=>$vv){
                    if($v['buss_id'] == $vv['buss_id'] && $v['date_time'] == $vv['date_time']){
                        $sumgetwx[$k]['new_getwx_only'] += $vv['new_getwx_only'];
                        $sumgetwx[$k]['new_getwx_repeat'] += $vv['new_getwx_repeat'];
                        $sumgetwx[$k]['old_getwx_only'] += $vv['old_getwx_only'];
                        $sumgetwx[$k]['old_getwx_repeat'] += $vv['old_getwx_repeat'];
                        $sumgetwx[$k]['new_complet_only'] += $vv['new_complet_only'];
                        $sumgetwx[$k]['new_complet_repeat'] += $vv['new_complet_repeat'];
                        $sumgetwx[$k]['old_complet_only'] += $vv['old_complet_only'];
                        $sumgetwx[$k]['old_complet_repeat'] += $vv['old_complet_repeat'];
                        $sumgetwx[$k]['new_follow_only'] += $vv['new_follow_only'];
                        $sumgetwx[$k]['new_follow_repeat'] += $vv['new_follow_repeat'];
                        $sumgetwx[$k]['old_follow_only'] += $vv['old_follow_only'];
                        $sumgetwx[$k]['old_follow_repeat'] += $vv['old_follow_repeat'];
                        $sumgetwx[$k]['new_end_only'] += $vv['new_end_only'];
                        $sumgetwx[$k]['new_end_repeat'] += $vv['new_end_repeat'];
                        $sumgetwx[$k]['old_end_only'] += $vv['old_end_only'];
                        $sumgetwx[$k]['old_end_repeat'] += $vv['old_end_repeat'];
                    }
                }
            }
        }
        $arr['data'] = $sumgetwx;
        $arr['buss'] = $buss_data['data'];
        $arr['num'] = $buss_data['count'];
        $arr['buss_list'] = $buss_arr;
        return $arr;
    }
    //渠道统计-查看详情
    public static function bussCount_detail($start_date,$end_date,$page,$pagesize,$buss,$child){
        if($start_date){
            $where[] = array('date_time','>=',$start_date);
        }else{
            $where[] = array('date_time','>=',date('Y-m-d',strtotime('-7days')));
        }
        if($end_date) {
            $where[] = array('date_time', '<=', $end_date);
        }else{
            $where[] = array('date_time', '<=', date('Y-m-d',strtotime('-1days')));
        }
        $buss_obj = BussModel::leftJoin('buss_info','buss_info.bid','=','bussiness.id')
                                    ->select('id','username','if_child','nick_name','pbid')
                                    ->where('id',$buss)
                                    ->first();
        if($buss_obj){
            $buss_info = $buss_obj->toArray();
            if($child =='' ){
                $bid_task = BussModel::leftJoin('y_getwx_summary','bussiness.id','=','y_getwx_summary.bid')->select('bussiness.id','pbid','new_sumgetwx_repeat','old_sumgetwx_repeat')->where('bussiness.id','=',$buss)->orWhere('pbid','=',$buss)->groupBy('bussiness.id')->get()->toArray();
                foreach($bid_task as $k=>$v){
                    if($v['new_sumgetwx_repeat']+$v['old_sumgetwx_repeat']+0 > 0){
                        $bid_arr[$v['id']] = $v['id'];
                        if($v['pbid'] == 0){
                            $bid_arr[$v['id']] = $v['id'];
                        }else{
                            $bid_arr[$v['pbid']] = $v['pbid'];
                        }
                        ksort($bid_arr);
                    }
                }
                $buss_data = BussModel::leftJoin('buss_info','buss_info.bid','=','bussiness.id')
                                            ->select('id','username','if_child','nick_name','pbid')
                                            ->whereIn('id',$bid_arr);
                $buss_list=self::getPages($buss_data, $page, $pagesize);
                $data = TaskSummaryModel::select('buss_id','date_time',DB::raw('sum(new_getwx_repeat) as new_getwx_repeat'),DB::raw('sum(old_getwx_repeat) as old_getwx_repeat'),
                                            DB::raw('sum(new_complet_repeat) as new_complet_repeat'),DB::raw('sum(old_complet_repeat) as old_complet_repeat'),
                                            DB::raw('sum(new_follow_repeat) as new_follow_repeat'),DB::raw('sum(old_follow_repeat) as old_follow_repeat'),
                                            DB::raw('sum(new_end_repeat) as new_end_repeat'),DB::raw('sum(old_end_repeat) as old_end_repeat'))
                                            ->where($where)
                                            ->whereIn('buss_id',$bid_arr)
                                            ->groupBy('buss_id')
                                            ->groupBy('date_time')
                                            ->orderBy('date_time','desc')
                                            ->get()
                                            ->toArray();
                $sumgetwx = GetwxSummaryModel::select('bid','date_time',DB::raw('sum(old_sumgetwx_repeat) as old_sumgetwx_repeat'),DB::raw('sum(new_sumgetwx_repeat) as new_sumgetwx_repeat'))
                                                ->where($where)
                                                ->whereIn('bid',$bid_arr)
                                                ->groupBy('bid')
                                                ->groupBy('date_time')
                                                ->orderBy('date_time','desc')
                                                ->get()
                                                ->toArray();
                foreach($sumgetwx as $kk=>$vv){
                    $sumgetwx[$kk] += array(
                        'new_getwx_repeat'=>0,
                        'old_getwx_repeat'=>0,
                        'new_complet_repeat'=>0,
                        'old_complet_repeat'=>0,
                        'new_follow_repeat'=>0,
                        'old_follow_repeat'=>0,
                        'new_end_repeat'=>0,
                        'old_end_repeat'=>0,
                    );
                    if(!empty($data)){
                        foreach($data as $k=>$v){
                            if($v['buss_id'] == $vv['bid'] && $v['date_time'] == $vv['date_time']){
                                $sumgetwx[$kk]['new_getwx_repeat'] += $v['new_getwx_repeat'];
                                $sumgetwx[$kk]['old_getwx_repeat'] += $v['old_getwx_repeat'];
                                $sumgetwx[$kk]['new_complet_repeat'] += $v['new_complet_repeat'];
                                $sumgetwx[$kk]['old_complet_repeat'] += $v['old_complet_repeat'];
                                $sumgetwx[$kk]['new_follow_repeat'] += $v['new_follow_repeat'];
                                $sumgetwx[$kk]['old_follow_repeat'] += $v['old_follow_repeat'];
                                $sumgetwx[$kk]['new_end_repeat'] += $v['new_end_repeat'];
                                $sumgetwx[$kk]['old_end_repeat'] += $v['old_end_repeat'];
                                unset($data[$k]);
                            }
                        }
                    }
                }
                $total = $sumgetwx;
                foreach($buss_list['data'] as $k=>$v){
                    foreach($sumgetwx as $kk=>$vv){
                        if($v['id'] == $vv['bid']){
                            $buss_list['data'][$k]['data'][$vv['date_time']] = $vv;
                        }
                    }
                }
//                foreach($buss_list['data'] as $k=>$v){
//                    foreach($data as $kk=>$vv){
//                        if($v['id'] == $vv['buss_id']){
//                            $buss_list['data'][$k]['data'][$vv['date_time']] = $vv;
//                            $buss_list['data'][$k]['data'][$vv['date_time']]['new_sumgetwx_repeat'] = 0;
//                            $buss_list['data'][$k]['data'][$vv['date_time']]['old_sumgetwx_repeat'] = 0;
//                        }
//                    }
//                    foreach($sumgetwx as $kk=>$vv){
//                        if($v['id'] == $vv['bid'] && isset($buss_list['data'][$k]['data'][$vv['date_time']])){
//                            $buss_list['data'][$k]['data'][$vv['date_time']]['new_sumgetwx_repeat'] = $vv['new_sumgetwx_repeat'];
//                            $buss_list['data'][$k]['data'][$vv['date_time']]['old_sumgetwx_repeat'] = $vv['old_sumgetwx_repeat'];
//                        }
//                    }
//                }
                $buss_list['data']['father'] = $buss_info;
                $buss_list['data']['total'] = $total;
                $buss_list['data']['count'] = $buss_list['count'];
                return $buss_list['data'];
            }else{
                $data = TaskSummaryModel::select('buss_id','date_time',DB::raw('sum(new_getwx_repeat) as new_getwx_repeat'),DB::raw('sum(old_getwx_repeat) as old_getwx_repeat'),
                                            DB::raw('sum(new_complet_repeat) as new_complet_repeat'),DB::raw('sum(old_complet_repeat) as old_complet_repeat'),
                                            DB::raw('sum(new_follow_repeat) as new_follow_repeat'),DB::raw('sum(old_follow_repeat) as old_follow_repeat'),
                                            DB::raw('sum(new_end_repeat) as new_end_repeat'),DB::raw('sum(old_end_repeat) as old_end_repeat'))
                                            ->where($where)
                                            ->where('buss_id',$buss)
                                            ->groupBy('date_time')
                                            ->orderBy('date_time','desc')
                                            ->get()
                                            ->toArray();
                $sumgetwx_page = GetwxSummaryModel::select('bid',DB::raw('sum(old_sumgetwx_repeat) as old_sumgetwx_repeat'),DB::raw('sum(new_sumgetwx_repeat) as new_sumgetwx_repeat'),'date_time')
                                                ->where($where)
                                                ->where('bid',$buss)
                                                ->groupBy('date_time')
                                                ->orderBy('date_time','desc');
                $sumgetwx_count = GetwxSummaryModel::select('bid',DB::raw('sum(old_sumgetwx_repeat) as old_sumgetwx_repeat'),DB::raw('sum(new_sumgetwx_repeat) as new_sumgetwx_repeat'),'date_time')
                                    ->where($where)
                                    ->where('bid',$buss)
                                    ->groupBy('date_time')
                                    ->orderBy('date_time','desc')
                                    ->get()
                                    ->toArray();
                $count = count($sumgetwx_count);
                $sumgetwx=self::getPages($sumgetwx_page, $page, $pagesize,$count);
                foreach($sumgetwx['data'] as $kk=>$vv){
                    $sumgetwx['data'][$kk] += array(
                        'new_getwx_repeat'=>0,
                        'old_getwx_repeat'=>0,
                        'new_complet_repeat'=>0,
                        'old_complet_repeat'=>0,
                        'new_follow_repeat'=>0,
                        'old_follow_repeat'=>0,
                        'new_end_repeat'=>0,
                        'old_end_repeat'=>0,
                    );
                    if(!empty($data)){
                        foreach($data as $k=>$v){
                            if($v['buss_id'] == $vv['bid'] && $v['date_time'] == $vv['date_time']){
                                $sumgetwx['data'][$kk]['new_getwx_repeat'] += $v['new_getwx_repeat'];
                                $sumgetwx['data'][$kk]['old_getwx_repeat'] += $v['old_getwx_repeat'];
                                $sumgetwx['data'][$kk]['new_complet_repeat'] += $v['new_complet_repeat'];
                                $sumgetwx['data'][$kk]['old_complet_repeat'] += $v['old_complet_repeat'];
                                $sumgetwx['data'][$kk]['new_follow_repeat'] += $v['new_follow_repeat'];
                                $sumgetwx['data'][$kk]['old_follow_repeat'] += $v['old_follow_repeat'];
                                $sumgetwx['data'][$kk]['new_end_repeat'] += $v['new_end_repeat'];
                                $sumgetwx['data'][$kk]['old_end_repeat'] += $v['old_end_repeat'];
                                unset($data[$k]);
                            }
                        }
                    }
                }
                $buss_info['data'] = $sumgetwx['data'];
                $buss_info['count'] = $sumgetwx['count'];
                return $buss_info;
            }
        }else{
            return false;
        }
    }
    //营收统计—渠道
    public static function revenueCountBuss($start_date,$end_date,$page,$pagesize,$newpage,$newpagesize){
        if($start_date){
            $where[] = array('date_time','>=',$start_date);
        }else{
            $where[] = array('date_time','>=',date('Y-m-d',strtotime('-7days')));
        }
        if($end_date) {
            $where[] = array('date_time', '<=', $end_date);
        }else{
            $where[] = array('date_time', '<=', date('Y-m-d',time()));
        }
        if(!empty($newpagesize)){
            $date = TaskSummaryModel::select('date_time')->where($where)->groupBy('date_time')->orderBy('date_time','desc');
            $date_arr = TaskSummaryModel::select('date_time')->where($where)->groupBy('date_time')->orderBy('date_time','desc')->get()->toArray();
            $count = count($date_arr);
            $date_data = self::getPages($date,$newpage,$newpagesize,$count);
            $arr['date_data'] = $date_data['data'];
            $arr['plat_num'] = $date_data['count'];
        }
        $buss_id_sum = TaskSummaryModel::select('buss_id',DB::raw('sum(new_follow_repeat) as new_follow'),DB::raw('sum(old_follow_repeat) as old_follow'))->groupBy('buss_id')->get()->toArray();
        $buss_id = TaskSummaryModel::select('buss_id')->where($where)->groupBy('buss_id')->get()->toArray();
        $pbid_sum = TaskSummaryModel::select('parent_id',DB::raw('sum(new_follow_repeat) as new_follow'),DB::raw('sum(old_follow_repeat) as old_follow'))->groupBy('parent_id')->get()->toArray();
        $pbid = TaskSummaryModel::select('parent_id')->where($where)->groupBy('parent_id')->get()->toArray();
        $buss_arr = BussModel::leftJoin('buss_info','buss_info.bid','=','bussiness.id')
                                ->select('id','username','if_child','nick_name','pbid','cost_price','reduce_percent')
                                ->whereIn('id',$pbid)
                                ->get()
                                ->toArray();
        $buss = BussModel::leftJoin('buss_info','buss_info.bid','=','bussiness.id')
                                ->select('id','username','if_child','nick_name','pbid','cost_price','reduce_percent')
                                ->whereIn('id',$pbid);
        $buss_data = self::getPages($buss,$page,$pagesize);
        foreach($buss_data['data'] as $v){
            $str[] = $v['id'];
        }
        $buss_list = BussModel::leftJoin('buss_info','buss_info.bid','=','bussiness.id')
                                    ->select('id','username','if_child','nick_name','pbid','cost_price','reduce_percent')
                                    ->whereIn('pbid',$str)
                                    ->whereIn('id',$buss_id)
                                    ->get()
                                    ->toArray();
        foreach($buss_list as $v){
            $buss_data['data'][] = $v;
        }
        $data = TaskSummaryModel::leftJoin('y_order','y_task_summary.order_id','=','y_order.order_id')
                                    ->select('buss_id','y_task_summary.order_id','o_per_price','date_time','new_follow_only','new_follow_repeat','old_follow_only','old_follow_repeat','new_unfollow_repeat','old_unfollow_repeat')
                                    ->where($where)
                                    ->orderBy('date_time','desc')
                                    ->get()
                                    ->toArray();
        $time = TaskSummaryModel::select('date_time')
                                    ->where($where)
                                    ->groupBy('date_time')
                                    ->orderBy('date_time','desc')
                                    ->get()
                                    ->toArray();
        $arr['buss'] = $buss_data['data'];
        $arr['data'] = $data;
        $arr['time'] = $time;
        $arr['list'] = $buss_arr;
        $arr['num'] = $buss_data['count'];
        return $arr;
    }

    public static function revenueCountBussExcel($start_date,$end_date){
        if($start_date){
            $where[] = array('date_time','>=',$start_date);
        }else{
            $where[] = array('date_time','>=',date('Y-m-d',strtotime('-7days')));
        }
        if($end_date) {
            $where[] = array('date_time', '<=', $end_date);
        }else{
            $where[] = array('date_time', '<=', date('Y-m-d',time()));
        }
        $buss_id = TaskSummaryModel::select('buss_id')->where($where)->groupBy('buss_id')->get()->toArray();
        $pbid = TaskSummaryModel::select('parent_id')->where($where)->groupBy('parent_id')->get()->toArray();
        $buss_arr = BussModel::leftJoin('buss_info','buss_info.bid','=','bussiness.id')
                                ->select('id','username','if_child','nick_name','pbid','cost_price','reduce_percent')
                                ->whereIn('id',$pbid)
                                ->get()
                                ->toArray();
        foreach($buss_arr as $v){
            $str[] = $v['id'];
        }
        $buss_list = BussModel::leftJoin('buss_info','buss_info.bid','=','bussiness.id')
                                    ->select('id','username','if_child','nick_name','pbid','cost_price','reduce_percent')
                                    ->whereIn('pbid',$str)
                                    ->whereIn('id',$buss_id)
                                    ->get()
                                    ->toArray();
        foreach($buss_list as $v){
            $buss_arr[] = $v;
        }
        $data = TaskSummaryModel::leftJoin('y_order','y_task_summary.order_id','=','y_order.order_id')
                                    ->select('buss_id','y_task_summary.order_id','o_per_price','date_time','new_follow_only','new_follow_repeat','old_follow_only','old_follow_repeat','new_unfollow_repeat','old_unfollow_repeat')
                                    ->where($where)
                                    ->orderBy('date_time','desc')
                                    ->get()
                                    ->toArray();
        $time = TaskSummaryModel::select('date_time')
                                    ->where($where)
                                    ->groupBy('date_time')
                                    ->orderBy('date_time','desc')
                                    ->get()
                                    ->toArray();
        $arr['buss'] = $buss_arr;
        $arr['data'] = $data;
        $arr['time'] = $time;
        return $arr;
    }
    //营收统计单个渠道
    public static function revenueCount_oneBuss($start_date,$end_date,$page,$pagesize,$bname){
        if($start_date){
            $where[] = array('date_time','>=',$start_date);
        }else{
            $where[] = array('date_time','>=',date('Y-m-d',strtotime('-7days')));
        }
        if($end_date) {
            $where[] = array('date_time', '<=', $end_date);
        }else{
            $where[] = array('date_time', '<=', date('Y-m-d',time()));
        }
        if($bname){
            $id = BussInfoModel::select('bid')->where('nick_name','=',$bname)->first();
            if($id)
                $buss = $id->bid;
        }
        if(isset($buss)){
            $parameter[] = array('parent_id','=',$buss);
        }else{
            return false;
            $parameter[] = array('parent_id','>',0);
        }
        $buss_id = TaskSummaryModel::select('buss_id')->where($where)->groupBy('buss_id')->get()->toArray();
        $pbid = TaskSummaryModel::select('parent_id')->where($where)->where($parameter)->groupBy('parent_id')->get()->toArray();
        $search_pbid = TaskSummaryModel::select('parent_id')->groupBy('parent_id')->get()->toArray();
        $buss_arr = BussModel::leftJoin('buss_info','buss_info.bid','=','bussiness.id')
                                ->select('id','username','if_child','nick_name','pbid','cost_price','reduce_percent')
                                ->whereIn('id',$search_pbid)
                                ->get()
                                ->toArray();
        $buss = BussModel::leftJoin('buss_info','buss_info.bid','=','bussiness.id')
                                ->select('id','username','if_child','nick_name','pbid','cost_price','reduce_percent')
                                ->whereIn('id',$pbid);
        $buss_data = self::getPages($buss,$page,$pagesize);
        foreach($buss_data['data'] as $v){
            $str[] = $v['id'];
        }
        if(!empty($str)){
            $buss_list = BussModel::leftJoin('buss_info','buss_info.bid','=','bussiness.id')
                ->select('id','username','if_child','nick_name','pbid','cost_price','reduce_percent')
                ->whereIn('pbid',$str)
                ->whereIn('id',$buss_id)
                ->get()
                ->toArray();
            foreach($buss_list as $v){
                $buss_data['data'][] = $v;
            }
            $data = TaskSummaryModel::leftJoin('y_order','y_task_summary.order_id','=','y_order.order_id')
                ->select('buss_id','y_task_summary.order_id','o_per_price','date_time','new_follow_repeat','old_follow_repeat','new_unfollow_repeat','old_unfollow_repeat')
                ->where($where)
                ->orderBy('date_time','desc')
                ->get()
                ->toArray();
            $time = TaskSummaryModel::select('date_time')
                ->groupBy('date_time')
                ->orderBy('date_time','desc')
                ->get()
                ->toArray();
            $arr['buss'] = $buss_data['data'];
            $arr['data'] = $data;
            $arr['time'] = $time;
            $arr['list'] = $buss_arr;
            $arr['num'] = $buss_data['count'];
            return $arr;
        }else{
            $arr['list'] = $buss_arr;
            return $arr;
        }

    }
    public static function revenueCount_oneBussExcel($start_date,$end_date,$buss){
        if($start_date){
            $where[] = array('date_time','>=',$start_date);
        }else{
            $where[] = array('date_time','>=',date('Y-m-d',strtotime('-7days')));
        }
        if($end_date) {
            $where[] = array('date_time', '<=', $end_date);
        }else{
            $where[] = array('date_time', '<=', date('Y-m-d',time()));
        }
        if($buss){
            $parameter[] = array('parent_id','=',$buss);
        } else{
            $parameter[] = array('parent_id','>',0);
        }
        $buss_id = TaskSummaryModel::select('buss_id')->where($where)->groupBy('buss_id')->get()->toArray();
        $pbid = TaskSummaryModel::select('parent_id')->where($where)->where($parameter)->groupBy('parent_id')->get()->toArray();
        $buss_arr = BussModel::leftJoin('buss_info','buss_info.bid','=','bussiness.id')
            ->select('id','username','if_child','nick_name','pbid','cost_price','reduce_percent')
            ->whereIn('id',$pbid)
            ->get()
            ->toArray();
        foreach($buss_arr as $v){
            $str[] = $v['id'];
        }
        $buss_list = BussModel::leftJoin('buss_info','buss_info.bid','=','bussiness.id')
            ->select('id','username','if_child','nick_name','pbid','cost_price','reduce_percent')
            ->whereIn('pbid',$str)
            ->whereIn('id',$buss_id)
            ->get()
            ->toArray();
        foreach($buss_list as $v){
            $buss_arr[] = $v;
        }
        $data = TaskSummaryModel::leftJoin('y_order','y_task_summary.order_id','=','y_order.order_id')
            ->select('buss_id','y_task_summary.order_id','o_per_price','date_time','new_follow_repeat','old_follow_repeat')
            ->where($where)
            ->orderBy('date_time','desc')
            ->get()
            ->toArray();
        $time = TaskSummaryModel::select('date_time')
            ->where($where)
            ->groupBy('date_time')
            ->orderBy('date_time','desc')
            ->get()
            ->toArray();
        $arr['buss'] = $buss_arr;
        $arr['data'] = $data;
        $arr['time'] = $time;
        $arr['list'] = $buss_arr;
        return $arr;
    }
    //营收统计-渠道-查看详情
    public static function revenueDetail_buss($bid,$start_date,$end_date,$page,$pagesize,$wx_name){
        if($bid){
            $where[] = array('buss_id','=',$bid);
            $orwhere[] = array('parent_id','=',$bid);
            $revenue_wechat[] = array('buss_id','=',$bid);
            $pbid_obj = BussModel::select('pbid')->where('id','=',$bid)->first();
            if($pbid_obj)
                $pbid = $pbid_obj->pbid;
        }else{
            $where[] = array('buss_id','>',0);
            $orwhere[] = array('parent_id','>',0);
            $revenue_wechat[] = array('buss_id','>',0);
        }
        if($wx_name){
            $id = WxModel::select('id')->where('wx_name','=',$wx_name)->first();
            if($id)
                $wx_id = $id->id;
        }
        if(isset($wx_id)){
            $where[] = array('o_wx_id','=',$wx_id);
        }
        if($start_date){
            $parameter[] = array('date_time','>=',$start_date);
        }else{
            $parameter[] = array('date_time','>=',date('Y-m-d',strtotime('-7days')));
        }
        if($end_date) {
            $parameter[] = array('date_time', '<=', $end_date);
        }else{
            $parameter[] = array('date_time', '<=', date('Y-m-d',time()));
        }
        if(!empty($bid) && isset($pbid) && $pbid > 0){
            $wid_sum = TaskSummaryModel::select('wx_id','date_time',DB::raw('sum(new_follow_repeat) as new_follow'),DB::raw('sum(old_follow_repeat) as old_follow'))->where($parameter)->where('buss_id',$bid)->groupBy('wx_id')->get()->toArray();
        }elseif(!empty($bid) && isset($pbid) && $pbid == 0){
            $wid_sum = TaskSummaryModel::select('wx_id','date_time',DB::raw('sum(new_follow_repeat) as new_follow'),DB::raw('sum(old_follow_repeat) as old_follow'))->where($parameter)->where('parent_id',$bid)->groupBy('wx_id')->get()->toArray();
        }else{
            $wid_sum = TaskSummaryModel::select('wx_id','date_time',DB::raw('sum(new_follow_repeat) as new_follow'),DB::raw('sum(old_follow_repeat) as old_follow'))->where($parameter)->groupBy('wx_id')->get()->toArray();
        }
        if($wid_sum){
            foreach($wid_sum as $k=>$v){
                if($v['new_follow'] + $v['old_follow'] > 0){
                    $wid[]['wx_id'] = $v['wx_id'];
                }
            }
        }else{
            return false;
        }

//        $wid = TaskSummaryModel::select('wx_id')->where($parameter)->groupBy('wx_id')->get()->toArray();
        if(isset($orwhere)){
            $wx_query = OrderTaskModel::leftJoin('y_order','y_order_task.order_id','=','y_order.order_id')
                                            ->select('buss_id','y_order_task.order_id','o_wx_id','wx_name','task_time','o_per_price')
                                            ->whereIn('o_wx_id',$wid);
            $wx = $wx_query->where(function($wx_query) use ($where,$bid){
                        $wx_query->where($where)->orWhere('parent_id','=',$bid);
            })->groupBy('y_order_task.order_id')->get()->toArray();

            $wx_name_query = OrderModel::leftJoin('y_order_task','y_order_task.order_id','=','y_order.order_id')
                                            ->select('o_wx_id','wx_name','y_order_task.order_id')
                                            ->whereIn('o_wx_id',$wid);
            $wx_name = $wx_name_query->where(function($wx_name_query) use ($where,$orwhere){
                        $wx_name_query->where('wx_name','!=',' ')->where($where)->orWhere($orwhere);
            })->groupBy('o_wx_id');

            $wx_arr_query = OrderModel::leftJoin('y_order_task','y_order_task.order_id','=','y_order.order_id')
                ->select('o_wx_id','wx_name')
                ->whereIn('o_wx_id',$wid);
            $wx_arr = $wx_arr_query->where(function($wx_arr_query) use ($revenue_wechat,$orwhere){
                $wx_arr_query->where($revenue_wechat)->orWhere($orwhere);
            })->groupBy('o_wx_id')->get()->toArray();

            $wx_count_query = OrderTaskModel::leftJoin('y_order','y_order_task.order_id','=','y_order.order_id')
                ->select('o_wx_id','wx_name')
                ->whereIn('o_wx_id',$wid);
            $wx_count = $wx_count_query->where(function($wx_count_query) use ($where,$orwhere){
                $wx_count_query->where($where)->orWhere($orwhere);
            })->groupBy('o_wx_id')->get()->toArray();
        }else{
            $wx_query = OrderTaskModel::leftJoin('y_order','y_order_task.order_id','=','y_order.order_id')
                ->select('buss_id','y_order_task.order_id','o_wx_id','wx_name','task_time','o_per_price')
                ->whereIn('o_wx_id',$wid);
            $wx = $wx_query->where(function($wx_query) use ($where,$bid){
                $wx_query->where($where)->orWhere('parent_id','=',$bid);
            })->groupBy('y_order_task.order_id')->get()->toArray();

            $wx_name_query = OrderModel::leftJoin('y_order_task','y_order_task.order_id','=','y_order.order_id')
                ->select('o_wx_id','wx_name','y_order_task.order_id')
                ->whereIn('o_wx_id',$wid);
            $wx_name = $wx_name_query->where(function($wx_name_query) use ($where){
                $wx_name_query->where('wx_name','!=',' ')->where($where);
            })->groupBy('o_wx_id');

            $wx_arr_query = OrderModel::leftJoin('y_order_task','y_order_task.order_id','=','y_order.order_id')
                ->select('o_wx_id','wx_name')
                ->whereIn('o_wx_id',$wid);
            $wx_arr = $wx_arr_query->where(function($wx_arr_query) use ($revenue_wechat){
                $wx_arr_query->where($revenue_wechat);
            })->groupBy('o_wx_id')->get()->toArray();

            $wx_count_query = OrderTaskModel::leftJoin('y_order','y_order_task.order_id','=','y_order.order_id')
                ->select('o_wx_id','wx_name')
                ->whereIn('o_wx_id',$wid);
            $wx_count = $wx_count_query->where(function($wx_count_query) use ($where){
                $wx_count_query->where($where);
            })->groupBy('o_wx_id')->get()->toArray();
        }
        $num = count($wx_count);
        $wx_data=self::getPages($wx_name,$page,$pagesize,$num);
        if($wx){
            foreach($wx as $v){
                $oid[] = $v['order_id'];
            }
        }else{
            return false;
        }
        if($bid){
            if($pbid == 0){
                $data = TaskSummaryModel::leftJoin('bussiness','bussiness.id','=','y_task_summary.buss_id')
                    ->select('wx_id','order_id','buss_id','cost_price','reduce_percent','date_time',DB::raw('sum(new_follow_repeat) as new_follow_repeat'),DB::raw('sum(old_follow_repeat) as old_follow_repeat'))
                    ->where($parameter)
                    ->where('parent_id','=',$bid)
                    ->whereIn('order_id',$oid)
                    ->whereIn('wx_id',$wid)
                    ->orderBy('date_time','desc')
                    ->groupBy('order_id')
                    ->groupBy('date_time')
                    ->groupBy('buss_id')
                    ->get()
                    ->toArray();
            }else{
                $data = TaskSummaryModel::leftJoin('bussiness','bussiness.id','=','y_task_summary.buss_id')
                    ->select('wx_id','order_id','buss_id','cost_price','reduce_percent','date_time',DB::raw('sum(new_follow_repeat) as new_follow_repeat'),DB::raw('sum(old_follow_repeat) as old_follow_repeat'))
                    ->where($parameter)
                    ->where('buss_id','=',$bid)
                    ->whereIn('order_id',$oid)
                    ->whereIn('wx_id',$wid)
                    ->orderBy('date_time','desc')
                    ->groupBy('order_id')
                    ->groupBy('date_time')
                    ->groupBy('buss_id')
                    ->get()
                    ->toArray();
            }
        }else{
            $data = TaskSummaryModel::leftJoin('bussiness','bussiness.id','=','y_task_summary.buss_id')
                                        ->select('wx_id','order_id','buss_id','cost_price','reduce_percent','date_time',DB::raw('sum(new_follow_repeat) as new_follow_repeat'),DB::raw('sum(old_follow_repeat) as old_follow_repeat'))
                                        ->where($parameter)
                                        ->whereIn('order_id',$oid)
                                        ->whereIn('wx_id',$wid)
                                        ->orderBy('date_time','desc')
                                        ->groupBy('order_id')
                                        ->groupBy('date_time')
                                        ->groupBy('buss_id')
                                        ->get()
                                        ->toArray();
        }
        foreach($data as $k=>$v){
            foreach($wx as $kk=>$vv){
                if($v['order_id'] == $vv['order_id']){
                    $data[$k]['per_price'] = $vv['o_per_price'];
                }
            }
        }
        foreach($wx_data['data'] as $k=>$v){
            foreach($data as $kk=>$vv){
                if($v['o_wx_id'] == $vv['wx_id']){
                    $wx_data['data'][$k]['data'][] = $vv;
                }
            }
        }
        $list['data'] = $wx_data['data'];
        $list['wx_name'] = $wx_arr;
        $list['num'] = $wx_data['count'];
        return $list;
    }
    //营收统计-微信-查看详情
    public static function revenueDetail_wechat($wid,$page,$pagesize,$start_date,$end_date,$bname){
        if($start_date){
            $where[] = array('date_time','>=',$start_date);
        }else{
            $where[] = array('date_time','>=',date('Y-m-d',strtotime('-7days')));
        }
        if($end_date) {
            $where[] = array('date_time', '<=', $end_date);
        }else{
            $where[] = array('date_time', '<=', date('Y-m-d',time()));
        }
        if($wid){
            $where[] = array('wx_id','=',$wid);
        }
        if($bname){
            $bid = BussInfoModel::select('bid')->where('nick_name','=',$bname)->first();
        }
        if(isset($bid)){
            $condition[] = array('id','=',$bid->bid);
            $parameter[] = array('pbid','=',$bid->bid);
        }else{
            $condition[] = array('id','>',0);
            $parameter[] = array('pbid','>=',0);
        }
        $bid_list = TaskSummaryModel::select('buss_id','parent_id','wx_id')->where('wx_id','=',$wid)->where($where)->where('new_follow_repeat','<>',' ')->orWhere('old_follow_repeat','<>',' ')->groupBy('buss_id')->orderBy('buss_id','desc')->get()->toArray();
        $pbid_list = TaskSummaryModel::select('buss_id','parent_id','wx_id')->where('wx_id','=',$wid)->where($where)->where('new_follow_repeat','<>',' ')->orWhere('old_follow_repeat','<>',' ')->groupBy('parent_id')->orderBy('buss_id','desc')->get()->toArray();
        if($bid_list) {
            foreach ($bid_list as $k => $v) {
                if ($v['parent_id'] != $v['buss_id']) {
                    $cid[$v['buss_id']] = array(
                        'bid' => $v['buss_id'],
                    );
                }
            }
            foreach($pbid_list as $k=>$v){
                $pid[] = array(
                    'bid'=>$v['parent_id'],
                );
            }
            $p_data = TaskSummaryModel::leftJoin('y_order','y_task_summary.order_id','=','y_order.order_id')
                                            ->select('wx_id','buss_id','y_task_summary.order_id','o_per_price','date_time',DB::raw('sum(new_follow_repeat) as new_follow_repeat'),DB::raw('sum(old_follow_repeat) as old_follow_repeat'))
                                            ->whereIn('buss_id',$pid)
                                            ->where($where)
                                            ->groupBy('buss_id')
                                            ->groupBy('date_time')
                                            ->orderBy('date_time','desc')
                                            ->get()
                                            ->toArray();
            $c_data = TaskSummaryModel::leftJoin('y_order','y_task_summary.order_id','=','y_order.order_id')
                                            ->select('wx_id','buss_id','y_task_summary.order_id','o_per_price','date_time',DB::raw('sum(new_follow_repeat) as new_follow_repeat'),DB::raw('sum(old_follow_repeat) as old_follow_repeat'))
                                            ->whereIn('buss_id',$cid)
                                            ->where($where)
                                            ->groupBy('buss_id')
                                            ->groupBy('date_time')
                                            ->orderBy('date_time','desc')
                                            ->get()
                                            ->toArray();
            $pid_list = BussModel::leftJoin('buss_info','buss_info.bid','=','bussiness.id')
                                        ->select('id','username','if_child','nick_name','pbid','cost_price','reduce_percent')
                                        ->whereIn('id',$pid)
                                        ->get()
                                        ->toArray();
            $pid_arr = BussModel::leftJoin('buss_info','buss_info.bid','=','bussiness.id')
                                        ->select('id','username','if_child','nick_name','pbid','cost_price','reduce_percent')
                                        ->whereIn('id',$pid)
                                        ->where($condition);
            $buss_data = self::getPages($pid_arr,$page,$pagesize);
            $cid_arr = BussModel::leftJoin('buss_info','buss_info.bid','=','bussiness.id')
                                        ->select('id','username','if_child','nick_name','pbid','cost_price','reduce_percent')
                                        ->whereIn('id',$cid)
                                        ->where($parameter)
                                        ->get()
                                        ->toArray();
            foreach($pid as $k=>$v){
                foreach($pid_list as $kk=>$vv){
                    if($v['bid'] == $vv['id']){
                        $pid[$k]['nick_name'] = $vv['nick_name'];
                    }
                }
            }
            foreach($buss_data['data'] as $k=>$v){
                foreach($p_data as $kk=>$vv){
                    if($v['id'] == $vv['buss_id']){
                        $buss_data['data'][$k]['data'][] = $vv;
                    }
                }
            }
            foreach($cid_arr as $k=>$v){
                foreach($c_data as $kk=>$vv){
                    if($v['id'] == $vv['buss_id']){
                        $cid_arr[$k]['data'][] = $vv;
                    }
                }
            }
            $arr['pid'] = $buss_data['data'];
            $arr['cid'] = $cid_arr;
            $arr['buss'] = $pid;
            $arr['num'] = $buss_data['count'];
            return $arr;
        }else{
            return false;
        }
    }
    //营收统计-微信-查看子渠道
    public static function revenueDetail_wechatOne($wid,$page,$pagesize,$start_date,$end_date,$bid){
        if($start_date){
            $where[] = array('date_time','>=',$start_date);
        }else{
            $where[] = array('date_time','>=',date('Y-m-d',strtotime('-7days')));
        }
        if($end_date) {
            $where[] = array('date_time', '<=', $end_date);
        }else{
            $where[] = array('date_time', '<=', date('Y-m-d',time()));
        }
        if($wid){
            $where[] = array('wx_id','=',$wid);
        }
        if($bid){
            $condition[] = array('id','=',$bid);
            $parameter[] = array('pbid','=',$bid);
        }else{
            $condition[] = array('id','>',0);
            $parameter[] = array('pbid','>=',$bid);
        }
        $bid_list = TaskSummaryModel::select('buss_id','parent_id','wx_id')->where('wx_id','=',$wid)->groupBy('buss_id')->orderBy('buss_id','desc')->get()->toArray();
        $pbid_list = TaskSummaryModel::select('buss_id','parent_id','wx_id')->where('wx_id','=',$wid)->groupBy('parent_id')->orderBy('buss_id','desc')->get()->toArray();
        if($bid_list) {
            foreach ($bid_list as $k => $v) {
                if ($v['parent_id'] != $v['buss_id']) {
                    $cid[$v['buss_id']] = array(
                        'bid' => $v['buss_id'],
                    );
                }
            }
            foreach($pbid_list as $k=>$v){
                $pid[] = array(
                    'bid'=>$v['parent_id'],
                );
            }
            $p_data = TaskSummaryModel::leftJoin('y_order','y_task_summary.order_id','=','y_order.order_id')
                ->select('wx_id','buss_id','y_task_summary.order_id','o_per_price','date_time',DB::raw('sum(new_follow_repeat) as new_follow_repeat'),DB::raw('sum(old_follow_repeat) as old_follow_repeat'))
                ->whereIn('buss_id',$pid)
                ->where($where)
                ->groupBy('buss_id')
                ->groupBy('date_time')
                ->orderBy('date_time','desc')
                ->get()
                ->toArray();
            $c_data = TaskSummaryModel::leftJoin('y_order','y_task_summary.order_id','=','y_order.order_id')
                ->select('wx_id','buss_id','y_task_summary.order_id','o_per_price','date_time',DB::raw('sum(new_follow_repeat) as new_follow_repeat'),DB::raw('sum(old_follow_repeat) as old_follow_repeat'))
                ->whereIn('buss_id',$cid)
                ->where($where)
                ->groupBy('buss_id')
                ->groupBy('date_time')
                ->orderBy('date_time','desc')
                ->get()
                ->toArray();
            $pid_list = BussModel::leftJoin('buss_info','buss_info.bid','=','bussiness.id')
                ->select('id','username','if_child','nick_name','pbid','cost_price','reduce_percent')
                ->whereIn('id',$pid)
                ->get()
                ->toArray();
            $pid_arr = BussModel::leftJoin('buss_info','buss_info.bid','=','bussiness.id')
                ->select('id','username','if_child','nick_name','pbid','cost_price','reduce_percent')
                ->whereIn('id',$pid)
                ->where($condition);
            $buss_data = self::getPages($pid_arr,$page,$pagesize);
            $cid_arr = BussModel::leftJoin('buss_info','buss_info.bid','=','bussiness.id')
                ->select('id','username','if_child','nick_name','pbid','cost_price','reduce_percent')
                ->whereIn('id',$cid)
                ->where($parameter)
                ->get()
                ->toArray();
            foreach($pid as $k=>$v){
                foreach($pid_list as $kk=>$vv){
                    if($v['bid'] == $vv['id']){
                        $pid[$k]['nick_name'] = $vv['nick_name'];
                    }
                }
            }
            foreach($buss_data['data'] as $k=>$v){
                foreach($p_data as $kk=>$vv){
                    if($v['id'] == $vv['buss_id']){
                        $buss_data['data'][$k]['data'][] = $vv;
                    }
                }
            }
            foreach($cid_arr as $k=>$v){
                foreach($c_data as $kk=>$vv){
                    if($v['id'] == $vv['buss_id']){
                        $cid_arr[$k]['data'][] = $vv;
                    }
                }
            }
            $arr['pid'] = $buss_data['data'];
            $arr['cid'] = $cid_arr;
            $arr['buss'] = $pid;
            $arr['num'] = $buss_data['count'];
            return $arr;
        }else{
            return false;
        }
    }
    public static function getCount($arr,$start_date,$end_date){
        $date = date('Y-m-d',time());
        $redis = date('Ymd',time());
        foreach($arr as $k=>$v){
            if(!isset($v['order_id'])||$v['order_id']==''){
                $arr[$k]['total_fans'] ='-';
                $arr[$k]['subscribe_today']='-';
                $arr[$k]['un_subscribe_today']='-';
                $arr[$k]['un_subscribe']='-';
            } else {
                //当日关注
                $arr[$k]['subscribe_today'] = FansLogModel::getTodayData($v['order_id'],1);
                //今日取关数
                $arr[$k]['un_subscribe_today'] = FansLogModel::getTodayData($v['order_id'],2);
                //总涨粉
                $arr[$k]['total_fans'] = TaskSummaryModel::where([
                        ['order_id', '=', $v['order_id']]
                    ])
                        ->sum('new_follow_repeat') + TaskSummaryModel::where([
                        ['order_id', '=', $v['order_id']]
                    ])
                        ->sum('old_follow_repeat') + $arr[$k]['subscribe_today'];


                //总取关数
                $arr[$k]['un_subscribe'] = TaskSummaryModel::where([
                        ['order_id', '=', $v['order_id']]
                    ])
                        ->sum('new_unfollow_repeat') + TaskSummaryModel::where([
                        ['order_id', '=', $v['order_id']]
                    ])
                        ->sum('old_unfollow_repeat') + $arr[$k]['un_subscribe_today'];
            }
        }
        return $arr;
    }

    /**订单数据
     * @param $getdade
     * @return array
     */
    public static function getorderinfo($getdade)
    {

        header("Content-type: text/html; charset=utf-8");

        $page = isset($getdade['page']) ? $getdade['page'] : 1;
        $pagesize = isset($getdade['pagesize']) ? $getdade['pagesize'] : 10;


        $getdade['start_date']  =  $getdade['start_date']==""? date('Y-m-d',strtotime('-7days')):$getdade['start_date'];
        $getdade['end_date']  =  $getdade['end_date']==""? date('Y-m-d',time()):$getdade['end_date'];


        $mindate = date('Y-m-d H:i:s', strtotime($getdade['start_date']));
        $maxdate = date('Y-m-d H:i:s', strtotime('+1 day',strtotime($getdade['end_date'])));


        if ($getdade['wxname'] != "") {

            $data = array(
                ['y_order.create_time', '>=', $mindate],
                ['y_order.create_time', '<=', $maxdate],
                ['y_order.wx_name', '=', $getdade['wxname']]
            );
        } else {
            $data = array(
                ['y_order.create_time', '>=', $mindate],
                ['y_order.create_time', '<=', $maxdate],
            );
        }



        $nameData = array(

        );

        $all_fans=DB::raw('(sum(y_task_summary.new_follow_repeat) + sum(y_task_summary.old_follow_repeat)) as all_fans');
        $un_subscribe = DB::raw('(sum(y_task_summary.new_unfollow_repeat) + sum(y_task_summary.old_unfollow_repeat)) as un_subscribe');

        $orderArray = DB::table('y_order')->leftJoin('y_task_summary', 'y_task_summary.order_id', '=', 'y_order.order_id')->select($all_fans, $un_subscribe, 'y_order.wx_name', 'y_order.create_time as o_start_date', 'y_order.o_total_fans', 'y_order.order_id')->where($data)->groupBy('y_order.order_id')->orderBy('y_order.create_time','desc');


        $wxnameArray = DB::table('y_order')->where($nameData)->select('wx_name')->orderBy('o_start_date','desc')->get()->toArray();

        $wxnameArray = self::object_array($wxnameArray);
        $wxnameArray =self::array_unique_fb($wxnameArray);


        $arr = array(['全部']);
        $wxnameArray = array_merge($arr,$wxnameArray);

        $count = count($orderArray->get()->toArray());

        $model=self::getPages($orderArray,$page,$pagesize,$count);

        $model['wxname'] = $wxnameArray;

        return $model;



    }


    static public function array_unique_fb($array2D){
        foreach ($array2D as $v){
            $v=join(',',$v);//降维,也可以用implode,将一维数组转换为用逗号连接的字符串
            $temp[]=$v;
        }
        $temp=array_unique($temp);//去掉重复的字符串,也就是重复的一维数组
        foreach ($temp as $k => $v){
            $temp[$k]=explode(',',$v);//再将拆开的数组重新组装
        }
        return $temp;
    }



    /**   报表平台数据    http://operatetest.youfentong.com/index.php/operate/analyzeplatform?user=0&startDate=2017-07-17&endDate=&excel=0
     * @param $getdade   http://operatetest.youfentong.com/index.php/operate/analyzeplatform?userSelect=0&start_date=2017-07-17&end_date=&pagesize=10&action=platform&excel=0&page=2
     * @return mixed
     */
    public static function getplatformdata($getdade)
    {


        $page = isset($getdade['page']) ? $getdade['page'] : 1;
        $pagesize = isset($getdade['pagesize']) ? $getdade['pagesize'] : 10;

        $getdade['start_date']  =  $getdade['start_date']==""? date('Y-m-d',strtotime('-7days')):$getdade['start_date'];
        $getdade['end_date']  =  $getdade['end_date']==""? date('Y-m-d',time()):$getdade['end_date'];


        $data = array(
                ['y_task_summary.date_time', '>=', $getdade['start_date']],
                ['y_task_summary.date_time', '<=', $getdade['end_date']]
        );

         if ($getdade['userselect'] == 0) {
            $all_fans=DB::raw('(sum(y_task_summary.new_follow_repeat) + sum(y_task_summary.old_follow_repeat)) as all_fans');
            $un_subscribe = DB::raw('(sum(y_task_summary.new_unfollow_repeat) + sum(y_task_summary.old_unfollow_repeat)) as un_subscribe');
        } else if ($getdade['userselect'] == 1) {
            $all_fans=DB::raw('sum(y_task_summary.new_follow_repeat) as all_fans');
            $un_subscribe = DB::raw('sum(y_task_summary.new_unfollow_repeat) as un_subscribe');
        } else if ($getdade['userselect'] == 2) {
            $all_fans=DB::raw('sum(y_task_summary.old_follow_repeat) as all_fans');
            $un_subscribe = DB::raw('sum(y_task_summary.old_unfollow_repeat) as un_subscribe');
        }

        $platformdata = DB::table('y_task_summary')->select('buss_id',DB::raw('group_concat(order_id) as order_id'),$all_fans, $un_subscribe,'y_task_summary.date_time')->where($data)->groupBy('y_task_summary.date_time')->orderBy('y_task_summary.date_time','desc');

        $count = count($platformdata->get()->toArray());

        $model=self::getPages($platformdata,$page,$pagesize,$count);

        $model = self::object_array($model);

        foreach ($model['data'] as $key=>$value){

            $oidarr = explode(',',$value['order_id']);

            $tmp = 0;
            foreach ($oidarr as $kk=>$vv){

                $o_total_fans = DB::table('y_order')->select('o_total_fans')->where('order_id', '=', $vv)->first();
                if ($o_total_fans == null) {
                    $o_total_fans = 0;
                } else {
                    $o_total_fans = $o_total_fans->o_total_fans;
                }
                $tmp += $o_total_fans;

            }

            $model['data'][$key]['total_fans'] = $tmp;
            unset($model['data'][$key]['order_id']);
            unset($model['data'][$key]['buss_id']);

        }

        $alldata = DB::table('y_task_summary')->select($all_fans, $un_subscribe)->where($data)->get()->toArray();
        $alldata = self::object_array($alldata);
        $alldata[0]['date_time'] = '总计';
        array_splice($model['data'],0,0,$alldata);
        return $model;
    }

    /**
     * @param $getdade
     * @return 所有渠道信息
     */
    public static function getchanneldata($getdade)
    {

        if($getdade['excel']==1)
        {
            $page = isset($getdade['page']) ? $getdade['page'] : 1;
            $pagesize = isset($getdade['pagesize']) ? $getdade['pagesize'] : 9999;
        }else{
            $page = isset($getdade['page']) ? $getdade['page'] : 1;
            $pagesize = isset($getdade['pagesize']) ? $getdade['pagesize'] : 10;
        }

        $namedata=array();

        $allbussname = TaskSummaryModel::where($namedata)->select('parent_id')->get()->toArray();

        $allbussnameid = self::remove_duplicate($allbussname);

        foreach ($allbussnameid as $nk =>$nv )
        {

            $bussname = DB::table('buss_info')->select('nick_name')->where('bid','=',$nv)->first()->nick_name;
            $allbussnameid[$nk]['bname'] = $bussname;
        }

        $arr =  array(['parent_id'=>0,'bname'=>'全部']);

        $allbussname = array_merge($arr,$allbussnameid);

        $getdade['start_date']  =  $getdade['start_date']==""? date('Y-m-d',strtotime('-7days')):$getdade['start_date'];
        $getdade['end_date']  =  $getdade['end_date']==""? date('Y-m-d',time()):$getdade['end_date'];


        $data = array(
                ['y_task_summary.date_time', '>=', $getdade['start_date']],
                ['y_task_summary.date_time', '<=', $getdade['end_date']],
        );

        $all_fans=DB::raw('(sum(y_task_summary.new_follow_repeat) + sum(y_task_summary.old_follow_repeat)) as all_fans');
        $un_subscribe = DB::raw('(sum(y_task_summary.new_unfollow_repeat) + sum(y_task_summary.old_unfollow_repeat)) as un_subscribe');

        $slldataModel = TaskSummaryModel::where($data)->select('parent_id', DB::raw('group_concat(order_id) as order_id'), $all_fans, $un_subscribe)->groupBy('parent_id');  //缺少,总数据,以及百分比
        $count = count($slldataModel->get()->toArray());
        $slldata = self::getPages($slldataModel, $page, $pagesize, $count);


        foreach ($slldata['data'] as $key=>$value){
            $oidarr = explode(',', $value['order_id']);

            $oidArray = TaskSummaryModel::where('parent_id','=',$value['parent_id'])->where($data)->whereIn('order_id',$oidarr)->select('parent_id as  buss_id',DB::raw('group_concat(order_id) as order_id'),'date_time',$all_fans, $un_subscribe)->groupBy('date_time')->orderBy('date_time','desc')->get()->toArray();

            $bussname = DB::table('buss_info')->select('nick_name')->where('bid', '=', $value['parent_id'])->first();
            if ($bussname == null) {
                $bussname = 0;
            } else {
                $bussname = $bussname->nick_name;
            }
            $slldata['data'][$key]['bussname'] = $bussname;
            unset($slldata['data']['order_id']);
            $slldata['data'][$key]['order_id'] =  join(',',$oidarr);
            $slldata['data'][$key]['percentage'] = self::percentage($value['un_subscribe'], $value['all_fans']);
            $slldata['data'][$key]['timedata'] = $oidArray;

        }

        foreach ($slldata['data'] as $kn => $vn)
        {

            foreach ($vn['timedata'] as $k1 => $v1)
            {
                $oidarr = explode(',', $v1['order_id']);
                $tmp = 0;
                foreach($oidarr as $k2=>$v2){
                    $o_total_fans = DB::table('y_order')->select('o_total_fans')->where('order_id', '=', $v2)->first();
                    if ($o_total_fans == null) {
                        $o_total_fans = 0;
                    } else {
                        $o_total_fans = $o_total_fans->o_total_fans;
                    }
                    $tmp += $o_total_fans;
                }
                $slldata['data'][$kn]['timedata'][$k1]['o_total_fans'] = $tmp;
                $slldata['data'][$kn]['timedata'][$k1]['percentage'] = self:: percentage($v1['un_subscribe'], $v1['all_fans']);

            }
        }

        foreach ($slldata['data'] as $kn1 => $vn1)
        {
            $tmpdata = 0;
            foreach ($vn1['timedata'] as $k1 => $v1){

                $tmpdata+= $slldata['data'][$kn1]['timedata'][$k1]['o_total_fans'];

            }

            $slldata['data'][$kn1]['o_total_fans'] = $tmpdata;

        }





            $excelArray = array();

        $slldata['bname'] = $allbussname;

        if($getdade['excel']==1){
            if($slldata['data']){
                $tmp_arr = array();
                //print_r($slldata['data']);die;
                foreach($slldata['data'] as $k1 => $v1){
                    $tmp_arr[] = $v1;
                    unset($tmp_arr[$k1]['timedata']);
                    if($v1['timedata']){
                        foreach($v1['timedata'] as $k2 => $v2){
                            $tmp_arr[] = $v2;
                            unset($tmp_arr[$k2]['timedata']);
                        }


                    }


                }

            }


            foreach ($tmp_arr  as  $k6=>$v6){
                if(isset($v6['bussname'])){

                }else{
                    $tmp_arr[$k6]['bussname'] = $v6['date_time'];
                }



            }

            //return $tmp_arr;

            foreach ($tmp_arr as $k7=>$v7){
                $newExcel = array();
                $newExcel['bussname'] = $v7['bussname'];
                $newExcel['all_fans'] = $v7['all_fans'];
                $newExcel['un_subscribe'] = $v7['un_subscribe'];
                $newExcel['percentage'] = $v7['percentage'];

                $lastExcel2[] = $newExcel;
            }

            return $lastExcel2;

        } else{


            return $slldata;
        }

    }

    /** 删除二维数组的重复内容
     * @param $array
     * @return array
     */
     public static function remove_duplicate($array){
        $result=array();
        for($i=0;$i<count($array);$i++){
            $source=$array[$i];
            if(array_search($source,$array)==$i && $source<>"" ){
                $result[]=$source;
            }
        }
        return $result;
    }

    /**
     *  获得单独某一渠道的数据
     */
    public static function getonechanneldata($getdade)
    {
        header("Content-type: text/html; charset=utf-8");

        $getdade['start_date']  =  $getdade['start_date']==""? date('Y-m-d',strtotime('-7days')):$getdade['start_date'];
        $getdade['end_date']  =  $getdade['end_date']==""? date('Y-m-d',time()):$getdade['end_date'];


        $getdade['start_date'] = date('Y-m-d', strtotime($getdade['start_date']));
        $getdade['end_date'] = date('Y-m-d', strtotime('+1 day',strtotime($getdade['end_date'])));

        $bid =  DB::table('buss_info')->where('nick_name','=',$getdade['bussid'])->select('bid')->first()->bid;
        $getdade['bussid'] = $bid;

        $data = array(
            ['date_time', '>=', $getdade['start_date']],
            ['date_time', '<', $getdade['end_date']],
            ['parent_id', '=', $getdade['bussid']]   // bid
        );

        $namedata = array(

        );

        $allbussname = TaskSummaryModel::where($namedata)->select('parent_id as buss_id')->get()->toArray();

        $allbussname = self::remove_duplicate($allbussname);

        foreach ($allbussname as $nk =>$nv )
        {

            $bussname = DB::table('buss_info')->select('nick_name')->where('bid','=',$nv)->first()->nick_name;
            $allbussname[$nk]['bname'] = $bussname;
        }


        $all_fans=DB::raw('(sum(y_task_summary.new_follow_repeat) + sum(y_task_summary.old_follow_repeat)) as all_fans');
        $un_subscribe = DB::raw('(sum(y_task_summary.new_unfollow_repeat) + sum(y_task_summary.old_unfollow_repeat)) as un_subscribe');


        $oidArray = TaskSummaryModel::where($data)->select('order_id')->get()->toArray();


        $alldata = TaskSummaryModel::where($data)->select('date_time','buss_id', $all_fans, $un_subscribe)->get()->toArray();  //缺少,总数据,以及百分比



        if(count($oidArray) == 0 || count($alldata) == 0)
        {
            return null;
        }


        $alldata[0]['percentage'] = self:: percentage($alldata[0]['un_subscribe'], $alldata[0]['all_fans']);


        $bussname = DB::table('buss_info')->select('nick_name')->where('bid', '=', $getdade['bussid'])->first();
        if ($bussname == null) {
            $bussname = 0;
        } else {
            $bussname = $bussname->nick_name;
        }
        $alldata[0]['bussname'] = $bussname;


        $alltimedata = TaskSummaryModel::where($data)->select('parent_id as buss_id', DB::raw('group_concat(order_id) as order_id'), 'date_time', $all_fans,$un_subscribe)->groupBy('date_time')->orderBy('date_time','desc')->get()->toArray();  //缺少,总数据,以及百分比

        foreach ($alltimedata as $key => $value) {
//            $oid = array_unique(explode(',', $value['order_id']));
           $oid = explode(',', $value['order_id']);
            $tmp = 0;
            foreach ($oid as $kk => $vv) {
                $o_total_fans = DB::table('y_order')->select('o_total_fans')->where('order_id', '=', $vv)->first();
                if ($o_total_fans == null) {
                    $o_total_fans = 0;
                } else {
                    $o_total_fans = $o_total_fans->o_total_fans;
                }

                @$tmp+= $o_total_fans;
            }
            $alltimedata[$key]['o_total_fans'] = $tmp;
            $alltimedata[$key]['percentage'] = self:: percentage($alltimedata[$key]['un_subscribe'], $tmp);

        }
        $allfans = 0;
        foreach ($alltimedata as $kk1=>$vv1){
            $allfans += $vv1['o_total_fans'];
        }
        $alldata[0]['o_total_fans'] = $allfans;
        $alldata['timedata'] = $alltimedata;


        // 父渠道

        $fatherdata = array(
            ['date_time', '>=', $getdade['start_date']],
            ['date_time', '<', $getdade['end_date']],
            ['buss_id', '=', $getdade['bussid']],
            ['parent_id', '=', $getdade['bussid']]
        );



        $foidArray = TaskSummaryModel::where($fatherdata)->select('order_id')->get()->toArray();

        $falldata = TaskSummaryModel::where($fatherdata)->select('date_time', DB::raw('group_concat(order_id) as order_id'),'buss_id', $all_fans, $un_subscribe)->get()->toArray();  //缺少,总数据,以及百分比


        $falldata[0]['percentage'] = self:: percentage($falldata[0]['un_subscribe'], $falldata[0]['all_fans']);

        $fbussname = DB::table('buss_info')->select('nick_name')->where('bid', '=', $getdade['bussid'])->first();
        if ($fbussname == null) {
            $fbussname = 0;
        } else {
            $fbussname = $fbussname->nick_name;
        }
        $falldata[0]['bussname'] = $fbussname;


        $falltimedata = TaskSummaryModel::where($fatherdata)->select('buss_id', DB::raw('group_concat(order_id) as order_id'), 'date_time', $all_fans, $un_subscribe)->groupBy('date_time')->orderBy('date_time','desc')->get()->toArray();

        foreach ($falltimedata as $key => $value) {
            $tmps = 0;
            $oid = explode(',', $value['order_id']);
            foreach ($oid as $kk => $vv) {
                $o_total_fansss = DB::table('y_order')->select('o_total_fans')->where('order_id', '=', $vv)->first();
                if ($o_total_fansss == null) {
                    $o_total_fansss = 0;
                } else {
                    $o_total_fansss = $o_total_fansss->o_total_fans;
                }

                @$tmps+= $o_total_fansss;

            }
            $falltimedata[$key]['o_total_fans'] = $tmps;
            $falltimedata[$key]['percentage'] = self:: percentage($falltimedata[$key]['un_subscribe'], $falltimedata[$key]['all_fans']);

        }

        $fatherAllFans = 0;
        foreach ($falltimedata as $kk1=>$vv1){
            $fatherAllFans += $vv1['o_total_fans'];
        }
        $falldata[0]['o_total_fans'] = $fatherAllFans;
        $falldata['timedata'] = $falltimedata;


        // 子渠道

        $sondata = array(
            ['date_time', '>=', $getdade['start_date']],
            ['date_time', '<', $getdade['end_date']],
            ['buss_id', '<>', $getdade['bussid']],
            ['parent_id', '=', $getdade['bussid']]
        );

        // 总数据

        $sonallArray = TaskSummaryModel::where($sondata)->select('date_time','buss_id',$all_fans,$un_subscribe)->groupBy('buss_id')->get()->toArray();

      foreach ($sonallArray as $sk=>$vk)
      {

          $tmpsondata = array(
              ['date_time', '>=', $getdade['start_date']],
              ['date_time', '<', $getdade['end_date']],
              ['buss_id', '=', $vk['buss_id']],
              ['parent_id', '=', $getdade['bussid']],
          );
          $bussname = DB::table('buss_info')->select('nick_name')->where('bid', '=', $vk['buss_id'])->first();
          if ($bussname == null) {
              $bussname = 0;
          } else {
              $bussname = $bussname->nick_name;
          }
          $sonallArray[$sk]['bussname'] = $bussname; // 各个渠道的总数据
          $sonallArray[$sk]['percentage'] = self:: percentage($sonallArray[$sk]['un_subscribe'], $sonallArray[$sk]['all_fans']);
          $sonallTimeArray = TaskSummaryModel::where($tmpsondata)->select('date_time as bussname',DB::raw('group_concat(order_id) as order_id'),'buss_id',$all_fans,$un_subscribe)->groupBy('date_time')->orderBy('date_time','desc')->get()->toArray();
          $sonallArray[$sk]['timedata'] = $sonallTimeArray;

      }


        foreach ($sonallArray as $kkk => $vvv)
        {
            $timeArray = $vvv['timedata'];
            if(count($timeArray)>0)
            {
                foreach ($timeArray as $kt=>$vt)
                {
                    $timeoid = explode(',', $vt['order_id']);
                    $timetmp = 0;
                    foreach ($timeoid as $kk => $vv) {
                        $o_total_fans = DB::table('y_order')->select('o_total_fans')->where('order_id', '=', $vv)->first();
                        if ($o_total_fans == null) {
                            $o_total_fans = 0;
                        } else {
                            $o_total_fans = $o_total_fans->o_total_fans;
                        }
                        @$timetmp+= $o_total_fans;
                    }
                    $sonallArray[$kkk]['timedata'][$kt]['o_total_fans'] = $timetmp;
                    $sonallArray[$kkk]['timedata'][$kt]['percentage'] = self:: percentage($vt['un_subscribe'],$vt['all_fans']);
                }

            }
        }

        foreach ($sonallArray as $kkk => $vvv) {
            $timeArray = $vvv['timedata'];
            if (count($timeArray) > 0) {
                $timetmpall = 0;
                foreach ($timeArray as $kt => $vt) {
                    @$timetmpall += $vt['o_total_fans'];
                }
                $sonallArray[$kkk]['o_total_fans'] = $timetmpall;
            }

        }

        if($getdade['excel']==1)
        {

            if($alldata && $alldata['timedata'] && $alldata['timedata'])
            {
                $allFirst = [$alldata[0]];
                $allTime = $alldata['timedata'];
                $allexcel = array_merge($allFirst,$allTime);
            }else{
                $allexcel = array();
            }

            if($falldata && $falldata['timedata'] && $falldata['timedata'])
            {
                $fFirst = [$falldata[0]];
                $fTime = $falldata['timedata'];
                $fexcel = array_merge($fFirst,$fTime);
            }else{
                $fexcel = array();
            }


//            return $sonallArray;


            if($sonallArray && count($sonallArray)>0)
            {

                foreach($sonallArray as $key1 =>$value1){
                    $newarr[]=array(
                        'buss_id'=>$value1['buss_id'],
                        'all_fans'=> $value1['all_fans'],
                        'un_subscribe'=> $value1['un_subscribe'],
                        'bussname'=>$value1['bussname'],
                        'percentage'=> $value1['percentage'],
                        'o_total_fans'=>$value1['o_total_fans'],
                        'date_time'=>$value1['bussname'],
                    );
                    foreach($value1['timedata'] as $key2 =>$value2){
                        $newarr[]=array(
                            'buss_id'=>$value2['buss_id'],
                            'all_fans'=>$value2['all_fans'],
                            'un_subscribe'=> $value2['un_subscribe'],
                            'bussname'=>$value2['bussname'],
                            'percentage'=>$value2['percentage'],
                            'o_total_fans'=> $value2['o_total_fans'],
                            'date_time'=>$value2['bussname'],
                        );
                    }
                }

            }else{
                $sexcel = array();
            }


            $excelArray =  array_merge($allexcel,$fexcel,$newarr);


            foreach ($excelArray as $kk=>$vv)
            {
                if(isset($vv['bussname'])){
                    $excelArray[$kk]['date_time'] = $vv['bussname'];
                }
            }

            $newexcel = array();
            foreach ($excelArray as $kk=>$value)
            {
                $tmpArray = array();
                $tmpArray['date_time'] = $value['date_time'];
                $tmpArray['all_fans'] = $value['all_fans'];
                $tmpArray['un_subscribe'] = $value['un_subscribe'];
                $tmpArray['percentage'] = $value['percentage'];

                $newexcel[$kk] = $tmpArray;
            }

            return $newexcel;
        }else{
            $lastdata['bname'] = $allbussname;
            $lastdata['alldata'] = $alldata;
            $lastdata['falldata'] = $falldata;
            $lastdata['sondata'] = $sonallArray;


            return $lastdata;
        }



    }


    /**
     * 图表数据
     */
    public static function getPictureData($data)
    {

        header("Content-type: text/html; charset=utf-8");
        $allfans = $data['allfans'];

        $page = isset($data['page']) ? $data['page'] : 1;
        $pagesize = isset($data['pagesize']) ? $data['pagesize'] : 10;


        $user = $data['user'];  // 0 全部 1 新用户 2 老用户

        $action = $data['action'];




        if ($action == 1) {

            if (empty($data['startdate']) && empty($data['enddate'])) {

                $date = $data['date'];
                $mindate = date('Y-m-d H:i:s', strtotime($date));
                $maxdate = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($date)));  // 向后追加一天 00:00:00


                $date = array(['date_time', '=', $date]);

                if($user == 0 )
                {
                    $logdate = array(
                        ['date', '>=', $mindate],
                        ['date', '<', $maxdate]                            // date时间结束必须精确到23:59:59
                    );

                }elseif($user == 1)
                {
                    $logdate = array(
                        ['date', '>=', $mindate],
                        ['date', '<', $maxdate],                         // date时间结束必须精确到23:59:59
                        ['isold','=',2]
                    );
                }elseif($user == 2){
                    $logdate = array(
                        ['date', '>=', $mindate],
                        ['date', '<', $maxdate],                         // date时间结束必须精确到23:59:59
                        ['isold','=',1]
                    );
                }

            } else {

                $data['startdate']  =  $data['startdate']==""? date('Y-m-d',strtotime('-7days')):$data['startdate'];
                $data['enddate']  =  $data['enddate']==""? date('Y-m-d',time()):$data['enddate'];

                $date = array(
                    ['date_time', '>=', $data['startdate']],     // summay
                    ['date_time', '<=', $data['enddate']]
                );

                $mindate = date('Y-m-d H:i:s', strtotime($data['startdate']));
                $maxdate = date('Y-m-d H:i:s', strtotime('+1 day',strtotime($data['enddate'])));

                if($user == 0 )
                {
                    $logdate = array(
                        ['date', '>=', $mindate],     // summay
                        ['date', '<',$maxdate]
                    );

                }elseif($user == 1)
                {

                    $logdate = array(
                        ['date', '>=', $mindate],     // summay
                        ['date', '<', $maxdate],
                        ['isold','=',2]
                    );

                }elseif($user == 2){
                    $logdate = array(
                        ['date', '>=', $mindate],     // summay
                        ['date', '<', $maxdate],
                        ['isold','=',1]
                    );
                }

            }
            // 渠道占比


            $channelArray = self::analyzeChannelData($oid=0,$date,$data);

            $alldata =  self::designPictureData($user,$date,$logdate,$allfans,$page,$pagesize);
            $alldata['channeldata'] =$channelArray;
            return $alldata;

        } elseif ($action == 2) {

            if (empty($data['startdate']) && empty($data['enddate'])) {

                $date = $data['orderdate'];
                $mindate = $date; // 下单时间
                $maxdate = date('Y-m-d H:i:s', time());   //当前时间

                $date = array(

                    ['order_id', '=', $data['oid']]
                );

                $logdate = array(
                                                // date时间结束必须精确到23:59:59
                    ['oid', '=', $data['oid']],
                );

                $bussdate = array(
                    ['y_task_summary.order_id', '=', $data['oid']]
                );
            } else {

                $data['startdate'] = $data['startdate'] == "" ? date('Y-m-d', strtotime('-7days')) : $data['startdate'];
                $data['enddate'] = $data['enddate'] == "" ? date('Y-m-d', time()) : $data['enddate'];

                $mindate = date('Y-m-d H:i:s', strtotime($data['startdate']));
                $maxdate = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($data['enddate'])));


                if($data['oid'] == 0 ){

                    $date = array(
                        ['date_time', '>=', $data['startdate']],     // summay
                        ['date_time', '<=', $data['enddate']],
                    );

                    $logdate = array(
                        ['date', '>=', $mindate],
                        ['date', '<=', $maxdate],                            // date时间结束必须精确到23:59:59
                    );

                }else{
                    $date = array(
                        ['date_time', '>=', $data['startdate']],     // summay
                        ['date_time', '<=', $data['enddate']],
                        ['order_id', '=', $data['oid']]
                    );

                    $logdate = array(
                        ['date', '>=', $mindate],
                        ['date', '<=', $maxdate],                            // date时间结束必须精确到23:59:59
                        ['oid', '=', $data['oid']]
                    );

                }

                $bussdate = array(
                    ['y_task_summary.date_time', '>=', $data['startdate']],     // summay
                    ['y_task_summary.date_time', '<=', $data['enddate']],
                    ['y_task_summary.order_id', '=', $data['oid']]
                );



            }
            $channelArray = self::analyzeChannelData($data['oid'],$bussdate,$data);
            $alldata =  self::designPictureData($user=0,$date,$logdate,$allfans,$page,$pagesize);
            $alldata['channeldata'] =$channelArray;
            return $alldata;

         // 渠道数据
        } elseif ($action == 3) {


            if (empty($data['startdate']) && empty($data['enddate'])) {

                if ($data['ob']=='all') {

                    $date = array(

                        ['parent_id', '=', $data['cid']]
                    );
                } elseif ($data['ob']=='father') {
                    $date = array(

                        ['buss_id', '=', $data['cid']],
                        ['parent_id', '=', $data['cid']]
                    );
                } elseif($data['ob']=='son') {

                    $date = array(

                        ['buss_id', '=', $data['cid']],
                        ['parent_id', '=', $data['parent_id']],
                    );
                }

                // 粉丝时间
                $logodate = date('Y-m-d H:i:s', time());

                // 粉丝时间
                if($user == 0 )
                {

                    $logdate = array(
                        ['date', '<=', $logodate],                            // date时间结束必须精确到23:59:59
                        ['bid', '=', $data['cid']]
                    );

                }elseif($user == 1)
                {

                    $logdate = array(
                        ['date', '<=', $logodate],                            // date时间结束必须精确到23:59:59
                        ['bid', '=', $data['cid']],
                        ['isold','=',2]
                    );

                }elseif($user == 2){
                    $logdate = array(
                        ['date', '<=', $logodate],                            // date时间结束必须精确到23:59:59
                        ['bid', '=', $data['cid']],
                        ['isold','=',1]
                    );

                }

            } else {
                if ($data['ob']=='all') {
                    $channelid =  DB::table('buss_info')->where('nick_name','=',$data['cid'])->select('bid')->first()->bid;
                    $data['cid'] = $channelid;
                    $date = array(
                        ['date_time', '>=', $data['startdate']],
                        ['date_time', '<=', $data['enddate']],
                        ['parent_id', '=', $data['cid']]
                    );
                } elseif ($data['ob']=='father') {

                    $channelid =  DB::table('buss_info')->where('nick_name','=',$data['cid'])->select('bid')->first()->bid;
                    $data['cid'] = $channelid;
                    $date = array(
                        ['date_time', '>=', $data['startdate']],
                        ['date_time', '<=', $data['enddate']],
                        ['buss_id', '=', $data['cid']],
                        ['parent_id', '=', $data['cid']]
                    );
                } elseif($data['ob']=='son') {

                    $parentid =  DB::table('buss_info')->where('nick_name','=',$data['parent_id'])->select('bid')->first()->bid;
                    $data['parent_id'] = $parentid;

                    $date = array(
                        ['date_time', '>=', $data['startdate']],
                        ['date_time', '<=', $data['enddate']],
                        ['buss_id', '=', $data['cid']],
                        ['parent_id', '=', $data['parent_id']],
                    );
                }

                $mindate = date('Y-m-d H:i:s', strtotime($data['startdate']));
                $maxdate = date('Y-m-d H:i:s', strtotime('+1 day',strtotime($data['enddate'])));
                if($user == 0 )
                {
                    $logdate = array(
                        ['date', '>=', $mindate],     // summay
                        ['date', '<', $maxdate],
                        ['bid', '=', $data['cid']]
                    );
                }elseif($user == 1)
                {

                    $logdate = array(
                        ['date', '>=', $mindate],     // summay
                        ['date', '<', $maxdate],
                        ['bid', '=', $data['cid']],
                        ['isold','=',2]
                    );

                }elseif($user == 2){

                    $logdate = array(
                        ['date', '>=', $mindate],     // summay
                        ['date', '<', $maxdate],
                        ['bid', '=', $data['cid']],
                        ['isold','=',1]
                    );

                }

            }


            $alldata =  self::designPictureData($user,$date,$logdate,$allfans,$page,$pagesize);
            return $alldata;

        }


    }

    public static function analyzeChannelData($oid,$date,$getdade){
        $all_fans=DB::raw('(sum(y_task_summary.new_follow_repeat) + sum(y_task_summary.old_follow_repeat)) as total_fans');

        $page = isset($getdade['busspage']) ? $getdade['busspage'] : 1;
        $pagesize = isset($getdade['busspagesize']) ? $getdade['busspagesize'] : 3;


        $bussCurrnrtFansModel = TaskSummaryModel::where($date)->leftJoin('bussiness','y_task_summary.buss_id','=','bussiness.id')->select($all_fans,'y_task_summary.buss_id','bussiness.username')->groupBy('y_task_summary.buss_id');


        $count = count($bussCurrnrtFansModel->get()->toArray());
        $bussCurrnrtFans=self::getPages($bussCurrnrtFansModel,$page,$pagesize,$count);



        $platformFans = TaskSummaryModel::where($date)->select($all_fans)->get()->toArray();


        $bussCurrnrtFans = self::object_array($bussCurrnrtFans);

        foreach($bussCurrnrtFans['data'] as $key=>$value){
            $bussCurrnrtFans['data'][$key]['busspercent'] =  self:: percentage($value['total_fans'] , $platformFans[0]['total_fans']);

        }

        return $bussCurrnrtFans;


    }

    /**
     * @param $date
     * @param $logdate
     * @return mixed
     */
    public static function designPictureData($user,$date,$logdate,$allfans,$page,$pagesize)
    {

        if($user==0)
        {
            $all_fans=DB::raw('(sum(y_task_summary.new_follow_repeat) + sum(y_task_summary.old_follow_repeat)) as total_fans');
            $un_subscribe = DB::raw('(sum(y_task_summary.new_unfollow_repeat) + sum(y_task_summary.old_unfollow_repeat)) as un_subscribe');
            $sexdata = TaskSummaryModel::where($date)->select(DB::raw('sum(old_boy) as old_boy '), DB::raw('sum(old_girl) as old_girl '), DB::raw('sum(new_boy) as new_boy'), DB::raw('sum(new_girl) as new_girl'), DB::raw('sum(new_nbg) as new_nbg'), DB::raw('sum(old_nbg) as old_nbg'), $all_fans)->get()->toArray();

            $allfans = TaskSummaryModel::where($date)->select($all_fans)->first()->total_fans;
            $nsexdata['boyfans'] = $sexdata[0]['old_boy'] + $sexdata[0]['new_boy'];
            $nsexdata['girfans'] = $sexdata[0]['old_girl'] + $sexdata[0]['new_girl'];
            $nsexdata['nbgfans'] = $sexdata[0]['new_nbg'] + $sexdata[0]['old_nbg'];
        }elseif($user==1)  // 新用户
        {
            $all_fans=DB::raw('sum(y_task_summary.new_follow_repeat) as total_fans');
            $un_subscribe = DB::raw('sum(y_task_summary.new_unfollow_repeat) as un_subscribe');
            $sexdata = TaskSummaryModel::where($date)->select(DB::raw('sum(old_boy) as old_boy '), DB::raw('sum(old_girl) as old_girl '), DB::raw('sum(new_boy) as new_boy'), DB::raw('sum(new_girl) as new_girl'), DB::raw('sum(new_nbg) as new_nbg'), DB::raw('sum(old_nbg) as old_nbg'), $all_fans)->get()->toArray();
            $allfans = TaskSummaryModel::where($date)->select($all_fans)->first()->total_fans;
            $nsexdata['boyfans'] = $sexdata[0]['new_boy'];
            $nsexdata['girfans'] =  $sexdata[0]['new_girl'];
            $nsexdata['nbgfans'] = $sexdata[0]['new_nbg'];

        }elseif($user==2)
        {
            $all_fans=DB::raw('sum(y_task_summary.old_follow_repeat) as total_fans');
            $un_subscribe = DB::raw('sum(y_task_summary.old_unfollow_repeat) as un_subscribe');
            $sexdata = TaskSummaryModel::where($date)->select(DB::raw('sum(old_boy) as old_boy '), DB::raw('sum(old_girl) as old_girl '), DB::raw('sum(new_boy) as new_boy'), DB::raw('sum(new_girl) as new_girl'), DB::raw('sum(new_nbg) as new_nbg'), DB::raw('sum(old_nbg) as old_nbg'), $all_fans)->get()->toArray();
            $allfans = TaskSummaryModel::where($date)->select($all_fans)->first()->total_fans;
            $nsexdata['boyfans'] = $sexdata[0]['old_boy'];
            $nsexdata['girfans'] = $sexdata[0]['old_girl'];
            $nsexdata['nbgfans'] = $sexdata[0]['old_nbg'];
        }

        $nsexdata['boypercentage'] = self:: percentage($nsexdata['boyfans'], $sexdata[0]['total_fans']);
        $nsexdata['girlpercentage'] = self:: percentage($nsexdata['girfans'], $sexdata[0]['total_fans']);
        $nsexdata['nbgpercentage'] = self:: percentage($nsexdata['nbgfans'], $sexdata[0]['total_fans']);

        $cityAreafans = DB::table('y_fans_log')->select('province', 'city', DB::raw('count(id) as time'))->where($logdate)->groupBy('city')->get()->toArray();

        $provinceAreafansModel = DB::table('y_fans_log')->select('province', DB::raw('count(id) as time'))->where($logdate)->groupBy('province');

        $count = count($provinceAreafansModel->get()->toArray());
        $provinceAreafans=self::getPages($provinceAreafansModel,$page,$pagesize,$count);

        $important = self::object_array($provinceAreafans['data']);  // 这里需需要对象数组转换为关系数组
        unset($provinceAreafans['data']);
        $provinceAreafans['data'] = $important;

        $cityAreafans = self::object_array($cityAreafans);

        foreach ($provinceAreafans['data'] as $key => $value) {
            $provinceAreafans['data'][$key]['times'] = self::percentage($value['time'], $allfans);

            $province = $value['province'];

            foreach ($cityAreafans as $kk => $vv) {

                if ($vv['province'] == $province) {
                    $vv['times'] = self::percentage($vv['time'], $allfans);
                    $provinceAreafans['data'][$key]['city'][] = $vv;

                }
            }

        }

        $alldata['provinceAreafans'] = $provinceAreafans;
        $alldata['sexComparison'] = $nsexdata;

        return $alldata;

    }

    public static function object_array($array)
    {
        if (is_object($array)) {
            $array = (array)$array;
        }
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = self::object_array($value);
            }
        }
        return $array;
    }

    /**
     *  重复百分比计算
     */
    public static function percentage($data, $sexdata)
    {

        if ($data != 0 && $sexdata != 0) {
            $un_attention = (int)$data / (int)$sexdata;
            $un_attention = number_format($un_attention, 4);
            $un_attention = ($un_attention * 100) . '%';
            return $un_attention;
        } else {
            return '0%';
        }


    }
    //商家带粉中心
    public static function fansEarn($start_date,$end_date,$page,$pagesize,$buss){
        if($start_date){
            $where[] = array('date_time','>=',$start_date);
        }else{
            $where[] = array('date_time','>=',date('Y-m-d',strtotime('-7days')));
        }
        if($end_date) {
            $where[] = array('date_time', '<=', $end_date);
        }else{
            $where[] = array('date_time', '<=', date('Y-m-d',time()));
        }
        $buss_sch = TaskSummaryModel::select('buss_id','parent_id')->where('buss_id','=',$buss)->orWhere('parent_id','=',$buss)->groupBy('buss_id')->get()->toArray();
        if(!empty($buss_sch)){
            foreach($buss_sch as $k=>$v){
                $arr[$v['buss_id']] = $v['buss_id'];
                $arr[$v['parent_id']] = $v['parent_id'];
            }
            ksort($arr);
            $buss_arr = BussModel::leftJoin('buss_info','bussiness.id','=','buss_info.bid')
                ->select('id','pbid','nick_name','cost_price')
                ->whereIn('id',$arr);
            $buss_data = self::getPages($buss_arr,$page,$pagesize);
            $buss_id = TaskSummaryModel::select('buss_id')->where('buss_id','=',$buss)->orWhere('parent_id','=',$buss)->groupBy('buss_id')->get()->toArray();
            $data = TaskSummaryModel::leftJoin('y_order','y_order.order_id','=','y_task_summary.order_id')
                                        ->select(DB::raw('sum(new_follow_repeat) as new_follow_repeat'),DB::raw('sum(old_follow_repeat) as old_follow_repeat'),'y_task_summary.order_id'
                                        ,DB::raw('sum(new_unfollow_repeat) as new_unfollow_repeat'),DB::raw('sum(old_unfollow_repeat) as old_unfollow_repeat'),'y_task_summary.date_time','y_task_summary.buss_id','o_per_price')
                                        ->where($where)
                                        ->whereIn('y_task_summary.buss_id',$buss_id)
                                        ->groupBy('y_task_summary.date_time')
                                        ->groupBy('y_task_summary.buss_id')
                                        ->groupBy('y_task_summary.order_id')
                                        ->orderBy('y_task_summary.date_time','desc')
                                        ->get()->toArray();
            foreach($buss_data['data'] as $k=>$v){
                foreach($data as $kk=>$vv){
                    if($v['id'] == $vv['buss_id']){
                        $buss_data['data'][$k]['data'][] = $vv;
                    }
                }
            }
            $buss_father = BussModel::leftJoin('buss_info','bussiness.id','=','buss_info.bid')
                ->select('id','pbid','nick_name','cost_price')
                ->where('id','=',$buss)
                ->first()->toArray();
            $buss_data['data']['count'] = $buss_data['count'];
            $buss_data['data']['father'] = $buss_father;
            return $buss_data['data'];
        }else{
            return false;
        }
    }
    //子商家带粉中心
    public static function fansEarn_child($start_date,$end_date,$page,$pagesize,$buss){
        if($start_date){
            $where[] = array('date_time','>=',$start_date);
        }else{
            $where[] = array('date_time','>=',date('Y-m-d',strtotime('-7days')));
        }
        if($end_date) {
            $where[] = array('date_time', '<=', $end_date);
        }else{
            $where[] = array('date_time', '<=', date('Y-m-d',time()));
        }
        if($buss)
            $where[] = array('buss_id','=',$buss);
        $count_data = TaskSummaryModel::leftJoin('y_order','y_order.order_id','=','y_task_summary.order_id')
            ->select(DB::raw('sum(new_follow_repeat) as new_follow_repeat'),DB::raw('sum(old_follow_repeat) as old_follow_repeat')
                                        ,DB::raw('sum(new_unfollow_repeat) as new_unfollow_repeat'),DB::raw('sum(old_unfollow_repeat) as old_unfollow_repeat'),'o_per_price','y_task_summary.date_time','y_task_summary.buss_id')
                                            ->where($where)
                                            ->groupBy('y_task_summary.date_time')
                                            ->groupBy('y_task_summary.order_id')
                                            ->orderBy('y_task_summary.date_time','desc')
                                            ->get()->toArray();
        $data = TaskSummaryModel::select('date_time','buss_id')
                                        ->where($where)
                                        ->groupBy('date_time')
                                        ->orderBy('date_time','desc');
        $count_num = TaskSummaryModel::select('date_time','buss_id')
                                        ->where($where)
                                        ->groupBy('date_time')
                                        ->orderBy('date_time','desc')->get()->toArray();
        $count = count($count_num);
        $list = self::getPages($data,$page,$pagesize,$count);
        foreach($list['data'] as $k=>$v){
            foreach($count_data as $kk=>$vv){
                if($v['date_time'] == $vv['date_time']){
                    $list['data'][$k]['count'][] = $vv;
                }
            }
        }
        $price = BussModel::select('cost_price')->where('id','=',$buss)->first()->cost_price;
        $arr['count'] = $count;
        $arr['data'] = $list['data'];
        $arr['price'] = $price;
        return $arr;
    }
    
    static public function getTimeDate($where) 
    {
        $model= TaskSummaryModel::select('new_getwx_repeat','old_getwx_repeat','new_complet_repeat','old_complet_repeat','new_follow_repeat','old_follow_repeat','new_end_repeat','old_end_repeat','new_unfollow_repeat','old_unfollow_repeat','o_per_price','y_task_summary.buss_id','y_task_summary.order_id','bussiness.money','cost_price','y_task_summary.parent_id','reduce_percent','y_task_summary.wx_id','y_task_summary.one_price')
                ->leftJoin('bussiness','y_task_summary.buss_id','=','bussiness.id')
                ->leftJoin('y_order','y_task_summary.order_id','=','y_order.order_id')
                ->where($where)
                ->get();
        return $model?$model->toArray():null;
    }
    
    static public function getSubTimeDate($bussid,$start,$end) 
    {
        $where[]=['date_time','>=',$start];
        $where[]=['date_time','<=',$end];
        //不用分页
        $selectarray=array(
            'buss_id',
            DB::raw('sum(new_follow_repeat) as new_follow_repeat'),
            DB::raw('sum(old_follow_repeat) as old_follow_repeat'),
            DB::raw('sum(new_unfollow_repeat) as new_unfollow_repeat'),
            DB::raw('sum(old_unfollow_repeat) as old_unfollow_repeat')
        );
        $model= TaskSummaryModel::select($selectarray)
                ->where($where)
                ->whereIn('buss_id',$bussid)
                ->groupBy('buss_id')
                ->get();
        return $model?$model->toArray():null;
    }
    
    
    static public function getSubListDate($where,$page,$pagesize) 
    {
        $retuen['count']=TaskSummaryModel::select()
            ->where($where)
            ->count(DB::raw('DISTINCT date_time'));
        //不用分页
        $selectarray=array(
            'buss_id',
            'date_time',
            DB::raw('sum(new_follow_repeat) as new_follow_repeat'),
            DB::raw('sum(old_follow_repeat) as old_follow_repeat'),
            DB::raw('sum(new_unfollow_repeat) as new_unfollow_repeat'),
            DB::raw('sum(old_unfollow_repeat) as old_unfollow_repeat')
        );
        $model= TaskSummaryModel::select($selectarray)
                ->where($where)
                ->groupBy('buss_id','date_time')
                ->orderBy('date_time','desc');
        $data= self::getGroupPages($model,$page,$pagesize,$retuen['count']);
        $retuen['data2']=$data['data'];
        return $retuen;
    }
    
    
    static public function getSubSumDate($where) 
    {
        //不用分页
        $selectarray=array(
            'buss_id',
            DB::raw('sum(new_follow_repeat) as new_follow_repeat'),
            DB::raw('sum(old_follow_repeat) as old_follow_repeat'),
            DB::raw('sum(new_unfollow_repeat) as new_unfollow_repeat'),
            DB::raw('sum(old_unfollow_repeat) as old_unfollow_repeat'),
            'y_order.o_per_price'
        );
        $model= TaskSummaryModel::select($selectarray)
                ->leftJoin('bussiness','y_task_summary.buss_id','=','bussiness.id')
                ->leftJoin('y_order','y_task_summary.order_id','=','y_order.order_id')
                ->where($where)
                ->orderBy('date_time','desc')
                ->get()
                ->first()
                ->toArray();
        return $model;
    }
    
    static public function getSubwxListDate($where,$page,$pagesize) 
    {
        $retuen['count']=TaskSummaryModel::select()
            ->where($where)
            ->count(DB::raw('DISTINCT wx_id,date_time'));
        $selectarray=array(
            'buss_id',
            'date_time',
            DB::raw('sum(new_follow_repeat) as new_follow_repeat'),
            DB::raw('sum(old_follow_repeat) as old_follow_repeat'),
            DB::raw('sum(new_unfollow_repeat) as new_unfollow_repeat'),
            DB::raw('sum(old_unfollow_repeat) as old_unfollow_repeat'),
            'wx_name'
        );
        $model= TaskSummaryModel::select($selectarray)
                ->leftjoin('wx_info','wx_info.id','=','y_task_summary.wx_id')
                ->where($where)
                ->groupBy('wx_id','date_time')
                ->orderBy('date_time','desc');
        $data= self::getGroupPages($model,$page,$pagesize, $retuen['count']);
        $retuen['date']=$data['data'];
        $retuen['count']=$retuen['count'];
        return $retuen;
    }
    
    static public function getSubwxNameList($where) {
        $wx_name = TaskSummaryModel::leftJoin('wx_info','wx_info.id','=','y_task_summary.wx_id')
            ->select('wx_id','wx_name')
            ->where($where)
            ->groupBy('wx_id')
            ->get();
        return $wx_name?$wx_name->toArray():null;
    }

    /**
     * 销售报表
     */
    static public function saleStatistics($start_date,$end_date,$page,$pagesize,$sales){
        if($start_date){
            $where[] = array('date_time','>=',$start_date);
        }else{
            $where[] = array('date_time','>=',date('Y-m-d',strtotime('-7days')));
        }
        if($end_date) {
            $where[] = array('date_time', '<=', $end_date);
        }else{
            $where[] = array('date_time', '<=', date('Y-m-d',time()));
        }
        if($sales){
            $condition[] = array('o_uid','=',$sales);
        }else{
            $condition[] = array('o_uid','>',0);
        }
        $user_id = TaskSummaryModel::leftJoin('y_order','y_task_summary.order_id','=','y_order.order_id')->select('o_uid')->where($where)->groupBy('o_uid')->get()->toArray();
        $order_id = TaskSummaryModel::select('order_id')->where($where)->groupBy('order_id')->get()->toArray();
        $user_arr = UserInfoModel::select('uid','nick_name')->whereIn('uid',$user_id)->get()->toArray();
        if($user_id && $order_id){
            $data = TaskSummaryModel::leftJoin('y_order','y_task_summary.order_id','=','y_order.order_id')->leftJoin('user_info','y_order.o_uid','=','user_info.uid')
                        ->select('date_time','nick_name','o_uid','o_per_price','y_task_summary.order_id',DB::raw('sum(new_follow_repeat) as new_follow'),DB::raw('sum(old_follow_repeat) as old_follow'),
                          DB::raw('sum(new_unfollow_repeat) as new_unfollow'),DB::raw('sum(old_unfollow_repeat) as old_unfollow'))
                        ->where($where)
                        ->groupBy('o_uid')
                        ->groupBy('y_task_summary.order_id')
                        ->orderBy('date_time','desc')
                        ->get()->toArray();
            $order_count = OrderModel::select('o_uid',DB::raw('avg(o_per_price) as price'))->where($condition)->whereIn('o_uid',$user_id)->whereIn('order_id',$order_id)->groupBy('o_uid')->get()->toArray();
            $count = count($order_count);
            $order_arr = OrderModel::select('o_uid',DB::raw('avg(o_per_price) as price'))->where($condition)->whereIn('o_uid',$user_id)->whereIn('order_id',$order_id)->groupBy('o_uid');
            $order = self::getPages($order_arr,$page,$pagesize,$count);
            $arr['data'] = $data;
            $arr['order'] = $order['data'];
            $arr['count'] = $order['count'];
            $arr['user'] = $user_arr;
            return $arr;
        }else{
            return false;
        }
    }

    //订单截止当前已涨粉数
    public static function orderFans($oid){
        $buss_id = TaskModel::select('buss_id')->where('order_id','=',$oid)->groupBy('buss_id')->get()->toArray();
        $total = 0;
        foreach ($buss_id as $k=>$v){
            $total += Redis::get('tot-'.$oid.'-'.$v['buss_id']);
        }
        return $total;
    }
}