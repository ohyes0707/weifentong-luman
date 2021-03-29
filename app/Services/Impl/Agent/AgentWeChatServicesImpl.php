<?php

namespace App\Services\Impl\Agent;

use App\Services\CommonServices;
use Illuminate\Support\Facades\Redis;
use App\Models\User\UserModel;


class AgentWeChatServicesImpl extends CommonServices
{
    /**
     * 获取子代理人列表
     * @param $arr
     * @return array
     */
    static public function getAgentList($arr) {
        $selectArray = array('id', 'username', 'nick_name');
        $userlist = UserModel::select($selectArray)
                    ->leftjoin('user_info','user_info.uid','=','user.id')
                    ->where('agent_id', '=', $arr['userid'])
                    ->orWhere('id', '=', $arr['userid'])
                    ->orderBy('id')
                    ->get()->toArray();
        return $userlist;
    }
    
    /**
     * 获取子代理公众号
     * @param $arr
     * @return array
     */
    static public function getAgentWechatList($arr) {
        
    }
}