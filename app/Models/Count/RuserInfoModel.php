<?php

namespace App\Models\Count;

use App\Models\CommonModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class RuserInfoModel extends CommonModel{

    protected $table = 'ruser_info_1';

    protected $primaryKey = 'id';

    public $timestamps = false;


    static public function getSomeUserInfo($openid,$num){
        $request = Request::capture();
        $table = $request->input('table') ? $request->input('table'):null; 
        $where[]=array('id','>=',($openid-1)*$num);
        $where[]=array('id','<=',($openid)*$num);
        $model =  DB::table($table)->where($where)->get();
        return $model?$model->toArray():null;
    }

}