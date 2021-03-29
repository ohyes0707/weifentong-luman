<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/11/6
 * Time: 16:38
 */
namespace App\Services\Impl\Store;
use App\Models\Store\StoreDevmacModel;
use App\Services\CommonServices;

class StoreMacServicesImpl extends CommonServices{
    public static function macList($mac,$area,$brand,$store,$page,$pagesize){
        return StoreDevmacModel::macList($mac,$area,$brand,$store,$page,$pagesize);
    }
}