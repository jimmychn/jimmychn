<?php
/**
 * 檔案：api/get_system_data.php
 * 功能：獲取門市、使用者、角色等基礎數據
 */
session_start();
require_once 'db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 1) {
    http_response_code(403); exit;
}

$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';

switch($type) {
    case 'users':
        $sql = "SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.id";
        break;
    case 'stores':
        $sql = "SELECT * FROM stores";
        break;
    case 'roles':
        $sql = "SELECT * FROM roles";
        break;
    default:
        exit(json_encode([]));
}

$stmt = $pdo->query($sql);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
