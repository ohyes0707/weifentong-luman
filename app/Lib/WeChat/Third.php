<?php
namespace App\Lib\WeChat;
use App\Services\Impl\Wechat\WechatServicesImpl;
use Illuminate\Support\Facades\Redis;

//第三方公众平台接口
class Third
{
    const YFT_AUTH_BACK = 'api.youfentong.com/auth_back'; //授权回调地址  //这里可以调成固定的回地址
    const EVENT_VERIFY_TICKET = 'component_verify_ticket';     // 定时推送ticket事件
    const EVENT_AUTHORIZED = 'authorized';                     //authorized授权成功事件
    const EVENT_CANCEL_AUTH = 'unauthorized';
    const EVENT_UPDATEAUTH = 'updateauthorized';

    const API_URL_PREFIX = 'https://api.weixin.qq.com/cgi-bin/component';   //第三方平台接口
    const ACCESS_TOKEN_URL = '/api_component_token';        
    const PREAUTH_CODE_URL = '/api_create_preauthcode?';      //获取预授权码
    const AUTH_INFO_URL = '/api_query_auth?';                 //
    const WX_INFO_URL = '/api_get_authorizer_info?';
    const AUTH_TOKEN_URL='/api_authorizer_token?';

    private $component_appid ='wx9f2c5b6bbfb45238';
    private $component_appsecret = '521bd2f16a9293c1400919540cc3e682';
    private $encodingToken = 'UY8MQYEYXPGB4G5DWI117G44QFB7PYFZ';
    private $encodingAesKey = '4PRWJ8R9Z53EDLM57GEBHA7I79XEJQ7FL41WBZC2UYL';
    private $auth_redirect;  //暂时的授权回调地址

    private $curPlat;
    
    private $component_access_token = '';   //调用公众号使用的平台access_token
    private $debug_mode = false;
    private $privileges = array('消息管理权限','用户管理权限','帐号服务权限','网页服务权限','微信小店权限','微信多客服权限',
        '群发与通知权限','微信卡券权限','微信扫一扫权限','微信连WIFI权限','素材管理权限','微信摇周边权限','微信门店权限','微信支付权限',
        '自定义菜单权限');

    private $encrypt_type;  //加密类型
    public $_receive;//接收到的数据
    public $postxml;//微信post来的xml
    public $errCode;
    public $errMsg;
    public $must_priv =array(1,2,3,10,13); 
    public $last_priv = array(1,7,2,3,11,13,10);//最新权限
    // private $component_verify_ticket = '';//10分钟推送一次
    //check module
    public function __construct($options)
    {

        // $third =new Third(array('aoem_id' => $platform_id,'rid'=>$rid));
        $url  = config('config.BUSS_URL');
        if($options['platform_id']>0){
            //根据代理id来获取微信三方授权平台参数，id为0只用优粉通默认配置
            $conf =  WechatServicesImpl::getPlatFormInfo($options['platform_id']);
            $this->component_appid = $conf['appid'];
            $this->component_appsecret = $conf['appsecret'];
            $this->encodingToken = $conf['token'];
            $this->encodingAesKey = $conf['encodingaeskey'];
            $this->curPlat = $conf;
            $confarray['platform_id'] = $options['platform_id'];
            $confarray['rid'] = isset($options['rid'])?$options['rid']:"";
            $config = serialize($confarray);
//            $this->auth_redirect = $this->auth_redirect.'?conf='.$config;
            if(isset($options['isAgent']) && $options['isAgent'] ==1){
                $confarray = unserialize($config);
                $confarray['isAgent'] = 1;
                $config = serialize($confarray);
                $this->auth_redirect = "$url/agent/auth_back?conf=$config";
            }elseif(isset($options['isStore']) && $options['isStore'] ==1){
                $this->auth_redirect = "$url/store/auth_back?conf=$config";
            }else{
                $this->auth_redirect = "$url/Wechat/auth_back?conf=$config";
            }

        }else{
        	$conf = WechatServicesImpl::getPlatFormInfo($options['platform_id']);
        	$this->component_appid = $conf['appid'];
        	$this->component_appsecret = $conf['appsecret'];
        	$this->encodingToken = $conf['token'];
        	$this->encodingAesKey = $conf['encodingaeskey'];
            $this->curPlat = $conf;
            $confarray['platform_id'] = 0;
            $confarray['rid'] = isset($options['rid'])?$options['rid']:"";
            $config = serialize($confarray);
//            $this->auth_redirect = $this->auth_redirect . '?conf=' . $config;

            if(isset($options['isAgent']) && $options['isAgent'] ==1){
                $confarray = unserialize($config);
                $confarray['isAgent'] = 1;
                $config = serialize($confarray);
                $this->auth_redirect = "$url/agent/auth_back?conf=$config";
            }elseif(isset($options['isStore']) && $options['isStore'] ==1){
                $this->auth_redirect = "$url/store/auth_back?conf=$config";
            }else{
                $this->auth_redirect = "$url/Wechat/auth_back?conf=$config";
            }
        }

        //$this->log(serialize($this->curPlat));

    }

