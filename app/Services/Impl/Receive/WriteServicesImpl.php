<?php

namespace App\Services\Impl\Receive;

use App\Models\Count\BussInessModel;
use App\Models\Count\GetWxSummaryModel;
use App\Models\Count\MoneyLogModel;
use App\Models\Count\OrderModel;
use App\Models\Count\RuserInfoModel;
use App\Models\Count\TaskSummaryModel;
use App\Services\UserServices;
use Illuminate\Support\Facades\Redis;
use App\Models\Count\GetWxLogModel;
use App\Models\Order\TaskModel;
use App\Models\Count\TaskStoreSummaryModel;
use App\Models\Count\StoreModel;

class WriteServicesImpl implements UserServices
{
    /**
     * getAddTaskData
     * @param $userId
     * @return null
     */
    static public function getAddTaskData()
    {
        //$date= date('Ymd');
        //$date=20170630;
        $date = date('Ymd',strtotime("-1 day"));
        //$date = date('Ymd');
        $array= Redis::hgetall($date);
        $oldIdArr = Redis::hgetall('brandoid');
        $i=1;
        foreach ($array as $key => $value) {
            
            //Redis::hdel($date,$key);
            $type= substr($key, 0, 3);
            switch ($type) {
                case 'sum':
                   // self::getUptaskSummary($key, $value,$date);
                    break;
                case 'old':
                    if(strpos($key,'sid') !== false){
                        self::getUptaskStoreSummary($key, $value,$date, $oldIdArr);
                    } else {
                        //判断订单不是为自己涨粉
                        $orderid=explode('-',$key)[1];
                        if(!isset($oldIdArr[$orderid])){
                            self::getUptaskSummary($key, $value,$date);
                        }
                    }
                    break;
                case 'new':
                    if(strpos($key,'sid') !== false){
                        self::getUptaskStoreSummary($key, $value,$date, $oldIdArr);
                    } else {
                        //判断订单不是为自己涨粉
                        $orderid=explode('-',$key)[1];
                        if(!isset($oldIdArr[$orderid])){
                            self::getUptaskSummary($key, $value,$date);
                        }
                    }
                    break;
                case 'sex':
                    if(strpos($key,'sid') !== false){
                        self::getUpSextaskStoreSummary($key, $value,$date, $oldIdArr);
                    } else {
                        //判断订单不是为自己涨粉
                        $orderid=explode('-',$key)[1];
                        if(!isset($oldIdArr[$orderid])){
                            self::getUpSextaskSummary($key, $value,$date);
                        }
                    }
                    
                    break;
                case 'nqg':
                    if(strpos($key,'sid') !== false){
                        self::getQgtaskStoreSummary($key, $value,$date, $oldIdArr);
                    } else {
                        //判断订单不是为自己涨粉
                        $orderid=explode('-',$key)[1];
                        if(!isset($oldIdArr[$orderid])){
                            self::getQgtaskSummary($key, $value,$date);
                        }
                    }
                    
                    break;
                default:
                    break;
            }
            $i++;
            if($i>1000){
                return 1;
            }
            Redis::hset($date.'copy',$key,$value);
            Redis::hdel($date,$key);
        }
    }
    
    static public function getUpSextaskSummary($key,$value,$date) {
        $array=explode('-',$key);
        $type=$array[0];
        $orderid = $array[1];
        $bid = $array[2];
        $behavior= self::getConvertBehavior($array[3]);
        //是老用户还是新用户
        $isold=self::getConvertOld($array[4]);
        
        if(!isset($array[5])){
            $array[5]=null;
        }
        //是男是女还是未知
        $issex=self::getConvertSex($array[5]);
        
        if($bid==''||$orderid==''){
            return FALSE;
        }
        $wherearray= array(
            'order_id'=>$orderid,
            'buss_id'=>$bid,
            'date_time'=> date('Y-m-d', strtotime($date))
        );
        $uparry=array(
            $isold.'_'.$issex=>$value
        );
        $TaskSummaryarray= TaskSummaryModel::getSearchtaskSummary($wherearray);
        if($TaskSummaryarray){
            TaskSummaryModel::getUptaskSummary($wherearray, $uparry);
        } else {
            $wxid= OrderModel::getWxId($orderid);
            $wherearray['wx_id']=$wxid;
            $wherearray['parent_id']= BussInessModel::getParentId($orderid,$bid);
            $wherearray['one_price']= TaskModel::getOnePrice($orderid,$bid);
            if($wherearray['parent_id'] == 0){
                $wherearray['parent_id'] = $bid;
            }
            $wherearray[$isold.'_'.$issex]=$value;
            TaskSummaryModel::getAddtaskSummary($wherearray);
        }
        //print_r($uparry);
    }
    
