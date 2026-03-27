<?php

$baseDir=dirname(__FILE__);
$baseURL=dirname($_SERVER["PHP_SELF"]);
//echo $baseDir.','.$baseURL.'<br/>'; //for TEST

//基本資料
$CompanyName="大陸光學眼鏡鐘錶有限公司";
$CompanyName="大陸光學眼鏡鐘錶有限公司";
$CompanyMail="cont.a00@msa.hinet.net";
$CompanyTalkFree="0800-03-33-22";
$ComplayUrl="https://www.cont.com.tw";
$CompanyHrUrl="https://www.1111.com.tw/corp/1266072/";
$CompanyFbUrl="https://www.facebook.com/cont2014/";
//$CompanyLineUrl="https://lin.ee/mNIeFZg";
//$CompanyIgUrl="https://www.instagram.com/cont.cc/";

//資料連結
$cnDB=['driver' => 'mysql', 'host' => 'localhost', 'port' => 3306, 'dbname'=> 'cont', 'username' => 'root', 'password' => '', 'charset' => 'utf8'];

//圖片相關設定=============================start
$picDir="./upload/"; //要上傳的資料夾
$picMax=1;  //圖片數量
$midMax=3;  //音樂數量
//圖片相關設定=============================end
?>