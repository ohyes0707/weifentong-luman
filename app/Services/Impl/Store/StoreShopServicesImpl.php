<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/11/4
 * Time: 16:37
 */
namespace App\Services\Impl\Store;
use App\Models\Buss\BussModel;
use App\Models\Store\BrandModel;
use App\Models\Store\StoreModel;
use App\Services\CommonServices;

class StoreShopServicesImpl extends CommonServices{
    public static function storeShopList($brand,$page,$pagesize){
        $data = StoreModel::storeShopList($brand,$page,$pagesize);
        if($data){
            if(isset($data['data']) && !empty($data['data'])){
                foreach($data['data'] as $k=>$v){
                    foreach($data['area_name'] as $kk=>$vv){
                        if($v['bid'] == $vv['bid'])
                            $data['data'][$k]['area_name'] = $vv['nick_name'];
                    }
                    foreach($data['brand_name'] as $kk=>$vv)
                        if($v['brand_id'] == $vv['brand_id'])
                            $data['data'][$k]['brand_name'] = $vv['brand_name'];
                }
            }
            $arr['data'] = $data['data'];
            $arr['count'] = $data['count'];
            $arr['brand'] = $data['brand_name'];
            $arr['brand_arr'] = $data['brand_arr'];
            return $arr;
        }
        return false;
    }

    public static function getAreaBrand(){
        $area = BussModel::getStoreArea();
        $brand = BrandModel::storeBrandList('',1,$pagesize=99999)['data'];
        $arr['area'] = $area;
        $arr['brand'] = $brand;
        return $arr;
    }

    public static function storeShopAdd($mac,$area,$brand,$shop){
        $data = StoreModel::storeShopAdd($mac,$area,$brand,$shop);
        return $data;
    }
}