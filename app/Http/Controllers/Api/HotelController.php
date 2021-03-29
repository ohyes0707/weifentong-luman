<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/8/1
 * Time: 16:07
 */
namespace App\Http\Controllers\Api;
use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Http\Controllers\Controller;
use App\Services\Impl\Business\SettlementServicesImpl;
use App\Services\Impl\Api\HotelServicesImpl;
use App\Services\Impl\Order\BussServicesImpl;
use Illuminate\Support\Facades\Redis;

use Illuminate\Http\Request;

class HotelController extends ApiController{

    const API_URL = 'api.weifentong.com.cn/index.php/api';
    const NEW_API_URL = 'http://api.youfentong.com/index.php/user/getWxInfo/v1.0';
    //const API_URL = 'http://test.gwifi.com.cn/cmps';
    const FOLLOW_URL = '/find_mac_follow';
    
    // //酒店mac查询
    // public function find_mac_follow(){
    //     if($_GET){
    //         $result['status'] = 0;
    //         $userMac  = isset($_GET['userMac'])?$_GET['userMac']:'';
    //         $channel  = isset($_GET['channel'])?$_GET['channel']:'';
    //         $code  = isset($_GET['code'])?$_GET['code']:'';
    //         $deviceMac  = isset($_GET['deviceMac'])?$_GET['deviceMac']:'';
    //         if(!$userMac){
    //             $result['status'] = 1;
    //             $result['msg'] ='缺少参数userMac';
    //         }elseif(!$channel){
    //             $result['status'] = 2;
    //             $result['msg'] ='缺少参数channel';
    //         }elseif(!$code){
    //             $result['status'] = 3;
    //             $result['msg'] ='缺少参数code';
    //         }elseif(!$deviceMac){
    //             $result['status'] = 4;
    //             $result['msg'] ='缺少参数deviceMac';
    //         }
    //         //判断参数是否传对
    //         if($result['status'] == 0){
    //             $md5_hotel = md5('hotel'); //这个MD5增加了代码阅读难度
    //             $md5_yp = md5('yp');
    //             $md5_channel = md5($channel);
    //             //判断是否是酒店来查询的
    //             if(($md5_channel == $md5_hotel) || ($md5_channel == $md5_yp) ){
    //                 $userMac = strtoupper($userMac);//小写转化为大写
    //                 //查询渠道
    //                 $buss_id=SettlementServicesImpl::get_buss_id($channel);
    //                 if($buss_id){
    //                     $task_arr=BussServicesImpl::get_order_task($buss_id);
    //                     $has = 0;
    //                     $follow = 0;
    //                     // 查询缓存中的以关注过的信息
    //                     $userMac_id = Redis::hgetall($userMac);
    //                     $sex = isset($userMac_id['sex'])?$userMac_id['sex']:0;
    //                     if($sex == 1){
    //                         $sex_s = 'male';
    //                     }elseif($sex == 2){
    //                         $sex_s = 'female';
    //                     }else{
    //                         $sex_s = 'other';
    //                     }
    //                     if($task_arr){
    //                         //data.has为0表示当前无公众号了，为1表示当前还有公众号
    //                         if(empty($userMac_id)){
    //                             $has = 1;
    //                         }else{
    //                             //判断是否全部关注
    //                             foreach($task_arr as $key => $value){
    //                                 $content = unserialize($value['content']);
    //                                 $ghid = $content['ghid'];
    //                                 if(isset($userMac_id['ghid']) && strpos($userMac_id['ghid'],$ghid) !== false){
    //                                     //无号
    //                                     $has = 0;
    //                                 }else{
    //                                     //有号
    //                                     $has = 1;
    //                                     break;
    //                                 }
    //                             }

    //                             //判断当天是否关注
    //                             foreach($task_arr as $key => $value){
    //                                 $content = unserialize($value['content']);
    //                                 $ghid = $content['ghid'];
    //                                 //查询当日是否有关注
    //                                 $userMac_id=HotelServicesImpl::get_ghid_fan($ghid,$userMac);

