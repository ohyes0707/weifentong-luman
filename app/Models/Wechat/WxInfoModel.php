<?php
/**
 * Created by PhpStorm.
 * User: li wei
 * Date: 2017/5/15
 * Time: 下午8:38
 */
namespace App\Models\Wechat;

use App\Models\CommonModel;
use App\Lib\HttpUtils\Net;
use App\Lib\HttpUtils\File;
use App\Models\Order\OrderModel;
use App\Models\User\UserInfoModel;
use App\Models\User\UserModel;
use App\Models\Store\WxStoreModel;
use App\Models\Report\WxReportModel;
use Illuminate\Support\Facades\DB;

class WxInfoModel extends CommonModel{

    protected $table = 'wx_info';

    protected $primaryKey = 'id';

    public $id;

    public $timestamps = false;

    /**更新微信信息
     * @param $where
     * @param $data
     */
    static public function updateWxInfo($where,$data,$orwhere = null){

        $sql = WxInfoModel::where($where);
        if($orwhere)
            $sql = $sql->orwhere($orwhere);
        return WxInfoModel::where($where)->update($data);
    }



    /**
     * 获取微信列表
     * @param $where
     * @return null
     */
    static public function getWxInfoAll($where){
        $model = WxInfoModel::where($where)->get();
        return $model?$model->toArray():null;
    }

    /***
     * 获取某条微信信息
     * @param $select
     * @param $where
     * @return null
     */
    static public function getWxInfoOne($select,$where){
        $model = WxInfoModel::select($select)->where($where)->first();
        return $model?$model->toArray():null;
    }


    /***
     * 获取订单某个字段的集合
     * @param $select
     * @param $where
     * @return null
     */
    static public function getwxid($where,$select){

        $idArray =  DB::table('y_order')->where($where)->select($select)->get()->toArray();
        return $idArray?$idArray:null;
    }





    /**
     * 获取美业授权成功的报备列表
     * @param $where
     * @param $orwhere
     * @return null
     */
    static public function getSuccessAuthReport($where){
        $model = WxStoreModel::where($where)->get();
        return $model?$model->toArray():null;
    }


    /**
     * @param $where
     * @param $orwhere
     * @param $data  美业更新
     * @return mixed
     */
    static public function updateReportOrWhere($where,$data){
        return WxStoreModel::where($where)->update($data);
    }


    /***
     * 保存微信信息
     * @param $data
     * @return null
     */
    static public function saveWxInfo($data){
        return WxInfoModel::insertGetId($data);
    }


    //注意会修改id 保存相对应的微信数据
     function add_wx($ghid,$plat_id=0)
    {
        if($ghid == '')return 0;
        $data = array(
            ['ghid','=',$ghid],
            ['plat_id','=',$plat_id]
        );


        if(WxInfoModel::where($data)->get()->first()!=null){
            $list = WxInfoModel::where($data)->get()->first()->toArray();
            if($list != ""){
                $this->id = $list['id'];
                self::updateWxInfo(array(['id','=',$list['id']]),array('status'=>1,'plat_id'=>$plat_id));

            }
        }else{
            $this->id  = self::saveWxInfo(array('ghid'=>$ghid,'status'=>1,'plat_id'=>$plat_id));
        }


        return $this->id;


    }



