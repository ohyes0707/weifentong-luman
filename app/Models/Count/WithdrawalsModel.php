<?php

namespace App\Models\Count;
use App\Lib\WeChat\Third;
use App\Models\Buss\BussModel;
use App\Lib\WeChat\Wechat;
use App\Models\CommonModel;
use App\Services\Impl\Wechat\WechatServicesImpl;
use App\Services\Impl\Wechat\WeChatReportServicesImpl;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class WithdrawalsModel extends CommonModel{

    protected $table='y_order';

    protected $primaryKey = 'order_id';

    public $timestamps = false;
    
    static public function getDepositlist($where,$date_time){
    
    }
    
}