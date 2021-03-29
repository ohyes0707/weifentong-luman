<?php
//http://weifentong.test.com/home/report/set_default?wxid=269&shopid=3861831&shopname=%E8%A5%BF%E6%BA%AA
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

// 定时轮训商家主页
$app->get('timing/setHompPage/v1.0', 'Wechat\WeChatController@checkShopPage');


//美业授权列表
$app->get('store/getReportList/v1.0', 'Store\ReportController@getReportList');
// 美业新增报备
$app->get('store/addreport/v1.0', 'Store\ReportController@addreport');
// 美业授权
$app->get('store/auth_redirect', 'Store\ReportController@auth_redirect');
// 美业授权返回
$app->get('store/auth_back', 'Store\ReportController@auth_back');

$app->get('store/add_auth', 'Store\ReportController@add_auth');
// 获取门店
$app->get('store/getShopInfo/v1.0', 'Store\ReportController@getShopInfo');

/***代理授权***/

$app->get('/agent/add_auth',['uses'=>'Agent\AgentController@add_auth']);
$app->get('/agent/auth_redirect',['uses'=>'Agent\AgentController@auth_redirect']);

/**代理授权返回地址**/
$app->get('agent/auth_back',['uses'=>'Agent\AgentController@auth_back']);
/***代理管理***/
$app->get('/managerAgent/List/v1.0',['uses'=>'Agent\AgentController@managerAgent']);
/***一级代理列表***/
$app->get('/agent/List/v1.0',['uses'=>'Agent\AgentController@agentList']);
/***代理授权***/
$app->get('/agent/List/v1.0',['uses'=>'Agent\AgentController@agentList']);   // setagentList
/***代理的开启,禁用,删除***/
$app->get('/agent/setagentList/v1.0',['uses'=>'Agent\AgentController@setagentList']); //addAgent
/***新增代理***/
$app->get('/agent/addAgent/v1.0',['uses'=>'Agent\AgentController@addAgent']); //addAgent
/***编辑代理***/
$app->get('/agent/editAgent/v1.0',['uses'=>'Agent\AgentController@editAgent']); //addAgent
/***子代理列表***/
$app->get('/agent/sonAgentList/v1.0',['uses'=>'Agent\AgentController@sonAgentList']); //addAgent
/***子代理分析***/
$app->get('/agent/analyseSonAgent/v1.0',['uses'=>'Agent\AgentController@analyseSonAgent']); //addAgent
/***子代理获取门店***/
$app->get('/agent/getShopInfo/v1.0', 'Agent\AgentController@getShopInfo');
/***子代理门店设置***/
$app->get('/agent/set_default/v1.0', 'Agent\AgentController@set_default');




/***角色列表 ***/
$app->get('/operate/roleList/v1.0',['uses'=>'Operate\UserController@roleList']);
/***编辑角色 ***/
$app->get('/operate/editRole/v1.0',['uses'=>'Operate\UserController@editRole']);

/*****新增角色*****/
$app->get('/operate/addRole/v1.0',['uses'=>'Operate\UserController@addRole']);

/*****左视图权限列表*****/
$app->get('/operate/getleftView/v1.0',['uses'=>'Operate\UserController@getleftView']);



/***管理员列表 ***/
$app->get('/operate/getAdminList/v1.0',['uses'=>'Operate\UserController@managerlist']);
/***新增管理员 ***/
$app->get('/operate/addUser/v1.0',['uses'=>'Operate\UserController@addUser']);
/***编辑管理员 ***/
$app->get('/operate/editUser/v1.0',['uses'=>'Operate\UserController@editUser']);

$app->get('/operate/setmanagerList/v1.0',['uses'=>'Operate\UserController@setmanagerList']);


// 商家当前任务

$app->get('Buss/getCurentReport/v1.0',['uses'=>'Buss\SonBussTaskController@CurrentTask']);


// 商家历史任务
$app->get('Buss/historyTaskList/v1.0',['uses'=>'Buss\SonBussTaskController@historyTaskList']);

//  子商户统计

$app->get('Buss/sonBussList/v1.0',['uses'=>'Buss\SonBussTaskController@sonBussList']);


// 拒绝任务
$app->get('Buss/refuseReport/v1.0',['uses'=>'Buss\SonBussTaskController@refuseReport']);


// 获取报表订单数据
$app->get('Operate/getReportData/v1.0',['uses'=>'Operate\AnalyzeController@getReportData']);


// 获取报表的订单数据

$app->get('Operate/getOrderData/v1.0',['uses'=>'Operate\AnalyzeController@getReportData']);


