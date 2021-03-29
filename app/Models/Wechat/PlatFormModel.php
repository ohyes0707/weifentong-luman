<?php
/**
 * Created by PhpStorm.
 * User: li wei
 * Date: 2017/5/15
 * Time: 下午8:38
 */
namespace App\Models\Wechat;

use App\Models\CommonModel;

class PlatFormModel extends CommonModel{

    protected $table = 'third_platform_info';

    protected $primaryKey = 'platform_id';

    public $timestamps = false;

    /**
     * 获取第三方平台配置信息
     */
    static public function getPlatFormInfo($platform_id){
        $model = PlatFormModel::where('platform_id', '=', $platform_id)->get()->first();
        return $model?$model->toArray():null;
    }

}