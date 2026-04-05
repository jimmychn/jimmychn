<?php
session_start();
require_once 'db_config.php';
require_once 'stock_functions.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$store_id = $_SESSION['user']['store_id'];
$user_id = $_SESSION['user']['id'];
$return_no = 'PR' . date('YmdHis');

try {
    $pdo->beginTransaction();

    // 1. 寫入退回單頭
    $stmt = $pdo->prepare("INSERT INTO purchase_returns (return_no, origin_purchase_no, store_id, supplier_id, return_date, total_amount, remark, created_by) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(array(
        $return_no, $input['origin_purchase_no'], $store_id, $input['supplier_id'], 
        $input['return_date'], $input['total_amount'], $input['remark'], $user_id
    ));
    $return_id = $pdo->lastInsertId();

    // 2. 處理明細與扣除庫存
    $stmtItem = $pdo->prepare("INSERT INTO purchase_return_items (return_id, product_id, qty, price, subtotal) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($input['items'] as $item) {
        $qty = (int)$item['qty'];
        $stmtItem->execute(array(
            $return_id, $item['product_id'], $qty, $item['price'], $qty * $item['price']
        ));

        // 核心：扣除庫存 (qty 傳入負數，標記為 OUT)
        updateStock($pdo, $item['product_id'], $store_id, -$qty, 'OUT', $return_no, $user_id);
    }

    $pdo->commit();
    echo json_encode(array('status' => 'success', 'return_no' => $return_no));
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
}