// 获得图表数据


$app->get('Operate/getPictureData/v1.0',['uses'=>'Operate\AnalyzeController@getPictureData']);


$app->get('Wechat/auth_redirect',['uses'=>'Wechat\WeChatController@authRedirect']);

// 接受公众平台发送的事件信息

$app->get('wechat/wxapi/event_notify/appid/{APPID}',['uses'=>'Wechat\WxApiController@event_notify']);
$app->post('wechat/wxapi/event_notify/appid/{APPID}',['uses'=>'Wechat\WxApiController@event_notify']);


// 获取门店信息

$app->get('Wechat/getShopInfo/v1.0',['uses'=>'Wechat\WeChatController@get_wifishop']);

// 设置门店

$app->get('Wechat/set_default/v1.0',['uses'=>'Wechat\WeChatController@set_default']);

//商家主页
$app->get('Wechat/shangjia/v1.0',['uses'=>'Wechat\WeChatController@shangjia']);



/***test ***/
$app->get('Wechat/testWx',['uses'=>'Wechat\WeChatController@testWx']);
$app->get('wechat/wxapi/auth_notify',['uses'=>'Wechat\WxApiController@authNotify']);
$app->post('wechat/wxapi/auth_notify',['uses'=>'Wechat\WxApiController@authNotify']);

$app->get('Wechat/testWx',['uses'=>'Wechat\WeChatController@testWx']);


$app->get('Wechat/add_auth',['uses'=>'Wechat\WeChatController@add_auth']);

$app->get('Wechat/auth_back',['uses'=>'Wechat\WeChatController@auth_back']);






/***获取订单列表 ***/
$app->get('Operate/Order/getOrderList/v1.0',['uses'=>'Operate\OrderController@getOrderList']);

$app->post('Operate/Order/setRedis/v1.0',['uses'=>'Operate\OrderController@setRedis']);

//订单粉丝详情
$app->get('Operate/Order/orderFans/v1.0',['uses'=>'Operate\OrderController@orderFans']);

/***获取销售统计列表***/
$app->get('Home/SellController/sellCount/v1.0',['uses'=>'Home\SellController@sellCount']);

/***获取优先级列表***/
$app->get('Operate/Level/levelList/v1.0',['uses'=>'Operate\LevelController@getLevelList']);
/***设置优先级****/
$app->get('Operate/Level/setLevel/v1.0',['uses'=>'Operate\LevelController@setLevel']);
$app->get('setLevel',['uses'=>'Operate\LevelController@setLevel']);

/***获取渠道列表***/
$app->get('Operate/Buss/getBussList/v1.0',['uses'=>'Operate\BussController@getBussList']);
$app->get('Operate/Buss/setBussList/v1.0',['uses'=>'Operate\BussController@setBussList']);

/***运营系统用户信息***/
$app->get('/operate/getAdminInfo/v1.0',['uses'=>'Operate\UserController@getAdminInfo']);

/***关闭任务***/
$app->get('Operate/Order/closeTask/v1.0',['uses'=>'Operate\OrderController@closeTask']);

$app->group(['middleware' => 'checkSign'], function($app)
{
    /*** 获取用户信息接口 ***/
    $app->get('/user/getUserInfo/v1.0',['uses'=>'Home\UserController@getUserInfo']);
    $app->post('/user/saveUserInfo/v1.0',['uses'=>'UserController@saveUserInfo']);


    /**用户登录接口***/
    $app->post('/home/doUserLogin/v1.0',['uses'=>'Home\UserController@doUserLogin']);

    /**运营登录接口***/
    $app->post('/operate/doOperateLogin/v1.0',['uses'=>'Operate\UserController@doOperateLogin']);

    $app->post('/buss/doBussLogin/v1.0',['uses'=>'Business\UserController@doBussLogin']);

    $app->post('/agent/doAgentLogin/v1.0',['uses'=>'Agent\UserController@doAgentLogin']);
    $app->get('/agent/getUserInfo/v1.0',['uses'=>'Agent\UserController@getUserInfo']);


//获取微信公众号名称列表
$app->get('/user/getWxNumberName/v1.0','Operate\WorkOrderController@getWxNumberName');

//销售代表
$app->get('/user/getShopName/v1.0','Operate\WorkOrderController@getShopName');

//获取场景列表
$app->get('/user/getSceneList/v1.0','Operate\WorkOrderController@getSceneList');



//获取单独的工单详细信息
$app->get('/user/getWOrderInfo/v1.0','Operate\WorkOrderController@getWOrderInfo');



//工单结束
/***获取报备列表***/
$app->get('home/getReportList/v1.0',['uses'=>'Home\ReportController@getReportList']);

/***更新报备信息***/
$app->get('report/updateReport/v1.0',['uses'=>'Operate\ReportController@updateReport']);

    //根据微信名获取报备信息
    $app->get('/home/getReportByWxname/v1.0','Home\ReportController@getReportByWxname');

});


