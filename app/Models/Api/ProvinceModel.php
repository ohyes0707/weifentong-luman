<?php
namespace App\Models\Api;
use App\Models\CommonModel;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Support\Facades\DB;


class ProvinceModel extends CommonModel{

    protected $table = 'province';

    protected $primaryKey = 'id';

    public $timestamps = false;

}