<?php
header("Content-Type: application/json; charset=UTF-8");
require_once "db.php";

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if ($action === 'list') {
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $pageSize = isset($_REQUEST['pageSize']) ? intval($_REQUEST['pageSize']) : 10;
    $tnoFilter = isset($_REQUEST['T_NO']) ? trim($_REQUEST['T_NO']) : '';
    $search = isset($_REQUEST['search']) ? trim($_REQUEST['search']) : '';
    $startRow = ($page - 1) * $pageSize + 1;
    $endRow = $page * $pageSize;

    $where = " WHERE 1=1 ";
    $params = array();
    if ($tnoFilter !== '') {
        $where .= " AND T_NO = ?";
        $params[] = $tnoFilter;
    }
    if ($search !== '') {
        $where .= " AND (TD_NO LIKE ? OR TD_NAME LIKE ?)";
        $like = '%' . $search . '%';
        $params[] = $like;
        $params[] = $like;
    }

    $sqlCount = "SELECT COUNT(*) FROM TAB" . $where;
    $countStmt = dbExecute($pdo, $sqlCount, $params, 'TABAPI', 'CountRows', $username, $storeID);
    $total = $countStmt->fetchColumn();

    $sql = "WITH TabList AS (
                SELECT T_NO, TD_NO, TD_NAME, SEQ,
                       ROW_NUMBER() OVER (ORDER BY T_NO ASC, TD_NO ASC) AS RowNum
                FROM TAB" . $where . "
            )
            SELECT T_NO, TD_NO, TD_NAME, SEQ
            FROM TabList
            WHERE RowNum BETWEEN ? AND ?";

    $params[] = $startRow;
    $params[] = $endRow;
    $stmt = dbExecute($pdo, $sql, $params, 'TABAPI', 'ListRows', $username, $storeID);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(array('total' => intval($total), 'rows' => $rows));
    exit;
}

if ($action === 'lookups') {
    $stmt = dbExecute($pdo, "SELECT DISTINCT T_NO FROM TAB ORDER BY T_NO", array(), 'TABAPI', 'LookupTNo', $username, $storeID);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(array('tnoList' => $rows));
    exit;
}

if ($action === 'view') {
    $tno = isset($_REQUEST['T_NO']) ? trim($_REQUEST['T_NO']) : '';
    $tdno = isset($_REQUEST['TD_NO']) ? trim($_REQUEST['TD_NO']) : '';
    $sql = "SELECT T_NO, TD_NO, TD_NAME, SEQ FROM TAB WHERE T_NO = ? AND TD_NO = ?";
    $stmt = dbExecute($pdo, $sql, array($tno, $tdno), 'TABAPI', 'ViewRow', $username, $storeID);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(array('row' => $row));
    exit;
}

if ($action === 'check') {
    $tno = isset($_GET['T_NO']) ? trim($_GET['T_NO']) : '';
    $tdno = isset($_GET['TD_NO']) ? trim($_GET['TD_NO']) : '';
    $stmt = dbExecute($pdo, "SELECT COUNT(*) AS cnt FROM TAB WHERE T_NO = ? AND TD_NO = ?", array($tno, $tdno), 'TABAPI', 'CheckID', $username, $storeID);
    $cnt = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    if ($cnt > 0) {
        echo json_encode(array('status' => 'error', 'message' => '資料已存在')); 
    } else {
        echo json_encode(array('status' => 'ok'));
    }
    exit;
}

if ($action === 'insert') {
    $tno = isset($_POST['T_NO']) ? trim($_POST['T_NO']) : '';
    $tdno = isset($_POST['TD_NO']) ? trim($_POST['TD_NO']) : '';
    $tdName = isset($_POST['TD_NAME']) ? trim($_POST['TD_NAME']) : '';
    $seq = isset($_POST['SEQ']) ? intval($_POST['SEQ']) : 0;

    $sql = "INSERT INTO TAB (T_NO, TD_NO, TD_NAME, SEQ) VALUES (?, ?, ?, ?)";
    dbExecute($pdo, $sql, array($tno, $tdno, $tdName, $seq), 'TABAPI', 'InsertRow', $username, $storeID);
    echo json_encode(array('success' => true));
    exit;
}

if ($action === 'update') {
    $tno = isset($_POST['T_NO']) ? trim($_POST['T_NO']) : '';
    $tdno = isset($_POST['TD_NO']) ? trim($_POST['TD_NO']) : '';
    $tdName = isset($_POST['TD_NAME']) ? trim($_POST['TD_NAME']) : '';
    $seq = isset($_POST['SEQ']) ? intval($_POST['SEQ']) : 0;

    $sql = "UPDATE TAB SET TD_NAME = ?, SEQ = ? WHERE T_NO = ? AND TD_NO = ?";
    dbExecute($pdo, $sql, array($tdName, $seq, $tno, $tdno), 'TABAPI', 'UpdateRow', $username, $storeID);
    echo json_encode(array('success' => true));
    exit;
}

if ($action === 'delete') {
    $tno = isset($_POST['T_NO']) ? trim($_POST['T_NO']) : '';
    $tdno = isset($_POST['TD_NO']) ? trim($_POST['TD_NO']) : '';
    $sql = "DELETE FROM TAB WHERE T_NO = ? AND TD_NO = ?";
    dbExecute($pdo, $sql, array($tno, $tdno), 'TABAPI', 'DeleteRow', $username, $storeID);
    echo json_encode(array('success' => true));
    exit;
}

echo json_encode(array('error' => 'Invalid action'));
exit;
