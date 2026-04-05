<?php
header("Content-Type: application/json; charset=UTF-8");
require_once "db.php";

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if ($action === 'list') {
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $pageSize = isset($_REQUEST['pageSize']) ? intval($_REQUEST['pageSize']) : 10;
    $search = isset($_REQUEST['search']) ? trim($_REQUEST['search']) : '';
    
    $startRow = ($page - 1) * $pageSize + 1;
    $endRow = $page * $pageSize;
    $where = " WHERE 1=1 ";
    $params = array();

    if ($search !== '') {
        $where .= " AND (U.UserID LIKE ? OR U.UserName LIKE ? OR U.Email LIKE ? OR S.StoreName LIKE ? OR R.RoleName LIKE ? )";
        $like = '%' . $search . '%';
        for ($i = 0; $i < 5; $i++) { $params[] = $like; }
    }

    $sqlCount = "SELECT COUNT(*) FROM Users U 
                 LEFT JOIN Stores S ON U.StoreID = S.StoreID 
                 LEFT JOIN Roles R ON U.RoleID = R.RoleID " . $where;
    
    $countStmt = dbExecute($pdo, $sqlCount, $params, 'UsersAPI', 'CountRows', 'System', '');
    $total = $countStmt->fetchColumn();

    $sql = "WITH UserList AS (
                SELECT U.StoreID,
                       S.StoreName,
                       U.UserID,
                       U.UserName,
                       U.Email,
                       U.RoleID,
                       R.RoleName,
                       U.IsActive,
                       ROW_NUMBER() OVER (ORDER BY U.StoreID ASC, U.UserID ASC) AS RowNum
                FROM Users U
                LEFT JOIN Stores S ON U.StoreID = S.StoreID
                LEFT JOIN Roles R ON U.RoleID = R.RoleID
                " . $where . "
            )
            SELECT StoreID, StoreName, UserID, UserName, Email, RoleID, RoleName, IsActive
            FROM UserList
            WHERE RowNum BETWEEN ? AND ?";
            
    $params[] = $startRow;
    $params[] = $endRow;
    $stmt = dbExecute($pdo, $sql, $params, 'UsersAPI', 'ListRows', 'System', '');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(array('total' => intval($total), 'rows' => $rows));
    exit;
}

if ($action === 'view') {
    $userID = isset($_REQUEST['UserID']) ? trim($_REQUEST['UserID']) : '';
    $storeID = isset($_REQUEST['StoreID']) ? trim($_REQUEST['StoreID']) : '';
    
    $sql = "SELECT U.UserID, U.UserName, U.Email, U.StoreID, U.RoleID, U.IsActive, S.StoreName, R.RoleName
            FROM Users U
            LEFT JOIN Stores S ON U.StoreID = S.StoreID
            LEFT JOIN Roles R ON U.RoleID = R.RoleID
            WHERE U.StoreID = ? AND U.UserID = ?";
    $stmt = dbExecute($pdo, $sql, array($storeID, $userID), 'UsersAPI', 'ViewRow', 'System', '');
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(array('row' => $row));
    exit;
}