//获取工单列表
$app->get('/user/getYWorkOrder/v1.0','Operate\WorkOrderController@getWOrderList');

//获取添加工单时信息
$app->get('/user/getWorkOrder/v1.0','Operate\WorkOrderController@getWorkOrder');

//修改工单状态
$app->get('/user/getUpWOrderStat/v1.0','Operate\WorkOrderController@getUpWOrderStat');

$app->get('/user/getWOrderTwoInfo/v1.0','Operate\WorkOrderController@getWOrderTwoInfo');

$app->get('/user/getAllCity/v1.0','Operate\WorkOrderController@getAllCity');

/***更新报备信息***/
$app->get('/user/getWOrderLogCompare/v1.0','Home\WorkOrderController@getLogList');



/***取号逻辑开始***/
$app->get('/user/getWxInfo/v1.0','Receive\PublicNumController@getWxInfo');

$app->get('/user/setChannelInfo/v1.0','Receive\PublicNumController@setChannelInfo');

$app->get('/user/setUserInfo/v1.0','Receive\PublicNumController@setUserInfo');

$app->get('/user/getDbUserInfo/v1.0','Receive\PublicNumController@getDbUserInfo');

$app->get('/user/getFansBehavior/v1.0','Receive\PublicNumController@getFansBehavior');

$app->post('/user/getDelTask/v1.0','Receive\PublicNumController@getDelTask');


/***检查是否关注***/
$app->get('user/search/checkSub','Receive\SearchController@checkSub');


/***导入数据库数据开始***/
$app->get('/data/getAddTaskData/v1.0','Receive\ImportDataController@getAddTaskData');

$app->get('/data/getTestData/v1.0','Receive\ImportDataController@getTestData');

/**新增门店信息**/
$app->get('Wechat/addShopInfo/v1.0','Wechat\WeChatController@addShopInfo');


//平台报表
$app->get('Count/PlatSummary/platCount/v1.0','Count\PlatSummaryController@platCount');
//平台报表excel数据
$app->get('Count/PlatSummary/platCountExcel/v1.0','Count\PlatSummaryController@platCountExcel');

//渠道报表
$app->get('Count/BussSummary/bussCount/v1.0','Count\BussSummaryController@bussCount');
//渠道报表查看详情
$app->get('Count/BussSummary/bussCount_detail/v1.0','Count\BussSummaryController@bussCount_detail');
//渠道报表excel数据
$app->get('Count/BussSummary/bussCountExcel/v1.0','Count\BussSummaryController@bussCountExcel');

//营收报表渠道
$app->get('Count/RevenueSummary/revenueCount/v1.0','Count\RevenueSummaryController@revenueCountBuss');
//营收报表渠道excel数据
$app->get('Count/RevenueSummary/revenueCountExcel/v1.0','Count\RevenueSummaryController@revenueCountBussExcel');
//营收报表渠道查看详情
$app->get('Count/RevenueSummary/revenueDetail_buss/v1.0','Count\RevenueSummaryController@revenueDetail_buss');
//营收报表公众号
$app->get('Count/RevenueSummary/revenue_wechat/v1.0','Count\RevenueSummaryController@revenueCountWechat');
//营收报表公众号查看详情
$app->get('Count/RevenueSummary/revenueDetail_wechat/v1.0','Count\RevenueSummaryController@revenueDetail_wechat');
//营收报表公众号查看子渠道
$app->get('Count/RevenueSummary/revenueDetail_wechatOne/v1.0','Count\RevenueSummaryController@revenueDetail_wechatOne');

//销售报表
$app->get('Count/SaleFormController/saleStatistics/v1.0','Count\SaleFormController@saleStatistics');

//以公众号为维度
$app->get('/data/getSearchWxTaskData/v1.0','Receive\ReportFormController@getSearchWxTaskData');
//以渠道为维度
$app->get('/data/getSearchWxBussTaskData/v1.0','Receive\ReportFormController@getSearchWxBussTaskData');
//以公众号为维度
$app->get('/data/getSearchBussTaskData/v1.0','Receive\ReportFormController@getSearchBussTaskData');
//以渠道为维度
$app->get('/data/getSearchBussWxTaskData/v1.0','Receive\ReportFormController@getSearchBussWxTaskData');
//以父渠道为维度查一个渠道
$app->get('/data/getSearchOneBussTaskData/v1.0','Receive\ReportFormController@getSearchOneBussTaskData');
//以子渠道为维度查一个渠道
$app->get('/data/getSearchSonBussWxTaskData/v1.0','Receive\ReportFormController@getSearchSonBussWxTaskData');

