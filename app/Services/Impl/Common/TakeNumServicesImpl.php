<?php
namespace App\Services\Impl\Common;

use App\Lib\Data\Yundai;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;
use App\Lib\HttpUtils\HttpRequest;
use App\Lib\HttpUtils\Net;

/**
 * 主要用来过取号方法
 */

class TakeNumServicesImpl {
    
    static public $area =array();

    static public function getSerializeArray($orderinfo) 
    {
        $dmxy= unserialize($orderinfo['content']);
        if(!isset($dmxy['qrcode_url'])){
            $dmxy['qrcode_url']='';
        }
        if(!isset($dmxy['head_url'])){
            $dmxy['head_url']='';
        }
        $list=array(
            "ssid" => $dmxy['ssid'],
            "oid" => $orderinfo['oid'],
            "ghname" => $dmxy['ghname'],
            "ghid" => $dmxy['ghid'],
            "sname" => $dmxy['sname'],
            "sid" => $dmxy['sid'],
            "appid" => $dmxy['appid'],
            "secretkey" => $dmxy['secretkey'],
            "portal_type" => "0",
            "portal_text" => null,
            "awifi_imgcode" => null,
            "price" => $orderinfo['price'],
            "type" => 0,
            "description" => "",
            "head_img" => $dmxy['head_url']
           // "qrcode_img" => $dmxy['qrcode_url']
        );
        return $list;
    }
    
    //云袋
    static public function getYunDaiArray($mac) 
    {
        $province = self::getParam('province', $mac);
        $city = self::getParam('city', $mac);
        $sex = self::getParam('sex', $mac);
        $yundai_class = new Yundai();
        $orderinfo = $yundai_class->get_order($mac, $sex, $province, $city);
        if($orderinfo){
            $res['list'][0] = array(
                'ssid'=>$orderinfo['ssid'],
                'oid'=>$orderinfo['order_no'],
                'ghname'=>$orderinfo['wechat_name'],
                'ghid'=>$orderinfo['appid'],
                'sname'=>'',
                'sid'=>$orderinfo['shopid'],
                'appid'=>$orderinfo['appid'],
                'secretkey'=>$orderinfo['secretkey'],
                'portal_type'=>0,
                'portal_text'=>null,
                'price'=>$orderinfo['price'],
                'type'=>3,
                'description'=>'',
                'head_img'=>'' 
            );
        }
        return isset($res['list'][0])?$res['list'][0]:null;
    }
    
    //山腾
    static public function getStPlatformArray($mac) 
    {
        $province = self::getParam('province', $mac);
        $city = self::getParam('city', $mac);
        $sex = self::getParam('sex', $mac);
        $url = 'http://wx.51login.cn/s.stp';
        $parameter['action'] = 'portalwxappid';
        $parameter['businessid'] = '5017-21';
        $parameter['stamac'] = $mac;
        $parameter['circleid'] = '';
        $parameter['userip'] = self::getParam('userIp', $mac);
        $parameter['code'] = $city;
        $orderinfo = HttpRequest::getApiServices('', '', 'GET', $parameter, $url);
        if(isset($orderinfo['resultcode'])&&$orderinfo['resultcode']==0){
            $res['list'][0] = array(
                'ssid'=>$orderinfo['ssid'],
                'oid'=>211,
                'ghname'=>$orderinfo['appname'],
                'ghid'=>$orderinfo['appid'],
                'sname'=>'',
                'sid'=>$orderinfo['shopid'],
                'appid'=>$orderinfo['appid'],
                'secretkey'=>$orderinfo['sercretKey'],
                'portal_type'=>0,
                'portal_text'=>null,
                'price'=>1,
                'type'=>5,
                'description'=>'',
                'head_img'=>'' 
            );
            Redis::set($mac.'51login', json_encode($res['list'][0]));
            Redis::expire($mac.'51login',300);
        }
        return isset($res['list'][0])?$res['list'][0]:null;
    }
    
