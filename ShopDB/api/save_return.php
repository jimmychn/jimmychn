<?php
session_start();
require_once 'db_config.php';
require_once 'stock_functions.php'; // 之前寫的 updateStock 函式
header('Content-Type: application/json');

if ($_SESSION['user']['role_id'] != 1) {
    echo json_encode(array('status' => 'error', 'message' => '僅店長具備退貨權限'));
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$store_id = $_SESSION['user']['store_id'];
$user_id = $_SESSION['user']['id'];
$return_no = 'RT' . date('YmdHis');

try {
    $pdo->beginTransaction();

    // 1. 儲存退回單頭
    $stmt = $pdo->prepare("INSERT INTO sales_returns (return_no, origin_order_no, store_id, created_by) VALUES (?, ?, ?, ?)");
    $stmt->execute(array($return_no, $input['origin_order_no'], $store_id, $user_id));
    $return_id = $pdo->lastInsertId();

    // 2. 處理明細與回補庫存
    $stmtItem = $pdo->prepare("INSERT INTO sales_return_items (return_id, product_id, qty, price) VALUES (?, ?, ?, ?)");
    
    foreach ($input['items'] as $item) {
        if ($item['qty'] <= 0) continue;

        $stmtItem->execute(array($return_id, $item['product_id'], $item['qty'], $item['price']));
        
        // 核心：回補庫存 (使用之前寫好的 updateStock 函式)
        updateStock($pdo, $item['product_id'], $store_id, $item['qty'], 'IN', $return_no, $user_id);
    }

    $pdo->commit();
    echo json_encode(array('status' => 'success', 'return_no' => $return_no));
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
}
