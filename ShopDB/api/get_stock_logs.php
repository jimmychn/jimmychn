<?php
session_start();
require_once 'db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    exit;
}

$product_id = isset($_REQUEST['product_id']) ? (int)$_REQUEST['product_id'] : 0;
$store_id   = $_SESSION['user']['store_id'];

if ($product_id <= 0) {
    echo json_encode(array('status' => 'error', 'message' => '無效的產品編號'));
    exit;
}

try {
    // 關聯 users 表取得操作者姓名
    $sql = "SELECT l.*, u.real_name 
        FROM stock_logs l
        LEFT JOIN users u ON l.created_by = u.id
        WHERE l.product_id = ? AND l.store_id = ?
        ORDER BY l.created_at DESC LIMIT 50";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array($product_id, $store_id));
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($logs);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
}
