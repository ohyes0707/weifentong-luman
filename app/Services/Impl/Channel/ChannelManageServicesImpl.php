<?php
 
namespace App\Services\Impl\Channel;
use App\Models\Count\TaskSummaryModel;
use App\Services\CommonServices;
use App\Services\Impl\Common\ScreenServicesImpl;
use App\Models\Buss\BussModel;
use App\Models\Count\FansLogModel;
use App\Models\Count\CapacityLogModel;
use App\Models\Order\TaskModel;

class ChannelManageServicesImpl extends CommonServices{
    
    static public function getChannelList($query) {
        $operat_query = ScreenServicesImpl::getArray($query);
        $pbdata = BussModel::getChannelList($operat_query);
        $data['data'] = BussModel::getChannelDescList($pbdata['data']);
        $data['count'] = $pbdata['count'];
        return $data;
    }
    
    static public function getUpdateChannelState($param) {
        $operat_query = ScreenServicesImpl::getArray($query);
        $data = BussModel::getChannelList($operat_query);
        return $data;
    }
    
    static public function getCapacityList($query) {
        
        $operat_query = ScreenServicesImpl::getArray($query);
        $bid_province = CapacityLogModel::getBidProvince($operat_query);
        $bid_city = CapacityLogModel::getBidCity();
        $pbdata = FansLogModel::getCapacityList($operat_query);
        
        $data = FansLogModel::getCapacityDescList($bid_province,$bid_city,$pbdata,$operat_query);
        return $data;
    }
    
    static public function getCapacitySonList($query) {
        $operat_query = ScreenServicesImpl::getArray($query);
        $bid_city = CapacityLogModel::getBidSonCity($operat_query);
        $pbdata = FansLogModel::getCapacitySonList($operat_query);
        $data=array(
            'city_name'=>$bid_city['city_name'],
            'capacity_num'=>$bid_city['capacity_num'],
            'dbcapacity_num'=>$pbdata['num'],
            'boy_num'=>$bid_city['boy_num'],
            'girl_num'=>$bid_city['girl_num'],
            'type'=>'city'
        );
        if($query['sex'] == 1){
            $data['capacity_num'] = $bid_city['boy_num'];
            $data['dbcapacity_num'] = $bid_city['boy_num'] - $pbdata['num'];
        } elseif ($query['sex'] == 2) {
            $data['capacity_num'] = $bid_city['girl_num'];
            $data['dbcapacity_num'] = $bid_city['girl_num'] - $pbdata['num'];
        } else {
            $data['capacity_num'] = $bid_city['capacity_num'];
            $data['dbcapacity_num'] = $bid_city['capacity_num'] - $pbdata['num'];
        }
        return $data;
    }
    
    static public function getIsPro($keycode) {
        $bid_province = CapacityLogModel::getIsPro($keycode);
        return $bid_province;
    }
    
    static public function getCapacityOrderList($query) 
    {    
        $operat_query = ScreenServicesImpl::getArray($query);
        $bid_province = CapacityLogModel::getBidProvince($operat_query);
        $bid_city = CapacityLogModel::getBidCity();
        $pbdata = TaskModel::getOrderTaskAreaList($operat_query);
        $areadata = TaskModel::getAreaData($pbdata);
        $data = TaskModel::getCapacityDescList($bid_province,$bid_city,$areadata,$operat_query);
        return $data;
    }
    
    static public function getCapacityOrderSonList($query) 
    {    
        $operat_query = ScreenServicesImpl::getArray($query);
        $bid_city = CapacityLogModel::getBidSonCity($operat_query);
        $pbdata = TaskModel::getOrderTaskAreaList($operat_query);
        $areadata = TaskModel::getAreaData($pbdata);
        $data=array(
            'city_name'=>$bid_city['city_name'],
            'capacity_num'=>$bid_city['capacity_num'],
            'dbcapacity_num'=> isset($areadata[$bid_city['city_name']])?$areadata[$bid_city['city_name']]:0,
            'boy_num'=>$bid_city['boy_num'],
            'girl_num'=>$bid_city['girl_num'],
            'type'=>'city'
        );
        if($query['sex'] == 1){
            $data['capacity_num'] = $bid_city['boy_num'];
            $data['dbcapacity_num'] = $bid_city['boy_num'] - $data['dbcapacity_num'];
        } elseif ($query['sex'] == 2) {
            $data['capacity_num'] = $bid_city['girl_num'];
            $data['dbcapacity_num'] = $bid_city['girl_num'] - $data['dbcapacity_num'];
        } else {
            $data['capacity_num'] = $bid_city['capacity_num'];
            $data['dbcapacity_num'] = $bid_city['capacity_num'] - $data['dbcapacity_num'];
        }
        return $data;
    }
}