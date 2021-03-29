<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Services\Impl\Count;
use App\Services\Impl\Common\ScreenServicesImpl;
use App\Models\Count\BussInessModel;
use App\Models\Count\TaskSummaryModel;
use Illuminate\Support\Facades\Redis;
use App\Models\Count\MoneyLogModel;

class SubMerchantServicesImpl {
    
    static public function getSearchSubTaskData($array) {
        $initialize = ScreenServicesImpl::getArray($array);
        $where = ScreenServicesImpl::getWhereArray($initialize,'date_time');
        //先取出所有的子渠道
        $subiness= BussInessModel::getSonList($initialize);
        //再拿出子渠道的统计
        //$newdate = TaskSummaryModel::getSubTimeDate($subiness['buss_id'], date('Y-m-d'), date('Y-m-d'));
        $yesterdaydate = MoneyLogModel::getSubTimeDate($subiness['buss_id'],date("Y-m-d",strtotime("-1 day")),date("Y-m-d",strtotime("-1 day")));
     //   $newqgdate = TaskSummaryModel::getSubTimeDate($subiness['buss_id'],$start,$end);
        $monthdate = MoneyLogModel::getSubTimeDate($subiness['buss_id'],date("Y-m-d",strtotime("-30 day")),date("Y-m-d",strtotime("-1 day")));
        //print_r($subiness);
       $newarray=array();
        foreach ($subiness['data'] as $key => $value) {
            $newarray[$key]['id']=$value['id'];
            $newarray[$key]['username']=$value['username'];
            $id=$value['id'];
            $newarray[$key]['newfans'] = Redis::hget(date('Ymd'),"sum--$id-3")?Redis::hget(date('Ymd'),"sum--$id-3"):0;
            $newarray[$key]['newcancelfans'] = Redis::hget(date('Ymd'),"sum--$id-5")?Redis::hget(date('Ymd'),"sum--$id-5"):0;
            $newarray[$key]['yesterdayfans'] = 0;
            $newarray[$key]['monthfans']= 0;
            $reduce_percent=$value['reduce_percent']/100;
            //$kl=0.1;
//            foreach ($newdate as $key2 => $value2) {
//                if($value2['buss_id']==$newarray[$key]['id']){
//                    $newarray[$key]['newfans'] = self::getAddParam($value2['new_follow_repeat'],$value2['old_follow_repeat'],$reduce_percent);
//                    $newarray[$key]['newcancelfans'] =self::getAddParam( $value2['new_unfollow_repeat'],$value2['old_unfollow_repeat'],$reduce_percent);
//                }
//            }

            $newarray[$key]['newfans'] = floor($newarray[$key]['newfans']*(1-$reduce_percent));
            $newarray[$key]['newcancelfans'] =floor($newarray[$key]['newcancelfans']*(1-$reduce_percent));
            foreach ($yesterdaydate as $key3 => $value3) {
                if($value3['buss_id']==$newarray[$key]['id']){
                    $newarray[$key]['yesterdayfans'] = $value3['follow'];
                }
            }
            foreach ($monthdate as $key4 => $value4) {
                if($value4['buss_id']==$newarray[$key]['id']){
                    $newarray[$key]['monthfans'] = $value4['follow'];
                }
            }
        }
        $arr['count']=$subiness['count'];
        $arr['date']=$newarray;
        return $arr;
        
    }
    
    static public function getSubShopReport($array) {
        $initialize = ScreenServicesImpl::getSetArray($array);
        $where = ScreenServicesImpl::getWhereArray($initialize,'date');
        //筛选取出数据
        $data1= MoneyLogModel::getSubListDate($where,$initialize['page'],$initialize['pagesize']);
        $data2= MoneyLogModel::getSubSumDate($where);
        $price= BussInessModel::getPrice($where['buss_id']);
        $reduce_percent=BussInessModel::getPercent($where['buss_id'])/100;
        $listdata['data2']=array(
        'date'=>'总计',
        'sumfans'=> $data2['follow'],
        'cancelfans'=>$data2['unfollow'],
        //'cancelrate'=>$data2['new_unfollow_repeat']+$data2['old_unfollow_repeat'],
        //'money'=>$data2['new_unfollow_repeat']+$data2['old_unfollow_repeat'],
        );
        $listdata['data2']['cancelrate']= self::getAddDivision($listdata['data2']['cancelfans'], $listdata['data2']['sumfans']);
        $listdata['data2']['money'] = round($data2['num'],2);
        foreach ($data1['data2'] as $key => $value) {
            $listdata['data1'][]=array(
                'date'=>$value['date_time'],
                'sumfans'=>$value['follow'],
                'cancelfans'=>$value['unfollow'],
                'money'=> round($value['num'],2)
            );
        }
        
        foreach ($listdata['data1'] as $key => $value) {
            $listdata['data1'][$key]['cancelrate']= self::getAddDivision($listdata['data1'][$key]['cancelfans'], $listdata['data1'][$key]['sumfans']);
        }
        $listdata['count']=$data1['count'];

        return $listdata;
    }
    
    static public function getHistoryFans($array) {
        
        $initialize = ScreenServicesImpl::getSetArray($array);
        $where = ScreenServicesImpl::getWhereArray($initialize,'date');
        $reduce_percent=BussInessModel::getPercent($where['buss_id'])/100;
        //筛选取出数据
        $listdate= MoneyLogModel::getSubwxListDate($where,$initialize['page'],$initialize['pagesize']);
        $newarray=array();
        foreach ($listdate['date'] as $key => $value) {
            $newarray[]=array(
                'date'=>$value['date'],
                'buss_id'=>$value['buss_id'],
                'username'=>$value['wx_name'],
                'sumfans'=> $value['follow'],
                'cancelfans'=>$value['unfollow'],
               // 'cancelrate'=>$value['new_follow_repeat']+$value['new_follow_repeat'],
            );
        }
        foreach ($newarray as $key => $value){
            $newarray[$key]['cancelrate']=self::getAddDivision($value['cancelfans'],$value['sumfans']);
        }
        $wxnamelist= MoneyLogModel::getSubwxNameList($where);
        $rest['wxlist']=$wxnamelist;
        $rest['date']=$newarray;
        $rest['count'] = $listdate['count'];
        return $rest;
    }
    
    static public function getAddParam($param1,$param2,$buckle=0.1) {
        if($buckle==null||$buckle==0){
            $buckle=0.1;
        }
        return floor(($param1+$param2)*(1-$buckle));
    }
    
    static public function getAddDivision($param1,$param2) {
        if($param2==0||$param2==null){
           return 0; 
        }
        return round($param1/$param2,4)*100;
    }
}
