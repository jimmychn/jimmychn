<?php
session_start();
require_once 'db_config.php';
header('Content-Type: application/json');

$q = isset($_REQUEST['q']) ? $_REQUEST['q'] : '';
$page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;
$show_inactive = isset($_REQUEST['show_inactive']) && $_REQUEST['show_inactive'] === 'true';
$limit = 10; // 每頁顯示 10 筆
$offset = ($page - 1) * $limit;

if (!isset($_SESSION['user'])) $_SESSION['user']['store_id']='STORE01'; else 
$store_id = $_SESSION['user']['store_id'];


// 1. 構建條件
$where = "WHERE store_id = ? AND (customer_name LIKE ? OR tel LIKE ?)";
if (!$show_inactive) {
    $where .= " AND is_active = 1";
}

// 2. 計算總筆數 (為了分頁計算)
$count_sql = "SELECT COUNT(*) FROM customers $where";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute(array($store_id, "%$q%", "%$q%"));
$total_rows = $count_stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

// 3. 抓取分頁資料
$data_sql = "SELECT * FROM customers $where ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($data_sql);
$stmt->execute(array($store_id, "%$q%", "%$q%"));
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(array(
    'data' => $customers,
    'pagination' => array(
        'current_page' => $page,
        'total_pages' => $total_pages,
        'total_rows' => (int)$total_rows
    )
));
