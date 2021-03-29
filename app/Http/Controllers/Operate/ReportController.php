<?php

namespace App\Http\Controllers\Operate;

use App\Http\Controllers\Controller;
use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Services\Impl\Report\ReportServicesImpl;


class ReportController extends Controller
{

    /**
     * 更新报备信息
     * @method GET
     * @return array
     */
    public function updateReport(){
        $id = isset($_GET['id'])?$_GET['id']:'';
        $status = isset($_GET['status'])?$_GET['status']:'';
        $where = array('id'=>$id);
        $data = array('status'=>$status);
        $result = ReportServicesImpl::updateReport($where,$data);
        return ApiSuccessWrapper::success($result);
    }

}
