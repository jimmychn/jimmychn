<?php
session_start();
require_once 'db_config.php';
header('Content-Type: application/json');

$store_id = $_SESSION['user']['store_id'];

// 抓取最近 7 天每天的銷售額
$sql = "SELECT order_date, SUM(final_amount) as daily_total 
        FROM sales_orders 
        WHERE store_id = ? AND order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY order_date ORDER BY order_date ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$store_id]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);