    /**
     * 返回当前使用的平台参数
     * @return [array] [当前使用的微信三方平台参数(appid,appsecret,token,encodingaeskey)]
     */
    public function get_cur_plat_conf(){
        return $this->curPlat;
    }

    public function check_priv($func)
    {
        if($func)
        {
            $tmp = array();
            foreach ($func as $key => $value) {
              $tmp[] = $value['funcscope_category']['id'];
            }
            if(count(array_intersect($this->must_priv,$tmp))==count($this->must_priv))return true;
        }
        return false;
    }
    /**
     * 获取微信服务器发来的信息
     */
    public function getRev()
    {
        if ($this->_receive) return $this;
        $postStr = !empty($this->postxml)?$this->postxml:file_get_contents("php://input");
        if (!empty($postStr)) {
            if($this->debug_mode)$this->log($postStr);
            $this->_receive = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $this->encrypt_type = isset($_GET["encrypt_type"]) ? $_GET["encrypt_type"]: '';
            if($this->debug_mode)$this->log($_GET);
            if($this->encrypt_type == 'aes')
            {
                if($this->debug_mode)
                {
                    $this->log('aes');
                    $this->timestamp  = isset($_GET["timestamp"]) ? $_GET["timestamp"]: '';
                    $this->nonce = isset($_GET["nonce"]) ? $_GET["nonce"]: '';
                    $this->msg_signature  = isset($_GET["msg_signature"]) ? $_GET["msg_signature"]: '';
                    $data = $this->timestamp.'|'.$this->nonce.'|'.$this->msg_signature;
                    $this ->log('data:'.$data);
                }
                $encryptStr = $this->_receive['Encrypt'];
                if($this->debug_mode)$this->log('encryptStr:'.$encryptStr);

                $pc = new Prpcrypt($this->encodingAesKey);
                $array = $pc->decrypt($encryptStr,$this->component_appid);

                if($this->debug_mode)$this->log($array);
                if (!isset($array[0]) || ($array[0] != 0)) {
                        die('error!');
                }
                $this->postxml = $array[1];
                //重新解析解密后的数据
                $this->_receive =  (array)simplexml_load_string($this->postxml, 'SimpleXMLElement', LIBXML_NOCDATA);
                if($this->debug_mode)$this->log($this->_receive);
            }
        }
        return $this;
    }

    /**
     * 获取接收消息的类型
     */
    public function getRevType() {
        if (isset($this->_receive['InfoType']))
            return $this->_receive['InfoType'];
        else
            return false;
    }
    public function getAuthAppid()
    {
         if (isset($this->_receive['AuthorizerAppid']))
            return $this->_receive['AuthorizerAppid'];
        else
            return '';
    }
    //保存10分钟推送的ticket
    public function setComponentTicket()
    {

         $this->setCache('wft_component_verify_ticket',$this->_receive['ComponentVerifyTicket']);
    }
    public function getComponentTicket()
    {
        return $this->getCache('wft_component_verify_ticket');
    }