    //                                 //查询ruser表中是否有相关数据
    //                                 //follow为0表示没有关注行为，为1表示有关注行为
    //                                 if($userMac_id['id']){
    //                                     $follow = 1;
    //                                     break;
    //                                 }else{
    //                                     $follow = 0;
    //                                 }
    //                             }

    //                             if($has == 0){
    //                                 $res = $this->http_get(self::API_URL.self::FOLLOW_URL.'?'.'&channel='.$channel.'&code='.$code.'&deviceMac='.$deviceMac.'&userMac='.$userMac);
    //                                 $data = json_decode($res,true);
    //                                 if($data['data']['has'] === 1)  $has = 1;
    //                                 if($data['data']['follow'] === 1) $follow = 1;
    //                                 if($data['data']['sex']) $sex_s = $data['data']['sex'];
    //                             }
    //                         }
    //                     }else{
    //                         $res = $this->http_get(self::API_URL.self::FOLLOW_URL.'?'.'&channel='.$channel.'&code='.$code.'&deviceMac='.$deviceMac.'&userMac='.$userMac);
    //                         $data = json_decode($res,true);
    //                         if($data['data']['has'] === 1)  $has = 1;
    //                         if($data['data']['follow'] === 1) $follow = 1;
    //                         if($data['data']['sex']) $sex_s = $data['data']['sex'];
    //                     }

    //                     $result['status'] = 0;
    //                     $result['msg'] ='Success';
    //                     $result['data'] =array(
    //                         'channel' => $channel,'follow'=>$follow,'sex'=>$sex_s,'has'=>$has,'code'=>$code,'deviceMac'=>$deviceMac,'userMac'=>$userMac
    //                     );

    //                 }else{
    //                     $result['status'] = 8;
    //                     $result['msg'] ='没有相关渠道信息';
    //                 }
    //             }else{
    //                 $result['status'] = 5;
    //                 $result['msg'] ='无权限发送请求';
    //             }
    //         }
    //     }else{
    //         $result['status'] = 7;
    //         $result['msg'] ='请求错误';
    //     }
    //     return $result;
    // }
    
    //酒店mac查询
    public function find_mac_follow(){
        if($_GET['userMac']&&$_GET['channel']&&$_GET['code']&&$_GET['deviceMac']){
            $result['status'] = 0;
            $has = 0;
            $follow = 0;
            $userMac  = strtoupper($_GET['userMac']);
            $channel  = $_GET['channel'];
            $code  = $_GET['code'];
            $deviceMac  = $_GET['deviceMac'];

            //判断是否全部关注
            //查询渠道
            $buss_id = SettlementServicesImpl::get_buss_id($channel);
            if(!$buss_id){
                $result['status'] = 6;
                $result['msg'] ='渠道错误';
            }
            $is_num = $this->http_get(self::NEW_API_URL.'?'.'&bid='.$buss_id.'&code='.$code.'&mac='.$userMac.'&is_count='.2);
            $is_num = json_decode($is_num,true);
            if($is_num['error'] != 40008){
                $has = 1;
            }else{
                $has = 0;
            }

            //查询当日是否有关注
            $get_ghid_fan=HotelServicesImpl::get_ghid_fan($userMac);
            if($get_ghid_fan['id']){
                $follow = 1;
            }else{
                $follow = 0;
            }

            // 查询缓存中的以关注过的信息
            $userMac_id = Redis::hgetall($userMac);
            $sex = isset($userMac_id['sex'])?$userMac_id['sex']:0;
            if($sex == 1){
                $sex_s = 'male';
            }elseif($sex == 2){
                $sex_s = 'female';
            }else{
                $sex_s = 'other';
            }

            $result['msg'] ='Success';
            $result['data'] =array(
                'channel' => $channel,'follow'=>$follow,'sex'=>$sex_s,'has'=>$has,'code'=>$code,'deviceMac'=>$deviceMac,'userMac'=>$userMac
            );

    
        }else{
            $result['status'] = 7;
            $result['msg'] ='请求错误';
        }
        return $result;
    }

