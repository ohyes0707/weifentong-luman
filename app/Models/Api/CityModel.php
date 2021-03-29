<?php
namespace App\Models\Api;
use App\Models\CommonModel;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Support\Facades\DB;


class CityModel extends CommonModel{

    protected $table = 'city';

    protected $primaryKey = 'id';

    public $timestamps = false;


}