<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/7/10
 * Time: 14:05
 */
namespace App\Models\Business;
use App\Models\CommonModel;
use App\Services\Impl\Business\SettlementServicesImpl;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class SettlementModel extends CommonModel{
    protected $table='bussiness';

    protected $primaryKey = 'id';

    public $timestamps = false;
    
    static public function getParentBuss($where,$page,$pagesize){
        //分页判断
        $bid = $where['buss_id'];
        if(empty($bid)){
            return null;
        }
        $create_date = $where['create_date'];

        if(!empty($where['status'])){
            $status = $where['status'];
            if(strstr($bid,',')){
                $bid = explode(',',$bid);
                $model= SettlementModel::select('buss_info.nick_name as username','bussiness.money','bussiness.id','bussiness.pbid','buss_tixian.create_date','buss_tixian.op_money','buss_tixian.real_name','buss_tixian.real_name','buss_tixian.tixian_account','buss_tixian.status','buss_tixian.tixian_type','buss_tixian.bid','buss_tixian.id as sid')
                ->rightJoin('buss_tixian','bussiness.id','=','buss_tixian.bid','buss_tixian.id as sid')
                ->rightJoin('buss_info','bussiness.id','=','buss_info.bid')
                ->where('buss_tixian.status','=',$status)
                ->where($create_date)
                ->whereIn('bussiness.id',$bid)
                ->where('buss_tixian.status','<>',0)
                ->orderBy('buss_tixian.create_date','DESC');
            }else{
                $model= SettlementModel::select('buss_info.nick_name as username','bussiness.money','bussiness.id','bussiness.pbid','buss_tixian.create_date','buss_tixian.op_money','buss_tixian.real_name','buss_tixian.real_name','buss_tixian.tixian_account','buss_tixian.status','buss_tixian.tixian_type','buss_tixian.bid','buss_tixian.id as sid')
                ->rightJoin('buss_tixian','bussiness.id','=','buss_tixian.bid')
                ->rightJoin('buss_info','bussiness.id','=','buss_info.bid')
                ->where('buss_tixian.status','=',$status)
                ->where($create_date)
                ->where('bussiness.id','=',$bid)
                ->where('buss_tixian.status','<>',0)
                ->orderBy('buss_tixian.create_date','DESC');
            }
        }else{
            if(strstr($bid,',')){
                $bid = explode(',',$bid);
                $model= SettlementModel::select('buss_info.nick_name as username','bussiness.money','bussiness.id','bussiness.pbid','buss_tixian.create_date','buss_tixian.op_money','buss_tixian.real_name','buss_tixian.real_name','buss_tixian.tixian_account','buss_tixian.status','buss_tixian.tixian_type','buss_tixian.bid','buss_tixian.id as sid')
                ->rightJoin('buss_tixian','bussiness.id','=','buss_tixian.bid')
                ->rightJoin('buss_info','bussiness.id','=','buss_info.bid')
                ->where($create_date)
                ->whereIn('bussiness.id',$bid)
                ->where('buss_tixian.status','<>',0)
                ->orderBy('buss_tixian.create_date','DESC');
            }else{
                $model= SettlementModel::select('buss_info.nick_name as username','bussiness.money','bussiness.id','bussiness.pbid','buss_tixian.create_date','buss_tixian.op_money','buss_tixian.real_name','buss_tixian.real_name','buss_tixian.tixian_account','buss_tixian.status','buss_tixian.tixian_type','buss_tixian.bid','buss_tixian.id as sid')
                ->rightJoin('buss_tixian','bussiness.id','=','buss_tixian.bid')
                ->rightJoin('buss_info','bussiness.id','=','buss_info.bid')
                ->where($create_date)
                ->where('bussiness.id','=',$bid)
                ->where('buss_tixian.status','<>',0)
                ->orderBy('buss_tixian.create_date','DESC');
            }
        }

        $data= self::getPages($model,$page,$pagesize);
        if($data && $data['count'] >0){
            return $data;
        }
        return null;
    }

    static public function getParentSum($where){
        //分页判断
        $bid = $where['buss_id'];
        $data= SettlementModel::select('money','id','pbid')
        ->where('id','=',$bid)
        ->get()
        ->first()
        ->toArray();
        if(!empty($data)){
            return $data;
        }
        return null;
    }
    
    //查询子商户总额
    static public function getSonSum($buss_id){
        $data= SettlementModel::select(DB::raw('SUM(money) as son_money'),'id','pbid')
        ->where('pbid','=',$buss_id)
        ->get()
        ->first()
        ->toArray();
        if(!empty($data)){
            return $data;
        }
        return null;
    }

    //查询子商户id
    static public function getSonBussId($buss_id){
        $data= SettlementModel::select('id','pbid','username')
        ->where('pbid','=',$buss_id)
        ->get()
        ->toArray();
        $id_list = array();

        if(!empty($data)){
            foreach ($data as $key => $value) {
                if(!empty($value['id'])){
                    $id_list[] = $value['id'];
                }
                if(!empty($value['username'])){
                    $name_list[] = $value['username'];
                }
            }
            $id_list = implode(',',$id_list);
            // unset($data);
            $data_list['id_list'] = $id_list;
            $data_list['name_list'] = $data;
            if(!empty($data_list)){
                return $data_list;
            }
        }
        
        return null;
    }

    //查询子商户id
    static public function getBussId($buss_id){
        
        if($buss_id == 0){
            $data= SettlementModel::select('id','pbid','username')
            ->where('pbid','=',0)
            ->get()
            ->toArray();
            $id_list = array();

            if(!empty($data)){
                foreach ($data as $key => $value) {
                    if(!empty($value['id'])){
                        $id_list[] = $value['id'];
                    }
                    if(!empty($value['username'])){
                        $name_list[] = $value['username'];
                    }
                    $data[$key]['id_list'] = $value['id'];
                    $data[$key]['name_list'] = $value['username'];
                }
                $id_list = implode(',',$id_list);
                // unset($data);
                $data_list['id_list'] = $id_list;
                $data_list['name_list'] = $data;
                if(!empty($data_list)){
                    return $data_list;
                }
            }
        }else{
            $data= SettlementModel::select('id','pbid','username')
            ->where('pbid','=',0)
            ->where('id','=',$buss_id)
            ->get()
            ->first()
            ->toArray();

            $id_list = array();
            $data_list['id_list'] = $data['id'];
            $data_list['name_list'] = $data['username'];
            if(!empty($data_list)){
                return $data_list;
            }
        }
        
        return null;
    }

    public static function getBussInfo($userId){
        $data = SettlementModel::select('username','password','create_time')->where('id','=',$userId)->first();
        return $data;
    }

    public static function deduct_money($array){
        $deduct_money = $array['op_money'];
        $lid = $array['lid'];
        $sbid = $array['sbid'];
        $data = SettlementModel::where('id',$sbid)->decrement('money', $deduct_money);
        return $data;
    }
    
    public static function get_buss_id($channel){

        $data = SettlementModel::select('id')->where('username','like',$channel.'%')->first();
        if($data){
            $data = json_decode($data,true);
            return $data['id'];
        }else {
            return $data;
        }
        
    }
}