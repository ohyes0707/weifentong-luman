<?php

namespace App\Services\Impl\Buss;

use App\Models\Buss\BussModel;
use Illuminate\Support\Facades\Log;
use App\Lib\Data\Su2Pass;
use App\Lib\Data\Su2Pass_BH;
use App\Lib\Data\BiHu;
use App\Lib\Data\Dandan;
use App\Lib\Data\Hcforward;
use Illuminate\Support\Facades\Redis;
/***
 * 渠道放行服务
 * Class BussPassServicesImpl
 * @package App\Services\Impl\Buss
 */
class BussPassServicesImpl {

    //渠道放行
    static public function getPassBuss($upsub_info,$openid,$orderinfo){
        switch($upsub_info['bid']){
            case 264 : //蛋蛋赚
                Dandan::task_complete($upsub_info,$openid,$orderinfo);
                break;
            case 266 : //寰创
                Hcforward::success_set($upsub_info,$openid,$orderinfo,true);
                break;
            case 346 : //壁虎
/*                $mac = $upsub_info['mac'];
                $md5_mac = md5('bihu_'.$mac);
                if(Redis::exists($md5_mac)){ //用户未访问
                    $tid = $appid;
                    $ext1 = Redis::get($md5_mac);
                    $umac = strtolower($upsub_info['mac']);
                    $dmac = strtolower($upsub_info['bmac']);
                    $bihu = new BiHu($tid,$umac,$dmac,$openid,$ext1);
                    $bihu->pass_authorize();
                }*/
                break;
            case 348 ://极路由
                $token = Redis::exists($upsub_info['mac'].'_token')?Redis::get($upsub_info['mac'].'_token'):'';
                $url_jly = 'http://wifidog.hiwifi.com/share.php?m=portal&a=thirdcallback&param_usermac='.$upsub_info['mac'].'&param_devmac='.$upsub_info['bmac'].'&token='.$token.'&openid='.$openid.'&isnew=1';
                file_get_contents($url_jly);
                break;
            default ://判断是否是自己渠道设备
                self::getSelfBussPass($upsub_info);
                break;
        }
    }

    //美业自己渠道放行
    static public function getSelfBussPass($upsub_info){
        if(self::getIsSelfBid($upsub_info['bid'])){
            $bmac_head = substr($upsub_info['bmac'],0,4);
            switch($bmac_head){
                case '8482' : //壁虎
                    $bh_obj = new Su2Pass_BH($upsub_info['mac'],$upsub_info['bmac']);
                    $bh_obj->pass_authorize();
                    break;
                case '84:8' : //壁虎 兼容
                    $bh_obj = new Su2Pass_BH($upsub_info['mac'],$upsub_info['bmac']);
                    $bh_obj->pass_authorize();
                    break;
                case '00:3' : //寰创 兼容
                    $su2_obj = new Su2Pass($upsub_info['mac'],$upsub_info['bmac'],$upsub_info['bid']);
                    $su2_obj->pass_authorize();
                    break;
                case '0034' : //寰创
                    $su2_obj = new Su2Pass($upsub_info['mac'],$upsub_info['bmac'],$upsub_info['bid']);
                    $su2_obj->pass_authorize();
                    break;
                default :  //特殊
                    Log::info('self_bid_bmac:'.$upsub_info['bmac']);
                    break;
            }
        }
    }
    
    //判断是否是美业自己的渠道
    static public function getIsSelfBid($bid)
    {
        $where = array(
            'id'=>$bid,
            'pbid'=>config('config.MEIYE_ID')
        );
        $model = BussModel::where($where)->get()->first();
        return $model?TRUE:FALSE;
    }
}
