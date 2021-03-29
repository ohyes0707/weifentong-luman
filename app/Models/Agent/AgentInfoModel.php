<?php
namespace App\Models\Agent;
use App\Models\CommonModel;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Support\Facades\DB;


class AgentInfoModel extends CommonModel{

    protected $table = 'user_info';

    protected $primaryKey = 'id';

    public $timestamps = false;

    /**
     * 根据url_head域名头部获取代理信息
     * @param $url_head
     * @return null
     */
    static public function getAgentInfo($url_head){
        $model = AgentInfoModel::where('website', 'like', "%$url_head%")->get()->first();

        return $model?$model->toArray():null;
    }

    /**
     * 根据url_head域名头部获取代理信息
     * @param $url_head
     * @return null
     */
    static public function addAgency($arr){
        $model = AgentInfoModel::insert($arr);;
        return $model?$model:null;
    }

    

}