    //酒店吸粉查询
    public function find_total_follow(){
        if($_GET){
            $result['status'] = 0;
            $date = $_GET['date'];
            $channel  = $_GET['channel'];
            if(!isset($date)){
                $result['status'] = 1;
                $result['msg'] ='缺少参数date';
            }elseif(!isset($channel)){
                $result['status'] = 2;
                $result['msg'] ='缺少参数channel';
            }
            //判断参数是否传对
            if($result['status'] == 0){
                $md5_hotel = md5('hotel');
                $md5_yp = md5('yp');
                $md5_w20 = md5('w20');
                $md5_channel = md5($channel);
                //判断是否是酒店来查询的
                if(($md5_channel == $md5_hotel) || ($md5_channel == $md5_yp) || ($md5_channel == $md5_w20)){
                    $Rinfo = null;

                    if($channel){
                        // $map['username'] = array('like',"$channel%");

                        //查询渠道
                        // $wx_info_id = M('bussiness')->where($map)->Field('id')->find();
                        $wx_info_id = SettlementServicesImpl::get_buss_id($channel);
                        if($wx_info_id){
                            $mapp['bid'] = $wx_info_id;
                            $mapp['pdate'] = $date;

                            $sum_total=HotelServicesImpl::sum_total($mapp);
                        }
                    }

                    //查询成功 用户还可以关注就写0，不能关注了就是1
                    if($sum_total){
                        $result['status'] = 0;
                        $result['msg'] ='Success';
                        $result['data'] =array(
                            'channel' => $channel,'quhao' => $sum_total['quhao'],'quhaochenggong' => $sum_total['quhaochenggong'],'lijilianjie' => $sum_total['lijilianjie'],'guanzhu' => $sum_total['guanzhu'],'date' => $date
                        );

                    }else{
                        //查询数据失败
                        $result['status'] = 6;
                        $result['msg'] ='未找到相关数据';
                        $result['data'] =array(
                            'channel' => $channel,'date' => $date
                        );
                    }
                }else{
                    $result['status'] = 5;
                    $result['msg'] ='无权限发送请求';
                }
            }
        }else{
            $result['status'] = 7;
            $result['msg'] ='请求错误';
        }
        // \My\Tools::jsonreturn($result);
        return $result;
    }

    //老酒店吸粉查询
    public function find_total_follow_old(){
        if($_GET){
            $result['status'] = 0;
            $date = $_GET['date'];
            $channel  = $_GET['channel'];
            if(!isset($date)){
                $result['status'] = 1;
                $result['msg'] ='缺少参数date';
            }elseif(!isset($channel)){
                $result['status'] = 2;
                $result['msg'] ='缺少参数channel';
            }
            //判断参数是否传对
            if($result['status'] == 0){
                $md5_hotel = md5('hotel');
                $md5_yp = md5('yp');
                $md5_w20 = md5('w20');
                $md5_channel = md5($channel);
                //判断是否是酒店来查询的
                if(($md5_channel == $md5_hotel) || ($md5_channel == $md5_yp) || ($md5_channel == $md5_w20)){
                    $Rinfo = null;

                    if($channel){
                        // $map['username'] = array('like',"$channel%");

                        //查询渠道
                        // $wx_info_id = M('bussiness')->where($map)->Field('id')->find();
                        $wx_info_id = SettlementServicesImpl::get_buss_id($channel);
                        if($wx_info_id){
                            $mapp['bid'] = $wx_info_id;
                            $mapp['pdate'] = $date;

                            $sum_total=HotelServicesImpl::sum_total($mapp);
                        }
                    }

                    //查询成功 用户还可以关注就写0，不能关注了就是1
                    if($sum_total){
                        $result['status'] = 0;
                        $result['msg'] ='Success';
                        $result['data'] =array(
                            'channel' => $channel,'total' => $sum_total['guanzhu'],'date' => $date
                        );

                    }else{
                        //查询数据失败
                        $result['status'] = 6;
                        $result['msg'] ='未找到相关数据';
                        $result['data'] =array(
                            'channel' => $channel,'date' => $date
                        );
                    }
                }else{
                    $result['status'] = 5;
                    $result['msg'] ='无权限发送请求';
                }
            }
        }else{
            $result['status'] = 7;
            $result['msg'] ='请求错误';
        }
        // \My\Tools::jsonreturn($result);
        return $result;
    }

    private function http_get($url){
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }

}