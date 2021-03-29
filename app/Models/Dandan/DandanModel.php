<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/8/11
 * Time: 11:00
 */
namespace App\Models\Dandan;
use App\Models\CommonModel;
use Illuminate\Support\Facades\DB;

class DandanModel extends CommonModel{

    protected $table = 'rsend_event';

    protected $primaryKey = 'id';

    public $timestamps = false;

    static public function add_data($array){
        $logic=DandanModel::insert($array);
        return $logic?TRUE:FALSE;
    }

    static public function setInc($id){
        $logic=DandanModel::where('id',$id)->increment("count");
        return $logic?TRUE:FALSE;
    }
    
    static public function rsend_delete($id){
        $logic=DandanModel::where('id',$id)->delete();
        return $logic?TRUE:FALSE;
    }

    static public function rsend_select(){
        $logic=DandanModel::where('id','>',0)->get()->toArray();
        return $logic;
    }
    
}