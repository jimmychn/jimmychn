<?php
header('Content-Type: application/json; charset=utf-8');

// 這裡之後可以連接資料庫
// $conn = new mysqli("localhost", "user", "password", "database");

// 目前先回傳模擬數據，讓前端頁面有東西可以顯示
$stats = [
        "today_sales" => 12000,
        "today_purchase" => 8000,
        "low_stock" => 5,
        "pending_orders" => 12,
];

echo json_encode($stats);
?>