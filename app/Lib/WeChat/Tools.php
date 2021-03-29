<?php
namespace App\Lib\WeChat;
class Tools
{
		//nl to <p>
	public static function nl2p($text){
	  return "<p>" . str_replace("\n", "</p><p>", $text) . "</p>";
	}
	//随机字符串
	public static function random_str($length)
	{
		$arr = array_merge(range(0, 9), range('a', 'z'));
	    $str = '';
	    $arr_len = count($arr);
	    for ($i = 0; $i < $length; $i++)
	    {
	        $rand = mt_rand(0, $arr_len-1);
	        $str.=$arr[$rand];
	    }
	    return $str;
	}
	//得到当前url
	public static function get_url() {
    $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
    $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
    $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
    $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
    return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
	}
	
	//log
	public static function log($msg){
	    $fd=fopen(date('Y-m-d')."wftdebug.txt", "a+");
	    if(is_array($msg)){$msg=var_export($msg,TRUE);}
	    $str="[".date("Y/m/d h:i:s",time() )."]".$msg;
	    fwrite($fd, $str."\n");
	    fclose($fd);
	}
	//网站运行日志
	public static function runlog($type,$msg,$log_url = false){
		$fd=fopen(date('Y-m-d')."runlog.txt", "a+");
	    if(is_array($msg)){$msg=var_export($msg,TRUE);}
	    if($log_url)fwrite($fd, self::get_url());
	    $str="[".date("Y/m/d h:i:s",mktime())."]"."[$type]".$msg;
	    fwrite($fd, $str."\n");
	    fclose($fd);	
	}
	//删除字符串中所有空格
	public static function trimall($str)
	{
	    $qian=array(" ","　","\t","\n","\r");$hou=array("","","","","");
	    return str_replace($qian,$hou,$str);    
	}
	public static function jsonreturn($arr){
        $callback = isset($_REQUEST['callback'])?$_REQUEST['callback']:null;
		if($callback){
			echo $callback.'('.json_encode($arr).')';
		}else{
			echo json_encode($arr);
		}
		exit;
	}
	//避免在第二页搜索无数据,属于重新访问，从p1开始
	public static function search2one()
	{
		$url = self::get_url();
		if($_GET['p'] > 1)
		{
			$url = preg_replace('/\/\d/','/1',$url);
			header('Location:'.$url);
			exit();
		}
		
	}
	public static function check_sms($mobile,$vcode)
	{
		   $url = 'http://61.164.47.132:8090/code/v1/'.$mobile.'/'.$vcode;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        $res = curl_exec($ch);
        curl_close($ch);
        $res = json_decode($res,true);
        if(is_array($res) && $res['error'] == 0){
            return 1;
        }else{
            return 0;
        }
	}

	public static function get_vcode($mobile){
		$url = 'http://61.164.47.132:8090/code/v1/test/'.$mobile.'/0';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
		$res = curl_exec($ch);

		curl_close($ch);
		$res = json_decode($res,true);
		if(is_array($res) && $res['error'] == 0){
			return 1;
		}else{
			return 0;
		}
	}
	//下单低价通知
	public static function get_vcode2($mobile){
		$appkey = 'fdf11d9ef1f272eb';//你的appkey
		$content = '有新的订单需要审核，请马上登录平台处理！【优粉通】';//utf8
		$url = "http://api.jisuapi.com/sms/send?appkey=$appkey&mobile=$mobile&content=$content";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$res = curl_exec($ch);
		curl_close($ch);
		$jsonarr = json_decode($res,true);	
		if($jsonarr['status'] != 0)
		{
			return 2;//失败
		}else{
			return 1;
		}
	}
	public static function is_https()
    {
        if ( ! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
        {
            return TRUE;
        }
        elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        {
            return TRUE;
        }
        elseif ( ! empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off')
        {
            return TRUE;
        }
  
        return FALSE;
    }


  
}
