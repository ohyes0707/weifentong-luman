<?php

namespace App\Models\ThirdData;

use App\Models\CommonModel;
use App\Models\Count\FansLogModel;

class YunDaiSubLogModel extends CommonModel{

    protected $table = 'yundai_sub_log';

    protected $primaryKey = 'id';


    public $timestamps = false;


    /***
     * 插入云袋关注记录
     * @param $bid
     * @param $oid
     * @param $mac
     * @param $openid
     * @param $bmac
     */
    static public function addYundaiSubLog($bid,$oid,$mac,$openid,$bmac,$res){
        $data['bid'] = $bid;
        $data['oid'] = $oid;
        $data['mac'] = $mac;
        $data['openid'] = $openid;
        $data['status'] = $res;
        $data['bmac'] = $bmac;
        $data['date'] = date('Y-m-d H:i:s');
        $model = YunDaiSubLogModel::insert($data);
        
        return 1;
    }
     

    /***
     * 插入云袋关注记录
     * @param $bid
     * @param $oid
     * @param $mac
     * @param $openid
     * @param $bmac
     */
    static public function addYundaiFansLog($bid,$oid,$mac,$openid,$bmac,$res){
        $data['bid'] = $bid;
        $data['oid'] = $oid;
        $data['mac'] = $mac;
        $data['openid'] = $openid;
        $data['bmac'] = $bmac;
        $data['date'] = date('Y-m-d H:i:s');
        $data['ghid'] = 'yundai';
        $data['isold'] = 2;
       // $data['oid'] = 135;

        FansLogModel::insert($data);
        
        return 1;
    }
}