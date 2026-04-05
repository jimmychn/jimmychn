<?php
/**
 * 檔案：api/save_store.php
 * 註解：處理門市的新增與修改
 */
session_start();
require_once 'db_config.php';
header('Content-Type: application/json');

// 僅限管理員
if (!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 1) {
    http_response_code(403); exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['store_id'];
$name = $input['store_name'];
$head = $input['store_head'];
$tel = $input['tel'];
$addr = $input['address'];

try {
    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE stores SET store_name=?, store_head=?, tel=?, address=? WHERE store_id=?");
        $stmt->execute([$name, $head, $tel, $addr, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO stores (store_id, store_name, store_head, tel, address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$id, $name, $head, $tel, $addr]);
    }
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
