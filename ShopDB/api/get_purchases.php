<?php
session_start();
require_once 'db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    exit;
}

$q = isset($_REQUEST['q']) ? $_REQUEST['q'] : '';
$page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$store_id = $_SESSION['user']['store_id'];

try {
    // 1. 條件過濾
    $where = "WHERE p.store_id = ? AND (p.purchase_no LIKE ? OR s.supplier_name LIKE ?)";
    $params = array($store_id, "%$q%", "%$q%");

    // 2. 計算總筆數
    $countSql = "SELECT COUNT(*) FROM purchase_orders p JOIN suppliers s ON p.supplier_id = s.id $where";
    $stmtCount = $pdo->prepare($countSql);
    $stmtCount->execute($params);
    $totalRows = $stmtCount->fetchColumn();
    $totalPages = ceil($totalRows / $limit);

    // 3. 抓取資料
    $sql = "SELECT p.*, s.supplier_name 
            FROM purchase_orders p 
            JOIN suppliers s ON p.supplier_id = s.id 
            $where 
            ORDER BY p.id DESC LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(array(
        'status' => 'success',
        'data' => $data,
        'pagination' => array(
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_rows' => (int)$totalRows
        )
    ));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
}
