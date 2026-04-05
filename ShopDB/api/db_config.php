<?php
// 資料庫設定
$host = 'localhost';
$db   = 'shopdb'; // 你的資料庫名稱
$user = 'root';               // 你的資料庫帳號
$pass = '';                   // 你的資料庫密碼
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = array(
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // 開啟錯誤異常
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // 預設抓取關聯陣列
    PDO::ATTR_EMULATE_PREPARES   => false,                  // 關閉模擬預處理，更安全
);

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
     // 如果連線失敗，回傳 JSON 錯誤
     header('Content-Type: application/json');
     http_response_code(500);
     echo json_encode(array('status' => 'error', 'message' => '資料庫連線失敗: ' . $e->getMessage()));
     exit;
}
?>