    //老平台
    static public function getOldPlatformArray($mac,$bid) 
    {
//        $parameter = array(
//            'province' => self::getParam('province', $mac),
//            'city' => self::getParam('city', $mac),
//            'sex' => self::getParam('sex', $mac),
//            'mac' => $mac,
//            'bid' => $bid,
//        );
        $request = Request::capture();
        $parameter = $request->all();
        unset($parameter['callback']);
        $orderdata = HttpRequest::getApiServices('api', 'get_wx', 'GET', $parameter);
        if(!isset($orderdata['list'][0])){
            return null;
        }
        $orderinfo = $orderdata['list'][0];
        if(!isset($orderinfo['ssid'])){
             return null;
        }
        if($orderinfo){
            $res['list'][0] = array(
                'ssid'=>$orderinfo['ssid'],
                'oid'=>$orderinfo['oid'],
                'ghname'=>$orderinfo['ghname'],
                'ghid'=>$orderinfo['ghid'],
                'sname'=>$orderinfo['sname'],
                'sid'=>$orderinfo['sid'],
                'appid'=>$orderinfo['appid'],
                'secretkey'=>$orderinfo['secretkey'],
                'portal_type'=>0,
                'portal_text'=>null,
                'price'=>$orderinfo['price'],
                'type'=>4,
                'description'=>'',
                'head_img'=>$orderinfo['head_img'],
            );
            $old = json_encode($res['list'][0]);
            Redis::hset('oldoid',$orderinfo['oid'],$old);
        }
        return isset($res['list'][0])?$res['list'][0]:null;
    }
    
    //取出公众号
    static public function getOnePublicNum($array,$bid,$mac,$ordernum) {
        if($ordernum==''||$ordernum<1){
            $ordernum=1;
        }
        $endnuminfo = array();
        $x=0;
        foreach ($array as $key => $value) {
            switch ($value['orderid']) {
                case '153':
                    $list = TakeNumServicesImpl:: getYunDaiArray($mac);
                    if($list != null){
                        $x=$x+1;
                         $endnuminfo[]=$list;
                        if($x>=$ordernum){
                            return $endnuminfo;
                        }
                    }
                    break;
                case '155':
                    $list = TakeNumServicesImpl:: getOldPlatformArray($mac,$bid);
                    if($list != null){
                        $x=$x+1;
                         $endnuminfo[]=$list;
                        if($x>=$ordernum){
                            return $endnuminfo;
                        }
                    }
                    break;
                case '211':
                    //去山腾那里取号
                    $list = TakeNumServicesImpl:: getStPlatformArray($mac,$bid);
                    if($list != null){
                        $x=$x+1;
                         $endnuminfo[]=$list;
                        if($x>=$ordernum){
                            return $endnuminfo;
                        }
                    }
                    break;
                default:
                        $x=$x+1;
                         $endnuminfo[]=$value['list'];
                        if($x>=$ordernum){
                            return $endnuminfo;
                        }
                    break;
            }
        }
        return $endnuminfo;
    }

    static public function getParam($param,$mac) {
        $request = Request::capture();
        if($request -> input($param)){
            return $request ->input($param);
        }
        if(Redis::hget($mac,$param)){
            return Redis::hget($mac,$param);
        }
        return null;
    }
    
    static public function getIsFollow($ghid,$userghid,$alreadynum) 
    {
        if(strpos($userghid, $ghid) === FALSE){
            return $alreadynum;
        } else {
            return 0;
        }
    }
    
    static public function getPlatformOid($oid,$type) 
    {
        switch ($type) {
            case 3:
                return 153;
                
            case 4:
                return 155;

            default:
                return $oid;
        }
    }
    
    /***
     * 拿到不同的mac
     * @param $bid
     * @param $mac
     * @return bool
     */
    static public function getMac($bid, $mac) {
        $request = Request::capture();
        switch ($bid) {
            case 264:
                $userid = $request->input('userid');
                return Net::get_new_mac($userid);
            case 232:
                $mobile = $request->input('mobile');
                return Net::get_new_mac($mobile);
            default:
                return $mac;
        }
    }
    
