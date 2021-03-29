<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/11/6
 * Time: 15:42
 */
namespace App\Models\Store;
use App\Models\Buss\BussInfoModel;
use App\Models\CommonModel;

class StoreDevmacModel extends CommonModel{

    protected $table = 'y_store_devmac';

    public $timestamps = false;

    public static function macList($mac,$area,$brand,$store,$page,$pagesize){
        $where[] = array('id','>',0);
        if($mac)
            $where[] = array('id','=',$mac);
        if($area)
            $where[] = array('bid','=',$area);
        if($brand)
            $where[] = array('brand_id','=',$brand);
        if($store)
            $where[] = array('y_store_devmac.store_id','=',$store);
        $data = StoreDevmacModel::leftJoin('y_store','y_store_devmac.store_id','=','y_store.store_id')->where($where);
        $list = self::getPages($data,$page,$pagesize);
        if($list['data']){
            foreach ($list['data'] as $k=>$v){
                $bid[$v['bid']] = $v['bid'];
                $brand_id[$v['brand_id']] = $v['brand_id'];
            }
            $area_name = BussInfoModel::select('bid','nick_name')->whereIn('bid',$bid)->get()->toArray();
            $brand_name = BrandModel::select('brand_id','brand_name')->whereIn('brand_id',$brand_id)->get()->toArray();
            foreach ($list['data'] as $k=>$v){
                foreach ($area_name as $kk=>$vv){
                    if($v['bid'] == $vv['bid'])
                        $list['data'][$k]['bid'] = $vv['nick_name'];
                }
                foreach ($brand_name as $kk=>$vv){
                    if($v['brand_id'] == $vv['brand_id'])
                        $list['data'][$k]['brand_id'] = $vv['brand_name'];
                }
            }
            $arr['data'] = $list['data'];
            $select = StoreDevmacModel::leftJoin('y_store','y_store_devmac.store_id','=','y_store.store_id')->get()->toArray();
            foreach ($select as $k=>$v){
                $mac_total[$v['dev_mac']] = array(
                    'mac_id'=>$v['id'],
                    'mac'=>$v['dev_mac']
                );
                $store_total[$v['desc']] = array(
                    'store_id'=>$v['store_id'],
                    'store'=>$v['desc']
                );
                $area_id_total[$v['bid']] = $v['bid'];
                $brand_id_total[$v['brand_id']] = $v['brand_id'];
            }
            $area_total = BussInfoModel::select('bid','nick_name')->whereIn('bid',$area_id_total)->get()->toArray();
            $brand_total = BrandModel::select('brand_id','brand_name')->whereIn('brand_id',$brand_id_total)->get()->toArray();
            $arr['count'] = $list['count'];
            $arr['mac_total'] = $mac_total;
            $arr['area_total'] = $area_total;
            $arr['brand_total'] = $brand_total;
            $arr['store_total'] = $store_total;
            return $arr;
        }
        return false;
    }

    /**
     * 根据bmac获取门店相关信息
     * @param $bmac
     * @return null
     */
    public static function getStoreByBmac($bmac){
        $where[] = array('y_store_devmac.dev_mac','=',$bmac);
        $list = StoreDevmacModel::select('y_store.bid','y_store.pid','y_store.brand_id','y_store.store_id','y_store.desc','y_brand.brand_name','y_brand.brand_portal')
            ->leftJoin('y_store','y_store_devmac.store_id','=','y_store.store_id')
            ->leftJoin('y_brand','y_store.brand_id','=','y_brand.brand_id')
            ->where($where)->first();
        return  $list?$list->toArray():null;

    }
}