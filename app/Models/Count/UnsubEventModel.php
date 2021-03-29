<?php

namespace App\Models\Count;
use App\Models\CommonModel;
use Illuminate\Support\Facades\Redis;


class UnsubEventModel extends CommonModel{

    protected $table = 'y_unsub_event';

    protected $primaryKey = 'id';

    public $timestamps = false;

    static public function getAddUnsubEventLog($date) {
        $addarray=array(
            'ghid'=>$date['ghid'],
            'oid'=>$date['oid'],
            'openid'=>$date['openid'],
            'mac'=>$date['mac'],
            'event_time'=> date('Y:m:d h:i:s')
        );
        UnsubEventModel::insert($addarray);
        return $date;
    }

    static public function getUnSubEventList($date,$count){
        $where = array();
        if($date){
            $where[] = array( 'event_time', '<',$date);
        }
        return UnsubEventModel::where($where)->take($count)->get()->toArray();
    }

    static public function getUnSubCount($date){
        $where = array();
        if($date){
            $where[] = array( 'event_time', '<',$date);
        }
        return UnsubEventModel::where($where)->count();
    }

    static public function delUnSubEvent($id){
        $where = array();
        if($id){
            $where[] = array( 'id', '=',$id);
        }
        return UnsubEventModel::where($where)->delete();
    }
}