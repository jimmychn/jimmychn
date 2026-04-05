<?php
/**
 * 檔案：api/get_blacklist.php
 * 功能：列出所有目前在黑名單中的 IP
 */
session_start();
require_once 'db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role_id'] != 1) {
    http_response_code(403); exit;
}

$stmt = $pdo->query("SELECT * FROM ip_blacklist ORDER BY created_at DESC");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
