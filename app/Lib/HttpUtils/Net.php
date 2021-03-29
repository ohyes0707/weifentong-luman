<?php

namespace App\Lib\HttpUtils;
use App\Models\Api\CityModel;
use App\Models\Api\ProvinceModel;
use App\Models\Api\RegionModel;
class Net
{
    public static function ip_info($ip){
        $res = self::http_get('http://ip.taobao.com/service/getIpInfo.php?ip='.$ip,1);
        $res = json_decode($res,true);
        if($res['code'] == 0){
            return $res['data'];
        }else{
            return false;
        }
    }

    //获取用户真实IP
    public static function user_ip() {
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } elseif (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } elseif (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) {
            $ip = getenv("REMOTE_ADDR");
        } elseif (isset ($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = "";
        };
        return $ip;
    }
    public static function cn_urlencode($url){
        $pregstr = "/[\x{4e00}-\x{9fa5}]+/u";//UTF-8中文正则
        if(preg_match_all($pregstr,$url,$matchArray)){//匹配中文，返回数组

            foreach($matchArray[0] as $key=>$val){
                $url=str_replace($val, urlencode($val), $url);//将转译替换中文
            }
            if(strpos($url,' ')){//若存在空格
                $url=str_replace(' ','%20',$url);
            }
        }
        return $url;
    }
    //下载图片，失败返回null,指定目录和文件名
	public static function download_img($url,$save_path)
	{
		set_time_limit(0);
		$url=self::cn_urlencode($url);  //中文url下载失败
		$ext=substr(strrchr($url, '.'),0);
		$types = '.gif.jpeg.png.bmp.jpg';
		if($url=='')return "";
		if(stripos($types,$ext)===false)$ext=".jpg";
		$save_path.=$ext;
		$dir= dirname($save_path)."/";
		if(!is_dir($dir))mkdir($dir,0777,true);   //多级加true
		 $ch=curl_init();
		 $timeout=500;
		 curl_setopt($ch,CURLOPT_URL,$url);
		 curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		 curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
		 curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		 $img=curl_exec($ch);
		 curl_close($ch);
		 $fp=fopen($save_path,"a+");
    	 $r=fwrite($fp,$img);
    	 fclose($fp);
    	 if($r)return $save_path;
    	 return "";
	}
	public static function http_get($url,$timeout = 0){
		$oCurl = curl_init();
		if(stripos($url,"https://")!==FALSE){
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
		}
		if($timeout>0){
			curl_setopt($oCurl, CURLOPT_TIMEOUT,$timeout); 
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
	public static function get_mac($mac){
		$mac = strtoupper(str_replace(array(':','-','：'),'',trim($mac)));
		if(strlen($mac) == 12) return $mac;
		return '';
	}
	public static function add_domain($domain){
		 exec('python3 /data/shell/vhost_add_sites.py add '.$domain,$res);
		 return $res[0] == 'Success';
	}
	public static function del_domain($domain){
		 exec('python3 /data/shell/vhost_add_sites.py del '.$domain,$res);
		 return $res[0] == 'Success';
	}

        static public function get_new_mac($mac){
            if(strlen($mac)<12){//补足12位
                $tmp_str = $mac.'*';
                $len = 12-strlen($tmp_str);
                if($len>0){
                    $tmp_str.= substr(md5($mac),0,$len);
                }
                $mac = $tmp_str;
            }else{
                $mac = substr($mac,0,12);
            }
            return $mac;	
        }


        //根据ip获取城市区域
        public static function GetIpCity($ip = ''){
            if(empty($ip)){
                $data['province']='';
                $data['city']='';
                return $data;
            }

            $res = @file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip=' . $ip);
            if(empty($res)){ return false; }
            $jsonMatches = array();
            preg_match('#\{.+?\}#', $res, $jsonMatches);
            if(!isset($jsonMatches[0])){ return false; }
            $json = json_decode($jsonMatches[0], true);
            if(isset($json['ret']) && $json['ret'] == 1){
                $json['ip'] = $ip;
                unset($json['ret']);
            }else{
                return false;
            }
            return $json;
        }

    //根据区地域码获取省份、城市
    public static function GetDistrictCity($d_code=''){
        if(empty($d_code)){
            $data['province']='';
            $data['city']='';
            return $data;
        }
        $d_code = substr_replace($d_code, '00', 4, 2);
        $where_city['city_code'] = $d_code;
        $city_list_pd = CityModel::where($where_city)->first();
        if($city_list_pd){
            $city_list = $city_list_pd->toArray();
            $data['city'] = str_replace("市","",$city_list['city_name']);
            $where_province['code'] = $city_list['province_code'];
            $province = ProvinceModel::where($where_province)->first()->toArray()['pro_name'];
            $data['province'] = str_replace("省","",$province);
            return $data;
        }else{
            return false;
        }
    }

    //用户昵称去表情
    public static function remove_emoji($str)
    {
        $str = preg_replace_callback('/./u', function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        }, $str);
        // $str = preg_replace_callback('/[\xf0-\xf7].{3}/', function($r) { return '@E' . base64_encode($r[0]);},$str);

        //     $countt=substr_count($str,"@");
        //     for ($i=0; $i < $countt; $i++) {
        //         $c = stripos($str,"@");
        //         $str=substr($str,0,$c).substr($str,$c+10,strlen($str)-1);
        //     }
        //     $str = preg_replace_callback('/@E(.{6}==)/', function($r) {return base64_decode($r[1]);}, $str);
        $oldchar = array(" ", "　", "\t", "\n", "\r");
        $newchar = array("", "", "", "", "");
        return str_replace($oldchar, $newchar, $str);
    }

    //酒店设备根据区地域码获取省份、城市
    static public function GetRegionCity($code='') {
        if(empty($code)){
            $data['province']='';
            $data['city']='';
            return $data;
        }
        $model = RegionModel::getArea($code);
        $data['province'] = $model['province'];
        $data['city'] = $model['city'];
        return $data;
    }
}
