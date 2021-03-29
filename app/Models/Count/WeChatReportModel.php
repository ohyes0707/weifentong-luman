<?php

namespace App\Models\Count;
use App\Lib\WeChat\Third;
use App\Models\Buss\BussModel;
use App\Lib\WeChat\Wechat;
use App\Models\CommonModel;
use App\Services\Impl\Wechat\WechatServicesImpl;
use App\Services\Impl\Wechat\WeChatReportServicesImpl;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Models\Count\WechatOrderModel;

class WeChatReportModel extends CommonModel{

    protected $table='y_task_summary';

    protected $primaryKey = 'id';

    public $timestamps = false;

    //微信数据以平台为维度
    static public function getReportList($where,$page,$pagesize){
        if(isset($where['excel'])){
            $excel = $where['excel'];
            unset($where['excel']);
        }
        
        //查询y_task_summary表里的数据
        $model= WeChatReportModel::select(DB::raw('GROUP_CONCAT(y_task_summary.order_id) as order_id_str'),DB::raw('SUM(IFNULL(y_task_summary.new_follow_repeat,0)+IFNULL(y_task_summary.old_follow_repeat,0)) as follow_repeat') , DB::raw('SUM((IFNULL(y_task_summary.new_follow_repeat,0)+IFNULL(y_task_summary.old_follow_repeat,0))*y_order.o_per_price) as flowing_water'),'y_task_summary.date_time',DB::raw('MIN(y_order.o_per_price) as o_per_price'))
                ->rightJoin('y_order','y_task_summary.order_id','=','y_order.order_id')
                ->where($where)
                ->groupBy('y_task_summary.date_time')
                ->orderBy('y_task_summary.date_time','DESC');

                // var_dump($model);die;
        $count = count($model->get()->toArray());
        if(isset($excel)){
            if($excel==1){
                $pagesize = $count;
            }
        }
        
        $data= self::getPages($model,$page,$pagesize,$count);
        return $data;
    }

