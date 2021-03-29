<?php
namespace App\Models\Agent;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CommonModel;
use App\Models\Order\OrderModel;

class AgentModel extends CommonModel{

    public static function agentList($getdade){
        $id = $getdade['agenid'];
        $page = isset($getdade['page']) ? $getdade['page'] : 1;
        $pagesize = isset($getdade['pagesize']) ? $getdade['pagesize'] : 10;
        $map = array(['user.agent_id','=',$id],['user.type','=','2']);
        $agentlist = DB::table('user')->where($map)->leftJoin('user_info','user.id','=','user_info.uid')->select('user.username','user_info.nick_name','user.id','user.status','user.ti_money')->orderBy('user.id','desc');
        $count = count($agentlist->get()->toArray());
        $agentlist=self::getPages($agentlist,$page,$pagesize,$count);
        $agentlist['data'] = self ::object_array($agentlist['data']);

        foreach ($agentlist['data'] as $key =>$value)
        {
            $auth = DB::table('y_wx_report')->where('user_id',$value['id'])->where(function($query){
                $query->where('status','=',3)->orWhere(function($query){
                    $query->where('status','=',4);
                });
            })->select(DB::raw('count(id) as auth'))->first()->auth;

            // 报备完成
            $report =DB::table('y_wx_report')->select(DB::raw('count(id) as report'))->where('status','=','4')->where('user_id',$value['id'])->first()->report;
            // 报备未完成
            $reportFail =DB::table('y_wx_report')->select(DB::raw('count(id) as reportFail'))->where('user_id',$value['id'])->first()->reportFail;
            // 工单
            $work =  DB::table('y_work_order')->select(DB::raw('count(id) as work'))->where('user_id',$value['id'])->first()->work;
            // 订单
            $order = OrderModel::select(DB::raw('count(order_id) as orders'))->where('o_uid',$value['id'])->first()->orders;

             $oidArray = DB::table('y_order')->where('o_uid',$value['id'])->select('order_id','o_per_price')->groupBy('order_id')->get()->toArray();
            $oidArray = self::object_array($oidArray);
            $agentMoney = 0 ;
            foreach ($oidArray as $kk=>$vv){
                $all_fans=DB::raw('(sum(y_task_summary.new_follow_repeat) + sum(y_task_summary.old_follow_repeat)) as total_fans');
                $fansSum= Db::table('y_task_summary')->where('order_id',$vv['order_id'])->select($all_fans)->first()->total_fans;
                $agentMoney += $fansSum*$vv['o_per_price'];
            }


            $agentlist['data'][$key]['auth'] = $auth;
            $agentlist['data'][$key]['report'] = $report;
            $agentlist['data'][$key]['reportFail'] = $reportFail;
            $agentlist['data'][$key]['work'] = $work;
            $agentlist['data'][$key]['order'] = $order;
            $agentlist['data'][$key]['agentMoney'] = $agentMoney;

        }

        if(isset($_GET['nick_name']) && $_GET['nick_name']!=null){

            foreach($agentlist['data'] as $kk =>$vv){
                if($vv['nick_name'] !=$_GET['nick_name'])
                {
                   unset($agentlist['data'][$kk]);
                }

            }

                return  $agentlist;

        }else{
            return $agentlist;

        }


     }


    public  static  function managerAnagerList($data){

        if($data['type'] == 'add'){
                self::addUser($data);
        }elseif($data['type'] == 'start'){

        }elseif($data['type'] == 'end'){

        }elseif($data['type'] == 'delete'){

        }elseif($data['type'] == 'edit'){
            self::editUser($data);
        }


    }