    //获取平台的access_token        component_access_token
    public function getAccessToken()
    {
        if($this->component_access_token!='')return $this->component_access_token;

        $timeout = $this->getCache('wft_component_token_time');
        $token =  $this->getCache('wft_component_access_token');

        if(time()<$timeout){
            $this->component_access_token = $token;
            return $token;
        }

        $data = array('component_appid' => $this->component_appid,
            'component_appsecret' => $this->component_appsecret,
            'component_verify_ticket' => $this->getComponentTicket()
            );
        $this->log('http_post data to get token:'.print_r($data,true));
        $result = $this->http_post(self::API_URL_PREFIX.self::ACCESS_TOKEN_URL,self::json_encode($data));


//dump($result);
//string(110) "{"errcode":61004,"errmsg":"access clientip is not registered hint: [99yMca0293e514] requestIP: 61.164.47.143"}"
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                $this->log('getAccessToken:'.$result);
                return '';
            }
            $this->setAccessToken($json['component_access_token'],$json['expires_in']);
            $this->component_access_token = $json['component_access_token'];
            return $json['component_access_token'];
        }
        return '';

    }

    public function setAccessToken($token,$expire)
    {
         $this->setCache('wft_component_access_token',$token);
         $this->setCache('wft_component_token_time',time()+$expire-600);
    }

    //获取预授权码
    public function getPreauthCode()
    {
         $timeout = isset($_SESSION['pre_auth_time_'.$this->curPlat['platform_id']])?$_SESSION['pre_auth_time_'.$this->curPlat['platform_id']]:'';
         $code =  isset($_SESSION['pre_auth_code_'.$this->curPlat['platform_id']])?$_SESSION['pre_auth_code_'.$this->curPlat['platform_id']]:'';
         if(time()<$timeout)return $code;
         $token =$this->getAccessToken(); //这里;

         $this->log('getPreauthCode -> token='.$token);
         $data = array('component_appid'=>$this->component_appid);

         $result = $this->http_post(self::API_URL_PREFIX.self::PREAUTH_CODE_URL.'component_access_token='.$token,self::json_encode($data));
         if($result)
         {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                $this->log('getPreauthCode:'.$result);
                return '';
            }
            $this->setPreauthCode($json['pre_auth_code'],$json['expires_in']);
            return $json['pre_auth_code'];
         }
         return '';
    }
    public function setPreauthCode($code,$expire)
    {
          $_SESSION['pre_auth_code_'.$this->curPlat['platform_id']] = $code;
          $_SESSION['pre_auth_time_'.$this->curPlat['platform_id']] = time()+$expire-72;
    }

    //跳转至授权页
    public function  getAuthRedirect($is_return = false)
    {
        $code = $this->getPreauthCode();  // 问题出在这里
        $url = 'https://mp.weixin.qq.com/cgi-bin/componentloginpage?';
        $args = 'component_appid='.$this->component_appid.'&pre_auth_code='.$code.'&redirect_uri='.$this->auth_redirect;
        if($is_return)return $url.$args;
        header('Location:'.$url.$args);
    }


    //查询授权后的信息
    //$auth_code授权码     微信授权后返回的参数
    public function queryAuthInfo($auth_code)
    {   
        $token =$this->getAccessToken();
        $data = array('component_appid'=>$this->component_appid,
            'authorization_code'=>$auth_code);
        $this->log($data);
        $result = $this->http_post(self::API_URL_PREFIX.self::AUTH_INFO_URL.'component_access_token='.$token,self::json_encode($data));
        $this->log($result);
         if($result)
         {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                $this->log('queryAuthInfo:'.$result);
                return '';
            }
            return $json;
         }
         return false;
    }    

    //获取授权方的公众号帐号基本信息    公众号appid
    public function getWxInfo($appid)
    {
         $token =$this->getAccessToken();
         $data['component_appid'] = $this->component_appid;
         $data['authorizer_appid'] = $appid;
         $result = $this->http_post(self::API_URL_PREFIX.self::WX_INFO_URL.'component_access_token='.$token,self::json_encode($data));

         if($result)
         {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                $this->log('getWxInfo:'.$result);
                return '';
            }
            return $json;
         }
         return false;
    }

    //刷新授权方公众号的token
    public function refresh_authorizer_token($appid,$fresh_token)
    {
        $token =$this->getAccessToken();


        $data = array('component_appid'=>$this->component_appid,
            'authorizer_appid'=>$appid,
            'authorizer_refresh_token'=>$fresh_token);
     $result = $this->http_post(self::API_URL_PREFIX.self::AUTH_TOKEN_URL.'component_access_token='.$token,self::json_encode($data));


     if($result)
     {
         $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                $this->log('refresh_authorizer_token:'.$result);
                return '';
            }
            return $json;
     }
     return false;
    }
    public function setCache($name,$v)
    {
        $name = $name.'_'.$this->curPlat['platform_id'];
        // $this->log('set cache '.$name.':'.$v);
        if($v == '')$v=null;
        Redis::set($name,$v);
        Redis::expire($name, 86400);//保存一天
    }
    public function getCache($name)
    {
        // if($this->curPlat['platform_id'] == 60)$this->curPlat['platform_id']=24;
        $name = $name.'_'.$this->curPlat['platform_id'];
        return  Redis::get($name);
    }
    public function getError()
    {
        return $this->errCode.':'.$this->errMsg;
    }
    public function log($msg){
//        $fd=fopen("third.txt", "a+");
//        if(is_array($msg)){$msg=var_export($msg,TRUE);}
//        $str="[".date("Y/m/d h:i:s",time())."]".$msg;
//        fwrite($fd, $str."\n");
//        fclose($fd);
    }

     /**
     * POST 请求
     * @param string $url
     * @param array $param
     * @param boolean $post_file 是否文件上传
     * @return string content
     */
    public function http_post($url,$param,$post_file=false){
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

/**
     * 微信api不支持中文转义的json结构
     * @param array $arr
     */
    static function json_encode($arr) {
        if (count($arr) == 0) return "[]";
        $parts = array ();
        $is_list = false;
        //Find out if the given array is a numerical array
        $keys = array_keys ( $arr );
        $max_length = count ( $arr ) - 1;
        if (($keys [0] === 0) && ($keys [$max_length] === $max_length )) { //See if the first key is 0 and last key is length - 1
            $is_list = true;
            for($i = 0; $i < count ( $keys ); $i ++) { //See if each key correspondes to its position
                if ($i != $keys [$i]) { //A key fails at position check.
                    $is_list = false; //It is an associative array.
                    break;
                }
            }
        }
        foreach ( $arr as $key => $value ) {
            if (is_array ( $value )) { //Custom handling for arrays
                if ($is_list)
                    $parts [] = self::json_encode ( $value ); /* :RECURSION: */
                else
                    $parts [] = '"' . $key . '":' . self::json_encode ( $value ); /* :RECURSION: */
            } else {
                $str = '';
                if (! $is_list)
                    $str = '"' . $key . '":';
                //Custom handling for multiple data types
                if (!is_string ( $value ) && is_numeric ( $value ) && $value<2000000000)
                    $str .= $value; //Numbers
                elseif ($value === false)
                $str .= 'false'; //The booleans
                elseif ($value === true)
                $str .= 'true';
                else
                    $str .= '"' . addslashes ( $value ) . '"'; //All other things
                // :TODO: Is there any more datatype we should be in the lookout for? (Object?)
                $parts [] = $str;
            }
        }
        $json = implode ( ',', $parts );
        if ($is_list)
            return '[' . $json . ']'; //Return numerical JSON
        return '{' . $json . '}'; //Return associative JSON
    }


}

