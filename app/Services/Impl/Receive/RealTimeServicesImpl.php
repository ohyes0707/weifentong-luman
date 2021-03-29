<?php
namespace App\Services\Impl\Receive;

use Illuminate\Support\Facades\Redis;
use App\Models\Buss\BussModel;

class RealTimeServicesImpl
{
    private $sumgetwx=0;
    private $getwx=0;
    private $complet=0;
    private $follow=0;
    private $sumbuss;

//    static public function getSumPlatform()
//    {
//        $platform=array(
//            'sumgetwx'=>0,
//            'getwx'=>0,
//            'complet'=>0,
//            'follow'=>0,
//        );
//        $date = isset($_GET['date'])?$_GET['date']:date('Ymd');
//        $array= Redis::hgetall($date);
//        foreach ($array as $key => $value) {
//            $pd=substr($key, 0, 5);
//            if($pd=='sum--'){
//                $param= explode('-', $key);
//                if($param[2]>0){
//                    switch ($param[3]) {
//                        case 1:
//                                $platform['getwx']=$platform['getwx']+$value;
//                            break;
//                        case 2:
//                                $platform['complet']=$platform['complet']+$value;
//                            break;
//                        case 3:
//                                $platform['follow']=$platform['follow']+$value;
//                            break;
//                        case 6:
//                                $platform['sumgetwx']=$platform['sumgetwx']+$value;
//                            break;
//                        default:
//                            break;
//                    }
//                }
//            }
//        }
//        return $platform;
//       // print_r($array);
//    }
    
    public function getSumPlatform()
    {

        $date = isset($_GET['date'])?$_GET['date']:date('Ymd');
        $array= Redis::hgetall($date);
        foreach ($array as $key => $value) {
            $param= explode('-', $key);
            switch ($param[0]) {
                case 'old':
                    $this->setNum($param,$value);
                    break;
                case 'new':
                    self::setNum($param,$value);
                    break;
                default:
                    break;
            }   
        }
        
        $platform=array(
            'sumgetwx' => $this->sumgetwx,
            'getwx' => $this->getwx,
            'complet' => $this->complet,
            'follow' => $this->follow
        );
        $platform['date'] = date('Y-m-d');
        $platform['fillrate'] = self::division($platform['getwx'], $platform['sumgetwx']);
        $platform['confirmrate'] = self::division($platform['complet'], $platform['getwx']);
        $platform['followate'] = self::division($platform['follow'], $platform['complet']);
        return $platform;
       // print_r($array);
    }
    
    public function setNum($param,$value) 
    {
        $behavior= self::getConvertBehavior($param[3]);

        if($param[1]>0&&$behavior!=null&&$param[2]>0&&$param[4]==1){
            $this->$behavior=$this->$behavior+$value;
        } elseif ($behavior=='sumgetwx'&&$param[2]>0&&$param[4]==1) {
            $this->$behavior=$this->$behavior+$value;
        } else {
            
        }
        
    }
    
    public function getConvertBehavior($num) {
        switch ($num) {
            case 1:
                return 'getwx';
            case 2:
                return 'complet';
            case 3:
                return 'follow';
           case 6:
                return 'sumgetwx';
            default:
                return null;
        }
    }
    
    //获取上下渠道
    public function getUpDownBuss($tj) {
        
        //拿出所有在redis的数据
        $date = isset($_GET['date'])?$_GET['date']:date('Ymd');
        $array= Redis::hgetall($date);
        foreach ($array as $key => $value) {
            $param= explode('-', $key);
            switch ($param[0]) {
                case 'old':
                    $this->setBussNum($param,$value);
                    break;
                case 'new':
                    self::setBussNum($param,$value);
                    break;
                default:
                    break;
            }   
        }
        $bussdata=$this->sumbuss;
        //取出渠道的信息
        $bussid= array_keys($bussdata);
        $pbinfo= BussModel::getBussName($bussid,$tj['pbid'],$tj['bussid']);
        //归纳数据
        $newarray=array();
        foreach ($pbinfo as $key => $value) {
            
            if($value['pbid']==0){
                $value['pbid']=$value['id'];
                $value['pbusername']=$value['username'];
            }
            if(!isset($newarray[$value['pbid']])){
                $newarray[$value['pbid']]=array(
                    'sumgetwx' => 0,
                    'getwx' => 0,
                    'complet' => 0,
                    'follow' => 0
                );
            }
            if(isset($bussdata[$value['id']]['sumgetwx'])){
                $newarray[$value['pbid']]['pbusername']=$value['pbusername'];
                $newarray[$value['pbid']]['username']=$value['username'];
                $newarray[$value['pbid']]['sumgetwx']=$newarray[$value['pbid']]['sumgetwx']+$bussdata[$value['id']]['sumgetwx'];
                $newarray[$value['pbid']]['getwx']=$newarray[$value['pbid']]['getwx']+$bussdata[$value['id']]['getwx'];
                $newarray[$value['pbid']]['complet']=$newarray[$value['pbid']]['complet']+$bussdata[$value['id']]['complet'];
                $newarray[$value['pbid']]['follow']=$newarray[$value['pbid']]['follow']+$bussdata[$value['id']]['follow'];
                $bussdata[$value['id']]['username']=$value['username'];
                $bussdata[$value['id']]['bussid']=$value['id'];
                $bussdata[$value['id']]['pbusername']=$value['pbusername'];
                $newarray[$value['pbid']]['list'][]=$bussdata[$value['id']];
            }

        }
        
        return $newarray;

    }
    
    public function setBussNum($param,$value) 
    {
        $behavior= self::getConvertBehavior($param[3]);
        if(!isset($this->sumbuss[$param[2]])){
            $this->sumbuss[$param[2]]=array(
                    'sumgetwx' => 0,
                    'getwx' => 0,
                    'complet' => 0,
                    'follow' => 0
            );
        }
        if($param[1]>0&&$behavior!=null&&$param[2]>0&&$param[4]==1){
            $this->sumbuss[$param[2]][$behavior]= isset($this->sumbuss[$param[2]][$behavior])?$this->sumbuss[$param[2]][$behavior]:0;
            $this->sumbuss[$param[2]][$behavior]=$this->sumbuss[$param[2]][$behavior]+$value;
        } elseif ($behavior=='sumgetwx'&&$param[2]>0&&$param[4]==1) {
            $this->sumbuss[$param[2]][$behavior]= isset($this->sumbuss[$param[2]][$behavior])?$this->sumbuss[$param[2]][$behavior]:0;
            $this->sumbuss[$param[2]][$behavior]=$this->sumbuss[$param[2]][$behavior]+$value;
        } else {
            
        }
        
    }
    
    static public function division($param1,$param2) {
        if($param2==''||$param2==0||$param1==''||$param1==0){
            return 0;
        }
        return round(($param1/$param2)*100,2);
    }
}