    //保存公众号信息
    //nick_name，    head_img       service_type_info 公众号类型
    //
      public function save_info($info,$ismeiye=0)
    {

        $info2 = $info['authorization_info'];
        $info = $info['authorizer_info'];

        if($info == null)return false;
        $tmp = array();
        if($ismeiye>0){
            $data['type'] = 2;
        }
        $data['wx_name'] = $info['nick_name'];
        $data['service_type'] = $info['service_type_info']['id'];
        $data['verify_type'] = $info['verify_type_info']['id'];
        $data['ghid'] = $info['user_name'];
        foreach ($info['business_info'] as $key => $value) {

            $tmp[] = $value;
        }
        $data['business_info'] = join(',',$tmp);
        $data['head_img'] = './storages/Wx/'.date('Y-m-d',time()).'/'.date('His',time()).rand(0,9999999);
        $data['head_img'] = @Net::download_img($info['head_img'],$data['head_img']);

        $data['head_img'] = str_replace('./storages/Wx/', '', $data['head_img']);
        $resourcedata = array(
            ['id','=',$this->id],
        );

        $string = array('head_img','qrcode_url');


        $old_list = self::getWxInfoOne($string,$resourcedata);

        $old_list['head_img'] = File::get_safe_filename($old_list['head_img']);



        if($data['head_img'] != '' && $old_list['head_img'] != '')@unlink('./storage/Wx/'.$old_list['head_img']);

        $data['wx_qrcodeurl'] = $info['qrcode_url'];//服务器的二维码地址

        if($old_list['qrcode_url'] == '')
        {
            $data['qrcode_url'] = './storages/Wxqrcode/'.date('Y-m-d',time()).'/'.date('His',time()).rand(0,9999999);
            $data['qrcode_url'] = Net::download_img($info['qrcode_url'],$data['qrcode_url']);
            $data['qrcode_url'] = str_replace('./storages/Wxqrcode/', '', $data['qrcode_url']);
        }


        $data['appid'] = $info2['authorizer_appid'];
         $this->save_privileges($info2['func_info']);
        self::updateWxInfo(array(['id','=',$this->id]),$data);
        return true;
    }


    // 请注意，由于现在公众号可以自定义选择部分权限授权给第三方平台，
    // 因此第三方平台开发者需要通过该接口来获取公众号具体授权了哪些权限，
    // 而不是简单地认为自己声明的权限就是公众号授权的权限。
    public function save_privileges($func_info)
    {
        $arr = array();
        foreach ($func_info as $key => $value) {
            $arr[] = $value['funcscope_category']['id'];
        }
        $arrstring = join(',',$arr);
        $data['privileges']  =  $arrstring;
        self::updateWxInfo(array(['id','=',$this->id]),$data);
    }

    /**
     * 运营系统公众号列表
     */
    static public function wechatList($ghid,$page,$pagesize){
        if($ghid){
            $where[] = array('wx_info.wx_name','like','%'.$ghid.'%');
        }else{
            $where[] = array('wx_info.id','>',0);
        }
        $wx_info = WxInfoModel::select('id','wx_name','service_type','verify_type','ghid','head_img','default_shopname',DB::raw('CASE WHEN status = 1 THEN 1 WHEN status = 0 THEN 2
        WHEN status = 2 THEN 3 END AS status'))->where($where)->orderBy('status','asc')->orderBy('id','desc');
        $wx_name = WxInfoModel::select('id','wx_name')->get()->toArray();
        $wx_arr = self::getPages($wx_info,$page,$pagesize);
        if(!empty($wx_arr['data'])){
            foreach($wx_arr['data'] as $k=>$v){
                $wx_id[] = $v['id'];
            }
            $uid = WxReportModel::select('user_name','wx_id')->whereIn('wx_id',$wx_id)->where('status','=',3)->groupBy('wx_id')->get()->toArray();
            if($uid){
                foreach($uid as $k=>$v){
                    $user = UserModel::leftJoin('user_info','user.id','=','user_info.uid')->select('user.id','nick_name','type')->whereIn('user.id',$uid)->get()->toArray();
                }
            }
            foreach($wx_arr['data'] as $k=>$v){
                $wx_arr['data'][$k]['uid'] = '';
                if(isset($uid) && !empty($uid)){
                    foreach($uid as $kk=>$vv){
                        if($v['id'] == $vv['wx_id'])
                            $wx_arr['data'][$k]['uid'] = $vv['user_name'];
                    }
                }
            }
            foreach($wx_arr['data'] as $k=>$v){
                $wx_arr['data'][$k]['nick_name'] = '';
                $wx_arr['data'][$k]['type'] = '';
                if(isset($user) && !empty($user)){
                    foreach($user as $kk=>$vv){
                        if($v['uid'] == $vv['id']){
                            $wx_arr['data'][$k]['nick_name'] = $vv['nick_name'];
                            $wx_arr['data'][$k]['type'] = $vv['type'];
                        }
                    }
                }

            }
            $arr['count'] = $wx_arr['count'];
            $arr['data'] = $wx_arr['data'];
            $arr['wx_name'] = $wx_name;
            return $arr;
        }else{
            return false;
        }
    }
}





























