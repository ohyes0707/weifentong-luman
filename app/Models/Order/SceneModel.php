<?php

namespace App\Models\Order;

use App\Models\CommonModel;

class SceneModel extends CommonModel{

    protected $table = 'scene';

    protected $primaryKey = 'id';

    public $timestamps = false;
 
    
    /** 
    * 获取场景列表
    * @return array 
    */      
    static public function getSceneList()
    {
        $model = SceneModel::select('id','scene_name')->get();
        return $model?$model->toArray():null;
    }

}