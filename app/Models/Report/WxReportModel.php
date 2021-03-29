<?php

namespace App\Models\Report;

use App\Models\CommonModel;

class WxReportModel extends CommonModel{

    protected $table = 'y_wx_report';

    protected $primaryKey = 'id';

    public $timestamps = false;

    /**
     * @param $keyword
     * @return null
     */
    static public function getWxInfo($keyword)
    {
        if ($keyword!=null) {
            $map[] = array('wx_name','like', '%' . $keyword . '%');
        }
        $map['status']=3;
        $model = WxReportModel::select('id','wx_name','ghid')->where($map)->get();
        return $model?$model->toArray():null;
    }

    /** 根据条件获取报备列表
     * @param $start_date
     * @param $end_date
     * @param $wx_name
     * @param $report_status
     * @param $userid
     * @param $page
     * @param $pagesize
     * @return array
     */
    static public function getReportList($start_date,$end_date,$wx_name,$user_name,$report_status,$userid,$page,$pagesize){
        $where = array();
        if($start_date)
            $where[] = array( 'create_time', '>=',$start_date.' 00:00:01');
        if($end_date)
            $where[] = array( 'create_time', '<=', $end_date.' 23:59:59');
        if($wx_name)
            $where[] = array( 'wx_name','=',$wx_name);
        if($user_name)
            $where[] = array( 'user_name','like','%'.$user_name.'%');
        if($report_status)
            $where[] = array( 'status', '=', $report_status);
        if($userid)
            $where[] = array('user_id','=',$userid);
        $model = WxReportModel::select('id','wx_name','create_time','company','contacts','telphone','status','user_name','type')
                                ->where($where)
                                ->orderBy('id','desc');
        $model=self::getPages($model,$page,$pagesize);
        return $model;
    }

    static public function updateReport($where,$data){
        return WxReportModel::where($where)->update($data);
    }

    /** 根据条件获取报备微信列表
     * @param $where
     * @return mixed
     */
    static public function getWxList($where){
        $model = WxReportModel::select('id','wx_name')
            ->where($where)
            ->groupBy('wx_name')
            ->orderBy('id', 'desc')
            ->get();
        return $model;
    }

    /** 根据条件获取一条报备信息
     * @param $where
     * @return null
     */
    static public function getReportByWxname($where){
        $model = WxReportModel::where($where)
            ->get()->first();
        return $model?$model->toArray():null;

    }

    /**
     * 获取授权成功的报备列表
     * @param $where
     * @param $orwhere
     * @return null
     */
    static public function getSuccessAuthReport($where,$orwhere){
        $model = WxReportModel::where($where)->orwhere($orwhere)
            ->get();
        return $model?$model->toArray():null;
    }

    /**
     * @param $where
     * @param $orwhere
     * @param $data
     * @return mixed
     */
    static public function updateReportOrWhere($where,$orwhere,$data){
        return WxReportModel::where($where)->orwhere($orwhere)->update($data);
    }



    /**更新微信信息
     * @param $where
     * @param $data
     */
    static public function updateWxInfo($where,$data,$orwhere = null){

        $sql = WxReportModel::where($where);
        if($orwhere)
            $sql = $sql->orwhere($orwhere);
        return WxReportModel::where($where)->update($data);
       // return WxReportModel::select()->where($where)->get()->toArray();

    }


    /**
     * @param $info
     * @return bool 判断是否有次公众号
     */
    static function decideReport($info,$isAgent=0){

        $wx_name = $info['authorizer_info']['nick_name'];

        if($isAgent==1){
            $rid = $_SESSION['agent_rid'];
        }else{
            $rid = $_SESSION['rid'];
        }
        $finddata = array(
            ['wx_name', '=', $wx_name],
            ['id','=',$rid]
        );

        if(WxReportModel::where($finddata)->get()->first()==null){
            return false;
        }else{
            return true;
        }
    }


    /**
     * @param $info  微信的信信息
     */
     static function changeReport($id,$info,$isAgent=0){

        $wx_name = $info['authorizer_info']['nick_name'];

         if($isAgent==1){
             $rid = $_SESSION['agent_rid'];
         }else{
             $rid = $_SESSION['rid'];
         }
        $finddata = array(
            ['wx_name', '=', $wx_name],
            ['id','=',$rid]

        );

         if(WxReportModel::where($finddata)->get()->first()!=null)
         {
             $list = WxReportModel::where($finddata)->get()->first()->toArray();

             if($list != null)
             {
                 $appid =  $info['authorization_info']['authorizer_appid'];
                 $ghname = $info['authorizer_info']['user_name'];
                 $data['status']=3;
                 $data['wx_id']= $id;
                 $data['appid']=$appid;
                 $data['ghid']=$ghname;
                 self::updateWxInfo(array(['wx_name','=',$wx_name],['id','=',$rid]),$data);

             }
         }


    }
    public static function get_wxid($rid){
        $wxid = WxReportModel::where('id',$rid)->select('wx_id')->first()->wx_id;
        return $wxid;
    }









}