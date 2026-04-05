<?php
/**
 * 檔案：api/get_roles_permissions.php
 * 註解：抓取所有角色及其擁有的權限，供權限設定頁面 (Checkbox 牆) 使用
 */
session_start();
require_once 'db_config.php';

// 抓取所有角色
$roles = $pdo->query("SELECT * FROM roles")->fetchAll();

// 抓取所有權限定義
$all_perms = $pdo->query("SELECT * FROM permissions")->fetchAll();

// 抓取現有的關聯 (role_permissions)
$mapping = $pdo->query("SELECT * FROM role_permissions")->fetchAll();

echo json_encode([
    'roles' => $roles,
    'all_permissions' => $all_perms,
    'current_mapping' => $mapping
]);