if ($action === 'insert') {
    $storeIDInput = isset($_POST['StoreID']) ? trim($_POST['StoreID']) : '';
    $userID = isset($_POST['UserID']) ? trim($_POST['UserID']) : '';
    $userName = isset($_POST['UserName']) ? trim($_POST['UserName']) : '';
    $email = isset($_POST['Email']) ? trim($_POST['Email']) : '';
    $password = isset($_POST['Password']) ? trim($_POST['Password']) : '';
    $roleID = isset($_POST['RoleID']) ? trim($_POST['RoleID']) : '';
    $isActive = isset($_POST['IsActive']) && $_POST['IsActive'] === '1' ? 1 : 0;
    
    if ($storeIDInput === '' || $userID === '' || $userName === '' || $password === '' || $roleID === '') {
        echo json_encode(array('success' => false, 'message' => '請完整填寫必填欄位'));
        exit;
    }
    
    // 檢查複合主鍵是否重複
    $checkSql = "SELECT COUNT(*) FROM Users WHERE StoreID = ? AND UserID = ?";
    if (dbExecute($pdo, $checkSql, array($storeIDInput, $userID), 'UsersAPI', 'CheckPK', 'System', '')->fetchColumn() > 0) {
        echo json_encode(array('success' => false, 'message' => '該門市下已存在相同的使用者代號！'));
        exit;
    }
    
    $sha256Pass = hash('sha256', $password);
    $passwordHash = password_hash($sha256Pass, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO Users (StoreID, UserID, UserName, Email, PasswordHash, RoleID, IsActive)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    dbExecute($pdo, $sql, array($storeIDInput, $userID, $userName, $email, $passwordHash, $roleID, $isActive), 'UsersAPI', 'InsertRow', 'System', '');
    echo json_encode(array('success' => true));
    exit;
}

if ($action === 'update') {
    $originalStoreID = isset($_POST['OriginalStoreID']) && trim($_POST['OriginalStoreID']) !== '' ? trim($_POST['OriginalStoreID']) : '';
    $originalUserID = isset($_POST['OriginalUserID']) && trim($_POST['OriginalUserID']) !== '' ? trim($_POST['OriginalUserID']) : '';

    $storeIDInput = isset($_POST['StoreID']) ? trim($_POST['StoreID']) : '';
    $userID = isset($_POST['UserID']) ? trim($_POST['UserID']) : '';
    $userName = isset($_POST['UserName']) ? trim($_POST['UserName']) : '';
    $email = isset($_POST['Email']) ? trim($_POST['Email']) : '';
    $password = isset($_POST['Password']) ? trim($_POST['Password']) : '';
    $roleID = isset($_POST['RoleID']) ? trim($_POST['RoleID']) : '';
    $isActive = isset($_POST['IsActive']) && $_POST['IsActive'] === '1' ? 1 : 0;

    if ($originalStoreID === '' || $originalUserID === '' || $storeIDInput === '' || $userID === '' || $userName === '' || $roleID === '') {
        echo json_encode(array('success' => false, 'message' => '請完整填寫必填欄位'));
        exit;
    }
    
    // 如果變更了主鍵，檢查新主鍵是否已存在
    if ($storeIDInput !== $originalStoreID || $userID !== $originalUserID) {
        $checkSql = "SELECT COUNT(*) FROM Users WHERE StoreID = ? AND UserID = ?";
        if (dbExecute($pdo, $checkSql, array($storeIDInput, $userID), 'UsersAPI', 'CheckPK', 'System', '')->fetchColumn() > 0) {
            echo json_encode(array('success' => false, 'message' => '您欲變更到的門市與使用者代號已存在！'));
            exit;
        }
    }

    if ($password === '') {
        $sql = "UPDATE Users SET StoreID = ?, UserID = ?, UserName = ?, Email = ?, RoleID = ?, IsActive = ? WHERE StoreID = ? AND UserID = ?";
        dbExecute($pdo, $sql, array($storeIDInput, $userID, $userName, $email, $roleID, $isActive, $originalStoreID, $originalUserID), 'UsersAPI', 'UpdateRow', 'System', '');
    } else {
        $sha256Pass = hash('sha256', $password);
        $passwordHash = password_hash($sha256Pass, PASSWORD_DEFAULT);
        $sql = "UPDATE Users SET StoreID = ?, UserID = ?, UserName = ?, Email = ?, PasswordHash = ?, RoleID = ?, IsActive = ? WHERE StoreID = ? AND UserID = ?";
        dbExecute($pdo, $sql, array($storeIDInput, $userID, $userName, $email, $passwordHash, $roleID, $isActive, $originalStoreID, $originalUserID), 'UsersAPI', 'UpdateRow', 'System', '');
    }

    echo json_encode(array('success' => true));
    exit;
}

if ($action === 'delete') {
    $storeID = isset($_POST['StoreID']) ? trim($_POST['StoreID']) : '';
    $userID = isset($_POST['UserID']) ? trim($_POST['UserID']) : '';
    if ($storeID === '' || $userID === '') {
        echo json_encode(array('success' => false, 'message' => '缺少主鍵參數'));
        exit;
    }
    $sql = "DELETE FROM Users WHERE StoreID = ? AND UserID = ?";
    dbExecute($pdo, $sql, array($storeID, $userID), 'UsersAPI', 'DeleteRow', 'System', '');
    echo json_encode(array('success' => true));
    exit;
}

echo json_encode(array('error' => 'Invalid action'));
exit;
