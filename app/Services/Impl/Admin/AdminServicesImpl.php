<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/6/16
 * Time: 11:11
 */
namespace App\Services\Impl\Admin;
use App\Models\Admin\AdminLogModel;
use App\Models\Admin\AdminModel;

class AdminServicesImpl
{
    public static function getAdminInfo($userId)
    {
        return AdminModel::getAdminInfo($userId);
    }

    public static function managerlist()
    {

        return AdminModel::managerlist();
    }

    public static function addUser()
    {

        return AdminModel::addUser();

    }


    public static function editUser()
    {

        return AdminModel::editUser();

    }


    public static function setmanagerList(){

        return AdminModel::setmanagerList();
    }

    public static function roleList(){

        return AdminModel::roleList();
    }

    public static function editRole(){

        return AdminModel::editRole();
    }

    public static function addRole(){

        return AdminModel::addRole();
    }

    public static function getleftView(){
        return AdminModel::getleftView();
    }

    public static function orderLogAdd($username,$userid,$useragent,$message,$userip,$order_id){
        if($message){
            $insert = array(
                'operator'=>$username,
                'operator_id'=>$userid,
                'ip'=>$userip,
                'agent'=>$useragent,
                'datetime'=>date('Y-m-d H:i:s',time()),
                'action'=>$message,
                'order_id'=>$order_id
            );
            return AdminLogModel::orderLogAdd($insert);
        }else{
            return false;
        }
    }

    public static function orderLog($order_id,$uid,$start_date,$end_date,$page,$pagesize){
        return AdminLogModel::orderLog($order_id,$uid,$start_date,$end_date,$page,$pagesize);
    }
}