//修改redis数据
//$app->get('redis','Operate\OrderController@updateRedis');

//定时任务处理取关事件
$app->get('/TimeEvent/delUnSunEvent/v1.0','Receive\TimeEventController@dealUnSubEvent');

//定时任务处理云袋立即连接事件
$app->get('/TimeEvent/yunDaiCheckSubTask/v1.0','Receive\TimeEventController@yunDaiCheckSubTask');

//查询是否关注
$app->get('/user/getIsFollownum/v1.0','Receive\PublicNumController@getIsFollownum');


//以订单为维度微信关注报表
$app->get('/data/getPlatformReport/v1.0','Count\WeChatReportController@getPlatformReport');
//以公众号为维度微信关注报表
$app->get('/data/getPublicSignalReport/v1.0','Count\SignalReportController@getPublicSignalReport');


//父商户结算管理
$app->get('/buss/getParentBuss/v1.0','Business\SettlementController@getParentBuss');
//子商户结算管理
$app->get('/buss/getSonBuss/v1.0','Business\SettlementController@getSonBuss');
//提现
$app->get('/buss/getWithdrawDeposit/v1.0','Business\SettlementController@getWithdrawDeposit');

//商家增粉模块
$app->get('Business/Fans/fansEarn/v1.0','Business\FansController@fansEarn');
//子商家增粉模块
$app->get('Business/Fans/fansEarn_child/v1.0','Business\FansController@fansEarn_child');

//提现查看
$app->get('/buss/getWithdrawLook/v1.0','Business\SettlementController@getWithdrawLook');
//总额
$app->get('/buss/getParentSum/v1.0','Business\SettlementController@getParentSum');
//子商户审核通过
$app->get('/buss/getLook/v1.0','Business\SettlementController@getLook');
//子商户审核驳回
$app->get('/buss/getReject/v1.0','Business\SettlementController@getReject');

//子商户
$app->get('/data/getSearchSubTaskData/v1.0','Business\SubMerchantController@getSearchSubTaskData');
$app->get('/data/getSubShopReport/v1.0','Business\SubMerchantController@getSubShopReport');
$app->get('/data/getHistoryFans/v1.0','Business\SubMerchantController@getHistoryFans');

//以公众号为维度
$app->get('/data/getAddmoney/v1.0','Receive\ImportDataController@getAddmoney');
//商户信息


$app->get('/buss/getBussInfo/v1.0','Business\SettlementController@getBussInfo');

//实时平台数据
$app->get('/data/getSumPlatform/v1.0','Receive\RealTimeController@getSumPlatform');

//实时渠道数据
$app->get('/data/getSumDesc/v1.0','Receive\RealTimeController@getSumDesc');

//运营系统销售列表
$app->get('Operate/SaleController/getSaleList/v1.0','Operate\SaleController@getSaleList');
//销售编辑
$app->get('Operate/SaleController/saleEdit/v1.0','Operate\SaleController@saleEdit');
//销售新增
$app->get('Operate/SaleController/saleAdd/v1.0','Operate\SaleController@saleAdd');
//销售报表
$app->get('Operate/SaleController/saleForm/v1.0','Operate\SaleController@saleForm');
//销售状态
$app->get('Operate/SaleController/saleStatus/v1.0','Operate\SaleController@saleStatus');
//销售删除
$app->get('Operate/SaleController/saleDel/v1.0','Operate\SaleController@saleDel');
//销售多选开启
$app->get('Operate/SaleController/startAll/v1.0','Operate\SaleController@startAll');
//销售多选禁用
$app->get('Operate/SaleController/endAll/v1.0','Operate\SaleController@endAll');
//销售多选删除
$app->get('Operate/SaleController/delAll/v1.0','Operate\SaleController@delAll');

//运营系统代理列表
$app->get('Agent/AgentListController/getAgentList/v1.0','Agent\AgentListController@getAgentList');
//运营系统代理列表-子代理
$app->get('Agent/AgentListController/subAgent/v1.0','Agent\AgentListController@subAgent');

