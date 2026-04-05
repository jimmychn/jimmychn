<?php
/**
 * 檔案：api/save_adjustment.php
 * 註解：處理手動盤點存檔，連動更新庫存表與異動日誌
 */
session_start();
require_once 'db_config.php';
require_once 'stock_functions.php'; // 引入 updateStock 函式
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$store_id = $_SESSION['user']['store_id'];
$user_id = $_SESSION['user']['id'];
$adj_no = 'ADJ' . date('YmdHis');

try {
    $pdo->beginTransaction();

    // 1. 寫入調整單頭
    $stmt = $pdo->prepare("INSERT INTO stock_adjustments (adj_no, store_id, adj_date, reason, created_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(array($adj_no, $store_id, $input['adj_date'], $input['reason'], $user_id));
    $adj_id = $pdo->lastInsertId();

    // 2. 處理明細與庫存更新
    foreach ($input['items'] as $item) {
        $pid = (int)$item['product_id'];
        $adj_qty = (int)$item['adj_qty']; // 差異數，例如帳面10實體8，則此值為 -2

        // 寫入調整明細表
        $stmtItem = $pdo->prepare("INSERT INTO stock_adjustment_items (adj_id, product_id, book_qty, actual_qty, adj_qty, item_remark) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtItem->execute(array($adj_id, $pid, $item['book_qty'], $item['actual_qty'], $adj_qty, $item['remark']));

        // 核心：更新實時庫存並產生 Log
        // 使用 type = 'ADJ' 標記為手動調整
        updateStock($pdo, $pid, $store_id, $adj_qty, 'ADJ', $adj_no, $user_id);
    }

    $pdo->commit();
    echo json_encode(array('status' => 'success', 'adj_no' => $adj_no));

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
}