    static public function getQgtaskSummary($key,$value,$date) {
        $array=explode('-',$key);
        $type= $array[0];
        $orderid = $array[1];
        $bid = $array[2];
        $isold=$array[3];
        
        if($orderid==''){
            return FALSE;
        }
        $wherearray= array(
            'order_id'=>$orderid,
            'buss_id'=>$bid,
            'date_time'=> date('Y-m-d', strtotime($date))
        );
        if($isold==1){
            $uparry['now_cancel_new']=$value; 
        }
        if($isold==2){
            $uparry['now_cancel_old']=$value; 
        }
        
        $TaskSummary= TaskSummaryModel::getSearchtaskSummary($wherearray);
        if($TaskSummary){
            TaskSummaryModel::getUptaskSummary($wherearray, $uparry);
        } else {
            $wxid= OrderModel::getWxId($orderid);
            $wherearray['wx_id']=$wxid;
            $wherearray['parent_id']= BussInessModel::getParentId($orderid,$bid);
            $wherearray['one_price']= TaskModel::getOnePrice($orderid,$bid);
            if($wherearray['parent_id'] == 0){
                $wherearray['parent_id'] = $bid;
            }
            if($isold==1){
                $wherearray['now_cancel_new']=$value; 
            }
            if($isold==2){
                $wherearray['now_cancel_old']=$value; 
            }
            TaskSummaryModel::getAddtaskSummary($wherearray);
        }
        //print_r($uparry);
    }
    
    static public function getUptaskSummary($key,$value,$date) {
        $array=explode('-',$key);
        $type=$array[0];
        $orderid = $array[1];
        $bid = $array[2];
        $behavior= self::getConvertBehavior($array[3]);
        $only=self::getConvertOnly($array[4]);
        
        if($array[3]==6){
            
        }elseif(!isset($array[0])||$array[0]==''||$orderid==''||$bid==''){
            return FALSE;
        } else {
            
        }
        
        $wherearray= array(
            'order_id'=>$orderid,
            'buss_id'=>$bid,
            'date_time'=> date('Y-m-d', strtotime($date))
        );
        $uparry=array(
            $type.'_'.$behavior.'_'.$only=>$value
        );
        
        switch ($behavior) {
            case 'sumgetwx':
                $wherearray['bid']=$wherearray['buss_id'];
                unset($wherearray['order_id']); 
                unset($wherearray['buss_id']); 
                $GetWxSummaryarray= GetWxSummaryModel::getSearchGetWxSummary($wherearray);
                if($GetWxSummaryarray){
                    GetWxSummaryModel::getUpGetWxSummary($wherearray, $uparry);
                } else {
                    $wherearray[$type.'_'.$behavior.'_'.$only]=$value;
                    GetWxSummaryModel::getGetWxSummary($wherearray);
                }

                break;

            default:
                $TaskSummaryarray= TaskSummaryModel::getSearchtaskSummary($wherearray);
                if($TaskSummaryarray){
                    TaskSummaryModel::getUptaskSummary($wherearray, $uparry);
                } else {
                    //$wxid= OrderModel::getWxId($orderid);
                    $wherearray['wx_id']=OrderModel::getWxId($orderid);
                    $wherearray['parent_id']= BussInessModel::getParentId($orderid,$bid);
                    $wherearray['one_price']= TaskModel::getOnePrice($orderid,$bid);
                    if($wherearray['parent_id'] == 0){
                        $wherearray['parent_id'] = $bid;
                    }
                    if($wherearray['parent_id']==null){
                        break;
                    }
                    $wherearray[$type.'_'.$behavior.'_'.$only]=$value;
                    TaskSummaryModel::getAddtaskSummary($wherearray);
                }
                break;
        }

     //   print_r($uparry);
    }

