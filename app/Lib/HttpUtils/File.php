<?php
namespace App\Lib\HttpUtils;
class File
{
	/**
	 * 获得文件的后缀名
	 * @param string $file  文件名
	 * @return .jpg o r false   
	 */
	public static function get_ext($file) 
	{ 
		return substr(strrchr($file, '.'),0); 
	} 

	/**
	 * 是否是图片文件
	 * @param string $filepath  文件路径
	 * @return 索引或false  
	 */
	public static function isImage($filepath){
	  $types = '.gif.jpeg.png.bmp.jpg';//定义检查的图片类型
	  if(file_exists($filepath)){
	      $info = getimagesize($filepath);
	      $ext = image_type_to_extension($info['2']);  //2图片类型
	      return stripos($types,$ext);
	  }else{
	      return false;
	  }
	}
	
	public static function create_img($type,$path)
	{
	    $img_r=null;
	    switch ($type) {
	            case 1 :
	               $img_r = imageCreateFromGif($path);
	            break;
	            case 2 :
	               $img_r = imageCreateFromJpeg($path);
	            break;
	            case 3 :
	                $img_r= imageCreateFromPng($path);
	            break;
	            case 6 :
	               $img_r= imageCreateFromBmp($path);
	            break;
	        }
	    return $img_r;
	}
	public static function write_img($dst_r,$type,$path)
	{
	    //imagejpeg($dst_r,'./Uploads/Header'.$data['header_img']);
	    switch ($type) {
	              case 1 :
	              imagegif($dst_r,$path);
	             
	            break;
	            case 2 :
	                imagejpeg($dst_r,$path);
	            break;
	            case 3 :
	                imagepng($dst_r,$path);
	            break;
	            case 6 :
	               imagebmp($dst_r, $path);
	            break;
	    }
	}

	/**
	 * 得到安全的文件名，防止../
	 * @param string $filename  文件名
	 * @return 文件名
	 */
	public static function get_safe_filename($filename)
	{ 
		$ext=self::get_ext($filename);
		$filename=str_replace($ext,"",$filename);
		$filename=str_replace(".","",$filename);
		return $filename.$ext;
	}

		/**
	 * 上传图片返回路径   默认为img1
	 * @param string $dir 目录名
	 * @param mb 
	 * @return 返回结果或""
	 */
	public static function upimg($dir,$mb,$input_name='img1')
	{
		$upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize   =    $mb*1024*1024 ;// 设置附件上传大小    
		$upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型    
		$upload->savePath  = './'.$dir.'/'; // 设置附件上传目录
		$upload->saveName = date('His',time()).rand(0,9999999);

		$info=$upload->uploadOne($_FILES[$input_name]);
		if(!$info) {// 上传错误提示错误信息  $this->error($upload->getError());
		 return "";
		 }
		 else{ 
		 $path=str_replace("./$dir", "", $info['savepath']);
    	 return $path.$info['savename'];
		 	//require(LIB_PATH.'Vendor\autoload.php');
		    //$imagine = new \Imagine\Gd\Imagine();
		    //$pattern = "/\.(jpg|gif|png|jpeg)$/";
		    //$replacement = '.jpg';
		    //$fileName=str_replace("\\","",preg_replace($pattern, $replacement,$info['savename']));

			//$image = $imagine->open("./Uploads/$dir/".$path.$info['savename'])->save("./Uploads/$dir/".date("Y-m-d")."/$fileName", array('jpeg_quality' => 50));
		//return $path.$fileName;
		}
	}


	public static function picupimg($dir,$mb,$input_name='img1')
	{
		$upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize   =    $mb*1024*1024 ;// 设置附件上传大小
		$upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		$upload->savePath  = './'.$dir.'/'; // 设置附件上传目录
		$name = explode('.', $_FILES[$input_name]['name']);
		$upload->saveName =$name[0];

		$info=$upload->uploadOne($_FILES[$input_name]);
		if(!$info) {// 上传错误提示错误信息  $this->error($upload->getError());
			return "";
		}else{
			$path=str_replace("./$dir", "", $info['savepath']);
			return $path.$info['savename'];
			//require(LIB_PATH.'Vendor\autoload.php');
			//$imagine = new \Imagine\Gd\Imagine();
			//$pattern = "/\.(jpg|gif|png|jpeg)$/";
			//$replacement = '.jpg';
			//$fileName=str_replace("\\","",preg_replace($pattern, $replacement,$info['savename']));

			//$image = $imagine->open("./Uploads/$dir/".$path.$info['savename'])->save("./Uploads/$dir/".date("Y-m-d")."/$fileName", array('jpeg_quality' => 50));
			//return $path.$fileName;
		}
	}


	public static function deleteDir($path) {
    $op = dir($path);
    while(false != ($item = $op->read())) {
        if($item == '.' || $item == '..') {
            continue;
        }
        if(is_dir($op->path.'/'.$item)) {
            self::deleteDir($op->path.'/'.$item);
            rmdir($op->path.'/'.$item);
        } else {
            unlink($op->path.'/'.$item);
        }
    }   
  }
}
