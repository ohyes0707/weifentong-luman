<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/8/11
 * Time: 11:00
 */
namespace App\Models\Business;
use App\Models\Buss\BussModel;
use App\Models\CommonModel;
use Illuminate\Support\Facades\DB;

class MoneyModel extends CommonModel{

    protected $table = 'y_money_log';

    protected $primaryKey = 'id';

    public $timestamps = false;

    public static function fansEarn($start_date,$end_date,$page,$pagesize,$buss_id){
        if($start_date){
            $where[] = array('date','>=',$start_date);
        }else{
            $where[] = array('date','>=',date('Y-m-d',strtotime('-7days')));
        }
        if($end_date) {
            $where[] = array('date', '<=', $end_date);
        }else{
            $where[] = array('date', '<=', date('Y-m-d',time()));
        }
        $buss = MoneyModel::select('buss_id','parent_id')->where('buss_id','=',$buss_id)->orWhere('parent_id','=',$buss_id)->groupBy('buss_id')->get()->toArray();
        if($buss){
            foreach($buss as $k=>$v){
                $bid[$v['buss_id']] = $v['buss_id'];
                $bid[$v['parent_id']] = $v['parent_id'];
            }
            $buss_arr = BussModel::leftJoin('buss_info','bussiness.id','=','buss_info.bid')
                ->select('id','pbid','nick_name')
                ->whereIn('id',$bid);
            $buss_data = self::getPages($buss_arr,$page,$pagesize);
            $data = MoneyModel::select(DB::raw('sum(follow) as follow'),DB::raw('sum(unfollow) as unfollow'),DB::raw('sum(num) as num'),'date','buss_id')
                ->where($where)
                ->groupBy('date')
                ->groupBy('buss_id')
                ->orderBy('date','desc')
                ->get()
                ->toArray();
            foreach($buss_data['data'] as $k=>$v){
                foreach($data as $kk=>$vv){
                    if($v['id'] == $vv['buss_id']){
                        $buss_data['data'][$k]['count'][$vv['date']] = $vv;
                    }
                }
            }
            $father = BussModel::leftJoin('buss_info','bussiness.id','=','buss_info.bid')
                ->select('id','pbid','nick_name')
                ->where('id','=',$buss_id)
                ->first()->toArray();
            $buss_data['data']['father'] = $father;
            $buss_data['data']['count'] = $buss_data['count'];
            return $buss_data['data'];
        }else{
            return false;
        }

    }
    public static function fansEarn_child($start_date,$end_date,$page,$pagesize,$buss){
        if($start_date){
            $where[] = array('date','>=',$start_date);
        }else{
            $where[] = array('date','>=',date('Y-m-d',strtotime('-7days')));
        }
        if($end_date) {
            $where[] = array('date', '<=', $end_date);
        }else{
            $where[] = array('date', '<=', date('Y-m-d',time()));
        }
        if($buss)
            $where[] = array('buss_id','=',$buss);
        $data = MoneyModel::select(DB::raw('sum(follow) as follow'),DB::raw('sum(unfollow) as unfollow'),DB::raw('sum(num) as num'),'date','buss_id')
                            ->where($where)
                            ->groupBy('date')
                            ->orderBy('date','desc');
//                            ->get()
//                            ->toArray();
        $count_data = MoneyModel::select(DB::raw('sum(follow) as follow'),DB::raw('sum(unfollow) as unfollow'),DB::raw('sum(num) as num'),'date','buss_id')
                            ->where($where)
                            ->groupBy('date')
                            ->orderBy('date','desc')
                            ->get()
                            ->toArray();
        $count = count($count_data);
        $list = self::getPages($data,$page,$pagesize,$count);
        $total = MoneyModel::select(DB::raw('sum(follow) as follow'),DB::raw('sum(unfollow) as unfollow'),DB::raw('sum(num) as num'))
                                ->where($where)
                                ->get()
                                ->toArray();
        $arr['data'] = $list['data'];
        $arr['count'] = $list['count'];
        $arr['total'] = $total;
        return $arr;
    }
}