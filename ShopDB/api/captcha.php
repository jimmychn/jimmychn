<?php
session_start();

// 1. 設定畫布尺寸（加大以容納大字體與干擾）
$width = 120;
$height = 45;
$img = imagecreatetruecolor($width, $height);

// 2. 顏色設定
$bg_color = imagecolorallocate($img, 240, 240, 240); // 淺灰色背景
imagefill($img, 0, 0, $bg_color);

// 產生隨機驗證碼
$code = (string)rand(1000, 9999);
$_SESSION['captcha'] = $code;

// 3. 加入干擾線條
for ($i = 0; $i < 6; $i++) {
    $line_color = imagecolorallocate($img, rand(100, 200), rand(100, 200), rand(100, 200));
    imageline($img, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $line_color);
}

// 4. 加入雜點干擾
for ($i = 0; $i < 200; $i++) {
    $pixel_color = imagecolorallocate($img, rand(50, 150), rand(50, 150), rand(50, 150));
    imagesetpixel($img, rand(0, $width), rand(0, $height), $pixel_color);
}

// 5. 繪製扭曲字體
// 請確保此路徑下有 ttf 字體檔，或是使用絕對路徑
$font = './arial.ttf'; 
if (!file_exists($font)) {
    // 如果找不到字體，退回原始模式但加大顏色區別
    $fg = imagecolorallocate($img, 0, 0, 0);
    imagestring($img, 5, 15, 15, $code, $fg);
} else {
    for ($i = 0; $i < strlen($code); $i++) {
        $text_color = imagecolorallocate($img, rand(0, 100), rand(0, 100), rand(0, 100));
        $font_size = rand(18, 22);   // 加大字體
        $angle = rand(-15, 15);      // 隨機旋轉角度（扭曲感）
        $x = 10 + ($i * 25);         // 每個字的間距
        $y = rand(30, 35);           // 隨機高度位置
        
        imagettftext($img, $font_size, $angle, $x, $y, $text_color, $font, $code[$i]);
    }
}

// 6. 輸出
header('Cache-Control: no-cache, must-revalidate');
header('Content-Type: image/png');
imagepng($img);
imagedestroy($img);