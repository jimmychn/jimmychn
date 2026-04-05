<?php
session_start();
header('Content-Type: application/json');
require_once 'db_config.php';

// 權限檢查 (範例：需有 orders_add 權限)
if (!isset($_SESSION['user']) || !in_array('orders_add', $_SESSION['user']['perms'])) {
    http_response_code(403);
    echo json_encode(array('status' => 'error', 'message' => '權限不足'));
    exit;
}

// 取得前端 JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(array('status' => 'error', 'message' => '資料格式錯誤'));
    exit;
}

try {
    // 【開始資料庫交易】
    $pdo->beginTransaction();

    // 1. 寫入單頭 (sales_orders)
    $stmt = $pdo->prepare("INSERT INTO sales_orders (order_no, store_id, customer_id, order_date, total_amount, remark, created_by) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    // 這裡單號建議由後端生成，確保唯一
    $order_no = 'SO' . date('YmdHis'); 
    
    $stmt->execute(array(
        $order_no,
        $_SESSION['user']['store_id'],
        $input['customer_id'],
        $input['order_date'],
        $input['total_amount'],
        $input['remark'],
        $_SESSION['user']['id']
    ));

    $order_id = $pdo->lastInsertId(); // 取得剛產生的單頭 ID

    // 2. 寫入單身 (sales_order_items) 並扣除庫存
    $stmtItem = $pdo->prepare("INSERT INTO sales_order_items (order_id, product_id, qty, price, subtotal) VALUES (?, ?, ?, ?, ?)");
    $stmtStock = $pdo->prepare("UPDATE products SET stock_qty = stock_qty - ? WHERE id = ?");

    foreach ($input['items'] as $item) {
        // 寫入明細
        $stmtItem->execute(array(
            $order_id,
            $item['product_id'],
            $item['qty'],
            $item['price'],
            $item['qty'] * $item['price']
        ));

        // 扣除庫存 (注意：實務上應檢查庫存是否足夠)
        $stmtStock->execute(array($item['qty'], $item['product_id']));
    }

    // 【提交交易】
    $pdo->commit();

    echo json_encode(array('status' => 'success', 'message' => '單據儲存成功', 'order_no' => $order_no));

} catch (Exception $e) {
    // 【失敗則復原】
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(array('status' => 'error', 'message' => '儲存失敗：' . $e->getMessage()));
}
?>
