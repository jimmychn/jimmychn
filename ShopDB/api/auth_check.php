<?php
// auth_check.php
session_start();

function checkPermission($required_permission) {
    // 1. 先檢查是否登入
    if (!isset($_SESSION['user'])) {  //檢查是否登入
        http_response_code(401);
        exit(json_encode(['status' => 'error', 'message' => '請先登入系統']));
    }

    // 2. 檢查權限清單中是否有該功能代碼
    if (!in_array($required_permission, $_SESSION['permissions'])) {  //檢查功能權限代碼是否存在
        http_response_code(403);
        exit(json_encode(['status' => 'error', 'message' => '無該功能執行權限 ('.$required_permission.')']));
    }
}

// 在實際 API (例如 import_excel.php) 使用方式：
// require_once 'auth_check.php';   //引入 auth_check.php
// checkPermission('p_import_excel'); // 帶入功能權限代碼。如果沒權限，這行會直接中斷並噴 403