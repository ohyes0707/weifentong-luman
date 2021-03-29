<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/7/10
 * Time: 14:05
 */
namespace App\Models\Dandan;
use App\Models\CommonModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class DandanlogModel extends CommonModel{
    protected $table='rsend_false_log';

    protected $primaryKey = 'id';

    public $timestamps = false;
    
    static public function add_log($array){
        $logic=DandanlogModel::insert($array);
        return $logic?TRUE:FALSE;
    }
    
}