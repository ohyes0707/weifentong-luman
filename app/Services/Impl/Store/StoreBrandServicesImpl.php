<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/11/4
 * Time: 14:27
 */
namespace App\Services\Impl\Store;
use App\Models\Store\BrandModel;
use App\Services\CommonServices;
use App\Models\Store\StoreDevmacModel;

class StoreBrandServicesImpl extends CommonServices{
    public static function storeBrandList($brand,$page,$pagesize){
        $data = BrandModel::storeBrandList($brand,$page,$pagesize);
        return $data;
    }

    /**
     * 根据bmac获取门店相关信息
     * @param $bmac
     * @return null
     */
    public static function getStoreByBmac($bmac){
        $data = StoreDevmacModel::getStoreByBmac($bmac);
        return $data;
    }
}