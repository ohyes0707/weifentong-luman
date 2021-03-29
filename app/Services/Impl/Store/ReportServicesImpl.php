<?php

namespace App\Services\Impl\Store;

use App\Models\Store\WxStoreModel;
use App\Services\CommonServices;
use App\Lib\WeChat\Third;
use App\Models\Wechat\WxInfoModel;



class ReportServicesImpl extends CommonServices
{
    /**
     */
    public static function getReportList($data){

        return WxStoreModel::ReportList($data);

    }

    public static function addreport($data){

        return WxStoreModel::addreport($data);

    }

    public static function get_wxid($rid){

        return WxStoreModel::get_wxid($rid);
    }


    public static function auth_back($queryauthcode, $confarray){

        $platform_id = $confarray['platform_id'];
        $rid = $confarray['rid']; //y_wx_report 的 id
//        $url = self::TEXT_URL . '/index.php/WxAuth/addwxauth';

        //TODO:域名检查
        $third = new Third(array('platform_id' => $platform_id, 'rid' => $rid));

        $third->log('auth_back plat_id=' . $platform_id);

        $result = $third->queryAuthInfo($queryauthcode);

        $result = $result['authorization_info'];

        $wx_info = $third->getWxInfo($result['authorizer_appid']);

        if ($result && $wx_info) {

            $decideResult = WxStoreModel::decideReport($wx_info);

            if($decideResult == false){
                header('Location:'.config('config.OPERATE_URL').'/index.php/store/report?action=error');
                exit();

            }

            if (!$third->check_priv($wx_info['authorization_info']['func_info']))//必须的权限
            {

                $third->setPreauthCode('', 0);
                //同一个码多次扫描可能权限有问题


                    header('Location:'.config('config.OPERATE_URL').'/index.php/store/report?action=limit');
                    exit();


            }
            $wxmodel = new WxInfoModel();
            $id = $wxmodel->add_wx($wx_info['authorizer_info']['user_name'], $platform_id); //wx_id;

            if ($id > 0) {
                //保存公众号token
                self::setToken($id, $result['authorizer_access_token'], $result['expires_in'], $result['authorizer_refresh_token']);
                //公众号信息

                $wxmodel->save_info($wx_info,2);

                WxStoreModel::changeReport($id, $wx_info);

                $select = array('default_shopid','default_shopname');
                $where = array('id'=>$id);

                $list = WxInfoModel::getWxInfoOne($select, $where);


                $third->setPreauthCode('',0);

//                if($list['default_shopname'] ==""){
                header('Location:'.config('config.OPERATE_URL').'/index.php/store/report?action=open&wxid='.$id);
                exit();

//                }else{
//                    header('Location:' . $urlArray[0].'?wxid='.$id);
//                }

            }

        }
    }


    /****
     * 设置微信token
     * @param $wx_id
     * @param $token
     * @param $expire
     * @param string $refresh_token
     * @return mixed
     */
    static public function setToken($wx_id,$token,$expire,$refresh_token = '')
    {
        $data['access_token'] = $token;
        $data['expires_in'] = time()+$expire-((int)($expire/12));
        if($refresh_token != '')$data['refresh_token'] = $refresh_token;
        $where = array('id'=>$wx_id);
        return WxInfoModel::updateWxInfo($where,$data);
    }

}
