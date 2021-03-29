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


class FansModel extends CommonModel{

    protected $table = 'y_fans_log';

    protected $primaryKey = 'id';

    public $timestamps = false;

    public static function get_ghid_fan($userMac){
        // var_dump($ghid);die;
        $date_mac = date("Y-m-d 00:00:00");
        // var_dump($date_mac);die;
        $request = FansModel::select('id')
              ->where('date','>',$date_mac)
              // ->where('ghid','=',$ghid)
              ->where('mac','=',$userMac)
              ->first();

        return $request?$request->toArray():null;
    }

    public static function sum_total($mapp){
        $time = $mapp['pdate'];
        $bid = $mapp['bid'];

        // $start = date("$time 00:00:00");
        // $end = date("$time 24:00:00");
        $start = date('Y-m-d 00:00:00',strtotime($time));
        $end = date('Y-m-d 23:59:59',strtotime($time));
        // var_dump($start,$end);die;
        $request = FansModel::select(DB::raw('count(0) as sum'))
              ->whereBetween('date',[$start,$end])
              ->where('bid','=',$bid)
              ->first();

        return $request?$request->toArray():null;
    }

}