<?php
namespace App\Models\Count;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Models\CommonModel;

class TaskStoreSummaryModel extends CommonModel
{
    protected $table = 'y_task_store_summary';

    protected $primaryKey = 'id';

    public $timestamps = false;

    static public function getSearchtaskSummary($where)
    {
        $model = TaskStoreSummaryModel::where($where)->get()->first();
        return $model ? TRUE : FALSE;
    }
    
    static public function getUptaskSummary($where, $date)
    {
        $model = TaskStoreSummaryModel::where($where)->update($date);
        return $model;
    }
    
    static public function getAddtaskSummary($date){
        $model = TaskStoreSummaryModel::insert($date);
        return $model?TRUE:FALSE;
    }
}