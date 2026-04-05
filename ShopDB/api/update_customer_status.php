<?php
session_start();
header('Content-Type: application/json');
require_once 'db_config.php';

// 1. 權限檢查：確認是否登入
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(array('status' => 'error', 'message' => '登入逾時，請重新登入'));
    exit;
}

// 2. 取得 JSON POST 資料
$json = file_get_contents('php://input');
$input = json_decode($json, true);

// 修正 PHP 舊版本不支援 ?? 的寫法
$id     = isset($input['id']) ? (int)$input['id'] : 0;
$status = isset($input['status']) ? (int)$input['status'] : 0; // 1:恢復, 0:停用
$store_id = $_SESSION['user']['store_id'];

// 3. 基礎驗證
if ($id <= 0) {
    echo json_encode(array('status' => 'error', 'message' => '無效的客戶編號'));
    exit;
}

try {
    // 4. 執行更新：加上 store_id 條件確保不能越權操作其他門市資料
    $sql = "UPDATE customers SET is_active = ? WHERE id = ? AND store_id = ?";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute(array($status, $id, $store_id));

    if ($result) {
        $msg = ($status === 1) ? '客戶已恢復使用' : '客戶已停用';
        echo json_encode(array('status' => 'success', 'message' => $msg));
    } else {
        echo json_encode(array('status' => 'error', 'message' => '更新失敗，請稍後再試'));
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array('status' => 'error', 'message' => '系統錯誤：' . $e->getMessage()));
}