    static public function getAddUserinfo() 
    {
        $num = Redis::get('oldadduserinfo')?Redis::get('oldadduserinfo'):1;
        
        if( Redis::get('oldadduserinfo')==null){
            $num = 1;
            Redis::set('oldadduserinfo',1);
        }
        Redis::incr('oldadduserinfo');
        $array = RuserInfoModel::getSomeUserInfo($num,60);
        if($array!=null){
            foreach ($array as $key => $value) {
                $ruserinfo = Redis::hgetall($value->mac);
                $ruserinfo['device_type'] = $value->device_type;
                $ruserinfo['sex'] = $value->sex;
                $ruserinfo['province'] = $value->province;
                $ruserinfo['city'] = $value->city;
                if(!isset($ruserinfo['ghid'])){
                    $ruserinfo['ghid']='';
                }
                if(!isset($ruserinfo['follownum']))
                {
                    $ruserinfo['follownum']=1;
                } 
                $ghidarray = explode(',', $value->old_wechat);
                foreach ($ghidarray as $val) {
                    if(stripos($ruserinfo['ghid'], $val)!==FALSE){
                        
                    }else{
                        $ruserinfo['ghid'] = $ruserinfo['ghid'].','.$val;
                    }
                }
                Redis::hmset($value->mac,$ruserinfo);
            }
        }
        return $num;
    }
    
    static public function getDelUserinfo() 
    {
        $start = Redis::get('olduserinfostart');
        $end = Redis::get('olduserinfoend');
        $num = Redis::get('olduserinfo');
        if($num>$end){
            die();
        }
        if( Redis::get('olduserinfo')==null){
            Redis::set('olduserinfo',$start);
        }
        Redis::incr('olduserinfo');
        $array = RuserInfoModel::getSomeUserInfo($num,1000);
        if($array!=null){
            foreach ($array as $key => $value) {
                $ruserinfo = Redis::hgetall($value->mac);
                if(!isset($ruserinfo['follownum'])||$ruserinfo['follownum']<1)
                {
                    //清除这个key
                    Redis::del($value->mac);
                } 
                
            }
        }
        return $num;
    }

    static public function getConvertBehavior($num) {
        switch ($num) {
            case 1:
                return 'getwx';
                break;
            case 2:
                return 'complet';
                break;
            case 3:
                return 'follow';
                break;
            case 4:
                return 'end';
                break;
           case 5:
                return 'unfollow';
                break;
           case 6:
                return 'sumgetwx';
                break;
            default:
                break;
        }
    }
    
    static public function getConvertOnly($num){
        switch ($num) {
            case 1:
                return 'repeat';
            case 2:
                return 'only';

            default:
                break;
        }
    }

    static public function getConvertOld($num){
        switch ($num) {
            case 1:
                return 'old';
                break;
            case 2:
                return 'new';

            default:
                break;
        }
    }
    
    static public function getConvertSex($num=null){
        switch ($num) {
            case 1:
                return 'boy';
            case 2:
                return 'girl';

            default:
                return 'nbg';
        }
    }
    
    
    /**
     * getAddTaskData
     * @param $userId
     * @return null
     */
    static public function getTestData()
    {
        //$array= TaskSummaryModel::find(3);
        Redis::select(1);
        $array=Redis::SCAN(1);
        //$array=Redis::HSCAN('20170713',1);
        print_r($array);
    }
    
        
    /**
     * getAddTaskData
     * @param $userId
     * @return null
     */
    static public function getAddmoney()
    {
        $date=isset($_GET['date'])?$_GET['date']:date("Y-m-d",strtotime("-1 day"));
        //print_r($date);
        $where[0]=['date_time','=',$date];
        //先取出所有导入的数据
        $timedate=TaskSummaryModel::getTimeDate($where);
        //循环，把取出的数据插入数据库，判断是否在日志表里存在
        foreach ($timedate as $value) {
            
            //扣量,单价
            $bussid['buss_id']=$value['buss_id'];
            $bussid['order_id']=$value['order_id'];
            $bussid['parent_id']=$value['parent_id'];
            $bussid['wx_id']=$value['wx_id'];
            $bussid['date']=$date;
            $pdone=MoneyLogModel::getMoneyLogOne($bussid);
            if(!$pdone){
                $bussid['price']= self::getPrice($value['o_per_price'],$value['cost_price'],$value['one_price']);
                $bussid['reduce_percent']= self::getBuckle($value['reduce_percent']);
                $bussid['getwx'] = self::getAddParam($value['new_getwx_repeat'] , $value['old_getwx_repeat'],$bussid['reduce_percent']);
                $bussid['complet'] = self::getAddParam($value['new_complet_repeat'] , $value['old_complet_repeat'],$bussid['reduce_percent']);
                $bussid['follow'] = self::getAddParam($value['new_follow_repeat'] , $value['old_follow_repeat'],$bussid['reduce_percent']);
                $bussid['end'] = self::getAddParam($value['new_end_repeat'] , $value['old_end_repeat'],$bussid['reduce_percent']);
                $bussid['unfollow'] = self::getAddParam($value['new_unfollow_repeat'] , $value['old_unfollow_repeat'],$bussid['reduce_percent']);
                $num=$bussid['follow']*$bussid['price'];
                $money= BussInessModel::getBussMoney($bussid['buss_id']);
                $bussid['num']=$num;
                $bussid['oldmoney']=$money;
                $bussid['newmoney']=$money+$num;
                MoneyLogModel::getMoneyLog($bussid);
                BussInessModel::getUpMoney($bussid['buss_id'], $bussid['newmoney']);
                //upda;
            }
        }
    }
    
