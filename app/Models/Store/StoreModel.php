<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/11/3
 * Time: 14:07
 */
namespace App\Models\Store;
use App\Models\Buss\BussInfoModel;
use App\Models\CommonModel;

class StoreModel extends CommonModel{

    protected $table = 'y_store';

    public $timestamps = false;

    public static function getStoreList(){
        //美业id
        $my_id = config('config.MEIYE_ID');
        if($my_id){
            //区域id
            $area_id = StoreModel::select('bid')->get()->toArray();
            //区域名
            $area = BussInfoModel::select('bid','nick_name')->whereIn('bid',$area_id)->get()->toArray();
            $arr['area'] = $area;
            return $arr;
        }else{
            return false;
        }
    }

    public static function storeShopList($brand,$page,$pagesize){
        $where[] = array('store_id','>',0);
        if($brand)
            $where[] = array('brand_id','=',$brand);
        $data = StoreModel::select('store_id','bid','brand_id','desc')->where($where);
        $list = self::getPages($data,$page,$pagesize);
        if($list['data']){
            foreach($list['data'] as $k=>$v){
                $area_id[$v['bid']] = $v['bid'];
                $brand_id[$v['brand_id']] = $v['brand_id'];
            }
            if(isset($area_id) && !empty($area_id))
                $area_name = BussInfoModel::select('bid','nick_name')->whereIn('bid',$area_id)->get()->toArray();

            if(isset($brand_id) && !empty($brand_id))
                $brand_name = BrandModel::select('brand_id','brand_name')->whereIn('brand_id',$brand_id)->get()->toArray();
            $bid_arr = StoreModel::select('brand_id')->get()->toArray();
            $brand_arr = BrandModel::select('brand_id','brand_name')->whereIn('brand_id',$bid_arr)->get()->toArray();
            $arr['data'] = $list['data'];
            $arr['count'] = $list['count'];
            $arr['area_name'] = $area_name;
            $arr['brand_name'] = $brand_name;
            $arr['brand_arr'] = $brand_arr;
            return $arr;
        }
        return false;
    }

    public static function storeShopAdd($mac,$area,$brand,$shop){
        //美业id
        $pid = config('config.MEIYE_ID');
        if($mac && $area && $brand && $shop){
            $store_id = StoreModel::insertGetId(['bid'=>$area,'pid'=>$pid,'brand_id'=>$brand,'desc'=>$shop]);
            if($store_id)
                StoreDevmacModel::insert(['store_id'=>$store_id,'dev_mac'=>$mac]);
            return $store_id;
        }
        return false;
    }
}