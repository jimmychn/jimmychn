<?php
/**
 * 檔案：api/manage_blacklist.php
 * 功能：手動移除黑名單 IP
 */
session_start();
require_once 'db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 1) {
    http_response_code(403); exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$ip = $input['ip'] ?? '';
$action = $input['action'] ?? '';

if ($action === 'delete' && !empty($ip)) {
    try {
        $stmt = $pdo->prepare("DELETE FROM ip_blacklist WHERE ip_address = ?");
        $stmt->execute([$ip]);
        
        // 同時清除該 IP 的錯誤嘗試記錄，讓計數重新開始
        $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?")->execute([$ip]);
        
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
