<?php

namespace App\Models\Order;

use App\Models\CommonModel;

class WxModel extends CommonModel{

    protected $table = 'wx_info';

    protected $primaryKey = 'id';

    public $timestamps = false;

    //得到二维码url
    public static function get_qrcode($id)
    {
        $list = WxModel::where("id",$id)->first();
        if($list == '')return '';
        $list=$list->toArray();
        if(is_file('./Uploads/Wxqrcode/'.$list['qrcode_url']))
        {
            return 'http://'.$_SERVER['SERVER_NAME'].'/Uploads/Wxqrcode/'.$list['qrcode_url'];
        }else{
            $data['qrcode_url'] = './Uploads/Wxqrcode/'.date('Y-m-d',time()).'/'.date('His',time()).rand(0,9999999);
           // $data['qrcode_url'] = \My\Net::download_img($list['wx_qrcodeurl'],$data['qrcode_url']);
            $data['qrcode_url'] = str_replace('./Uploads/Wxqrcode/', '', $data['qrcode_url']); 
            if(is_file('./Uploads/Wxqrcode/'.$data['qrcode_url']))
            {
                $this->where("id=%d",$id)->setField('qrcode_url',$data['qrcode_url']);
                return 'http://'.$_SERVER['SERVER_NAME'].'/Uploads/Wxqrcode/'.$data['qrcode_url'];                
            }
        }
        return '';
    }
}