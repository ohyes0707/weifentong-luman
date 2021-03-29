<?php

namespace App\Http\Controllers\Operate;
use App\Http\Controllers\Controller;
use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Services\Impl\Channel\ChannelManageServicesImpl;
use Illuminate\Http\Request;

class ChannelManageController extends Controller{
    
    public function getChannelList(Request $request) {
        $query = array(
            'keycode' => $request->input('keycode'),
            'page' => $request->input('page'),
            'pagesize' => $request->input('pagesize'),
        );
        $data = ChannelManageServicesImpl::getChannelList($query);
        return ApiSuccessWrapper::success($data);
    }
    
    
    public function getUpdateChannelState(Request $request) {
        $query = array(
            'keycode' => $request->input('keycode'),
        );
        $data = ChannelManageServicesImpl::getUpdateChannelState($query);
        return ApiSuccessWrapper::success($data);
    }

    public function getCapacityList(Request $request) {
        $query = array(
            'keycode' => $request->input('keycode'),
            'time' => $request->input('time'),
            'sex' => $request->input('sex')
        );
        //判断是否是省份
        if($query['keycode']=='' || ChannelManageServicesImpl::getIsPro($query['keycode'])){
            $data = ChannelManageServicesImpl::getCapacityList($query);
        } else {
            $data = ChannelManageServicesImpl::getCapacitySonList($query);
            $data['type']=1;
        }
        
        return ApiSuccessWrapper::success($data);
    }
    

    public function getCapacityOrderList(Request $request) {
        $query = array(
            'keycode' => $request->input('keycode'),
            'time' => $request->input('time'),
            'sex' => $request->input('sex')
        );
        //判断是否是省份
        if($query['keycode']=='' || ChannelManageServicesImpl::getIsPro($query['keycode'])){
            $data = ChannelManageServicesImpl::getCapacityOrderList($query);
        } else {
            $data = ChannelManageServicesImpl::getCapacityOrderSonList($query);
            $data['type']=1;
        }
        
        return ApiSuccessWrapper::success($data);
    }
}