//可能和Wechat.class.php类存在冲突
if(class_exists('App\Lib\WeChat\PKCS7Encoder') != true)
{   
/**
 * PKCS7Encoder class
 *
 * 提供基于PKCS7算法的加解密接口.
 */
class PKCS7Encoder
{
    public static $block_size = 32;

    /**
     * 对需要加密的明文进行填充补位
     * @param $text 需要进行填充补位操作的明文
     * @return 补齐明文字符串
     */
    function encode($text)
    {
        $block_size = PKCS7Encoder::$block_size;
        $text_length = strlen($text);
        //计算需要填充的位数
        $amount_to_pad = PKCS7Encoder::$block_size - ($text_length % PKCS7Encoder::$block_size);
        if ($amount_to_pad == 0) {
            $amount_to_pad = PKCS7Encoder::block_size;
        }
        //获得补位所用的字符
        $pad_chr = chr($amount_to_pad);
        $tmp = "";
        for ($index = 0; $index < $amount_to_pad; $index++) {
            $tmp .= $pad_chr;
        }
        return $text . $tmp;
    }

    /**
     * 对解密后的明文进行补位删除
     * @param decrypted 解密后的明文
     * @return 删除填充补位后的明文
     */
    function decode($text)
    {

        $pad = ord(substr($text, -1));
        if ($pad < 1 || $pad > PKCS7Encoder::$block_size) {
            $pad = 0;
        }
        return substr($text, 0, (strlen($text) - $pad));
    }

}
}

