<?php
/**
 * 檔案：api/save_transfer.php
 * 功能：執行門市間庫存調撥，同時扣除轉出店、增加轉入店庫存
 */
session_start();
require_once 'db_config.php';
require_once 'stock_functions.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) { http_response_code(401); exit; }

$input = json_decode(file_get_contents('php://input'), true);
$from_store = $input['from_store_id'];
$to_store   = $input['to_store_id'];
$user_id    = $_SESSION['user']['id'];
$tr_no      = 'TR' . date('YmdHis');

if ($from_store === $to_store) {
    exit(json_encode(['status' => 'error', 'message' => '轉出與轉入門市不可相同']));
}

try {
    $pdo->beginTransaction();

    // 1. 寫入單頭
    $stmt = $pdo->prepare("INSERT INTO stock_transfers (transfer_no, from_store_id, to_store_id, transfer_date, remark, created_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$tr_no, $from_store, $to_store, $input['transfer_date'], $input['remark'], $user_id]);
    $tr_id = $pdo->lastInsertId();

    // 2. 處理明細與雙向庫存更新
    foreach ($input['items'] as $item) {
        $pid = (int)$item['product_id'];
        $qty = (int)$item['qty'];

        $pdo->prepare("INSERT INTO stock_transfer_items (transfer_id, product_id, qty) VALUES (?, ?, ?)")
            ->execute([$tr_id, $pid, $qty]);

        // 轉出店：扣除 (OUT)
        updateStock($pdo, $pid, $from_store, -$qty, 'ADJ', "調撥出:$tr_no", $user_id);
        
        // 取消改成兩段式 轉入店：增加 (IN)
        //updateStock($pdo, $pid, $to_store, $qty, 'ADJ', "調撥入:$tr_no", $user_id);
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'transfer_no' => $tr_no]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
