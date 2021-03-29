<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/11/3
 * Time: 14:23
 */
namespace App\Models\Store;
use App\Models\CommonModel;

class BrandModel extends CommonModel{

    protected $table = 'y_brand';

    public $timestamps = false;

    public static function storeBrandList($brand,$page,$pagesize){
        $where[] = array('brand_id','>',0);
        if($brand)
            $where[] = array('brand_id','=',$brand);
        $data = BrandModel::select('brand_id','brand_name')->where($where);
        $brand = BrandModel::select('brand_id','brand_name')->get()->toArray();
        $list = self::getPages($data,$page,$pagesize);
        $arr['data'] = $list['data'];
        $arr['count'] = $list['count'];
        $arr['brand'] = $brand;
        return $arr;
    }

}