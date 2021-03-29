<?php
namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Impl\Agent\AgentWeChatServicesImpl;
use App\Lib\HttpUtils\ApiSuccessWrapper;

class AgentWeChatController extends Controller
{
    /**
     * 获取子代理人列表接口
     * @method GET
     * @param userid
     * @return array
     */
    public function getAgentList(Request $request){
        $arr = array(
            'userid' => $request->input('userid'),
        );
        $agentList = AgentWeChatServicesImpl::getAgentList($arr);
        return ApiSuccessWrapper::success($agentList);
    }
    
    
    /**
     * 获取子代理公众号接口
     * @method GET
     * @param userid
     * @return array
     */
    public function getAgentWechatList(Request $request){
        $arr = array(
            'userid' => $request->input('userid'),
        );
        $agentWechatList = AgentWeChatServicesImpl::getAgentWechatList($arr);
        return ApiSuccessWrapper::success($agentWechatList);
    }
}