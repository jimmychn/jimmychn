<?php
session_start();
require_once 'db_config.php';
header('Content-Type: application/json');

$q = isset($_REQUEST['q']) ? $_REQUEST['q'] : '';
$page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;
$show_inactive = isset($_REQUEST['show_inactive']) && $_REQUEST['show_inactive'] === 'true';
$limit = 10;
$offset = ($page - 1) * $limit;
$store_id = $_SESSION['user']['store_id'];

$where = "WHERE store_id = ? AND (supplier_name LIKE ? OR tel LIKE ?)";
if (!$show_inactive) $where .= " AND is_active = 1";

$sql = "SELECT * FROM suppliers $where ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute(array($store_id, "%$q%", "%$q%"));
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(array('data' => $data, 'pagination' => array('current_page' => $page, 'total_pages' => 1)));
