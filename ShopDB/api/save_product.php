<?php
session_start();
require_once 'db_config.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$user_id  = $_SESSION['user']['id'];
$store_id = $_SESSION['user']['store_id'];

$id        = isset($input['id']) ? (int)$input['id'] : 0;
$sku       = isset($input['sku']) ? $input['sku'] : '';
$name      = isset($input['name']) ? $input['name'] : '';
$spec      = isset($input['spec']) ? $input['spec'] : '';
$unit      = isset($input['unit']) ? $input['unit'] : '個';
$purchase  = isset($input['purchase_price']) ? (float)$input['purchase_price'] : 0;
$sales     = isset($input['sales_price']) ? (float)$input['sales_price'] : 0;
$initStock = isset($input['init_stock']) ? (int)$input['init_stock'] : 0;

if (empty($sku) || empty($name)) {
    echo json_encode(array('status' => 'error', 'message' => '品號與品名必填'));
    exit;
}

try {
    $pdo->beginTransaction();

    if ($id > 0) {
        // 修改產品基本資料
        $sql = "UPDATE products SET sku=?, name=?, spec=?, unit=?, purchase_price=?, sales_price=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($sku, $name, $spec, $unit, $purchase, $sales, $id));
    } else {
        // 新增產品
        $sql = "INSERT INTO products (sku, name, spec, unit, purchase_price, sales_price) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($sku, $name, $spec, $unit, $purchase, $sales));
        $newId = $pdo->lastInsertId();

        // 如果有初始庫存，寫入 inventory 並留 log
        if ($initStock != 0) {
            // 寫入庫存
            $invSql = "INSERT INTO inventory (product_id, store_id, stock_qty) VALUES (?, ?, ?)";
            $pdo->prepare($invSql)->execute(array($newId, $store_id, $initStock));

            // 寫入日誌
            $logSql = "INSERT INTO stock_logs (product_id, store_id, type, qty, relation_no, created_by) 
                       VALUES (?, ?, 'ADJ', ?, '初始導入', ?)";
            $pdo->prepare($logSql)->execute(array($newId, $store_id, $initStock, $user_id));
        }
    }

    $pdo->commit();
    echo json_encode(array('status' => 'success'));

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(array('status' => 'error', 'message' => '儲存失敗：' . $e->getMessage()));
}
