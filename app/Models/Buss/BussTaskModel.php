<?php
namespace App\Models\Buss;
use App\Models\CommonModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class BussTaskModel extends  CommonModel
{
    protected $table = 'y_order_task';

    protected $primaryKey = 'id';

    public $timestamps = false;

    /*
     *  获取商家任务列表
     */
    static  public  function CurrentTask($getdade){


//        $getdade['bussid'] = 227;

        if(empty($getdade['bussid'])) return null;

        $bussid = $getdade['bussid'];

        $page = isset($getdade['page']) ? $getdade['page'] : 1;
        $pagesize = isset($getdade['pagesize']) ? $getdade['pagesize'] : 10;

        $reduce_percent = DB::table('bussiness')->where('id',$getdade['bussid'])->get()->first()->reduce_percent;

        $reduce_percent = (1/100)*$reduce_percent;


        $currentmap = array(['buss_id','=',$bussid],
            ['task_status','=',1]
            );

        $oidArrayModel =  BussTaskModel::where($currentmap)->select('order_id','plan_fans','task_status','order_day_fans','day_fans')->groupBy('order_id');

        $count = count($oidArrayModel->get()->toArray());

        $oidArray=self::getPages($oidArrayModel,$page,$pagesize,$count);


        $oidArray = self::object_array($oidArray);


        $all_fansdata=DB::raw('(sum(y_task_summary.new_follow_repeat) + sum(y_task_summary.old_follow_repeat)) as all_fans');



        foreach ($oidArray['data'] as $kk=>$value){

            $oidInfo = DB::table('y_order')->where('order_id','=',$value['order_id'])->select('o_per_price','wx_name','create_time')->get()->toArray();
            $oidInfo = self::object_array($oidInfo);

            // 当前某个渠道下某个订单单独涨粉
            $selfFans= Redis::get('tot-'.$value['order_id'].'-'.$getdade['bussid']);
            $selfFans = $selfFans == null? 0:$selfFans;


            // 去到订单当日剩余涨粉
            $redisdate = date('Ymd',time());
            $evetyNeedFans= $oidArray['data'][$kk]['plan_fans'] - (Redis::hget($redisdate,'new-'.$value['order_id'].'-'.$getdade['bussid'].'-3-1')+Redis::hget($redisdate,'old-'.$value['order_id'].'-'.$getdade['bussid'].'-3-1'));
            $evetyNeedFans = $evetyNeedFans<0 ? 0:$evetyNeedFans;


            if(count($oidInfo)>0){

                $tasksummary = array(
                    ['order_id','=',$value['order_id']],
                    ['buss_id','=',$getdade['bussid']]
                );
                $oidArray['data'][$kk]['wx_name'] =$oidInfo[0]['wx_name'];  //->orderBy('task_time','Desc')
                $oidArray['data'][$kk]['task_time'] = $oidInfo[0]['create_time'];  // strtotime('2010-03-24 08:15:42')
                $oidArray['data'][$kk]['timeString'] = strtotime($oidInfo[0]['create_time']);

                $bussidArray = DB::table('y_order_task')->where('order_id',$value['order_id'])->select('buss_id','order_id')->groupBy('buss_id')->get()->toArray();
                $bussidArray = self::object_array($bussidArray);
                // redis 里面当前涨粉量
                $total = 0;
                foreach($bussidArray as $k=>$v){
                    $total += Redis::get('tot-'.$v['order_id'].'-'.$v['buss_id']);
                }
                $nowFans = ceil($total*(1-$reduce_percent));
                // redis 里面获取的当前涨粉丝量
                $allfans = $nowFans == null? 0:$nowFans;
                // 单纯的订单粉丝
                $orderFans = DB::table('y_order')->where('order_id',$value['order_id'])->get()->first()->o_total_fans;

                // 去到订单当日剩余涨粉

                    $redisdate = date('Ymd',time());

                if($oidArray['data'][$kk]['day_fans']>0){
                    $everyfans=  $oidArray['data'][$kk]['day_fans'] - (Redis::hget($redisdate,'new-'.$value['order_id'].'-'.$getdade['bussid'].'-3-1')+Redis::hget($redisdate,'old-'.$value['order_id'].'-'.$getdade['bussid'].'-3-1'));
                    $evetyNeedFans = $everyfans < 0? 0:$everyfans;

                }else{
                    // 订单当日所有渠道涨粉量
                    $dayAllfans =  Redis::hget($redisdate,'sum-'.$value['order_id'].'--3');
                    $evetyFans= $oidArray['data'][$kk]['order_day_fans'] -$dayAllfans;
                    $evetyNeedFans = $evetyFans < 0? 0:$evetyFans;

                }

                 // 大于0,说明有量
                if($oidArray['data'][$kk]['plan_fans']>0){

                    $lastFans = ceil($orderFans)-$allfans; // 总剩余粉丝说,

                    // 单个订单剩余涨粉数字 =
                    $orderfans = $oidArray['data'][$kk]['plan_fans']-$selfFans;

                    if($lastFans>$orderfans){

                        $oidArray['data'][$kk]['plan_fans'] = $orderfans;
                    }else{
                        $oidArray['data'][$kk]['plan_fans'] = $lastFans;

                    }

                }else{
                    //TODO
                    // 这里的总涨粉量需不需要扣量?

                    $oidArray['data'][$kk]['plan_fans'] = ceil($orderFans)-$allfans;;
                }

                $tasksummaryprice = array(
                    ['order_id','=',$value['order_id']],
                    ['buss_id','=',$getdade['bussid']],
                    ['task_status','=','1'],

                );

                $obPrice = DB::table('y_order_task')->where($tasksummaryprice)->get()->first()->one_price;

                if(empty($obPrice)){
                    $bussprice = DB::table('bussiness')->where('id',$getdade['bussid'])->get()->first()->cost_price;
                    if(empty($bussprice)){
                        $orderprice = DB::table('y_order')->where('order_id',$value['order_id'])->get()->first()->o_per_price;
                        $oidArray['data'][$kk]['o_per_price'] =$orderprice;
                    }else{
                        $oidArray['data'][$kk]['o_per_price'] =$bussprice;
                    }
                }else{
                    $oidArray['data'][$kk]['o_per_price'] =$obPrice;
                }

            }

            $oidArray['data'][$kk]['all_fans'] =$selfFans;

            $oidArray['data'][$kk]['every_fans'] =$evetyNeedFans;

        }

        $flag = array();
        foreach ($oidArray['data'] as $valuedata){
            $flag[] = $valuedata['timeString'];
        }

        array_multisort($flag,SORT_DESC,$oidArray['data']);

        return $oidArray;

    }


    /**
     *  历史商家任务列表
     */

    static public  function historyTaskList($getdade)
    {


        header("Content-type: text/html; charset=utf-8");
//        $getdade['bussid']  = 227;
        if(isset($getdade['excel']) && $getdade['excel']==1)
        {
            $page = isset($getdade['page']) ? $getdade['page'] : 1;
            $pagesize = isset($getdade['pagesize']) ? $getdade['pagesize'] : 9999;
        }else{
            $page = isset($getdade['page']) ? $getdade['page'] : 1;
            $pagesize = isset($getdade['pagesize']) ? $getdade['pagesize'] : 10;
        }
        //date('Y-m-d',strtotime('-10days'))
        // date('Y-m-d',time())
        $getdade['start_date']  = isset($getdade['start_date'])&&$getdade['start_date']==""? date('Y-m-d',strtotime('-10days')): date('Y-m-d H:i:s',strtotime($getdade['start_date']));
        $getdade['end_date']  = isset($getdade['end_date'])&&$getdade['end_date']==""? date('Y-m-d',time()):date('Y-m-d H:i:s',strtotime($getdade['end_date']));


        if($getdade['start_date']== $getdade['end_date'])
        {
            $mindate =  $getdade['start_date'];
            $maxdate = date('Y-m-d H:i:s', strtotime('+1 day',strtotime($getdade['end_date'])));
        }else{
            $mindate =  $getdade['start_date'];
            $maxdate =  date('Y-m-d H:i:s', strtotime('+1 day',strtotime($getdade['end_date'])));
        }


        $nameid = isset($getdade['wxname']) ? $getdade['wxname'] : "";  // 其实是orderid;



        if(isset($getdade['wxname']) && $getdade['wxname']!="")
        {

            $nameid = DB::table('y_order')->where('wx_name','=',$nameid)->select('order_id')->get()->toArray();

            $nameid = self::object_array($nameid);
            $nameArray= array();
            foreach ($nameid as $key=>$value){
                    $nameArray[] =   $value['order_id'];
            }


            $map = array(
                ['y_order_task.task_status', '=', 3],
                ['y_order_task.buss_id','=', $getdade['bussid']]
            );

            $timeString = array(['y_order.create_time', '>=', $mindate],
                ['y_order.create_time', '<', $maxdate]);
        }else{
            $map = array(
                ['y_order_task.task_status', '=', 3],
                ['y_order_task.buss_id','=', $getdade['bussid']]
            );

            $timeString = array(['y_order.create_time', '>=', $mindate],
                ['y_order.create_time', '<', $maxdate]);
        }

        $wxnamemap = array(
            ['task_status', '=', 3],
            ['buss_id','=', $getdade['bussid']]
        );


       // 获得所有的订单数据
        if(isset($getdade['wxname']) && $getdade['wxname']!=""){


            $taskArray = DB::table('y_order')->where($timeString)->leftJoin('y_order_task','y_order.order_id','=','y_order_task.order_id')->where($map)->whereIn('y_order_task.order_id',$nameArray)->select('y_order_task.buss_id','y_order_task.order_id')->groupBy('y_order_task.order_id');

//            $taskArray = BussTaskModel::where($map)->whereIn('order_id',$nameArray)->select('buss_id','task_time','order_id')->groupBy('order_id');

    }else{


            $taskArray = DB::table('y_order')->where($timeString)->leftJoin('y_order_task','y_order.order_id','=','y_order_task.order_id')->where($map)->select('y_order_task.buss_id','y_order_task.order_id')->groupBy('y_order_task.order_id');



    }

        $count = count($taskArray->get()->toArray());
        $historyArray=self::getPages($taskArray,$page,$pagesize,$count);


//        return $historyArray;

        $historyArray = self::object_array($historyArray);
        $wxnameList = DB::table('y_order_task')->where($wxnamemap)->select('order_id')->get()->toArray();
        $wxnameList = self::remove_duplicate($wxnameList);
        $wxnameList = self::object_array($wxnameList);

        foreach ($wxnameList as $k1=>$v1)
        {
            $nameoid[] = $v1['order_id'];
        }

        $wxnamearray = DB::table('y_order')->whereIn('order_id',$nameoid)->select('wx_name')->get()->toArray();
        $wxnamearray = self::object_array($wxnamearray);
        foreach ($wxnamearray as $kn => $vn)
        {
            $name[] = $vn['wx_name'];
        }

        $wxnameList = array_unique($name);

        $arr =  array('0'=>'全部');

        $wxnameList = array_merge($arr,$wxnameList);

//        $all_fans=DB::raw('sum(unfollow)');
//        $un_subscribe = DB::raw('(sum(y_task_summary.new_unfollow_repeat) + sum(y_task_summary.old_unfollow_repeat)) as un_subscribe');


        foreach ($historyArray['data'] as $key=>$value)
        {
            $orderinfo = DB::table('y_order')->where('order_id','=',$value['order_id'])->select('wx_name','create_time')->get()->toArray();
            $orderinfo = self::object_array($orderinfo);
            $historyArray['data'][$key]['wx_name'] = $orderinfo[0]['wx_name'];    //substr(string,start,length)
            $historyArray['data'][$key]['task_time'] = $orderinfo[0]['create_time'];  // strtotime('2010-03-24 08:15:42')
            $historyArray['data'][$key]['timeString'] = strtotime($orderinfo[0]['create_time']);
            $tmpmap = array(['order_id','=',$value['order_id']],
                ['buss_id','=',$getdade['bussid']]
            );

            // 关注,取关数据,暂时如此  DB::raw('(sum(y_task_summary.new_follow_repeat)
            $taskarray= DB::table('y_money_log')->where($tmpmap)->select(DB::raw('sum(follow) as follow'),DB::raw('sum(unfollow) as unfollow'))->get()->toArray();
            $taskarray= self::object_array($taskarray);
            $historyArray['data'][$key]['un_subscribe_nu'] = $taskarray[0]['unfollow']==null?0:$taskarray[0]['unfollow'];
            $historyArray['data'][$key]['all_fans'] = $taskarray[0]['follow']==null?0:$taskarray[0]['follow'];
            if($historyArray['data'][$key]['all_fans'] == 0){
                $unfollowRate = '0';
            }else{
                $unfollowRate = (sprintf('%.2f',$taskarray[0]['unfollow']/$taskarray[0]['follow'])*100).'%';
            }
            $historyArray['data'][$key]['unfollowRate'] = $unfollowRate;

        }

        $flag = array();
        foreach ($historyArray['data'] as $valuedata){
            $flag[] = $valuedata['timeString'];
        }

        array_multisort($flag,SORT_DESC,$historyArray['data']);

        // 在这里加name列表

        $historyArray['namelist'] = $wxnameList;


        if(isset($getdade['excel']) && $getdade['excel']==1 && isset($historyArray['data']) && count($historyArray['data'])>0)
        {

            $excelArray =array();
            foreach ($historyArray['data'] as $key =>$value){
                // 时间 公众号 成功关注 取关量 取关率
                $excel['task_time'] = $value['task_time'];
                $excel['wx_name'] = $value['wx_name'];
                $excel['all_fans'] = $value['all_fans'];
                $excel['un_subscribe_nu'] = $value['un_subscribe_nu'];
                $excel['unfollowRate'] = $value['unfollowRate'];
                $excelArray[] = $excel;
            }
            return $excelArray;
        }else{

            return  $historyArray;
        }
    }

    /**
     *   子商家列表   有个问题是,查task 表的话,只会存在有数据的情况,没有数据的子商家就不会存在
     */
    static public function sonBussList($getdade)
    {

//        $getdade['bussid'] = 227;
        if(isset($getdade['excel']) && $getdade['excel']==1){
            $page = isset($getdade['page']) ? $getdade['page'] : 1;
            $pagesize = isset($getdade['pagesize']) ? $getdade['pagesize'] : 9999;
        }else{
            $page = isset($getdade['page']) ? $getdade['page'] : 1;
            $pagesize = isset($getdade['pagesize']) ? $getdade['pagesize'] : 10;
        }

        $getdade['start_date'] = $getdade['start_date'] == "" ? date('Y-m-d', strtotime('-10days')) : $getdade['start_date'];
        $getdade['end_date'] = $getdade['end_date'] == "" ? date('Y-m-d', time()) : $getdade['end_date'];
        $mindate = $getdade['start_date'];
        $maxdate = $getdade['end_date'];
        $nameid = isset($getdade['wxname']) ? $getdade['wxname'] : "";

        if($nameid == ""){
            $map = array(
                ['date', '>=', $mindate],
                ['date', '<=', $maxdate],
                ['parent_id', '=', $getdade['bussid']],
            );


        }else{
            $map = array(
                ['date', '>=', $mindate],
                ['date', '<=', $maxdate],
                ['parent_id', '=', $getdade['bussid']],
                ['buss_id', '=', $nameid]
            );
        }

        $wxnamemap = array(['parent_id', '=', $getdade['bussid']],
            ['bid', '<>', $getdade['bussid']]);

        $wxnameList = DB::table('y_task_summary')->where($wxnamemap)->leftJoin('buss_info', 'y_task_summary.buss_id', '=', 'buss_info.bid')->select('y_task_summary.buss_id', 'buss_info.nick_name')->get()->toArray();

        $fathermap = array(
            ['bid', '=', $getdade['bussid']]);

        $fatherName = DB::table('buss_info')->where($fathermap)->get()->first()->nick_name;

        $wxnameList = self::remove_duplicate($wxnameList);

        $arr =  array(['buss_id'=>0,'nick_name'=>'全部'],['buss_id'=> intval($getdade['bussid']),'nick_name'=>$fatherName]);

        $wxnameList = array_merge($arr,$wxnameList);


        $all_fans=DB::raw('sum(y_money_log.follow) as all_fans');
        $un_subscribe = DB::raw('sum(y_money_log.unfollow) as un_subscribe');
        $complet_repeat = DB::raw('sum(y_money_log.complet) as complet_repeat');


        $sonList = DB::table('y_money_log')->where($map)->leftJoin('buss_info', 'y_money_log.buss_id', '=', 'buss_info.bid')->select('date as date_time',$complet_repeat,$all_fans,$un_subscribe, 'y_money_log.buss_id', 'buss_info.nick_name')->groupBy('buss_id');


        $count = count($sonList->get()->toArray());
        $sonList=self::getPages($sonList,$page,$pagesize,$count);

        $sonList = self::object_array($sonList);

        foreach ($sonList['data'] as $kk => $vv) {
            if($vv['all_fans']==0){
                $sonList['data'][$kk]['unfollowRate'] = 0;
            }else{
                $sonList['data'][$kk]['unfollowRate'] = (sprintf('%.4f', $vv['un_subscribe'] / $vv['all_fans']) * 100) . '%';
            }
            $sonList['data'][$kk]['complet_repeat'] = $vv['complet_repeat'];
        }

        $sonList['namelist'] = $wxnameList;
        // excel
        if(isset($getdade['excel']) && $getdade['excel']==1 && isset($sonList['data']) && count($sonList['data'])>0)
        {

            $excelArray =array();
            foreach ($sonList['data'] as $key =>$value){

                $excel['name'] = $value['nick_name'];
                $excel['date_time'] = $value['date_time'];
                $excel['complet_repeat'] = $value['complet_repeat'];
                $excel['all_fans'] = $value['all_fans'];
                $excel['un_subscribe'] = $value['un_subscribe'];
                $excel['unfollowRate'] = $value['unfollowRate'];
                $excelArray[] = $excel;
            }

            return $excelArray;

        }else{
            return $sonList;

        }


    }

    public static function refuseReport($getdata){

        $map = array(['buss_id','=',$getdata['bussid']],
            ['order_id','=',$getdata['orderid']]
        );

        $changeMap = array(['task_status','=',1],
            ['order_id','=',$getdata['orderid']]
        );


        $data = Redis::hdel($getdata['bussid'],$getdata['orderid']);
        if($data!=null && $data!=0)
        {
            $result =  DB::table('y_order_task')
                ->where($map)
                ->update(array('task_status' => 2));

            $bidArray =  DB::table('y_order_task')
                ->where($changeMap)
                ->select('id')->get()->toArray();

            if(empty($bidArray)){
                DB::table('y_order')
                    ->where('order_id',$getdata['orderid'])
                    ->update(array('order_status' => 2));
            }

            return $result;
        }



    }


    /** 对象转数组
     * @param $array
     * @return array
     */
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

}


