<?php
namespace App\Lib\Data;
class Smschanzor{
	//畅卓科技
	const APPID = 'lingliqianli-yx';
	const APPKEY = 'su2987';
	const URL = 'http://api.chanzor.com';

	public static function sendmsg($wx_name,$type=null){
		$mobile = '18069404746,15715788773,17681829140,18590343336';
        if($type){
            $content = '警告:新平台涨粉量5分钟内低于10个，请注意！！【i快客】';
            //$wx_name = '测试-平台涨粉监控';
            //$content = '新平台-优粉通'.$wx_name.'订单数已经完成，请知晓【i快客】';
        }else{
            $content = '新平台-优粉通'.$wx_name.'订单数已经完成，请知晓【i快客】';
        }
		$url = self::URL.'/send?account='.self::APPID.'&password='.strtoupper(MD5(self::APPKEY)).'&mobile='.$mobile.'&content='.$content;
		$res = self::http_get($url);
		$res = json_decode($res,true);
		if($res['status'] == 0){
			return true;
		}else{
			return false;
		}
	}

	private static function http_get($url,$timeout = 0){
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

	/**
	 * POST 请求
	 * @param string $url
	 * @param array $param
	 * @param boolean $post_file 是否文件上传
	 * @return string content
	 */
	private static function http_post($url,$param,$post_file=false){
		$oCurl = curl_init();
		if(stripos($url,"https://")!==FALSE){
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
		}
		if (is_string($param) || $post_file) {
			$strPOST = $param;
		} else {
			$aPOST = array();
			foreach($param as $key=>$val){
				$aPOST[] = $key."=".urlencode($val);
			}
			$strPOST =  join("&", $aPOST);
		}
		curl_setopt($oCurl, CURLOPT_URL, $url);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($oCurl, CURLOPT_POST,true);
		curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
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