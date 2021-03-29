<?php
namespace App\Models\Count;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Models\CommonModel;

class StoreModel extends CommonModel
{
    protected $table = 'y_store';

    protected $primaryKey = 'store_id';

    public $timestamps = false;

    static public function getStoreInfo($store_id) 
    {
        $model = StoreModel::where('store_id', $store_id)->first();
        return $model?$model->toArray():null;
    }
}