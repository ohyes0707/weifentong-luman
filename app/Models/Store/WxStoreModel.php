<?php

namespace App\Models\Store;

use App\Models\CommonModel;

class WxStoreModel extends CommonModel{

    protected $table = 'y_store_wx';

    protected $primaryKey = 'id';

    public $timestamps = false;


    /**
     * @param $data 美业报备列表
     */
    static public function ReportList($data){

        $page = isset($data['page']) ? $data['page'] : 1;
        $pagesize = isset($data['pagesize']) ? $data['pagesize'] : 10;
        $wx_name = isset($data['wx_name'])?$data['wx_name']:'';
        $wx_name = trim($wx_name);
        $report_status = isset($data['report_status'])?$data['report_status']:'';
        $where = array();
        if($wx_name){
            $where[] = array( 'wx_name', '=',$wx_name);
        }
        if($report_status){
            $where[] = array( 'status', '=', $report_status);

        }

        $dataModel = WxStoreModel::select()->where($where)->orderBy('create_time','desc');
        $count = count($dataModel->get()->toArray());
        $dataList=self::getPages($dataModel,$page,$pagesize,$count);

        $wxnameList = WxStoreModel::select('wx_name')->get()->toArray();

        $allArray = array(['wx_name'=>'全部']) ;

        $wxnameList = array_merge($allArray,$wxnameList);
        $dataList['nameList'] = $wxnameList;

        return $dataList;

    }

    public static function get_wxid(){
        $wxid = WxStoreModel::where('id',$_REQUEST['rid'])->select('wx_id')->first()->wx_id;
        return $wxid;
    }


    public  static function addreport($data){

        $date = date('Y-m-d H:i:s',time());

        $namelist =WxStoreModel::select('wx_name')->get()->toArray();
        $namelist =self::object_array($namelist);
        $newlist = array();
        foreach ($namelist as $key=>$value)
        {

            $newlist[] = $value['wx_name'];

        }

        if(in_array($data['wxname'],$newlist))
        {

            return 9999;

        }else{

            $userid = WxStoreModel::insertGetId(
                ['wx_name' => $data['wxname'], 'contacts' => $data['contact'], 'contact_way' => $data['contactway'],'store_name'=>$data['shopname'],'status'=> 1,'create_time'=>$date] // 缺名字
            );

            return $userid;

        }

    }

    /**更新微信信息
     * @param $where
     * @param $data
     */
    static public function updateWxInfo($where,$data,$orwhere = null){

        $sql = WxStoreModel::where($where);
        if($orwhere)
            $sql = $sql->orwhere($orwhere);
        return WxStoreModel::where($where)->update($data);
       // return WxReportModel::select()->where($where)->get()->toArray();

    }


    /**
     * @param $info
     * @return bool 判断是否有次公众号
     */
    static function decideReport($info){

        $wx_name = $info['authorizer_info']['nick_name'];
        $rid =  $_SESSION['store_rid'];
        $finddata = array(['wx_name', '=', $wx_name]);
        if(isset($rid) && $rid!="")
        {
            $finddata[] = array('id','=',$rid);
        }

        if(WxStoreModel::where($finddata)->get()->first()==null){
            return false;
        }else{
            return true;
        }
    }





    /**
     * @param $info  微信的信信息
     */
     static function changeReport($id,$info){

         $wx_name = $info['authorizer_info']['nick_name'];
         $rid = $_SESSION['store_rid'];

         $finddata = array(['wx_name', '=', $wx_name]);
         if(isset($rid) && $rid!="")
         {
             $finddata[] = array('id','=',$rid);
         }
         if(WxStoreModel::where($finddata)->get()->first()!=null)
         {
             $list = WxStoreModel::where($finddata)->get()->first()->toArray();

             if($list != null)
             {
                 $appid =  $info['authorization_info']['authorizer_appid'];
                 $ghname = $info['authorizer_info']['user_name'];
                 $data['status']=2;
                 $data['wx_id']= $id;
                 $data['appid']= $appid;
                 self::updateWxInfo(array(['wx_name','=',$wx_name],['id','=',$rid]),$data);

             }
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