    /**
     *  新增角色
     */
    public static function addUser($data)
    {

            $create_time = time();
            $password = md5('123456' . $create_time);

            $namelist = DB::table('user')->select('username')->get()->toArray();
            $namelist =self::object_array($namelist);
            $newlist = array();
            foreach ($namelist as $key=>$value)
            {

                $newlist[] = $value['username'];

            }

            if(in_array($data['username'],$newlist))
            {

                return 9999;

            }else{
                $userid = DB::table('user')->insertGetId(
                    ['username' => $data['username'], 'create_time' => $create_time, 'ti_money' => $data['ti_money'],'password'=>$password,'type'=>2,'agent_id'=> $data['agenid']] // 缺名字
                );
                if($userid>0){

                    $bool=DB::insert("insert into user_info(uid,user_type,nick_name)
            values(?,?,?)",[$userid,1,$data['nick_name']]);

                    if($bool==true){
                        return $userid;
                    }

                }

            }


        }

    /**
     * 编辑角色
     */
    public static function editUser(){


        if(empty($_GET['ti_money'])){

            $data = DB::table('user')->select('username','ti_money')->where('id','=',$_GET['aid'])->get()->toArray();

            $nick_name = DB::table('user_info')->select('nick_name')->where('uid','=',$_GET['aid'])->first()->nick_name;

            $data = self::object_array($data);

            $data[0]['nick_name'] = $nick_name;

            return $data;
        }else{

            if(isset($_GET['password']))
            {        // md5 加密规则
//                $create_time = AdminModel::select('create_time')->where('id','=',$_GET['aid'])->first()->create_time;
                $create_time = time();
                $password = md5('123456'.$create_time);
                $result =  DB::table('user')->where('id', $_GET['aid'])->update(['username' => $_GET['username'],'password'=>$password,'ti_money'=>$_GET['ti_money']]);
                $result1 =  DB::table('user_info')->where('uid', $_GET['aid'])->update(['nick_name' => $_GET['nick_name']]);

                if($result==1 || $result1==1){

                    return  1;
                }else{
                    return 0;
                }

            }else{

                $result =  DB::table('user')->where('id', $_GET['aid'])->update(['username' => $_GET['username'],'ti_money'=>$_GET['ti_money']]);
                $result1 =  DB::table('user_info')->where('uid', $_GET['aid'])->update(['nick_name' => $_GET['nick_name']]);

                if($result==1 || $result1==1){

                    return  1;
                }else{
                    return 0;
                }

            }

        }

    }


    /**
     *  删除 禁止 开启 得用 in
     */
    public static function setagentList(){

        if(isset($_GET['type']) && isset($_GET['id'])){

            $type = $_GET['type'];


            // id 转化为数组

            if(strpos($_GET['id'], ',') !== false){
                $idstring = substr($_GET['id'],0,strlen($_GET['id'])-1);

                if(strpos($idstring, ',') !== false){
                    $idArray = explode(',',$idstring);
                }else{
                    $idArray = array();
                    $idArray[0] = $idstring;
                }

            }else{
                $idArray = array();
                $idArray[0] = $_GET['id'];

            }


            if($type=='forbidden'){



                $result =  DB::table('user')->whereIn('username', $idArray)->update(['status' => 0]);

                return $result;


            }elseif($type=='delete'){

                $uidArray =  DB::table('user')->whereIn('username', $idArray)->select('id')->get()->toArray();
                $uidArray = self::object_array($uidArray);
                $uidList = array();
                foreach ($uidArray as $key=>$value)
                {
                    $uidList[$key] = $value['id'];
                }

                $result =  DB::table('user')->whereIn('username', $idArray)->delete();
                $infoResult =  DB::table('user_info')->whereIn('uid', $uidList)->delete();

                if($result ==  true && $infoResult == true)
                {
                    return 1;
                }else{
                    return 0;
                }

            }elseif($type=='use'){


                $result =  DB::table('user')->whereIn('username', $idArray)->update(['status' => 1]);

                return $result;
            }


        }


    }



    public  static function sonAgentList($getdade){


        $page = isset($getdade['page']) ? $getdade['page'] : 1;
        $pagesize = isset($getdade['pagesize']) ? $getdade['pagesize'] : 10;

        if($getdade['sonagency']==null && $getdade['agency']==null)
        {

            $map =array(['type','=',2],['agent_id','<>',0]);
            $sonlListModel = DB::table('user')->where($map)->select('id','agent_id','username')->orderBy('id','desc');
            $count = count($sonlListModel->get()->toArray());
            $sonlList=self::getPages($sonlListModel,$page,$pagesize,$count);
            $data = self::AgentInfo($sonlList);
            return $data;

        }elseif($getdade['sonagency']!=null && $getdade['agency']==null){

            $sonname = $getdade['sonagency'];
            $sonArrayModel = DB::table('user_info')->where('nick_name',$sonname)->select('uid','nick_name');
            $count = count($sonArrayModel->get()->toArray());
            $sonlList=self::getPages($sonArrayModel,$page,$pagesize,$count);
            $sonArray = self::object_array($sonlList['data']);
            $sonlList['data'] = $sonArray;

            foreach ($sonlList['data'] as $key=>$value)
            {
                $map =array(['type','=',2],['agent_id','<>',0],['id','=',$value['uid']]);
                $userArray = DB::table('user')->where($map)->select('id','username','agent_id')->get()->toArray();
                $userArray = self::object_array($userArray);
                if(empty($userArray)){
                    unset($sonlList['data'][$key]);
                }else{
                    $sonlList['data'][$key]['username'] = isset($userArray[0]['username'])?$userArray[0]['username']:0;
                    $sonlList['data'][$key]['id'] = isset($userArray[0]['id'])?$userArray[0]['id']:0;
                    $sonlList['data'][$key]['agent_id'] =isset($userArray[0]['agent_id'])?$userArray[0]['agent_id']:0;
                }

            }
            $data = self::AgentInfo($sonlList);
            return $data;
        }elseif($getdade['sonagency']==null && $getdade['agency']!=null){
            $fathername = $getdade['agency'];
            // 假如重名 会有多对;
            $fatherModel = DB::table('user_info')->where('user_info.nick_name',$fathername)->leftJoin('user','user_info.uid','=','user.agent_id')->where('user.type','=',2)->select('user.agent_id','user.id','user.username');
            $count = count($fatherModel->get()->toArray());
            $fatherList=self::getPages($fatherModel,$page,$pagesize,$count);
            $data = self::AgentInfo($fatherList);
            return $data;
        }elseif($getdade['sonagency']!=null && $getdade['agency']!=null){
            $fathername = $getdade['agency'];
            $sonname = $getdade['sonagency'];

           $agentlist = DB::table('user_info')->where('nick_name',$sonname)->leftJoin('user','user_info.uid','=','user.id')->select('user_info.uid as id','user.username','user_info.nick_name')->get()->toArray();

            if(empty($agentlist)){
                $fatherArray = self::getAllAgentName();
                $arr = array(['nick_name'=>'全部']);
                $wxnameArray = array_merge($arr,$fatherArray);
                $sonlList['name'] = $wxnameArray;
                return $sonlList;
            }
            $agentlist = self::object_array($agentlist);
            $agent_id= DB::table('user_info')->where('nick_name',$fathername)->select('uid')->first()->uid;
            $agentlist[0]['agent_id'] = $agent_id;
            $where  =array(['id','=',$agentlist[0]['id']],['agent_id','=',$agent_id],['type','=','2']);
            $data = DB::table('user')->where($where)->select();
            $count = count($data->get()->toArray());
            $List=self::getPages($data,$page,$pagesize,$count);
            $sonArray = self::object_array($List['data']);

            if(isset($List['data']) && count($List['data'])>0)
            {
                $List['data'][0] = $agentlist[0];
            }
            $data = self::AgentInfo($List);
            return $data;

        }

    }


    public static function AgentInfo($sonlList){
        $sonlList = self::object_array($sonlList);
        foreach ($sonlList['data'] as $key =>$value){
            $sonname = DB::table('user_info')->where('uid',$value['id'])->select('nick_name')->first()->nick_name;

            $fatrhername = DB::table('user_info')->where('uid',$value['agent_id'])->select('nick_name')->first()->nick_name;

            $auth = DB::table('y_wx_report')->where('user_id',$value['id'])->where(function($query){
                $query->where('status','=',3)->orWhere(function($query){
                    $query->where('status','=',4);
                });
            })->select(DB::raw('count(id) as auth'))->first()->auth;

            // 报备完成
            $report =DB::table('y_wx_report')->select(DB::raw('count(id) as report'))->where('status','=','4')->where('user_id',$value['id'])->first()->report;
            // 报备未完成
            $reportFail =DB::table('y_wx_report')->select('user_id',DB::raw('count(id) as reportFail'))->where('user_id',$value['id'])->first()->reportFail;
            // 工单
            $work =DB::table('y_work_order')->select('user_id',DB::raw('count(id) as work'))->where('user_id',$value['id'])->first()->work;
            // 订单
            $order = OrderModel::select('o_uid',DB::raw('count(order_id) as orders'))->where('o_uid',$value['id'])->first()->orders;

            $oidArray = DB::table('y_order')->where('o_uid',$value['id'])->select('order_id','o_per_price')->groupBy('order_id')->get()->toArray();

            $agentMoney = 0 ;
            $oidArray =self::object_array($oidArray);
            foreach ($oidArray as $kk=>$vv){
                $all_fans=DB::raw('(sum(y_task_summary.new_follow_repeat) + sum(y_task_summary.old_follow_repeat)) as total_fans');
                $fansSum= Db::table('y_task_summary')->where('order_id',$vv['order_id'])->select($all_fans)->first()->total_fans;
                $agentMoney += $fansSum*$vv['o_per_price'];
            }
            $sonlList['data'][$key]['sonname'] = $sonname;
            $sonlList['data'][$key]['fatrhername'] = $fatrhername;
            $sonlList['data'][$key]['auth'] = $auth;
            $sonlList['data'][$key]['report'] = $report;
            $sonlList['data'][$key]['reportFail'] = $reportFail;
            $sonlList['data'][$key]['work'] = $work;
            $sonlList['data'][$key]['order'] = $order;
            $sonlList['data'][$key]['agentMoney'] = $agentMoney;

        }
        $fatherArray = self::getAllAgentName();
        $arr = array(['nick_name'=>'全部']);
        $wxnameArray = array_merge($arr,$fatherArray);
        $sonlList['name'] = $wxnameArray;
        return $sonlList;

    }

    public static function getAllAgentName()
    {
        $map = array(['user.type','=',2]);
        $fatherArray = DB::table('user')->where($map)->leftJoin('user_info','user.agent_id','=','user_info.uid')->select('user_info.nick_name')->groupBy('user_info.nick_name')->get()->toArray();
        $fatherArray = self::object_array($fatherArray);
        foreach($fatherArray as $key=>$value){
            if($value['nick_name'] == null){
                unset($fatherArray[$key]);
            }
        }

        return $fatherArray;
    }



    /**
     * 子代理分析
     */
    public static function analyseSonAgent($getdade){

        $page = isset($getdade['page']) ? $getdade['page'] : 1;
        $pagesize = isset($getdade['pagesize']) ? $getdade['pagesize'] : 10;
        $getdade['startdate']  =  $getdade['startdate']==""? date('Y-m-d',strtotime('-7days')):$getdade['startdate'];
        $getdade['enddate']  =  $getdade['enddate']==""? date('Y-m-d',time()):$getdade['enddate'];
        $all_fans=DB::raw('(sum(y_task_summary.new_follow_repeat) + sum(y_task_summary.old_follow_repeat)) as total_fans');
        $un_subscribe = DB::raw('(sum(y_task_summary.new_unfollow_repeat) + sum(y_task_summary.old_unfollow_repeat)) as un_subscribe');

        $data = array(
            ['y_task_summary.date_time', '>=',  $getdade['startdate']],
            ['y_task_summary.date_time', '<=',  $getdade['enddate']]
        );

        $oidArray = DB::table('y_order')->where('o_uid',$getdade['id'])->select('order_id','o_per_price')->groupBy('order_id')->get()->toArray();
        $oidArray =self::object_array($oidArray);
        $nOidAray = array();
        foreach ($oidArray as $k1 =>$v1){
            $nOidAray[] = $v1['order_id'];
        }

            $dataModel= Db::table('y_task_summary')->whereIn('order_id',$nOidAray)->where($data)->select($all_fans,$un_subscribe,'y_task_summary.date_time','order_id')->groupBy('y_task_summary.date_time')->orderBy('y_task_summary.date_time','desc');
        $count = count($dataModel->get()->toArray());
        $datalist=self::getPages($dataModel,$page,$pagesize,$count);
        $datalist['data'] = self ::object_array($datalist['data']);

        $dataArray= Db::table('y_task_summary')->whereIn('order_id',$nOidAray)->where($data)->select($all_fans,$un_subscribe,'y_task_summary.date_time','order_id')->groupBy('y_task_summary.date_time')->groupBy('order_id')->orderBy('y_task_summary.date_time','desc')->get()->toArray();

        $dataArray = self::object_array($dataArray);

        foreach ($dataArray as $kk=>$vv)
        {
            $oidPrice = DB::table('y_order')->where('o_uid',$getdade['id'])->select('o_per_price')->first()->o_per_price;
            $dataArray[$kk]['all'] = $vv['total_fans']*$oidPrice;

        }

        $total_money = array();
        foreach ($dataArray as $kk =>$value)
        {
            if(isset($total_money[$value['date_time']])){
                $total_money[$value['date_time']] += $value['all'];
            }else{
                $total_money[$value['date_time']] = $value['all'];
            }

        }

        // 总收益
        $allEarnings  = 0;
        foreach ($datalist['data'] as $k=>$v)
        {
            if($v['total_fans']==0){
                $datalist['data'][$k]['money'] = 0;
            }else{
                foreach($total_money as $k1=>$v1)
                {

                    if($v['date_time']==$k1)
                    {
                        $datalist['data'][$k]['money'] = $v1;
                    }
                }
            }

            $allEarnings+=$datalist['data'][$k]['money'];
        }

        // 总计数据
        $tatal_data= Db::table('y_task_summary')->whereIn('order_id',$nOidAray)->where($data)->select($all_fans,$un_subscribe)->get()->toArray();
        $tatal_data = self::object_array($tatal_data);

        $tatal_data[0]['money'] = $allEarnings;  //
        $tatal_data[0]['date_time'] = '全部';
        $datalist['data'] = array_merge($tatal_data,$datalist['data']);

        foreach ($datalist['data'] as $kk=>$vv)
        {
            $datalist['data'][$kk]['percent'] = self::percentage($vv['un_subscribe'],$vv['total_fans']);
        }

        return $datalist;



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



}