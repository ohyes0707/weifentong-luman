# Lumen PHP Framework
# 2017年5月16日 第一次提交API接口项目

    路由要求:/模块/功能|操作/版本, 例如:user/getUserInfo/v1.0

    predis 使用方法:
       use Illuminate\Support\Facades\Redis;
       $string = Redis::get("key");