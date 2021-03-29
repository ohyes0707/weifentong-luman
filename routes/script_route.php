<?php

//redis取数据
$app->get('/getredis','Receive\ImportDataController@getData');
$app->get('/gethredis','Receive\ImportDataController@getHData');
$app->get('/delredis','Receive\ImportDataController@delData');
$app->get('/setredis','Receive\ImportDataController@setData');
$app->get('/delhashredis','Receive\ImportDataController@delHashData');
$app->get('/sethashredis','Receive\ImportDataController@setHashData');

//导数据
$app->get('/writeuserinfo','Receive\ImportDataController@getAddUserinfo');

//导数据
$app->get('/deluserinfo','Receive\ImportDataController@getDelUserinfo');
//导数据获取两个初始值
$app->get('/deluserinfoid','Receive\ImportDataController@getDelUserinfoId');

//检查订单是否过期
$app->get('/orderDecide','Receive\OrderDecideController@orderDecide');

//检测涨粉系统是否正常
$app->get('/checkSystemFans','Receive\ImportDataController@checkSystemFans');