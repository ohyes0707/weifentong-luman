<?php

namespace App\Services\Impl\Agent;

use App\Services\CommonServices;
use App\Models\Agent\AgentInfoModel;
use App\Models\User\UserModel;


class AgentInfoServicesImpl extends CommonServices
{
    /**
     * 获取代理信息
     * @param $arr
     * @return array
     */
    static public function getAgentInfo($url_head) {
        //获取代理信息
        $Parent = AgentInfoModel::getAgentInfo($url_head);
        return $Parent;
    }

    /**
     * 新增代理
     * @param $arr
     * @return array
     */
    static public function add_agent($arr) {
        //新增代理信息
        $Parent = UserModel::add_agent($arr);
        return $Parent;
    }

    /**
     * 新增代理信息
     * @param $arr
     * @return array
     */
    static public function addAgency($arr) {
        //新增代理信息
        $Parent = AgentInfoModel::addAgency($arr);
        return $Parent;
    }
    
    
}