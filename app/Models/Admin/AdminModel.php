<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/6/16
 * Time: 11:16
 */
namespace App\Models\Admin;
use App\Models\CommonModel;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Support\Facades\DB;


class AdminModel extends CommonModel{

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

    /**
     *  新增角色
     */
    public static function addUser()
    {


        if (empty($_GET['name'])) {
            $userGroup = DB::table('user_group')->select('id', 'title')->get()->toArray();
            return $userGroup;
        } else {
            $create_time = time();
            $password = md5('123456' . $create_time);


            $namelist = AdminModel::select('username')->get()->toArray();
            $namelist =self::object_array($namelist);
            $newlist = array();
            foreach ($namelist as $key=>$value)
            {

                $newlist[] = $value['username'];

            }
            if(in_array($_GET['name'],$newlist))
            {

                return 9999;

            }else{
                $id = AdminModel::insertGetId(
                    ['username' => $_GET['name'], 'create_time' => $create_time, 'password' => $password, 'remark' => $_GET['remark'], 'roleid' => $_GET['role']]
                );
                return $id;

            }


        }

    }

    /**
     * 编辑角色
     */
    public static function editUser(){


        if(empty($_GET['name'])){

            $data = AdminModel::select('username','remark','roleid')->where('id','=',$_GET['aid'])->first();

            $userGroup = DB::table('user_group')->select('id','title')->get()->toArray();

            $data['userGroup'] = $userGroup;

            return $data;
        }else{

            if(isset($_GET['password']))
            {        // md5 加密规则
//                $create_time = AdminModel::select('create_time')->where('id','=',$_GET['aid'])->first()->create_time;
                $create_time = time();
                $password = md5('123456'.$create_time);
               $result =  DB::table('admin')->where('id', $_GET['aid'])->update(['username' => $_GET['name'],'password'=>$password,'remark'=>$_GET['remark'],'roleid'=>$_GET['role'],'create_time'=>$create_time]);

            }else{
               $result =  DB::table('admin')->where('id', $_GET['aid'])->update(['username' => $_GET['name'],'remark'=>$_GET['remark'],'roleid'=>$_GET['role']]);

            }
           return $result;
        }

    }

    /**
     *  删除 禁止 开启 得用 in
     */
    public static function setmanagerList(){

        if(isset($_GET['type']) && isset($_GET['id'])){

            $type = $_GET['type'];


            // id 转化为数组

            if(strpos($_GET['id'], ',') !== false){
                $idstring = substr($_GET['id'],0,strlen($_GET['id'])-1);

                if(strpos($idstring, ',') !== false){
                    $idArray = explode(',',$idstring);
                }else{
                    $idArray = array();
                    $idArray[0] = $idstring;
                }

            }else{
                $idArray = array();
                $idArray[0] = $_GET['id'];

            }



            if($type=='forbidden'){

               $result =  AdminModel::whereIn('username', $idArray)->update(['status' => 0]);

               return $result;


            }elseif($type=='delete'){

                $result = AdminModel::whereIn('username', $idArray)->delete();

                return $result;

            }elseif($type=='use'){


                $result = AdminModel::whereIn('username', $idArray)->update(['status' => 1]);

                return $result;
            }


        }


    }


    /**
     *  角色列表
     */
    public static function roleList(){

        if(isset($_GET['name'])){

            if(strpos($_GET['name'], ',') !== false){
                $idstring = substr($_GET['name'],0,strlen($_GET['name'])-1);

                if(strpos($idstring, ',') !== false){
                    $idArray = explode(',',$idstring);
                }else{
                    $idArray = array();
                    $idArray[0] = $idstring;
                }

            }else{
                $idArray = array();
                $idArray[0] = $_GET['name'];

            }

            $result = DB::table('user_group')->whereIn('title',$idArray)->delete();
            return $result;
        }else{
            $userGroup = DB::table('user_group')->select('id', 'title')->get()->toArray();
            return $userGroup;
        }

    }


    /**
     *  编辑角色
     */
    public static function editRole(){

        if(isset($_GET['lookdata']) || isset($_GET['operatedata'])){
            $namelist = DB::table('user_group')->get()->toArray();
            $namelist =self::object_array($namelist);
            $newlist = array();
            foreach ($namelist as $key=>$value)
            {

                $newlist[] = $value['title'];

            }
//            if(in_array($_GET['name'],$newlist))
//            {
//                return 9999;
//
//            }else{
                $id =  DB::table('user_group')->where('id','=',$_GET['id'])->update(['title' => $_GET['name'],'lookdata'=>$_GET['lookdata'],'operatedata'=>$_GET['operatedata']]);
                return $id;
//            }
        }else{
            $topmoudle = DB::table('module')->select('topModule')->distinct()->get()->toArray();
            $bottommoudle = DB::table('module')->select('id','topModule','bottomModule')->distinct()->get()->toArray();
            $data['topmodele'] = $topmoudle;
            $data['bottommodele'] = $bottommoudle;

            $roleInfo = DB::table('user_group')->where('id','=',$_GET['id'])->get()->toArray();
            $data['roleInfo'] = $roleInfo;
            return $data;
        }

    }

    /**
     * 新增角色
     */
    public static function addRole(){

        if(isset($_GET['lookdata']) || isset($_GET['operatedata']))
        {

            $namelist = DB::table('user_group')->get()->toArray();
            $namelist =self::object_array($namelist);
            $newlist = array();
            foreach ($namelist as $key=>$value)
            {

                $newlist[] = $value['title'];

            }
            if(in_array($_GET['name'],$newlist))
            {
                return 9999;

            }else{
                $id = DB::table('user_group')->insertGetId(
                    ['title' => $_GET['name'], 'lookdata' => $_GET['lookdata'], 'operatedata' => $_GET['operatedata']]
                );
                return $id;
            }

        }else{

            $topmoudle = DB::table('module')->select('topModule')->distinct()->get()->toArray();
            $bottommoudle = DB::table('module')->select('id','topModule','bottomModule')->distinct()->get()->toArray();
            $data['topmodele'] = $topmoudle;
            $data['bottommodele'] = $bottommoudle;
            return $data;
        }



    }

    /**
     * 左视图规则
     */
    public static function getleftView(){

        $id = $_GET['adminid'];
        $roleid =  AdminModel::select('roleid')->where('id','=',$id)->first()->roleid;
        $lookdata =  DB::table('user_group')->select('lookdata')->where('id','=',$roleid)->first()->lookdata;

        $operatedata =  DB::table('user_group')->select('operatedata')->where('id','=',$roleid)->first()->operatedata;
        $operatedataArray = explode(',',$operatedata);
        $operatedataArray = self::object_array($operatedataArray);
        $lookdataArray = explode(',',$lookdata);
        $lookdata =  DB::table('module')->whereIn('id',$lookdataArray)->select()->get()->toArray();
        $lookdata = self::object_array($lookdata);
        foreach($lookdata as $key =>$value)
        {
            $topModule[] = $value['topModule'];
            $lookdata[$key]['operate'] = '0';
            foreach($operatedataArray as $k=>$v)
            {
                if($v==$value['id']){
                    $lookdata[$key]['operate'] = $v;
                }
            }
        }
        $topModule = array_unique($topModule);
        $newdata['data'] = $lookdata;
        $newdata['topModule'] = $topModule;
        return $newdata;
    }

    public static function object_array($array)
    {
        if (is_object($array)) {
            $array = (array)$array;
        }
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = self::object_array($value);
            }
        }
        return $array;
    }




}