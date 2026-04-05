<?php
session_start();
require_once 'db_config.php';
header('Content-Type: application/json');

$q = isset($_REQUEST['q']) ? $_REQUEST['q'] : '';
$page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$store_id = $_SESSION['user']['store_id'];

$sql = "SELECT o.*, c.customer_name FROM sales_orders o 
        JOIN customers c ON o.customer_id = c.id 
        WHERE o.store_id = ? AND (o.order_no LIKE ? OR c.customer_name LIKE ?)
        ORDER BY o.id DESC LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute(array($store_id, "%$q%", "%$q%"));
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 取得總頁數... (略)
echo json_encode(array('data' => $data, 'pagination' => array('current_page'=>$page, 'total_pages'=>1, 'total_rows'=>count($data))));
