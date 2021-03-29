<?php

//代理报备获取下面的子代理
$app->get('/agent/getagentlist/v1.0','Agent\AgentWeChatController@getAgentList');

//代理报备获取所有的公众号,包括子代理
$app->get('/agent/getagentwechatlist/v1.0','Agent\AgentWeChatController@getAgentWechatList');

//获取代理信息getAgentInfo
$app->get('/agent/getAgentInfo/v1.0','Agent\AgentInfoController@getAgentInfo');

//新增代理
$app->get('/agent/addAgency/v1.0','Agent\AgentInfoController@addAgency');

//新增代理首页banner图
$app->get('/agent/addAgencyImg/v1.0','Agent\AgentInfoController@addAgencyImg');