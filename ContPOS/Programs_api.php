<?php
require_once("db.php"); // 這裡有 $pdo (PDO 連線)

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if ($_GET['action'] === 'list') {
    // 分頁參數
    $limit  = isset($_REQUEST['limit']) ? intval($_REQUEST['limit']) : 10;
    $offset = isset($_REQUEST['offset']) ? intval($_REQUEST['offset']) : 0;

    $page     = floor($offset / $limit) + 1;
    $startRow = $offset + 1;
    $endRow   = $offset + $limit;

    // 排序參數
    $sort  = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'ProgramID';
    $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : 'ASC';

    // 搜尋參數
    $keyword = !empty($_REQUEST['keyword']) ? $_REQUEST['keyword'] : (!empty($_REQUEST['search']) ? $_REQUEST['search'] : '');
    $active  = isset($_REQUEST['active']) ? $_REQUEST['active'] : '';

    // 組合 WHERE 條件
    $where = " WHERE 1=1 ";
    $params = [];

    if ($keyword !== '') {
        $where .= " AND (ProgramName LIKE :keyword OR ProgramCode LIKE :keyword)";
        $params[':keyword'] = "%$keyword%";
    }

    if ($active !== '') {
        $where .= " AND Active = :active";
        $params[':active'] = $active;
    }

    // 查總筆數
    $sqlCount = "SELECT COUNT(*) AS Total FROM Programs $where";
    $stmt = dbExecute($pdo, $sqlCount, $params, 'ProgramsAPI', 'CountRows', $username, $storeID);
    $total = $stmt->fetchColumn();

    // 分頁查詢
    $sql = "
        WITH OrderedPrograms AS (
            SELECT ProgramID, ProgramName, ProgramCode, Active, CreatedAt, ModifiedAt,
                   ROW_NUMBER() OVER (ORDER BY $sort $order) AS RowNum
            FROM Programs
            $where
        )
        SELECT ProgramID, ProgramName, ProgramCode, Active, CreatedAt, ModifiedAt
        FROM OrderedPrograms
        WHERE RowNum BETWEEN :startRow AND :endRow
    ";

    $extraBindings = [
        [':startRow', $startRow, PDO::PARAM_INT],
        [':endRow',   $endRow,   PDO::PARAM_INT]
    ];

    $stmt = dbExecute($pdo, $sql, $params, 'ProgramsAPI', 'ListRows', $username, $storeID, $extraBindings);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "total" => $total,
        "rows"  => $rows
    ]);
    exit;
}

// 新增
if ($_GET['action'] === 'insert') {
    $sql = "INSERT INTO Programs (ProgramName, ProgramCode, Active, CreatedAt) 
            VALUES (:name, :code, :active, SYSDATETIME())";
    $params = [
        ':name'   => $_POST['ProgramName'],
        ':code'   => $_POST['ProgramCode'],
        ':active' => $_POST['Active']
    ];
    dbExecute($pdo, $sql, $params, 'ProgramsAPI', 'InsertRow', $username, $storeID);
    echo json_encode(["success" => true]);
    exit;
}

// 更新
if ($_GET['action'] === 'update') {
    $sql = "UPDATE Programs 
            SET ProgramName = :name, ProgramCode = :code, Active = :active, ModifiedAt = SYSDATETIME()
            WHERE ProgramID = :id";
    $params = [
        ':id'     => $_POST['ProgramID'],
        ':name'   => $_POST['ProgramName'],
        ':code'   => $_POST['ProgramCode'],
        ':active' => $_POST['Active']
    ];
    dbExecute($pdo, $sql, $params, 'ProgramsAPI', 'UpdateRow', $username, $storeID);
    echo json_encode(["success" => true]);
    exit;
}

// 刪除
if ($_GET['action'] === 'delete') {
    $sql = "DELETE FROM Programs WHERE ProgramID = :id";
    $params = [':id' => $_POST['ProgramID']];
    dbExecute($pdo, $sql, $params, 'ProgramsAPI', 'DeleteRow', $username, $storeID);
    echo json_encode(["success" => true]);
    exit;
}

// 檢視單筆資料
if ($_GET['action'] === 'view') {
    $sql = "SELECT ProgramID, ProgramName, ProgramCode, Active, CreatedAt, ModifiedAt
            FROM Programs
            WHERE ProgramID = :id";

    $params = [':id' => $_REQUEST['ProgramID']];

    $stmt = dbExecute($pdo, $sql, $params, 'ProgramsAPI', 'ViewRow', $username, $storeID);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "row" => $row
    ]);
    exit;
}

?>
