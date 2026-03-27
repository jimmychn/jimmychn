<?php
/**
 * Date: 2008/02/28
 * Shen(http://blog.ring.idv.tw)
 */
class ImageResize
{
	private $src_image;
	private $src_width;
	private $src_height;
	private $dest_image;
	private $dest_width;
	private $dest_height;

	function __construct(){}
	public function readImage($imgpath)
	{
		$this->src_image = imagecreatefromjpeg($imgpath);
		$size = getimagesize($imgpath);	
		$this->src_width = $size[0];
		$this->src_height = $size[1];
	}
	public function thumbnailImage($width,$height)
	{
		if(($this->src_width > $this->src_height) && ($width > $height))
		{
			$this->dest_width = $width;
			$this->dest_height = ($this->src_height/$this->src_width)*$width;
		}else{
			$this->dest_height = $height;
			$this->dest_width = ($this->src_width/$this->src_height)*$height;
		}

		$this->dest_image = imagecreatetruecolor($this->dest_width,$this->dest_height);
		imagecopyresampled($this->dest_image,$this->src_image,0,0,0,0,$this->dest_width,$this->dest_height,$this->src_width,$this->src_height);
	}
	public function writeImage($imgpath)
	{
		imagejpeg($this->dest_image,$imgpath,100);
	}
	public function destory()
	{
		imagedestroy($this->src_image);
		imagedestroy($this->dest_image);
	}
}


$from_filename=@$_GET['path'];
$in_width=@$_GET['w'];
$in_height=@$_GET['h'];
$img_info = getimagesize($from_filename);

$ir = new ImageResize();
$ir->readImage($_GET['path']);

$small_image="../cache/".$in_width."-".$path_parts['basename'];

$ir->thumbnailImage($_GET['w'],$_GET['h']);

$ir->writeImage($_FILES['uploadfile']['name']);
$ir->destory();


?>