//报表系统提现列表
$app->get('/data/getDepositlist/v1.0','Count\WithdrawalsController@getDepositlist');
//提现查看
$app->get('/data/getWithdrawLook/v1.0','Count\WithdrawalsController@getWithdrawLook');
//子商户审核通过
$app->get('/data/getLook/v1.0','Count\WithdrawalsController@getLook');
//子商户审核驳回
$app->get('/data/getReject/v1.0','Count\WithdrawalsController@getReject');

//门店数据回填列表
$app->get('/data/getBackFill/v1.0','Count\BackfillController@getBackFill');
//门店数据回填编辑
$app->get('/data/getBackEdit/v1.0','Count\BackfillController@getBackEdit');
//门店数据回填编辑
$app->get('/data/BackEdit/v1.0','Count\BackfillController@BackEdit');


//---------------------------渠道管理-------------------------------//
$app->get('/data/getChannelList/v1.0','Operate\ChannelManageController@getChannelList');
//---------------------------渠道管理结束----------------------------//

//---------------------------产能列表-------------------------------//
$app->get('/data/getCapacityList/v1.0','Operate\ChannelManageController@getCapacityList');
$app->get('/data/getCapacityOrderList/v1.0','Operate\ChannelManageController@getCapacityOrderList');
//---------------------------渠道管理结束----------------------------//


//订单报表
$app->get('Count/Order/orderForm/v1.0','Count\OrderSummaryController@orderForm');

//订单查询
$app->get('Operate/Order/orderSearch/v1.0','Operate\OrderController@orderSearch');

//第三方取号查询订单信息
$app->get('Operate/Order/oidbidinfo/v1.0','Receive\SearchController@orderSearch');


//渠道redis数据
$app->get('Operate/BussController/buss_redis/v1.0','Operate\BussController@buss_redis');

//脚本的程序
require_once( __DIR__.'/script_route.php');

//对外接口
require_once( __DIR__.'/outside_api.php');

//代理系统接口
require_once( __DIR__.'/agent_route.php');

//代理系统接口
require_once( __DIR__.'/operate_route.php');

//定时任务处理重发机制记录表
$app->get('Wechat/rsend_timer_task/v1.0','Wechat\WeChatController@rsend_timer_task');

//定时任务处理重发机制记录表
$app->get('Wechat/task_complete/v1.0','Wechat\WeChatController@task_complete');

//编辑渠道一口价更新redis的price
$app->get('Buss/upRedisBussPrice/v1.0','Buss\SonBussTaskController@upRedisBussPrice');


//代理系统销售统计
$app->get('Agent/AgentListController/agentSale/v1.0','Agent\AgentListController@agentSale');

//运营系统公众号列表
$app->get('Operate/WechatController/wechatList/v1.0','Operate\WechatController@wechatList');

//操作日志
$app->post('Operate/Order/orderLogAdd/v1.0',['uses'=>'Operate\OrderController@orderLogAdd']);
//操作日志列表
$app->get('Operate/Order/orderLogs/v1.0',['uses'=>'Operate\OrderController@orderLogs']);

//美业订单列表
$app->get('Store/StoreOrder/storeOrderList/v1.0',['uses'=>'Store\StoreOrderController@storeOrderList']);
//美业新增订单公众号筛选
$app->get('Store/StoreOrder/storeOrderAddWx/v1.0',['uses'=>'Store\StoreOrderController@storeOrderAddWx']);
//美业新增订单
$app->post('Store/StoreOrder/storeOrderAdd/v1.0',['uses'=>'Store\StoreOrderController@storeOrderAdd']);
//美业订单状态修改
$app->get('Store/Order/changeStatus/v1.0',['uses'=>'Store\StoreOrderController@changeStatus']);
//美业品牌列表
$app->get('Store/Brand/storeBrandList/v1.0',['uses'=>'Store\StoreBrandController@storeBrandList']);
//美业门店列表
$app->get('Store/Shop/storeShopList/v1.0',['uses'=>'Store\StoreShopController@storeShopList']);
//美业获取全部区域、品牌
$app->get('Store/Shop/getAreaBrand/v1.0',['uses'=>'Store\StoreShopController@getAreaBrand']);
//美业添加门店
$app->get('Store/Shop/storeShopAdd/v1.0',['uses'=>'Store\StoreShopController@storeShopAdd']);
//美业设备列表
$app->get('Store/Mac/macList/v1.0',['uses'=>'Store\StoreMacController@macList']);

//云袋通知接口
$app->post('user/yundai/notice/v1.0','Receive\SearchController@YUNDAI_notice');
//云袋检查关注、计算当日剩余涨粉数
$app->get('user/yundai/count_fans','Receive\SearchController@YUNDAI_Search');