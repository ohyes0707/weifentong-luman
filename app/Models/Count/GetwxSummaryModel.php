<?php

namespace App\Models\Count;

use App\Models\CommonModel;
use Illuminate\Support\Facades\DB;

class GetWxSummaryModel extends CommonModel{

    protected $table='y_getwx_summary';

    protected $primaryKey = 'id';

    public $timestamps = false;
    
    static public function getGetWxSummary($date){
        $logic=GetWxSummaryModel::insert($date);
        return $logic?TRUE:FALSE;
    }
    
    static public function getSearchGetWxSummary($where){
        $logic=GetWxSummaryModel::where($where)->get()->first();
        return $logic?TRUE:FALSE;
    }

    static public function getUpGetWxSummary($where,$date){
        $logic=GetWxSummaryModel::where($where)->update($date);
        return $logic;
    }

    static public function getwx_total($mapp){
        $bid = $mapp['bid'];
        $date_time = $mapp['pdate'];
        $request=GetWxSummaryModel::select(DB::raw('SUM(IFNULL(old_sumgetwx_repeat,0)+IFNULL(new_sumgetwx_repeat,0)) as getwx_total'))
              ->where('bid','=',$bid)
              ->where('date_time','=',$date_time)
              ->get()->first();
        return $request?$request->toArray():null;
    }
}