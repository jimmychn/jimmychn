<?php
//以寬度為主的版本，高度會自動裁切====================

//ImageResize("images/photo/bdb943cb1817d472f3f6002bf79bac70.jpg",200,200);

// 本函数从源文件取出图像，设定成指定大小，并输出到目的文件 

// 源文件格式：gif，jpg，png 

// 目的文件格式：gif 

// $srcFile：源文件 

// $dstFile: 目标文件 

// $dstW：目标图片宽度 

// $dstH：目标文件高度 

$from_filename=@$_GET['path'];

$in_width=@$_GET['w'];

$in_height=@$_GET['h'];

//取得縮圖檔名
$path_parts = pathinfo($from_filename);
//$small_image="$baseDir/cache/".$in_width."-".$path_parts['basename'];
$small_image="../cache/".$in_width."-".$path_parts['basename'];

//echo $small_image;

$lv=100;
if (@$_GET['lv']!=""){
	$lv=@$_GET['lv'];
}
    $allow_format = array('jpeg', 'png', 'gif','jpg');

    $sub_name = $t = '';



    // Get new dimensions

    $img_info = getimagesize($from_filename);

    $width    = $img_info['0'];

    $height   = $img_info['1'];

    $imgtype  = $img_info['2'];

    $imgtag   = $img_info['3'];

    $bits     = $img_info['bits'];

    $channels = $img_info['channels'];

    $mime     = $img_info['mime'];

	//$aa=filesize($from_filename);

	//die("$aa");

    list($t, $sub_name) = explode('/', $mime);

    if ($sub_name == 'jpg') {

        $sub_name = 'jpeg';

    }

	header("Content-type: image/".strtolower($sub_name));



    if (!in_array($sub_name, $allow_format)) {

        return false;

    }

if (!file_exists($small_image)) {
	//縮圖檔名如果不存在，則重新產生檔案
    // 取得縮在此範圍內的比例

    $percent = getResizePercent($width, $height, $in_width, $in_height);
	$new_width  = $width * $percent;
    $new_height = $height * $percent;
	
	if ($in_width>$in_height){
		$new_height=$in_height;
		$new_width=round($width*($in_height/$height));
	}
	if ($in_height>$in_width){
		$new_width=$in_width;
		$new_height=round($height*($in_width/$width));
	}
	
	if ($new_width<$in_width){
		$new_width=$in_width;
		$new_height=round($height*($in_width/$width));
	}
	if ($new_height<$in_height){
		$new_height=$in_height;
		$new_width=round($width*($in_height/$height));
	}
	
	if ($new_width>$width || $new_height>$height){
		$new_width=$width;
		$new_height=$height;
	}
	

	//圖片垂直水平置中
	$myX=($in_width-$new_width)/2;
	$myY=($in_height-$new_height)/2;

    // 補背景色
    $image_new = imagecreatetruecolor($in_width, $in_height);
	$color = imagecolorallocate( $image_new, 255, 255, 255);
	if ($imgtype==3){ //png設定透明背景
		$color = imagecolortransparent($image_new,$color);
	}
	imagefill( $image_new, 0, 0, $color);
	//========================================
	switch ($imgtype) {
		case 1: $image = imagecreatefromgif($from_filename); 
			imagecopyresampled($image_new, $image, $myX, $myY, 0, 0, $new_width, $new_height, $width, $height);
			imagegif($image_new,$small_image);
			break;
		case 2: $image = imagecreatefromjpeg($from_filename);
	    	imagecopyresampled($image_new, $image, $myX, $myY, 0, 0, $new_width, $new_height, $width, $height);
	        imagejpeg($image_new,$small_image,$lv);
			break;
		case 3: $image = imagecreatefrompng($from_filename);
			imagecopyresampled($image_new, $image, $myX, $myY, 0, 0, $new_width, $new_height, $width, $height);
			imagepng($image_new,$small_image);
		default: return false; break;
	}
}
//========================================
if (file_exists($small_image)) {
	switch ($imgtype) {
		case 1: $image_s = imagecreatefromgif($small_image); 
			imagegif($image_s);
			break;
		case 2: $image_s = imagecreatefromjpeg($small_image);
	        imagejpeg($image_s,NULL,$lv);
			break;
		case 3: $image_s = imagecreatefrompng($small_image);
			imagepng($image_s);
		default: return false; break;
	}
}
//========================================
/**

 * 抓取要縮圖的比例

 * $source_w : 來源圖片寬度

 * $source_h : 來源圖片高度

 * $inside_w : 縮圖預定寬度

 * $inside_h : 縮圖預定高度

 *

 * Test:

 *   $v = (getResizePercent(1024, 768, 400, 300));

 *   echo 1024 * $v . "\n";

 *   echo  768 * $v . "\n";

 */

function getResizePercent($source_w, $source_h, $inside_w, $inside_h)

{

    if ($source_w < $inside_w && $source_h < $inside_h) {

        return 1; // Percent = 1, 如果都比預計縮圖的小就不用縮

    }



    $w_percent = $inside_w / $source_w;

    $h_percent = $inside_h / $source_h;



    return ($w_percent > $h_percent) ? $h_percent : $w_percent;

}


?>

