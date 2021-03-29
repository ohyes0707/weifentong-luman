<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/8/23
 * Time: 17:18
 */
namespace App\Models\User;
use App\Models\CommonModel;

class UserInfoModel extends CommonModel{
    protected $table = 'user_info';

    protected $primaryKey = 'uid';

    public $timestamps = false;
}