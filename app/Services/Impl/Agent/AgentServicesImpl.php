<?php
namespace App\Services\Impl\Agent;
use App\Models\Agent\AgentModel;
use App\Lib\HttpUtils\ApiSuccessWrapper;


class AgentServicesImpl{

    public static function agentList($data)
    {

        return AgentModel::agentList($data);
    }


    public static function addUser($data)
    {
        return AgentModel::addUser($data);

    }
    public static function editUser()
    {
        return AgentModel::editUser();

    }
    public static function setAgentList()
    {
        return AgentModel::setagentList();

    }

    public static function sonAgentList($data){

        return AgentModel::sonAgentList($data);
    }



    public static function analyseSonAgent($data){


        return AgentModel::analyseSonAgent($data);
    }

}