    //获取单价
    static public function getPrice($param1,$param2,$param3) {
        //$param1是订单的钱,$param2是渠道一口价
        if($param3!=''&&$param3>0){
            return $param3;
        }
        if($param2!=''&&$param2>0){
            return $param2;
        }
            return $param1;

    }
    
    //获取扣量,0.1
    static public function getBuckle($param1) {
        //$param1渠道扣量百分比
        if($param1!=''){
            return $param1/100;
        }
        return 0;
    }
    
    //计算价格
    static public function getAddParam($param1,$param2,$buckle=0.1) {
        if($buckle==null||$buckle==0){
            $buckle=0;
        }
        return floor(($param1+$param2)*(1-$buckle));
    }
    
    //计算粉丝数
    static public function getAddDivision($param1,$param2) {
        if($param2==0||$param2==null){
           return 0; 
        }
        return round($param1/$param2,4)*100;
    }
    
    static public function getDelUserinfoid() 
    {
        $idarr = GetWxLogModel::getInitialId();
        Redis::set('olduserinfo',$idarr['start']);
        //Redis::set('olduserinfostart',$idarr['start']);
        Redis::set('olduserinfoend',$idarr['end']);
    }
    
    static public function getUptaskStoreSummary($key,$value,$date, $oldIdArr) {
        $array=explode('-',$key);
        $type=$array[0];
        $orderid = $array[1];
        $bid = str_replace('sid', '', $array[2]);
        $behavior= self::getConvertBehavior($array[3]);
        $only=self::getConvertOnly($array[4]);
        
        if($array[3]==6){
            
        }elseif(!isset($array[0])||$array[0]==''||$orderid==''||$bid==''){
            return FALSE;
        } else {
            
        }
        $wherearray= array(
            'order_id'=>$orderid,
            'store_id'=>$bid,
            'date_time'=> date('Y-m-d', strtotime($date))
        );
        $uparry=array(
            $type.'_'.$behavior.'_'.$only=>$value
        );
        
        switch ($behavior) {
            case 'sumgetwx':

                break;

            default:
                $TaskSummaryarray= TaskStoreSummaryModel::getSearchtaskSummary($wherearray);
                if($TaskSummaryarray){
                    TaskStoreSummaryModel::getUptaskSummary($wherearray, $uparry);
                } else {
                    $wxid = OrderModel::getWxId($orderid);
                    $wherearray['wx_id'] = $wxid;
                    $storeInfo = StoreModel::getStoreInfo($bid);
                    if($storeInfo == null){
                        break;
                    }
                    $wherearray['buss_id']= $storeInfo['bid'];
                    $wherearray['brand_id']= $storeInfo['brand_id'];
                    $wherearray['parent_id']= $storeInfo['pid'];
                    if(isset($oldIdArr[$orderid])){
                        $wherearray['order_type'] = 1;
                        $wherearray['one_price'] = 1;
                    } else {
                        $wherearray['order_type'] = 2;
                        $wherearray['one_price'] = TaskModel::getOnePrice($orderid, $storeInfo['bid']);
                    }
                    $wherearray[$type.'_'.$behavior.'_'.$only]=$value;
                    TaskStoreSummaryModel::getAddtaskSummary($wherearray);
                }
                break;
        }
     //   print_r($uparry);
    }
    
