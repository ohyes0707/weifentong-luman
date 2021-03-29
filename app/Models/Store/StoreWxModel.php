<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/11/3
 * Time: 11:19
 */
namespace App\Models\Store;
use App\Models\CommonModel;

class StoreWxModel extends CommonModel{

    protected $table = 'y_store_wx';

    protected $primaryKey = 'id';

    public $timestamps = false;

    public static function storeOrderAddWx(){
        return StoreWxModel::select('wx_id','wx_name')->where('wx_id','!=','0')->where('status','=','2')->get()->toArray();
    }

}