<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommonModel extends Model{

    public static function getPages($query, $curPage, $pageSize = 10,$count = null,$search = null)
    {
        if ($search)
            $query = $query->andFilterWhere($search);

        if($count){
            $data['count'] = $count;
        }else{
            $data['count'] = $query->count();
        }
        if (!$data['count']) {
            return ['count' => 0, 'curPage' => $curPage, 'pageSize' => $pageSize,
                'start' => 0, 'end' => 0, 'data' => []];
        }
        //当前页
        $curPage = (ceil($data['count'] / $pageSize) < $curPage) ? ceil($data['count'] / $pageSize) : $curPage;
        //起始页，尾页，每页数量
        $data['start'] = ($curPage - 1) * $pageSize + 1;
        $data['end'] = (ceil($data['count'] / $pageSize) == $curPage ? $data['count'] : $curPage * $pageSize);
        $data['pageSize'] = $pageSize;
        //数据
        $data['data'] = $query->offset(($curPage - 1) * $pageSize)
            ->limit($pageSize)
            ->get()
            ->toArray();
        return $data;
    }
    

    public static function getGroupPages($query, $curPage, $pageSize = 10, $count)
    {

        $data['count'] = $count;
        if (!$data['count']) {
            return ['count' => 0, 'curPage' => $curPage, 'pageSize' => $pageSize,
                'start' => 0, 'end' => 0, 'data' => []];
        }
        //当前页
        $curPage = (ceil($data['count'] / $pageSize) < $curPage) ? ceil($data['count'] / $pageSize) : $curPage;
        //起始页，尾页，每页数量
        $data['start'] = ($curPage - 1) * $pageSize + 1;
        $data['end'] = (ceil($data['count'] / $pageSize) == $curPage ? $data['count'] : $curPage * $pageSize);
        $data['pageSize'] = $pageSize;
        //数据
        $data['data'] = $query->offset(($curPage - 1) * $pageSize)
            ->limit($pageSize)
            ->get()
            ->toArray();
        return $data;
    }
}