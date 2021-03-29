<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/5/27
 * Time: 13:53
 */
namespace App\Models\Buss;
use App\Models\CommonModel;
use App\Models\Store\StoreModel;

class BussModel extends CommonModel{

    protected $table = 'bussiness';

    protected $primaryKey = 'id';

    public $timestamps = false;

    /**
     * 获取渠道列表
     * @return mixed 数组
     */
    static public function getBussList(){
        $data = BussModel::leftJoin('buss_info','buss_info.bid','=','bussiness.id')
                            ->select('id','username','if_child','nick_name','buss_area')
                            ->where('pbid','=',0)
                            ->where('status','=',1)
                            ->get()
                            ->toArray();

        foreach($data as $k=>$v){
            $str[] = $v['id'];
        }
        $list = BussModel::leftJoin('buss_info','buss_info.bid','=','bussiness.id')
                                ->select('id','username','if_child','nick_name','buss_area','pbid')
                                ->where('status','=',1)
                                ->whereIn('pbid',$str)
                                ->get()
                                ->toArray();
        foreach($data as $k=>$v){
            foreach($list as $key=>$value){
                if($value['pbid']==$v['id'] && $v['if_child']!=0){
                    $data[$k][$v['username']][] = $value;
                }
            }
        }
        return $data;
    }

    /**
     * 根据渠道名称获取渠道信息
     * @param $username
     * @return null
     */
    static public function getUserByUsername($username){
        $model = BussModel::where('username', '=', $username)->get()->first();
        return $model?$model->toArray():null;
    }
    
    static public function getBussName($bussid,$pbid,$buss) {
        if($buss!=''){
            $list = BussModel::leftJoin('buss_info as pbnick','bussiness.pbid','=','pbnick.bid')
                                    ->leftJoin('buss_info as nick','bussiness.id','=','nick.bid')
                                    ->select('bussiness.id','nick.nick_name as username','bussiness.pbid','pbnick.nick_name as pbusername')
                                    ->where('bussiness.id','=',$buss)
                                    ->get()
                                    ->toArray();
        } elseif ($buss==''&&$pbid!='') {
            $list = BussModel::leftJoin('buss_info as pbnick','bussiness.pbid','=','pbnick.bid')
                                    ->leftJoin('buss_info as nick','bussiness.id','=','nick.bid')
                                    ->select('bussiness.id','nick.nick_name as username','bussiness.pbid','pbnick.nick_name as pbusername')
                                    ->where('bussiness.pbid','=',$pbid)
                                    ->orWhere('bussiness.id','=',$pbid)
                                    ->get()
                                    ->toArray();
        } else {
            $list = BussModel::leftJoin('buss_info as pbnick','bussiness.pbid','=','pbnick.bid')
                                    ->leftJoin('buss_info as nick','bussiness.id','=','nick.bid')
                                    ->select('bussiness.id','nick.nick_name as username','bussiness.pbid','pbnick.nick_name as pbusername')
                                    ->whereIn('bussiness.id',$bussid)
                                    ->get()
                                    ->toArray();
        }

        return $list;
    }
    
    /**
     * 根据渠道id获取渠道名称
     * @param $username
     * @return null
     */
    static public function getUserByBussId($id){
        $model = BussModel::where('id', '=', $id)->get()->first();
        return $model?$model->toArray()['username']:null;
    }
    
    
    /**
     * 根据渠道id获取渠道名称
     * @param $username
     * @return null
     */
    static public function getUserPbName($id){
        $pbid = BussModel::where('id', '=', $id)->get()->first()->toArray()['pbid'];
        $model = BussModel::where('id', '=', $pbid)->get()->first();
        return $model?$model->toArray()['username']:null;
    }
    
    /**
     * 根据渠道名称来获取渠道列表
     * @param $username
     * @return null
     */
    static public function getChannelList($query) {
        $model = BussModel::select('id','username','cost_price','status','nick_name')
                ->leftJoin('buss_info','bussiness.id','=','buss_info.bid')
                ->where('pbid','=',0);
        if($query['keycode']!=null){
            $model->where('nick_name','like','%'.$query['keycode'].'%');
        }
        $model->orderBy('status','desc')->orderBy('cost_price','desc')->orderBy('id','desc');
        $data = self::getPages($model,$query['page'],$query['pagesize']);
        return $data;
    }
    
    static public function getChannelDescList($array) {
        $pbarray = array();
        foreach ($array as $key => $value) {
            $pbarray[$value['id']] = $value;
        }
        $model = BussModel::select('id','username','cost_price','status','pbid','nick_name')
                ->leftJoin('buss_info','bussiness.id','=','buss_info.bid')
                ->whereIn('pbid', array_keys($pbarray))->get()->toArray();
        foreach ($model as $key => $value) {
            $pbarray[$value['pbid']]['list'][]=$value;
        }
        return $pbarray;

    }

    static public function getStoreArea(){
        //美业id
        $my_id = config('config.MEIYE_ID');
        if($my_id){
            $area = BussModel::leftJoin('buss_info','bussiness.id','=','buss_info.bid')->select('id','nick_name')->where('pbid','=',$my_id)->get()->toArray();
            return $area;
        }
        return false;
    }
}