<?php
/**
 * 檔案：api/get_customer_statement.php
 * 功能：取得特定客戶在指定期間內的銷貨與退貨明細
 * 應用：客戶對帳報表，用於結算應收帳款
 */
session_start();
require_once 'db_config.php';
header('Content-Type: application/json');

// 權限驗證
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    exit;
}

// 接收前端參數
$customer_id = isset($_REQUEST['customer_id']) ? (int)$_REQUEST['customer_id'] : 0;
$date_start  = isset($_REQUEST['date_start']) ? $_REQUEST['date_start'] : date('Y-m-01');
$date_end    = isset($_REQUEST['date_end']) ? $_REQUEST['date_end'] : date('Y-m-d');
$store_id    = $_SESSION['user']['store_id'];

if ($customer_id <= 0) {
    echo json_encode(array('status' => 'error', 'message' => '請選擇客戶'));
    exit;
}

$user_store = $_SESSION['user']['store_id'];
$is_admin_store = ($user_store === 'STORE01'); // 假設 STORE01 為總部

// 取得前端傳來的 store_filter (總部人員切換門市用，若不傳則看全分店)
$filter_store = isset($_REQUEST['store_filter']) ? $_REQUEST['store_filter'] : '';

// --- 核心權限邏輯 ---
if ($is_admin_store) {
    // 總部人員：如果有指定分店就過濾，沒指定就看全部
    $store_query = !empty($filter_store) ? " AND store_id = '$filter_store'" : "";
} else {
    // 分店人員：強制鎖定只能看自己的店
    $store_query = " AND store_id = '$user_store'";
}

try {
    /**
     * 核心邏輯：
     * 1. 從 sales_orders 抓取「銷貨」金額 (final_amount 為扣除折扣後的實付金額)
     * 2. 從 sales_returns 抓取「退回」金額 (以負數呈現)
	 * 3. 總部/分店人員條件改變
     */
	$sql = "
		SELECT '銷貨' as type, order_no as doc_no, order_date as doc_date, final_amount as amount, remark 
		FROM sales_orders 
		WHERE customer_id = ? $store_query AND status = 1 AND order_date BETWEEN ? AND ?
		
		UNION ALL
		
		SELECT '退回' as type, return_no as doc_no, return_date as doc_date, (total_return_amount * -1) as amount, reason as remark 
		FROM sales_returns 
		WHERE origin_order_no IN (
			SELECT order_no FROM sales_orders WHERE customer_id = ? $store_query
		) AND status = 1 AND return_date BETWEEN ? AND ?
		
		ORDER BY doc_date ASC, doc_no ASC
	";
	
    $stmt = $pdo->prepare($sql);
    // 綁定參數
    $params = array(
        $customer_id, $date_start, $date_end, // 銷貨單參數
        $customer_id, $date_start, $date_end  // 退回單參數
    );
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 計算應收總計
    $balance = 0;
    foreach ($rows as $r) {
        $balance += $r['amount'];
    }

    echo json_encode(array(
        'status' => 'success',
        'customer_id' => $customer_id,
        'balance' => $balance,
        'details' => $rows
    ));

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
}
