<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/5/27
 * Time: 14:07
 */
namespace App\Models\Order;
use App\Models\CommonModel;
use Illuminate\Support\Facades\Redis;

class CountModel extends CommonModel{

    protected $table = 'y_task_summary';

    protected $primaryKey = 'id';

    public $timestamps = false;

    public static function getCount($arr,$start_date,$end_date){
        $date = date('Y-m-d',time());
        $redis = date('Ymd',time());
        foreach($arr as $k=>$v){
            if(!isset($v['order_id'])||$v['order_id']==''){
                $arr[$k]['total_fans'] ='-';
                $arr[$k]['subscribe_today']='-';
                $arr[$k]['un_subscribe_today']='-';
                $arr[$k]['un_subscribe']='-';
            } else {
                //订单已涨粉
                $arr[$k]['total_fans'] = Redis::get('tot-'.$v['order_id'].'-'.$v['buss_id']);
                //今日涨粉数
                $arr[$k]['subscribe_today'] = Redis::hget($redis,'new-'.$v['order_id'].'-'.$v['buss_id'].'-3-1')+Redis::hget($redis,'old-'.$v['order_id'].'-'.$v['buss_id'].'-3-1');
                //今日取关数
                $arr[$k]['un_subscribe_today'] = Redis::hget($redis,'nqg-'.$v['order_id'].'-'.$v['buss_id'].'-1')+Redis::hget($redis,'nqg-'.$v['order_id'].'-'.$v['buss_id'].'-2');
                //总取关数
                $arr[$k]['un_subscribe'] = CountModel::where([
                        ['order_id','=',$v['order_id']]
                    ])
                    ->sum('new_unfollow_repeat')+CountModel::where([
                        ['order_id','=',$v['order_id']]
                    ])
                        ->sum('old_unfollow_repeat');
            }
        }
        return $arr;
    }
}