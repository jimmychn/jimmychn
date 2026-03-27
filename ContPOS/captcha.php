<?php
session_start();

// 產生隨機字串 (4~6碼)
$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$captcha = substr(str_shuffle($chars), 0, 5);

// 存入 Session
$_SESSION['captcha'] = $captcha;

// 建立圖片
$width = 150;
$height = 50;
$image = imagecreate($width, $height);

// 背景顏色
$bgColor = imagecolorallocate($image, 255, 255, 255);

// 干擾線顏色
$lineColor = imagecolorallocate($image, 200, 200, 200);

// 畫干擾線
for ($i = 0; $i < 6; $i++) {
    imageline($image, rand(0,$width), rand(0,$height), rand(0,$width), rand(0,$height), $lineColor);
}

// 使用 TTF 字型
$font = __DIR__ . '/fonts/arial.ttf'; // 請放一個 TTF 字型檔在 fonts 資料夾

for ($i = 0; $i < strlen($captcha); $i++) {
    // 隨機顏色
    $textColor = imagecolorallocate($image, rand(0,150), rand(0,150), rand(0,150));
    // 隨機角度
    $angle = rand(-20, 20);
    // 字元位置
    $x = 20 + ($i * 25);
    $y = rand(30, 45);
    // 畫字
    imagettftext($image, 20, $angle, $x, $y, $textColor, $font, $captcha[$i]);
}

// 輸出圖片
header("Content-type: image/png");
imagepng($image);
imagedestroy($image);
?>
