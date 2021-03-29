<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/5/27
 * Time: 13:53
 */
namespace App\Models\Buss;
use App\Models\CommonModel;

class DeviceInfoModel extends CommonModel{

    protected $table = 'device_info';

    protected $primaryKey = 'id';

    public $timestamps = false;

    /***
     * @param $device_code
     * @return null
     */
    public static function getDeviceInfo($device_code){
        $model = DeviceInfoModel::select('bid')->where('device_code','=',$device_code)->get()->first();
        return $model?$model->toArray():null;
    }


}