<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/6/16
 * Time: 11:16
 */
namespace App\Models\Api;
use App\Models\CommonModel;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Support\Facades\DB;


class RegionModel extends CommonModel{

    protected $table = 'region';

    protected $primaryKey = 'id';

    public $timestamps = false;

    public static function getArea($code){
        $data = array(
            'province'=>'',
            'city'=>''
        );
        if($code<100){
            $model = RegionModel::where('code','=',$code)->first();
            if($model){
                $model = $model->toArray();
                $data['province'] =mb_substr($model['name'],0,2,'utf-8');
            }
        }elseif($code<1000000) {
           $pid = substr($code, 0, 2);
           $cid = substr($code, 0, 4);
           $pmodel = RegionModel::where('code','=',$pid)->first()->toArray();
           $cmodel = RegionModel::where('code','=',$cid)->first()->toArray();
           $data['province'] =mb_substr($pmodel['name'],0,2,'utf-8');
           $data['city'] =mb_substr($cmodel['name'],0,2,'utf-8');
        }else {
            
        }
        return $data;
    }

}