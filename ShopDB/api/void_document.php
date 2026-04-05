<?php
/**
 * 檔案：api/void_document.php
 * 功能：作廢銷貨單或進貨單，並執行反向庫存沖銷
 */
session_start();
require_once 'db_config.php';
require_once 'stock_functions.php';
header('Content-Type: application/json');

// 僅限店長/管理員權限
if ($_SESSION['user']['role_id'] != 1) {
    exit(json_encode(['status' => 'error', 'message' => '權限不足，僅限管理員作廢單據']));
}

$input = json_decode(file_get_contents('php://input'), true);
$type = $input['type']; // 'sales' 或 'purchase'
$doc_no = $input['doc_no'];
$store_id = $_SESSION['user']['store_id'];
$user_id = $_SESSION['user']['id'];

try {
    $pdo->beginTransaction();

    if ($type === 'sales') {
        // --- 銷貨單作廢：庫存要 加回來 ---
        $stmt = $pdo->prepare("SELECT id FROM sales_orders WHERE order_no = ? AND store_id = ? AND status = 1");
        $stmt->execute([$doc_no, $store_id]);
        $order = $stmt->fetch();
        if (!$order) throw new Exception("找不到該有效單據");

        $items = $pdo->prepare("SELECT product_id, qty FROM sales_order_items WHERE order_id = ?");
        $items->execute([$order['id']]);
        foreach ($items->fetchAll() as $item) {
            updateStock($pdo, $item['product_id'], $store_id, $item['qty'], 'ADJ', "作廢:$doc_no", $user_id);
        }
        $pdo->prepare("UPDATE sales_orders SET status = 0 WHERE id = ?")->execute([$order['id']]);

    } else {
        // --- 進貨單作廢：庫存要 扣掉 ---
        $stmt = $pdo->prepare("SELECT id FROM purchase_orders WHERE purchase_no = ? AND store_id = ? AND status = 1");
        $stmt->execute([$doc_no, $store_id]);
        $pur = $stmt->fetch();
        if (!$pur) throw new Exception("找不到該有效單據");

        $items = $pdo->prepare("SELECT product_id, qty FROM purchase_order_items WHERE purchase_id = ?");
        $items->execute([$pur['id']]);
        foreach ($items->fetchAll() as $item) {
            updateStock($pdo, $item['product_id'], $store_id, -$item['qty'], 'ADJ', "作廢:$doc_no", $user_id);
        }
        $pdo->prepare("UPDATE purchase_orders SET status = 0 WHERE id = ?")->execute([$pur['id']]);
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => '單據已作廢並沖銷庫存']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
