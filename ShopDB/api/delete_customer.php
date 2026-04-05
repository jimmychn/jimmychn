<?php
session_start();
require_once 'db_config.php';
header('Content-Type: application/json');

// 權限檢查 (建議只有管理員或特定權限可刪除)
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = isset($input['id']) ? $input['id'] : '';
$store_id = $_SESSION['user']['store_id'];

if (empty($id)) {
    echo json_encode(array('status' => 'error', 'message' => '缺少 ID'));
    exit;
}

try {
    // 執行軟刪除：將 is_active 設為 0
    $sql = "UPDATE customers SET is_active = 0 WHERE id = ? AND store_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array($id, $store_id));

    echo json_encode(array('status' => 'success', 'message' => '客戶已停用'));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
}
