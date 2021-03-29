<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/5/27
 * Time: 13:53
 */
namespace App\Models\Buss;
use App\Models\CommonModel;
use Illuminate\Support\Facades\Redis;

class BussInfoModel extends CommonModel{

    protected $table = 'buss_info';

    protected $primaryKey = 'bid';

    public $timestamps = false;

    /****
     * @param $bid
     * @return null
     */
    public static function getBussInfo($bid){
        $model = BussInfoModel::select('shangjia_url')->where('bid','=',$bid)->get()->first();
        return $model?$model->toArray():null;
    }

    static public function buss_redis(){
        $data = BussInfoModel::select('bid','buss_area')->get()->toArray();
        foreach($data as $k=>$v){
            if($v['buss_area'] == ''){
                $data[$k]['buss_area'] = '全国';
            }
            Redis::hset('bidredis',$v['bid'],$data[$k]['buss_area']);
        }
        return true;
    }
}