if(class_exists('App\Lib\WeChat\Prpcrypt') != true)
{   
/**
 * Prpcrypt class
 *
 * 提供接收和推送给公众平台消息的加解密接口.
 */
class Prpcrypt
{
    public $key;

    function __construct($k) {
        $this->key = base64_decode($k . "=");
    }

    /**
     * 兼容老版本php构造函数，不能在 __construct() 方法前边，否则报错
     */
    function Prpcrypt($k)
    {
        $this->key = base64_decode($k . "=");
    }

    /**
     * 对明文进行加密
     * @param string $text 需要加密的明文
     * @return string 加密后的密文
     */
    public function encrypt($text, $appid)
    {
        try {
            //获得16位随机字符串，填充到明文之前
            $random = $this->getRandomStr();//"aaaabbbbccccdddd";
            $text = $random . pack("N", strlen($text)) . $text . $appid;
            $iv = substr($this->key, 0, 16);
            $pkc_encoder = new PKCS7Encoder;
            $text = $pkc_encoder->encode($text);
            $encrypted = openssl_encrypt($text,'AES-256-CBC',substr($this->key, 0, 32),OPENSSL_ZERO_PADDING,$iv);
            return array(ErrorCode::$OK, $encrypted);
        } catch (Exception $e) {
            //print $e;
            return array(ErrorCode::$EncryptAESError, null);
        }
    }
    /**
     * 对密文进行解密
     * @param string $encrypted 需要解密的密文
     * @return string 解密得到的明文
     */
    public function decrypt($encrypted, $appid)
    {
        try {
            $iv = substr($this->key, 0, 16);
            $decrypted = openssl_decrypt($encrypted,'AES-256-CBC',substr($this->key, 0, 32),OPENSSL_ZERO_PADDING,$iv);
        } catch (Exception $e) {
            return array(ErrorCode::$DecryptAESError, null);
        }
        try {
            //去除补位字符
            $pkc_encoder = new PKCS7Encoder;
            $result = $pkc_encoder->decode($decrypted);
            //去除16位随机字符串,网络字节序和AppId
            if (strlen($result) < 16)
                return "";
            $content = substr($result, 16, strlen($result));
            $len_list = unpack("N", substr($content, 0, 4));
            $xml_len = $len_list[1];
            $xml_content = substr($content, 4, $xml_len);
            $from_appid = substr($content, $xml_len + 4);
            if (!$appid)
                $appid = $from_appid;
            //如果传入的appid是空的，则认为是订阅号，使用数据中提取出来的appid
        } catch (Exception $e) {
            //print $e;
            return array(ErrorCode::$IllegalBuffer, null);
        }
        if ($from_appid != $appid)
            return array(ErrorCode::$ValidateAppidError, null);
        //不注释上边两行，避免传入appid是错误的情况
        return array(0, $xml_content, $from_appid);
        //增加appid，为了解决后面加密回复消息的时候没有appid的订阅号会无法回复
    }


    /**
     * 随机生成16位字符串
     * @return string 生成的字符串
     */
    function getRandomStr()
    {

        $str = "";
        $str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($str_pol) - 1;
        for ($i = 0; $i < 16; $i++) {
            $str .= $str_pol[mt_rand(0, $max)];
        }
        return $str;
    }

}

}
if(class_exists('App\Lib\WeChat\ErrorCode') != true)
{   
/**
 * error code
 * 仅用作类内部使用，不用于官方API接口的errCode码
 */
class ErrorCode
{
    public static $OK = 0;
    public static $ValidateSignatureError = 40001;
    public static $ParseXmlError = 40002;
    public static $ComputeSignatureError = 40003;
    public static $IllegalAesKey = 40004;
    public static $ValidateAppidError = 40005;
    public static $EncryptAESError = 40006;
    public static $DecryptAESError = 40007;
    public static $IllegalBuffer = 40008;
    public static $EncodeBase64Error = 40009;
    public static $DecodeBase64Error = 40010;
    public static $GenReturnXmlError = 40011;
    public static $errCode=array(
            '0' => '处理成功',
            '40001' => '校验签名失败',
            '40002' => '解析xml失败',
            '40003' => '计算签名失败',
            '40004' => '不合法的AESKey',
            '40005' => '校验AppID失败',
            '40006' => 'AES加密失败',
            '40007' => 'AES解密失败',
            '40008' => '公众平台发送的xml不合法',
            '40009' => 'Base64编码失败',
            '40010' => 'Base64解码失败',
            '40011' => '公众帐号生成回包xml失败'
    );
    public static function getErrText($err) {
        if (isset(self::$errCode[$err])) {
            return self::$errCode[$err];
        }else {
            return false;
        };
    }
}
}