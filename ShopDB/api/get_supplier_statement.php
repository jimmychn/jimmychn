<?php
/**
 * 檔案：api/get_supplier_statement.php
 * 功能：取得供應商對帳明細與總計
 * 應用：報表模組，用於查詢特定期間的採購金額與退貨抵扣
 */
session_start();
require_once 'db_config.php';
header('Content-Type: application/json');

// 權限與登入驗證
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    exit;
}

// 取得過濾參數
$supplier_id = isset($_REQUEST['supplier_id']) ? (int)$_REQUEST['supplier_id'] : 0;
$date_start  = isset($_REQUEST['date_start']) ? $_REQUEST['date_start'] : date('Y-m-01'); // 預設本月1號
$date_end    = isset($_REQUEST['date_end']) ? $_REQUEST['date_end'] : date('Y-m-d');    // 預設今天
$store_id    = $_SESSION['user']['store_id'];

if ($supplier_id <= 0) {
    echo json_encode(array('status' => 'error', 'message' => '請選擇供應商'));
    exit;
}

try {
    /**
     * 邏輯說明：
     * 我們使用 UNION ALL 將「進貨單 (PO)」與「進貨退回單 (PR)」合併查詢。
     * 進貨金額為正數，退回金額顯示為負數，以便加總計算應付款。
     */
    $sql = "
        SELECT '進貨' as type, purchase_no as doc_no, purchase_date as doc_date, total_amount as amount, remark 
        FROM purchase_orders 
        WHERE supplier_id = ? AND store_id = ? AND status = 1 AND purchase_date BETWEEN ? AND ?
        
        UNION ALL
        
        SELECT '退回' as type, return_no as doc_no, return_date as doc_date, (total_amount * -1) as amount, remark 
        FROM purchase_returns 
        WHERE supplier_id = ? AND store_id = ? AND status = 1 AND return_date BETWEEN ? AND ?
        
        ORDER BY doc_date ASC, doc_no ASC
    ";

    $stmt = $pdo->prepare($sql);
    // 傳入兩次參數分別給 UNION 的兩個查詢部分
    $params = array($supplier_id, $store_id, $date_start, $date_end, $supplier_id, $store_id, $date_start, $date_end);
    $stmt->execute($params);
    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 計算本期總計
    $total_sum = 0;
    foreach ($details as $row) {
        $total_sum += $row['amount'];
    }

    echo json_encode(array(
        'status' => 'success',
        'supplier_id' => $supplier_id,
        'date_range' => $date_start . ' ~ ' . $date_end,
        'total_sum' => $total_sum,
        'details' => $details
    ));

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
}
