<?php

namespace App\Models\Count;

use App\Models\CommonModel;
use Illuminate\Support\Facades\DB;

class MoneyLogModel extends CommonModel{

    protected $table = 'y_money_log';

    protected $primaryKey = 'id';

    public $timestamps = false;


    static public function getMoneyLog($date){
        $date['addtime']= date('Y-m-d H-i-s');
         MoneyLogModel::insert($date);
    }

    static public function getMoneyLogOne($where){
        $model = MoneyLogModel::where($where)->first();
        return $model?true:false;
    }
    
    static public function getSubTimeDate($bussid,$start,$end) 
    {
        $where[]=['date','>=',$start];
        $where[]=['date','<=',$end];
        //不用分页
        $selectarray=array(
            'buss_id',
            DB::raw('sum(follow) as follow'),
            DB::raw('sum(unfollow) as unfollow')
        );
        $model= MoneyLogModel::select($selectarray)
                ->where($where)
                ->whereIn('buss_id',$bussid)
                ->groupBy('buss_id')
                ->get();
        return $model?$model->toArray():null;
    }
    
    
    static public function getSubListDate($where,$page,$pagesize) 
    {
        $retuen['count']=MoneyLogModel::select()
            ->where($where)
            ->count(DB::raw('DISTINCT date'));
        //不用分页
        $selectarray=array(
            'buss_id',
            'date as date_time',
            DB::raw('sum(follow) as follow'),
            DB::raw('sum(unfollow) as unfollow'),
            DB::raw('sum(num) as num'),
        );
        $model= MoneyLogModel::select($selectarray)
                ->where($where)
                ->groupBy('buss_id','date')
                ->orderBy('date','desc');
        $data= self::getGroupPages($model,$page,$pagesize,$retuen['count']);
        $retuen['data2']=$data['data'];
        return $retuen;
    }
    
    
    static public function getSubSumDate($where) 
    {
        //不用分页
        $selectarray=array(
            'buss_id',
            DB::raw('sum(follow) as follow'),
            DB::raw('sum(unfollow) as unfollow'),
            DB::raw('sum(num) as num'),
            'y_order.o_per_price'
        );
        $model= MoneyLogModel::select($selectarray)
                ->leftJoin('bussiness','y_money_log.buss_id','=','bussiness.id')
                ->leftJoin('y_order','y_money_log.order_id','=','y_order.order_id')
                ->where($where)
                ->orderBy('date','desc')
                ->get()
                ->first()
                ->toArray();
        return $model;
    }
    
    static public function getSubwxListDate($where,$page,$pagesize) 
    {
        $retuen['count']=MoneyLogModel::select()
            ->where($where)
            ->count(DB::raw('DISTINCT wx_id,date'));
        $selectarray=array(
            'buss_id',
            'date',
            DB::raw('sum(follow) as follow'),
            DB::raw('sum(unfollow) as unfollow'),
            'wx_name'
        );
        $model= MoneyLogModel::select($selectarray)
                ->leftjoin('wx_info','wx_info.id','=','y_money_log.wx_id')
                ->where($where)
                ->groupBy('wx_id','date')
                ->orderBy('date','desc');
        $data= self::getGroupPages($model,$page,$pagesize, $retuen['count']);
        $retuen['date']=$data['data'];
        $retuen['count']=$retuen['count'];
        return $retuen;
    }
    
    static public function getSubwxNameList($where) {
        $wx_name = MoneyLogModel::leftJoin('wx_info','wx_info.id','=','y_money_log.wx_id')
            ->select('wx_id','wx_name')
            ->where($where)
            ->groupBy('wx_id')
            ->get();
        return $wx_name?$wx_name->toArray():null;
    }

    //获取order信息
    static public function getOrder_backups($where){
        // var_dump($where);die;
        $date_time = $where['datetime'];
        $wx_id = $where['wx_id'];
        $bid = $where['bid'];
        $model = MoneyLogModel::select('y_money_log.id','y_money_log.price','y_money_log.oldmoney','y_money_log.wx_id','y_money_log.buss_id as bid','y_money_log.order_id as oid','y_money_log.num','y_money_log.newmoney','y_money_log.follow')
        ->where('y_money_log.date','=',$date_time)
        ->where('y_money_log.wx_id','=',$wx_id)
        ->where('y_money_log.buss_id','=',$bid);

        $data= self::getPages($model,1,1);
        if($data['data']){
            return $data['data'][0];
        }else{
            return '未获取money数据';
        }
        
    }

    //更新回填数据
    public static function updateMon($getdata,$id){
        $result = MoneyLogModel::where('id', $id)
        ->update($getdata);
        var_dump($result);die;
        return $result;
    }
}