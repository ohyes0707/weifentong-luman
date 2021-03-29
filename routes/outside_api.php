<?php

//酒店判断mac
$app->get('/find_mac_follow','Api\HotelController@find_mac_follow');

//酒店吸粉查询
$app->get('/find_total_follow','Api\HotelController@find_total_follow');


//爱快立即连接查询接口
$app->get('/api/get_upsubscribe','Api\ApiController@get_upsubscribe');

//微信关注接口查询
$app->get('/api/subscribe_wxapi','Api\ApiController@subscribe_wxapi');

//老酒店吸粉查询
$app->get('/find_total_follow_old','Api\HotelController@find_total_follow_old');


//山腾关注接口
$app->get('/st_follow','Receive\OtherBussController@setStFollow');

//顺巴查询渠道实时涨粉接口
$app->get('/api/get_now_fans','Api\ApiController@getTodayFans');
