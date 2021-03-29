<?php
namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Impl\Agent\AgentInfoServicesImpl;
use App\Lib\HttpUtils\ApiSuccessWrapper;

class AgentInfoController extends Controller
{
    /**
     * 获取代理信息
     * @method GET
     * @param request
     * @return array
     */
    public function getAgentInfo(Request $request){
        $url_head = $request->input('url_head');
        $agentList = AgentInfoServicesImpl::getAgentInfo($url_head);
        return ApiSuccessWrapper::success($agentList);
    }
    
    /**
     * 新增代理信息
     * @method GET
     * @param request
     * @return array
     */
    public function addAgency(Request $request){
        
        $array=array(
            'username' =>$request->input('account'),
            'password'=>md5('123456'.time()),
            'create_time'=>time(),
            'status'=>'1',
            'discount' =>$request->input('minprice'),
            'type'=>'2',
            'oem_ok'=>$request->input('oem_ok'),
        );

        $addAgent = AgentInfoServicesImpl::add_agent($array);
        if($addAgent){
            $array_agency=array(
                'uid'=>$addAgent,
                'user_type'=>'2',
                'nick_name'=>$request->input('agencyname'),
                'company'=>$request->input('company'),
                'phone'=>$request->input('phone'),
                'qq_bumber' =>$request->input('qq_bumber'),
                'address'=>$request->input('address'),
                'production'=>$request->input('production'),
                'website'=>$request->input('website'),
                'description'=>$request->input('description'),
                'img_list'=>$request->input('img_list'),
                'index_banner_imgs'=>$request->input('index_banner_imgs'),
            );
            $addAgency = AgentInfoServicesImpl::addAgency($array_agency);

            return ApiSuccessWrapper::success($addAgency);
        }else{
            return ApiSuccessWrapper::fail($code = 1005,$trace = '代理添加失败或已添加');
        }
        
        
    }
}