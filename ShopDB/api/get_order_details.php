<?php
session_start();
require_once 'db_config.php';
header('Content-Type: application/json');

$order_no = isset($_REQUEST['order_no']) ? $_REQUEST['order_no'] : '';
$store_id = $_SESSION['user']['store_id'];

if (empty($order_no)) {
    echo json_encode(array('status' => 'error', 'message' => '請輸入單號'));
    exit;
}

try {
    // 查詢單頭與關聯的單身明細
    $sql = "SELECT i.*, p.name as product_name, p.sku, o.customer_id, c.customer_name 
            FROM sales_order_items i
            JOIN sales_orders o ON i.order_id = o.id
            JOIN products p ON i.product_id = p.id
            JOIN customers c ON o.customer_id = c.id
            WHERE o.order_no = ? AND o.store_id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array($order_no, $store_id));
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$items) {
        echo json_encode(array('status' => 'error', 'message' => '找不到該單號或不屬於本門市'));
    } else {
        echo json_encode(array('status' => 'success', 'data' => $items));
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
}
