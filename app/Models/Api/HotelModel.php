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


class HotelModel extends CommonModel{

    protected $table = 'admin';

    protected $primaryKey = 'id';

    public $timestamps = false;

    public static function getAdminInfo($userId){
        $data = AdminModel::select('username','password','create_time')->where('id','=',$userId)->first();
        return $data;
    }

    /**
     * 管理员列表
     */
    public static function managerlist(){

        $data = AdminModel::select('id','username','remark','status','roleid')->where('username','<>','admin')->get()->toArray();
        $data = self::object_array($data);

        foreach ($data as $key=>$value)
        {

            $rolename = DB::table('user_group')->where('id','=',$value['roleid'])->select('title')->first();
            if($rolename){
                $data[$key]['rolename'] = $rolename->title;
            }else{
                $data[$key]['rolename'] = '';
            }

        }

        return $data;
    }

}