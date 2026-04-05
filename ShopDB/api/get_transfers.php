<?php
/**
 * 檔案：api/get_transfers.php
 * 功能：取得調撥單清單，支援總部全覽與分店過濾
 */
session_start();
require_once 'db_config.php';
header('Content-Type: application/json');

$user_store = $_SESSION['user']['store_id'];
$is_admin = ($user_store === 'STORE01');
$page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// 權限邏輯：總部看全部，分店只能看到「轉出」或「轉入」包含自己的單據
$where = $is_admin ? "WHERE 1=1" : "WHERE from_store_id = '$user_store' OR to_store_id = '$user_store'";

try {
    $sql = "SELECT t.*, s1.store_name as from_name, s2.store_name as to_name 
            FROM stock_transfers t
            JOIN stores s1 ON t.from_store_id = s1.id
            JOIN stores s2 ON t.to_store_id = s2.id
            $where ORDER BY t.id DESC LIMIT $limit OFFSET $offset";
            
    $stmt = $pdo->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $data]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
