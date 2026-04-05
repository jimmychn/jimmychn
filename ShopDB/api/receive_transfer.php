<?php
/**
 * 檔案：api/receive_transfer.php
 * 功能：轉入店點收確認，正式增加轉入店庫存並結案
 */
session_start();
require_once 'db_config.php';
require_once 'stock_functions.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$tr_no = $input['transfer_no'];
$user_id = $_SESSION['user']['id'];
$to_store = $_SESSION['user']['store_id']; // 以點收人的門市為準

try {
    $pdo->beginTransaction();

    // 1. 檢查單據狀態是否為待收貨，且目的地是否正確
    $stmt = $pdo->prepare("SELECT * FROM stock_transfers WHERE transfer_no = ? AND to_store_id = ? AND status = 1");
    $stmt->execute([$tr_no, $to_store]);
    $order = $stmt->fetch();

    if (!$order) throw new Exception("無效的調撥單或目的地門市不符");

    // 2. 抓取明細並增加庫存
    $items = $pdo->prepare("SELECT * FROM stock_transfer_items WHERE transfer_id = ?");
    $items->execute([$order['id']]);
    
    foreach ($items->fetchAll() as $item) {
        // 轉入店：增加 (IN)
        updateStock($pdo, $item['product_id'], $to_store, $item['qty'], 'IN', "調撥入:$tr_no", $user_id);
    }

    // 3. 更新單據狀態為已結案 (status = 2)
    $pdo->prepare("UPDATE stock_transfers SET status = 2 WHERE id = ?")->execute([$order['id']]);

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => '點收完成，庫存已入庫']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
