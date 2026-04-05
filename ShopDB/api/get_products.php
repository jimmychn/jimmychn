<?php
session_start();
require_once 'db_config.php';
header('Content-Type: application/json');

// 1. 取得參數
$q = isset($_REQUEST['q']) ? $_REQUEST['q'] : '';
$page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;
$is_active = isset($_REQUEST['is_active']) ? (int)$_REQUEST['is_active'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$store_id = $_SESSION['user']['store_id'];

// 2. 構建條件：Join inventory 表取得該店庫存，若無資料則為 0
$where = "WHERE p.is_active = ? AND (p.name LIKE ? OR p.product_id LIKE ?)";
$params = array($is_active, "%$q%", "%$q%");

// 3. 計算總數
$countSql = "SELECT COUNT(*) FROM products p $where";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRows = $countStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// 4. 抓取資料 (LEFT JOIN 確保沒庫存紀錄的產品也能顯示)
$sql = "SELECT p.*, IFNULL(i.stock_qty, 0) as stock_qty 
        FROM products p 
        LEFT JOIN inventory i ON p.product_id = i.product_id AND i.store_id = ? 
        $where 
        ORDER BY p.product_id DESC 
        LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
// 注意：參數順序必須與 SQL 中的問號順序一致
$stmt->execute(array_merge(array($store_id), $params));
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(array(
    'data' => $products,
    'pagination' => array(
        'current_page' => $page,
        'total_pages' => $totalPages
    )
));
