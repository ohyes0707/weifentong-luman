<?php

namespace App\Models\Count;

use App\Models\CommonModel;
use Illuminate\Support\Facades\DB;

class FansLogModel extends CommonModel{

    protected $table;

    protected $primaryKey = 'id';

    public $timestamps = false;

    public function __construct() {
        $this->table='y_fans_log';
    }
    /**
     * 获取第三方平台配置信息
     */
    static public function getAddFansLog($date){
        if(!isset($date["nickname"])){
            $date["nickname"]='';
        }
        $array=array(
            'mac'=>$date['mac'],
            'bid'=>$date['bid'],
            'bmac'=>$date['bmac'],
            'oid'=>$date['order_id'],
            'date'=>date("Y-m-d H:i:s"),
            'openid'=>$date["openid"],
            'ghid'=>$date["ghid"],
            'sex'=>$date["sex"],
            'city'=>$date["city"],
            'nickname'=>$date["nickname"],
            'province'=>$date["province"],
            'isold' => $date['isold'],
            'bid_city'=>$date["bid_city"],
            'bid_province'=>$date["bid_province"],
        );
        if(isset($date["province"])){
            $array['province']=$date["province"];
        }
        if(isset($date["store_id"])&&$date["store_id"]!=''){
            $array['store_id']=$date["store_id"];
        }
        $model = FansLogModel::insert($array);
       // return $model?$model->toArray():null;
    }
    
    /**
     * 获取第三方平台配置信息
     */
    static public function getUpFansLog($date){
        
        $array=array(
            'un_date'=>date("Y-m-d H:i:s"),
        );
        $FansLog = FansLogModel::select()->where('openid',$date['openid'])->where('un_date','1970-01-01 00:00:00')->orderBy('id', 'DESC') ->first();
        if($FansLog==''){
            return FALSE;
        } else {
            $FansLog=$FansLog->toArray();
            $model = FansLogModel::where('id', $FansLog['id'])->update($array);
            return $FansLog;
        }

    }

    /**
     * 检查是否关注
     * @param $openid
     * @return array
     */
    static public function checkSub($openid,$oid,$bid){
        $message = array(
            'error' => 0,
            'subscribe' => 0,
            'message' => 'ok'
        );
        if($openid && $oid && $bid){
            //$where[]=['oid','=',$oid];暂时去掉
            $where[]=['bid','=',$bid];
            $where[]=['openid','=',$openid];
            $list = FansLogModel::select('id')->where($where)->orderBy('id','desc')->first();
            if($list){
                $message = array(
                    'error' => 0,
                    'subscribe' => 1,
                    'message' => 'ok'
                );
            }
        }else{
            $message = array(
                'error' => -1,
                'subscribe' => 0,
                'message' => '缺少必要参数'
            );
        }
        return $message;
    }
    
    static public function getTodayData($orderid,$num) {
        $date=date('Y-m-d');
        switch ($num) {
            case 1:
                $sum= FansLogModel::where('date','>',$date)->where('oid','=',$orderid)->count('openid');
                return $sum;
            case 2:
                $sum= FansLogModel::where('date','>',$date)->where('oid','=',$orderid)->where('un_date','>',$date)->count('openid');
                return $sum;
            default:
                break;
        }
    }
    
    static public function getCapacityList($param) {
        $field = array(
            'bid_province',
            'bid_city',
            DB::raw('count(id) as num')
        );
        $where[]=['date','>=',$param['time']];
        $where[]=['date','<=',date("Y-m-d",strtotime($param['time'])+86400)];
        if(isset($param['sex'])&&$param['sex']!=''){
            $where[]=['sex','=',$param['sex']];
        }
        $model = FansLogModel::select($field)
                ->where($where)
                ->groupBy('bid_province','bid_city')
                ->get();
        return $model?$model->toArray():null;
    }
    
    static public function getCapacitySonList($operat_query) {
        $field = array(
            'bid_province',
            'bid_city',
            DB::raw('count(id) as num')
        );
        $where[]=['date','>=',$operat_query['time']];
        $where[]=['date','<=',date("Y-m-d",strtotime($operat_query['time'])+86400)];
        if(isset($operat_query['sex'])&&$operat_query['sex']!=''){
            $where[]=['sex','=',$operat_query['sex']];
        }
        $model = FansLogModel::select($field)
                ->where('bid_city','like','%'.$operat_query['keycode'].'%')
                ->where($where)
                ->groupBy('bid_province','bid_city')
                ->get()
                ->first();
        return $model?$model->toArray():null;
    }
    
    static public function getCapacityDescList($bid_province,$bid_city,$pbdata,$operat_query) {
        $newarray=array(
            
        );
        foreach ($bid_province as $key => $value) {
            $newarray[$key]=$value;
            $newarray[$key]['db_capacity_num']=0;
            foreach ($bid_city as $key2 => $value2) {
                if($value2['province_name'] == $value['province_name']&&$value2['city_name']!=''){
                    $newarray[$key]['list'][$key2] = $value2;
                    $newarray[$key]['list'][$key2]['db_capacity_num'] = 0;
                    foreach ($pbdata as $key3 => $value3) {
                        if($value3['bid_province'] == $value['province_name'] && $value3['bid_city'] == $value2['city_name']){
                            $newarray[$key]['list'][$key2]['db_capacity_num'] = $value3['num'];
                        }
                    }
                }
            }
            foreach ($pbdata as $key3 => $value3) {
                if($value3['bid_province'] == $value['province_name']){
                    $newarray[$key]['db_capacity_num']=$newarray[$key]['db_capacity_num']+$value3['num'];
                }
            }
        }
        
        if($newarray[0]['province_name'] == '全国'){
            $newarray[0]['db_capacity_num']= self::getCapacityNum($operat_query);
        }
        return $newarray;
    }
    
    static public function getCapacityNum($operat_query) {
        
        $where[]=['date','>=',$operat_query['time']];
        $where[]=['date','<=',date("Y-m-d",strtotime($operat_query['time'])+86400)];
        if(isset($operat_query['sex'])&&$operat_query['sex']!=''){
            $where[]=['sex','=',$operat_query['sex']];
        }
        $model = FansLogModel::select('id')
                ->where($where)
                ->count();
        return $model?$model:null;
    }

    //订单粉丝详情
    static public function orderFans($order_id,$start_date,$end_date){
        if($order_id){
            $where[] = array('id','>',0);
            if($start_date)
                $where[] = array('date','>=',$start_date);
            if($end_date)
                $where[] = array('date','<=',$end_date);
            $list = FansLogModel::select('openid','date','nickname','un_date','province','city','sex')->where($where)->where('oid','=',$order_id)->get()->toArray();
            return $list;
        }else{
            return false;
        }
    }
}