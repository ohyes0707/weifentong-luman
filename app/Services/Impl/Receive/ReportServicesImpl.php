<?php

namespace App\Services\Impl\Receive;

use App\Services\UserServices;
use App\Models\Count\TaskSummaryModel;
use App\Models\Count\OrderTaskModel;
use App\Models\Count\BussInessModel;
use App\Services\Impl\Common\ScreenServicesImpl;
use App\Models\Wechat\WxInfoModel;
use App\Models\Buss\BussModel;

class ReportServicesImpl implements UserServices
{
    //所有微信的数据
    static public function getSearchWxTaskData($array) {
        $initialize= ScreenServicesImpl::getArray($array);
        $where= ScreenServicesImpl::getWhereArray($initialize,'date_time');
        $wxdata=TaskSummaryModel::getListWxid($where,$initialize['page'],$initialize['pagesize']);
        $biddata= TaskSummaryModel::getListData($where,$wxdata['wxid']);
        $endarray['date']= ReportServicesImpl::getWxFansData($wxdata['data'],$biddata,$initialize['type'],'wx_id');
        $endarray['count']=$wxdata['count'];
        $endarray['wxname']= TaskSummaryModel::getListWx();
        return $endarray;
    }
    
    //微信下的渠道
    static public function getSearchWxBussTaskData($array) {
        //需要多个渠道id
        $initialize= ScreenServicesImpl::getArray($array);
        $usertype=$initialize['type'];
        unset($initialize['type']);
        $where= ScreenServicesImpl::getWhereArray($initialize,'date_time');
        //获取父渠道
        $wxdata=TaskSummaryModel::getListBussid($where);
        //取出上面父渠道的数据
        $bussdata=TaskSummaryModel::getBussListData($where,$wxdata['parent_id']);
        //获取今天的数据
        
        //汇总
        $endarray['count']=$wxdata['count'];
        $endarray['date']= ReportServicesImpl::getWxFansData($wxdata['data'],$bussdata,$usertype,'parent_id');
        unset($initialize['wx_id']);
        $endarray['bussname']= TaskSummaryModel::getListBussid($where)['data'];
        $endarray['wxname']= WxInfoModel::getWxInfoOne('wx_name',['id' => $array['wx_id']]);
        return $endarray;
    }
    
    //总渠道
    static public function getSearchBussTaskData($array) {
        //需要多个渠道id
        $initialize= ScreenServicesImpl::getArray($array);
        $usertype=$initialize['type'];
        unset($initialize['type']);
        $where= ScreenServicesImpl::getWhereArray($initialize,'date_time');
        //获取父渠道
        $wxdata=TaskSummaryModel::getListBussid($where,$initialize['page'],$initialize['pagesize']);
        //取出上面父渠道的数据
        $bussdata=TaskSummaryModel::getBussListData($where,$wxdata['parent_id']);
        //获取今天的数据
        
        //汇总
        $endarray['count']=$wxdata['count'];
        $endarray['date']= ReportServicesImpl::getWxFansData($wxdata['data'],$bussdata,$usertype,'parent_id');
        $endarray['bussname']= TaskSummaryModel::getListBuss();
        return $endarray;
    }
    
    //渠道下面的微信
    static public function getSearchBussWxTaskData($array) {
        $initialize= ScreenServicesImpl::getArray($array);
        $where= ScreenServicesImpl::getWhereArray($initialize,'date_time');
        $wxdata=TaskSummaryModel::getListWxid($where,$initialize['page'],$initialize['pagesize']);
        $biddata= TaskSummaryModel::getListData($where,$wxdata['wxid']);
        $endarray['date']= ReportServicesImpl::getWxFansData($wxdata['data'],$biddata,$initialize['type'],'wx_id');
        $endarray['count']=$wxdata['count'];
        if($array['buss_id']==''){
            $endarray['bussname']= BussModel::getUserByBussId($array['parent_id']);
        } else {
            $endarray['bussname']= BussModel::getUserPbName($array['buss_id']);
        }
        return $endarray;
    }
    
    
    //渠道下面的微信
    static public function getSearchSonBussWxTaskData($array) {
        $initialize= ScreenServicesImpl::getArray($array);
        $where= ScreenServicesImpl::getWhereArray($initialize,'date_time');
        $wxdata=TaskSummaryModel::getListWxid($where,$initialize['page'],$initialize['pagesize']);
        $biddata= TaskSummaryModel::getListData($where,$wxdata['wxid']);
        $endarray['date']= ReportServicesImpl::getWxFansData($wxdata['data'],$biddata,$initialize['type'],'wx_id');
        $endarray['count']=$wxdata['count'];
        $endarray['wxname']= TaskSummaryModel::getListWx($array['buss_id']);
        $endarray['bussname']= BussModel::getUserPbName($array['buss_id']);
        return $endarray;
    }
    
    static public function getSearchOneBussTaskData($array){
        //需要多个渠道id
        $initialize= ScreenServicesImpl::getArray($array);
        $usertype=$initialize['type'];
        unset($initialize['type']);
        $where= ScreenServicesImpl::getWhereArray($initialize,'date_time');
        //获取父渠道
        $wxdata=TaskSummaryModel::getListBussid($where);
        //取出上面父渠道的数据
        $bussdata=TaskSummaryModel::getBussListData($where,$wxdata['parent_id']);        
        //汇总
        $endarray['count']=$wxdata['count'];
        $endarray['date'][0]= ReportServicesImpl::getWxFansData($wxdata['data'],$bussdata,$usertype,'parent_id');
        
        //取出所有子渠道
        $wxsondata=TaskSummaryModel::getListBussSonid($where,$array['pagesize']);
        //取出所有子渠道数据
        $busssondata=TaskSummaryModel::getBussOneListData($where,$wxsondata['buss_id']); 
        //汇总
        $endarray['date'][1]= ReportServicesImpl::getWxFansData($wxsondata['data'],$busssondata,$usertype,'buss_id');
        $endarray['bussname']= TaskSummaryModel::getListBuss();
        $endarray['wxname']= WxInfoModel::getWxInfoOne('wx_name',['id' => $array['wx_id']]);
        return $endarray;
    }

