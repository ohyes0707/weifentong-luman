<?php
/**
 * Created by PhpStorm.
 * User: li wei
 * Date: 2017/5/15
 * Time: 下午8:38
 */
namespace App\Models\User;

use App\Models\CommonModel;

class AdminModel extends CommonModel{

    protected $table = 'admin';

    protected $primaryKey = 'id';

    public $timestamps = false;

    /**
     * @param $userId
     * @return null
     */
    static public function getUserInfo($userId)
    {
        $model = AdminModel::select('username','user_mail','nick_name','password')
                                ->leftJoin('user_info','user.id','=','user_info.uid')
                                ->where('id', '=', $userId)
                                ->first();
        return $model?$model->toArray():null;
    }

    /**
     * @param $username
     * @return null
     */
    static public function getUserByUsername($username){
        $model = AdminModel::where('username', '=', $username)->get()->first();
        return $model?$model->toArray():null;
    }

}