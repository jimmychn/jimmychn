<?php
require 'db.php'; // 你的 PDO 初始化

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if ($action === 'list') {
    $limit  = isset($_REQUEST['limit']) ? intval($_REQUEST['limit']) : 10;
    $offset = isset($_REQUEST['offset']) ? intval($_REQUEST['offset']) : 0;
	$search = '';
    //$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
	if (isset($_REQUEST['search'])) {
		$search = is_array($_REQUEST['search']) 
			? json_encode($_REQUEST['search'], JSON_UNESCAPED_UNICODE) 
			: (string)$_REQUEST['search'];
	}
	//偵錯用
	if ($GLOBALS['debug']>0) writeSystemLog($pdo,$username,$storeID,'SystemLogsAPI','ListLogs','debug','','REQUEST['.$offset.','.$limit.','.$search.']');
	
	$params = array();

    // 基本查詢
    $baseSql = "FROM SystemLogs WHERE 1=1";

    if ($search !== '') {
        $baseSql .= " AND (
            Username LIKE ? OR
            StoreID LIKE ? OR
            ModuleName LIKE ? OR
            Activity LIKE ? OR
            Status LIKE ? OR
            IPAddress LIKE ? OR
            ErrorMessage LIKE ?
        )";
        $like = "%".$search."%";
        $params = array($like, $like, $like, $like, $like, $like, $like);
    }

    // 計算總筆數
    $countSql = "SELECT COUNT(*) AS total ".$baseSql;
    $countStmt = dbExecute($pdo, $countSql, $params, 'SystemLogsAPI', 'CountLogs', $username, $storeID);
    $total = $countStmt->fetchColumn();

    // MSSQL 分頁：ROW_NUMBER()
    $sql = "
        WITH Logs AS (
            SELECT 
                LogID, Username, StoreID, ModuleName, Activity, Status, IPAddress, ErrorMessage, CreatedAt,
                ROW_NUMBER() OVER (ORDER BY CreatedAt DESC) AS RowNum
            ".$baseSql."
        )
        SELECT * FROM Logs
        WHERE RowNum BETWEEN ? AND ?
        ORDER BY RowNum
    ";

    $start = $offset + 1;
    $end   = $offset + $limit;
	$extraBindings = [
		//[8, (int)$start, PDO::PARAM_INT], // 開始位置參數
		//[9, (int)$end,   PDO::PARAM_INT]  // 結束位置參數
		[count($params) + 1, (int)$start, PDO::PARAM_INT], // 開始位置參數
		[count($params) + 2, (int)$end,   PDO::PARAM_INT]  // 結束位置參數
		//[':start', (int)$start, PDO::PARAM_INT], // 開始位置
		//[':end',   (int)$end,   PDO::PARAM_INT]  // 結束位置
	];

    $stmt = dbExecute($pdo, $sql, $params, 'SystemLogsAPI', 'ListLogs', $username, $storeID, $extraBindings);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(array(
        "total" => $total,
        "rows"  => $rows
    ));
    exit;
}

if ($action === 'view') {
    $sql = "SELECT * FROM SystemLogs WHERE LogID = :id";
    $params = [':id' => $_REQUEST['LogID']];
    $stmt = dbExecute($pdo, $sql, $params, 'SystemLogsAPI', 'ViewLog', $username, $storeID);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(["row" => $row]);
    exit;
}
?>
