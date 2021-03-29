<?php
/**
 * Created by PhpStorm.
 * User: luojia
 * Date: 2017/5/18
 * Time: 15:42
 */

namespace App\Http\Middleware;
use App\Lib\HttpUtils\ApiSuccessWrapper;
use App\Lib\HttpUtils\SignUtils;
use Closure;

class CheckSignMiddleware {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $method = $request->getMethod();
        $sysSign = '';
        if($method == "GET"){
            $sysSign = SignUtils::verifySign('common', $_GET, 'GET');
        }else{
            $parameter = file_get_contents('php://input', 'r');
            $parameterArray = array();
            if ($parameter) {
                $parameterArray = json_decode($parameter, true);
                $_POST = $parameterArray;
                $sysSign = SignUtils::verifySign('common', $parameterArray, 'POST');
            }
        }
        $sign = isset($_GET['sign']) ? $_GET['sign'] : null;
        if (!$sign) {
            return ApiSuccessWrapper::fail(1000);
        }
        if ($sign != $sysSign) {
            return ApiSuccessWrapper::fail(1000);
        }else{
            return $next($request);
        }
    }
}