    //微信数据以公众号为维度
    static public function getWxASumtwhere($where,$page,$pagesize){
        //分页判断
        $date_time = $where['date_time'];
        $order = $where['order']['order_id_str'];
        $order_id = explode(',',$order);
        $pagesize = 10;
        $model= WeChatReportModel::select(DB::raw('SUM(IFNULL(y_task_summary.new_follow_repeat,0)+IFNULL(y_task_summary.old_follow_repeat,0)) as follow_repeat') , DB::raw('SUM((IFNULL(y_task_summary.new_follow_repeat,0)+IFNULL(y_task_summary.old_follow_repeat,0))*y_order.o_per_price) as flowing_water'),'y_task_summary.date_time')
                ->rightJoin('y_order','y_task_summary.order_id','=','y_order.order_id')
                ->whereIn('y_order.order_id',$order_id)
                ->where($date_time)
                ->groupBy('y_task_summary.date_time')
                ->orderBy('y_task_summary.date_time','DESC');
                // ->having(DB::raw('SUM(IFNULL(y_task_summary.new_follow_repeat,0)+IFNULL(y_task_summary.old_follow_repeat,0))') ,'<>',0);
        $count = count($model->get()->toArray());
    
        if(empty($where['wx_id'])){
            $pagesize = $count;
        }
        $data= self::getPages($model,$page,$pagesize,$count);
// var_dump($data);die;
        $data_sum['order'] = $where['order'];
        $data_sum['order']['wx_sum'] = 0;
        $data_sum['order']['wx_water_sum'] = 0;
        $data_sum['order']['follow_repeat_sum'] = 0;
        $data_sum['order']['flowing_water_sum'] = 0;
        $data_sum['order']['flowing_fans_water'] = 0;
        if(strstr($data_sum['order']['order_id_str'],',')){
            $data_sum['order']['color'] = 'blue';
        }
        if(count($data['data'])>0){
            $newarray=$data['data'];
            foreach ($newarray as $key => $value) {
                if($value['date_time']!=''){
                    
                    /*先查看Wxstatistics表里是否存储数据*/
                    $data_wx_one = WxstatisticsWxModel::getWxstatisticsWxList($data_sum['order'],$value['date_time']);
                    if(!empty($data_wx_one) ){
                        $data_wx = $data_wx_one;
                    }else{
                        /*查询微信数据*/
                        $data_wx = WeChatReportModel::getWxDataSumtwhere($data_sum['order'],$value['date_time']);

                        /*编辑插入数据和更新数据*/
                        $add_data = $data_wx+$value;
                        $data_wx['flowing_fans_water'] = round($add_data['flowing_water']-$add_data['new_fans_water'],2);
                        //日期
                        $add['date_time'] = $add_data['date_time'];
                        //成功关注
                        $add['follow_repeat'] = $add_data['follow_repeat'];
                        //流水
                        $add['flowing_water'] = round($add_data['flowing_water'],2);
                        //微信关注
                        $add['new_fans'] = $add_data['new_fans'];
                        //微信id
                        $add['wx_id'] = $data_sum['order']['xid'];
                        //微信关注流水
                        $add['new_fans_water'] = round($add_data['new_fans_water'],2);
                        //流水增幅
                        $add['flowing_fans_water'] = $data_wx['flowing_fans_water'];

                        /*对Wxstatistics表里的数据微信数据进行更新*/
                        if(!empty($data_wx_one)){
                            $add['id'] = $data_wx_one['id'];
                            $data_wx_one_up = WxstatisticsWxModel::updateWxstatisticsWx($add);
                            unset($add);
                        }else{
                            $data_wx_one_add = WxstatisticsWxModel::addWxstatisticsWx($add); 
                        }
                    }

                    /*数据重组*/
                    $data_wx['xid'] = $data_sum['order']['xid'];
                    //微信关注
                    $data_sum['order']['wx_sum'] += $data_wx['new_fans'];
                    //微信关注流水
                    $data_sum['order']['wx_water_sum'] += $data_wx['new_fans_water'];
                    //成功关注
                    $data_sum['order']['follow_repeat_sum'] = $data_sum['order']['follow_repeat_sum'] + $value['follow_repeat'];
                    //流水
                    $data_sum['order']['flowing_water_sum'] = $data_sum['order']['flowing_water_sum'] + $value['flowing_water'];

                    $data_total[] = $data_wx+$value;
                }
            }
            //流水增幅
            $data_sum['order']['flowing_fans_water'] = round($data_sum['order']['flowing_water_sum']-$data_sum['order']['wx_water_sum'],2);
            $data_sum['list'] = $data_total;
            return $data_sum;
        }
        return $data_sum;
    }
    
    //微信数据以公众号为维度
    static public function getWxDataSumtwhere($order,$date_time){
        if($order){
            $data_wx['begin_date'] = $date_time;
            $data_wx['end_date'] = $date_time;
            $options['access_token'] = WechatServicesImpl::getToken($order['xid']);
            
            $Wechat = new Wechat($options);
            // var_dump($data_wx,$options);
            $data_wx_sum = $Wechat->getShopWifi($data_wx['begin_date'],$data_wx['end_date'],$order['shopid']);
            //获取新增数据
            if(empty($data_wx_sum['data'])){
                $data_wx['new_fans'] = 0;
                $data_wx['new_fans_water'] = 0;
            }else{
                $data_wx['new_fans'] = $data_wx_sum['data'][0]['new_fans'];
                $data_wx['new_fans_water'] = $data_wx_sum['data'][0]['new_fans']*$order['price'];  
            }
            return $data_wx;
        }
    }