    //数据开始整合
    static public function getWxFansData($wxdata,$biddata,$usertype,$datetype){
        if(count($wxdata)<=0){
            return null;
        }
        foreach ($wxdata as $key => $value) {
            switch ($datetype) {
                case 'wx_id':
                    $fanddata[$key]['wx_id']=$value['wx_id'];
                    $fanddata[$key]['wx_name']=$value['wx_name'];
                    break;
                case 'parent_id':
                    $fanddata[$key]['parent_id']=$value['parent_id'];
                    $fanddata[$key]['buss_name']=$value['username'];
                    break;
                default:
                    $fanddata[$key]['buss_id']=$value['buss_id'];
                    $fanddata[$key]['buss_name']=$value['username'];
                    $datetype='id';
                    break;
            }

            $nowfollow=0;
            $unnowfollow=0;
            $unfollow=0;
            foreach ($biddata as $key2 => $value2) {
                switch ($datetype) {
                case 'wx_id':
                    if($value[$datetype]==$value2[$datetype]){
                        $fanddata[$key]['list'][]= ReportServicesImpl::getAas($value2,$usertype,$value);
                        $nowfollow=$nowfollow+ReportServicesImpl::getSpecialAdd($value2['new_follow_repeat'], $value2['old_follow_repeat'], $usertype);
                        $unnowfollow=$unnowfollow+ReportServicesImpl::getSpecialAdd($value2['now_cancel_new'], $value2['now_cancel_old'], $usertype);
                        $unfollow=$unfollow+ReportServicesImpl::getSpecialAdd($value2['new_unfollow_repeat'], $value2['old_unfollow_repeat'], $usertype);
                    }
                    break;
                case 'parent_id':
                    if($value[$datetype]==$value2[$datetype]){
                        $fanddata[$key]['list'][]= ReportServicesImpl::getAas($value2,$usertype,$value);
                        $nowfollow=$nowfollow+ReportServicesImpl::getSpecialAdd($value2['new_follow_repeat'], $value2['old_follow_repeat'], $usertype);
                        $unnowfollow=$unnowfollow+ReportServicesImpl::getSpecialAdd($value2['now_cancel_new'], $value2['now_cancel_old'], $usertype);
                        $unfollow=$unfollow+ReportServicesImpl::getSpecialAdd($value2['new_unfollow_repeat'], $value2['old_unfollow_repeat'], $usertype);
                    }
                    break;
                default:
                    if($value['buss_id']==$value2['buss_id']){
                        $fanddata[$key]['list'][]= ReportServicesImpl::getAas($value2,$usertype,$value);
                        $nowfollow=$nowfollow+ReportServicesImpl::getSpecialAdd($value2['new_follow_repeat'], $value2['old_follow_repeat'], $usertype);
                        $unnowfollow=$unnowfollow+ReportServicesImpl::getSpecialAdd($value2['now_cancel_new'], $value2['now_cancel_old'], $usertype);
                        $unfollow=$unfollow+ReportServicesImpl::getSpecialAdd($value2['new_unfollow_repeat'], $value2['old_unfollow_repeat'], $usertype);
                    }
                    break;
                    }

            }
            $fanddata[$key]['nowfollow']=$nowfollow;
            $fanddata[$key]['unnowfollow']=$unnowfollow;           
            $fanddata[$key]['unfollow']=$unfollow;
            $fanddata[$key]['unfollowrate']= ScreenServicesImpl::getDivision($unfollow, $nowfollow)*100;
            $fanddata[$key]['unnowfollowrate']=ScreenServicesImpl::getDivision($unnowfollow, $nowfollow)*100;
        }
        return isset($fanddata)?$fanddata:null;
    }

    static public function getAas($parameter,$usertype,$value) {
            $structure=array(
                'datetime'=>$parameter['date_time'],
                'nowfollow'=> ReportServicesImpl::getSpecialAdd($parameter['new_follow_repeat'], $parameter['old_follow_repeat'], $usertype),
                'unfollow'=>ReportServicesImpl::getSpecialAdd($parameter['new_unfollow_repeat'], $parameter['old_unfollow_repeat'], $usertype),
                'unnowfollow'=>ReportServicesImpl::getSpecialAdd($parameter['now_cancel_new'], $parameter['now_cancel_old'], $usertype),
            );
            $structure['unnowfollowrate'] = ScreenServicesImpl::getDivision($structure['unnowfollow'],$structure['nowfollow'],4)*100;
            $structure['unfollowrate'] = ScreenServicesImpl::getDivision($structure['unfollow'],$structure['nowfollow'],4)*100;
            return $structure;
    }

    static public function getSpecialAdd($parameter1,$parameter2,$usertype) {
        switch ($usertype) {
            case 1:
                return $parameter1;
            case 2:
                return $parameter2;
            default:
                return $parameter2+$parameter1;
        }
    }
    
    static public function getWxId_Name($wxname) {
        $id = WxInfoModel::select('id')->where('wx_name','=',$wxname)->first();
        return $id?$id->toArray()['id']:null;
    }
    
    static public function getBussId_Name($bussname) {
        $id = BussInessModel::select('id')->where('username','=',$bussname)->first();
        return $id?$id->toArray()['id']:null;
    }
}
