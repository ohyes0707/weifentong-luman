<?php
//管理员操作记录接口
$app->get('/operate/admin_log','Api\ApiController@admin_log');

$app->get('/store_redirect','Api\ApiController@store_redirect');

//打标签接口
$app->get('/batchTag','Api\ApiController@batchTag');