    //取出存在门店的日期
    static public function getBackDate($where,$page,$pagesize){
        $date_time = $where['date_time'];
        // var_dump($date_time);die;
        $model = WeChatReportModel::select('y_task_summary.id','y_task_summary.date_time','wx_info.status as wx_status')
        ->leftJoin('wx_info','y_task_summary.wx_id','=','wx_info.id')
        ->where('wx_info.status','=',2)
        ->where($date_time)
        ->groupBy('y_task_summary.date_time')
        ->orderBy('y_task_summary.date_time','DESC');
        // ->get()
        // ->first()
        // ->toArray()
        $count = count($model->get()->toArray());
        $data_wx= self::getPages($model,$page,$pagesize,$count);
        return $data_wx;
    }

    //根据日期取出相应的公众号
    static public function getBackFilllist($where){
        $date_time = $where['date_time'];
        $data_wx = WeChatReportModel::select('y_task_summary.date_time','wx_info.wx_name','wx_info.id as wx_id')
        ->leftJoin('wx_info','y_task_summary.wx_id','=','wx_info.id')
        ->where('y_task_summary.date_time','=',$date_time)
        ->where('wx_info.status','=',2)
        ->groupBy('wx_info.id')
        ->get()
        // ->first()
        ->toArray();
        return $data_wx;
    }
    
    //根据日期取出相应的公众号
    static public function getBackEdit($where){
        $date_time = $where['datetime'];
        $data_wx = WeChatReportModel::select('y_task_summary.date_time','wx_info.wx_name','wx_info.id as wx_id')
        ->leftJoin('wx_info','y_task_summary.wx_id','=','wx_info.id')
        ->where('y_task_summary.date_time','=',$date_time)
        ->where('wx_info.status','=',2)
        ->groupBy('wx_info.id')
        ->get()
        // ->first()
        ->toArray();
        return $data_wx;
    }

    //根据公众号获取buss_id
    static public function getBuss($where){
        $date_time = $where['date_time'];
        $wx_id = $where['wx_id'];
        $data_wx = WeChatReportModel::select('y_task_summary.id','y_task_summary.buss_id as bid','buss_info.nick_name as bname')
        ->leftJoin('bussiness','y_task_summary.buss_id','=','bussiness.id')
        ->leftJoin('buss_info','y_task_summary.buss_id','=','buss_info.bid')
        ->where('y_task_summary.date_time','=',$date_time)
        ->where('y_task_summary.wx_id','=',$wx_id)
        ->groupBy('y_task_summary.buss_id')
        ->get()
        // ->first()
        ->toArray();
        return $data_wx;
    }

    //获取order信息
    static public function getOrder($where){
        $date_time = $where['datetime'];
        $wx_id = $where['wx_id'];
        $bid = $where['bid'];
        $data_wx = WeChatReportModel::select('y_task_summary.id','y_task_summary.wx_id','y_task_summary.buss_id as bid','y_task_summary.order_id as oid','y_task_summary.new_follow_repeat','y_task_summary.new_nbg')
        ->where('y_task_summary.date_time','=',$date_time)
        ->where('y_task_summary.wx_id','=',$wx_id)
        ->where('y_task_summary.buss_id','=',$bid)
        ->get()
        ->first()
        ->toArray();
        // var_dump($data_wx);die;
        return $data_wx;
    }

    //更新回填数据
    public static function updateSum($getdata,$id){
        $result = WeChatReportModel::where('id', $id)
        ->update($getdata);
        return $result;
    }

    static public function success_getwx_total($mapp){
        $bid = $mapp['bid'];
        $date_time = $mapp['pdate'];
        $request=WeChatReportModel::select(DB::raw('SUM(IFNULL(new_getwx_repeat,0)+IFNULL(old_getwx_repeat,0)) as success_getwx_total'),DB::raw('SUM(IFNULL(new_complet_repeat,0)+IFNULL(old_complet_repeat,0)) as complet_total'))
              ->where('buss_id','=',$bid)
              ->where('date_time','=',$date_time)
              ->get()->first();
        return $request?$request->toArray():null;
    }
    
    
}