    /***
     * 判断mac是否正确
     * @param $bid
     * @param $mac
     * @return bool
     */
    static public function getIsContinue($mac) {
        if(Net::get_mac($mac)!=''){
            
        } else {
            die('{40008}');
        }
    }
    
    /***
     * 把公众号信息转换成蛋蛋的
     * @param $list 公共号列表
     * @return $list 蛋蛋需要的公众号列表
     */
    static public function getDanDan($data, $bid, $mac, $bmac) {
        $newarray = array();
        foreach ($data as $key => $value) {
            $head_img = base64_encode($value['head_img']);
            $newarray[]=array(
                'ghname'=>$value['ghname'],
                'head_img'=>$value['head_img'],
                'price'=>$value['price'],
                'link'=>'http://res.youfentong.com/dandanz/index.html?bid='.$bid.'&mac='.$mac.'&bmac='.$bmac.'&head_img='.$head_img.'&ghname='.$value['ghname'].'&oid='.$value['oid'].'&ssid='.$value['ssid'].'&sid='.$value['sid'].'&appid='.$value['appid'].'&secretkey='.$value['secretkey'],
            );
        }
        return $newarray;
    }
    
    /***
     * 把渠道发送过来的省份转换成数据库里的
     * @param $list 公共号列表
     * @return $list 蛋蛋需要的公众号列表
     */
    static public function getBidCity($bid) {
        $request = Request::capture();
        switch ($bid) {
            case 264:
                $arr = Net::GetIpCity($request->input('userIp'));
                self::$area['city'] = $arr['city'];
                self::$area['province'] = $arr['province'];
                break;
            case 238:
                $rel_city = Net::GetIpCity($request->input('wan_ip'));    //获取AP区域所在城市
                if(!empty($rel_city['province'])){
                    self::$area['province'] = $rel_city['province'];
                }
                if(!empty($rel_city['city'])){
                    self::$area['city'] = $rel_city['city'];
                }
                break;
            case 267:
                $rel_city = Net::GetIpCity($request->input('wan_ip'));    //获取AP区域所在城市
                if(!empty($rel_city['province'])){
                    self::$area['province'] = $rel_city['province'];
                }
                if(!empty($rel_city['city'])){
                    self::$area['city'] = $rel_city['city'];
                }
                break;
            case 344:
                $rel_city = Net::GetIpCity($request->input('ap_ip'));    //获取AP区域所在城市
                if(!empty($rel_city['province'])){
                    self::$area['province'] = $rel_city['province'];
                }
                if(!empty($rel_city['city'])){
                    self::$area['city'] = $rel_city['city'];
                }
                break;
            case 346:
                $code = (int)$request->input('code');  //区域码
                $rel_city = Net::GetDistrictCity($code);  //获取AP区域所在城市
                if(!empty($rel_city['province'])){
                    self::$area['province'] = $rel_city['province'];
                }
                if(!empty($rel_city['city'])){
                    self::$area['city'] = $rel_city['city'];
                }
                break;
            case 220:
                $arr = Net::GetRegionCity($request->input('code'));
                self::$area['city'] = $arr['city'];
                self::$area['province'] = $arr['province'];
                break;
            case 234:
                $arr = Net::GetRegionCity($request->input('code'));
                self::$area['city'] = $arr['city'];
                self::$area['province'] = $arr['province'];
                break;
            default:
                if($request->input('userIp')!=''){
                    $arr = Net::GetIpCity($request->input('userIp'));
                    self::$area['city'] = $arr['city'];
                    self::$area['province'] = $arr['province'];
                }elseif ($request->input('code')!='') {
                    $code = (int)$request->input('code');  //区域码
                    $rel_city = Net::GetDistrictCity($code);  //获取AP区域所在城市
                    if(!empty($rel_city['province'])){
                        self::$area['province'] = $rel_city['province'];
                    }
                    if(!empty($rel_city['city'])){
                        self::$area['city'] = $rel_city['city'];
                    }
                } else {
                    self::$area['city'] = $request->input('city');
                    self::$area['province'] = $request->input('province');
                }
                break;
        }
    }
    
