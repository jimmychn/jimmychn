<?php
/**
 * 檔案：api/save_user.php
 * 功能：使用者帳號維護 (CRUD)
 */
session_start();
require_once 'db_config.php';
header('Content-Type: application/json');

// 僅限管理員 (role_id = 1) 操作
if (!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 1) {
    http_response_code(403);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = isset($input['id']) ? (int)$input['id'] : 0;
$user = $input['username'];
$real_name = $input['real_name'];
$role_id = $input['role_id'];
$store_id = $input['store_id'];
$new_pass = $input['password']; // 新增或重設時使用的密碼

try {
    if ($id > 0) {
        // 修改資料
        if (!empty($new_pass)) {
            // 如果有輸入新密碼，則更新密碼
            $hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET real_name=?, role_id=?, store_id=?, password=? WHERE id=?";
            $params = [$real_name, $role_id, $store_id, $hash, $id];
        } else {
            // 不更新密碼
            $sql = "UPDATE users SET real_name=?, role_id=?, store_id=? WHERE id=?";
            $params = [$real_name, $role_id, $store_id, $id];
        }
    } else {
        // 新增帳號
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password, real_name, role_id, store_id) VALUES (?, ?, ?, ?, ?)";
        $params = [$user, $hash, $real_name, $role_id, $store_id];
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
