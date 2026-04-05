<?php
session_start();
require_once 'db_config.php';
require_once 'stock_functions.php'; // 必須引入之前寫好的 updateStock 函式
header('Content-Type: application/json');

// 1. 權限與登入檢查
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(array('status' => 'error', 'message' => '登入逾時'));
    exit;
}

// 2. 取得前端 JSON
$input = json_decode(file_get_contents('php://input'), true);
$store_id = $_SESSION['user']['store_id'];
$user_id = $_SESSION['user']['id'];

// 產生進貨單號 (PO + 年月日時分秒)
$purchase_no = 'PO' . date('YmdHis');

if (!$input || empty($input['items'])) {
    echo json_encode(array('status' => 'error', 'message' => '單據資料不可為空'));
    exit;
}

try {
    $pdo->beginTransaction();

    // 3. 寫入進貨單頭 (purchase_orders)
    $stmt = $pdo->prepare("INSERT INTO purchase_orders (purchase_no, store_id, supplier_id, purchase_date, total_amount, remark, created_by) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(array(
        $purchase_no,
        $store_id,
        $input['supplier_id'],
        $input['purchase_date'],
        $input['total_amount'],
        $input['remark'],
        $user_id
    ));
    $purchase_id = $pdo->lastInsertId();

    // 4. 處理明細 (purchase_order_items) 並增加庫存
    $stmtItem = $pdo->prepare("INSERT INTO purchase_order_items (purchase_id, product_id, qty, price, subtotal) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($input['items'] as $item) {
        $pid = (int)$item['product_id'];
        $qty = (int)$item['qty'];
        $price = (float)$item['price'];
        $subtotal = $qty * $price;

        $stmtItem->execute(array($purchase_id, $pid, $qty, $price, $subtotal));

        // 核心：增加庫存 (呼叫之前定義的通用函式)
        // 傳入 $qty 為正數，type 為 'IN'
        updateStock($pdo, $pid, $store_id, $qty, 'IN', $purchase_no, $user_id);
    }

    $pdo->commit();
    echo json_encode(array('status' => 'success', 'purchase_no' => $purchase_no));

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(array('status' => 'error', 'message' => '進貨存檔失敗：' . $e->getMessage()));
}
