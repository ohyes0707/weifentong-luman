<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/6/16
 * Time: 11:16
 */
namespace App\Models\Admin;
use App\Models\CommonModel;


class AdminLogModel extends CommonModel{

    protected $table = 'admin_log';

    protected $primaryKey = 'id';

    public $timestamps = false;

    public static function orderLogAdd($insert){
        $data = AdminLogModel::insert($insert);
        return $data;
    }

    public static function orderLog($order_id,$uid,$start_date,$end_date,$page,$pagesize){
        if($order_id){
            $where[] = array('order_id','=',$order_id);
            $user = AdminLogModel::select('operator_id','operator')->where($where)->groupBy('operator_id')->get()->toArray();
            if($start_date)
                $where[] = array('datetime','>=',$start_date.' 00:00:01');
            if($end_date)
                $where[] = array('datetime','<=',$end_date.' 23:59:59');
            if($uid)
                $where[] = array('operator_id','=',$uid);
            $data = AdminLogModel::select('datetime','action','operator')->where($where);
            $list = self::getPages($data,$page,$pagesize);
            $arr['data'] = $list['data'];
            $arr['count'] = $list['count'];
            $arr['user'] = $user;
            return $arr;
        }else{
            return false;
        }
    }
}