    static public function getUpSextaskStoreSummary($key,$value,$date, $oldIdArr) {
        $array=explode('-',$key);
        $type=$array[0];
        $orderid = $array[1];
        $bid = str_replace('sid', '', $array[2]);
        $behavior= self::getConvertBehavior($array[3]);
        //是老用户还是新用户
        $isold=self::getConvertOld($array[4]);
        
        if(!isset($array[5])){
            $array[5]=null;
        }
        //是男是女还是未知
        $issex=self::getConvertSex($array[5]);
        
        if($bid==''||$orderid==''){
            return FALSE;
        }
        $wherearray= array(
            'order_id'=>$orderid,
            'store_id'=>$bid,
            'date_time'=> date('Y-m-d', strtotime($date))
        );
        $uparry=array(
            $isold.'_'.$issex=>$value
        );
        $TaskSummaryarray= TaskStoreSummaryModel::getSearchtaskSummary($wherearray);
        if($TaskSummaryarray){
            TaskStoreSummaryModel::getUptaskSummary($wherearray, $uparry);
        } else {
            $wxid= OrderModel::getWxId($orderid);
            $wherearray['wx_id']=$wxid;
            $storeInfo = StoreModel::getStoreInfo($bid);
            if($storeInfo == null){
                return 1;
            }
            $wherearray['buss_id']= $storeInfo['bid'];
            $wherearray['brand_id']= $storeInfo['brand_id'];
            $wherearray['parent_id']= $storeInfo['pid'];
            if(isset($oldIdArr[$orderid])){
                $wherearray['order_type'] = 1;
                $wherearray['one_price'] = 1;
            } else {
                $wherearray['order_type'] = 2;
                $wherearray['one_price'] = TaskModel::getOnePrice($orderid, $storeInfo['bid']);
            }
            $wherearray[$isold.'_'.$issex]=$value;
            TaskStoreSummaryModel::getAddtaskSummary($wherearray);
        }
        //print_r($uparry);
    }
    
    static public function getQgtaskStoreSummary($key,$value,$date, $oldIdArr) {
        $array=explode('-',$key);
        $type= $array[0];
        $orderid = $array[1];
        $bid = str_replace('sid', '', $array[2]);
        $isold=$array[3];
        
        if($orderid==''){
            return FALSE;
        }
        $wherearray= array(
            'order_id'=>$orderid,
            'store_id'=>$bid,
            'date_time'=> date('Y-m-d', strtotime($date))
        );
        if($isold==1){
            $uparry['now_cancel_new']=$value; 
        }
        if($isold==2){
            $uparry['now_cancel_old']=$value; 
        }
        
        $TaskSummary= TaskStoreSummaryModel::getSearchtaskSummary($wherearray);
        if($TaskSummary){
            TaskStoreSummaryModel::getUptaskSummary($wherearray, $uparry);
        } else {
            $wxid= OrderModel::getWxId($orderid);
            $wherearray['wx_id']=$wxid;
            $storeInfo = StoreModel::getStoreInfo($bid);
            if($storeInfo == null){
                return 1;
            }
            $wherearray['buss_id']= $storeInfo['bid'];
            $wherearray['brand_id']= $storeInfo['brand_id'];
            $wherearray['parent_id']= $storeInfo['pid'];
            if(isset($oldIdArr[$orderid])){
                $wherearray['order_type'] = 1;
                $wherearray['one_price'] = 1;
            } else {
                $wherearray['order_type'] = 2;
                $wherearray['one_price'] = TaskModel::getOnePrice($orderid, $storeInfo['bid']);
            }
            
            if($isold==1){
                $wherearray['now_cancel_new']=$value; 
            }
            if($isold==2){
                $wherearray['now_cancel_old']=$value; 
            }
            TaskStoreSummaryModel::getAddtaskSummary($wherearray);
        }
        //print_r($uparry);
    }
}