    /***
     * 把渠道发送过来的省份转换成数据库里的
     * @param $list 公共号列表
     * @return $list 蛋蛋需要的公众号列表
     */
    static public function area_compare($hot_area,$bid,$province,$city,$redisarea){
        $num = 0;
        if(empty($province) && empty($city)){
            $buss_area = $redisarea;
        }else{
            $buss_area = $province.'/'.$city;
            if($city == ''|| $city=='未知城市'){
                $buss_area = $province;
            }
        }
        $buss_area_arr = explode(',',$buss_area);
        $buss_count = count($buss_area_arr);
        $hot_area_arr = explode(',',$hot_area);
        foreach($buss_area_arr as $v){
            foreach($hot_area_arr as $vv){
                $buss_arr = explode('/',$v);
                $hot_arr = explode('/',$vv);
                $hot_count = count($hot_arr);
                if($hot_count != 1 && !empty(end($buss_arr))){
                    if(end($hot_arr) == end($buss_arr))
                        $num += 1;
                }else{
                    if(reset($hot_arr) == reset($buss_arr))
                        $num += 1;
                }
            }
        }
        if($num >= $buss_count){
            return TRUE;
        }else{
            return FALSE;
        }
    }

    /***
     * 取得一个值
     * @param $bid bid
     * @param $cid cid
     * @return $list 
     */
    static public function getHaveValue($param1, $param2) {
        if($param2!=''){
            return 'cid'.$param2;
        }
        return $param1;
    }
    
    /***
     * 加密cid
     * @param $bid bid
     * @param $cid cid
     * @return $list 
     */
    static public function getCodeCid($param1, $param2) {
        if($param2!=''){
            return $param2;
        }
        return $param1;
    }
    
    /***
     * 解码cid
     * @param $bid bid
     * @param $cid cid
     * @return $list 
     */
    static public function getDecodeCid($cid) {
        $unb64 = base64_encode($cid);
        
    }
    
    
    /***
     * 判断是否是cid
     * @param $bid bid
     * @param $cid cid
     * @return $list 
     */
    static public function getIsCodeCid($cid) {
        if($cid!=''){
            return false;
        }
        $unb64 = base64_encode($cid);
        if(strpos($unb64,'cid') !== false){
            return true;
        }else{
            return false;
        }
    }
    
    /***
     * 判断参数是否在
     * @param $bid bid
     * @param $cid cid
     * @return $list 
     */
    static public function getDefaultCompare($array) 
    {
        if(!isset($array['behavior'])){
            $array['behavior'] = 2;
        }
        
        switch ($array['behavior']) {
            case 1:
                $must = array(
                    'mac','bid','order_id','bmac'
                );
                if(TakeNumServicesImpl::getCompareParam($must, $array)){
                    return 1;
                }
                return 0;
            case 2:
                $must = array(
                    'mac','bid','order_id','bmac','openid'
                );
                if(TakeNumServicesImpl::getCompareParam($must, $array)){
                    return 2;
                }
                return 0;
            case 3:
                $must = array(
                    'sex','city','ghid','openid','nickname','province'
                );
                if(TakeNumServicesImpl::getCompareParam($must, $array)){
                    return 3;
                }
                return 0;
            case 4:
                $must = array(
                    'openid'
                );
                if(TakeNumServicesImpl::getCompareParam($must, $array)){
                    return 4;
                }
                return 0;
            case 5:
                $must = array(
                    'openid'
                );
                if(TakeNumServicesImpl::getCompareParam($must, $array)){
                    return 5;
                }
                return 0;
            case 6:
                $must = array(
                    'mac','bid','order_id'
                );
                if(TakeNumServicesImpl::getCompareParam($must, $array)){
                    return 6;
                }
                return 0;
            default:
                return 0;
        }
    }
    
    static public function getCompareParam($active,$cover) {
        foreach ($active as $key => $value) {
            if(!isset($cover[$value])){
                return FALSE;
            }
        }
        return TRUE;
    }
}
