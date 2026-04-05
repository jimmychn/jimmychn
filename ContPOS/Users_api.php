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
        $where .= " AND (U.UserID LIKE ? OR U.Username LIKE ? OR U.UserName LIKE ? OR U.Email LIKE ? OR S.StoreName LIKE ? OR R.RoleName LIKE ? )";
        $like = '%' . $search . '%';
        for ($i = 0; $i < 6; $i++) { $params[] = $like; }
    }
    $sqlCount = "SELECT COUNT(*) FROM Users U LEFT JOIN Stores S ON U.StoreID = S.StoreID LEFT JOIN Roles R ON U.RoleID = R.RoleID " . $where;
    $countStmt = dbExecute($pdo, $sqlCount, $params, 'UsersAPI', 'CountRows', $username, $storeID);
    $total = $countStmt->fetchColumn();
    $sql = "WITH UserList AS (
                SELECT U.UserID,
                       U.Username,
                       U.UserName AS DisplayName,
                       U.Email,
                       U.StoreID,
                       S.StoreName,
                       U.RoleID,
                       R.RoleName,
                       U.IsActive,
                       ROW_NUMBER() OVER (ORDER BY U.UserID ASC) AS RowNum
                FROM Users U
                LEFT JOIN Stores S ON U.StoreID = S.StoreID
                LEFT JOIN Roles R ON U.RoleID = R.RoleID
                " . $where . "
            )
            SELECT UserID, Username, DisplayName AS UserName, Email, StoreID, StoreName, RoleID, RoleName, IsActive
            FROM UserList
            WHERE RowNum BETWEEN ? AND ?";
    $params[] = $startRow;
    $params[] = $endRow;
    $stmt = dbExecute($pdo, $sql, $params, 'UsersAPI', 'ListRows', $username, $storeID);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(array('total' => intval($total), 'rows' => $rows));
    exit;
}
if ($action === 'view') {
    $userID = isset($_REQUEST['UserID']) ? trim($_REQUEST['UserID']) : '';
    $sql = "SELECT U.UserID, U.Username, U.UserName, U.Email, U.StoreID, U.RoleID, U.IsActive, S.StoreName, R.RoleName
            FROM Users U
            LEFT JOIN Stores S ON U.StoreID = S.StoreID
            LEFT JOIN Roles R ON U.RoleID = R.RoleID
            WHERE U.UserID = ?";
    $stmt = dbExecute($pdo, $sql, array($userID), 'UsersAPI', 'ViewRow', $username, $storeID);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(array('row' => $row));
    exit;
}
if ($action === 'check') {
    $field = isset($_REQUEST['field']) ? $_REQUEST['field'] : '';
    $value = isset($_REQUEST['value']) ? trim($_REQUEST['value']) : '';
    $exclude = isset($_REQUEST['exclude']) ? trim($_REQUEST['exclude']) : '';
    if ($field === 'UserID' || $field === 'Username') {
        $sql = "SELECT COUNT(*) AS cnt FROM Users WHERE " . ($field === 'UserID' ? 'UserID' : 'Username') . " = ?";
        $params = array($value);
        if ($exclude !== '') {
            $sql .= " AND UserID <> ?";
            $params[] = $exclude;
        }
        $stmt = dbExecute($pdo, $sql, $params, 'UsersAPI', 'CheckUnique', $username, $storeID);
        $cnt = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
        echo json_encode(array('status' => $cnt > 0 ? 'error' : 'ok', 'field' => $field));
    } else {
        echo json_encode(array('status' => 'error', 'message' => '未知欄位'));
    }
    exit;
}
if ($action === 'insert') {
    $userID = isset($_POST['UserID']) ? trim($_POST['UserID']) : '';
    $usernameInput = isset($_POST['Username']) ? trim($_POST['Username']) : '';
    $userName = isset($_POST['UserName']) ? trim($_POST['UserName']) : '';
    $email = isset($_POST['Email']) ? trim($_POST['Email']) : '';
    $password = isset($_POST['Password']) ? trim($_POST['Password']) : '';
    $storeIDInput = isset($_POST['StoreID']) ? trim($_POST['StoreID']) : '';
    $roleID = isset($_POST['RoleID']) ? trim($_POST['RoleID']) : '';
    $isActive = isset($_POST['IsActive']) && $_POST['IsActive'] === '1' ? 1 : 0;
    if ($userID === '' || $usernameInput === '' || $userName === '' || $password === '' || $storeIDInput === '' || $roleID === '') {
        echo json_encode(array('success' => false, 'message' => '請完整填寫必填欄位'));
        exit;
    }
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO Users (UserID, Username, UserName, Email, PasswordHash, StoreID, RoleID, IsActive)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    dbExecute($pdo, $sql, array($userID, $usernameInput, $userName, $email, $passwordHash, $storeIDInput, $roleID, $isActive), 'UsersAPI', 'InsertRow', $username, $storeID);
    echo json_encode(array('success' => true));
    exit;
}
if ($action === 'update') {
    $userID = isset($_POST['UserID']) ? trim($_POST['UserID']) : '';
    $usernameInput = isset($_POST['Username']) ? trim($_POST['Username']) : '';
    $userName = isset($_POST['UserName']) ? trim($_POST['UserName']) : '';
    $email = isset($_POST['Email']) ? trim($_POST['Email']) : '';
    $password = isset($_POST['Password']) ? trim($_POST['Password']) : '';
    $storeIDInput = isset($_POST['StoreID']) ? trim($_POST['StoreID']) : '';
    $roleID = isset($_POST['RoleID']) ? trim($_POST['RoleID']) : '';
    $isActive = isset($_POST['IsActive']) && $_POST['IsActive'] === '1' ? 1 : 0;
    if ($userID === '' || $usernameInput === '' || $userName === '' || $storeIDInput === '' || $roleID === '') {
        echo json_encode(array('success' => false, 'message' => '請完整填寫必填欄位'));
        exit;
    }
    if ($password === '') {
        echo json_encode(array('success' => false, 'message' => '更新時請輸入密碼')); 
        exit;
    }
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $sql = "UPDATE Users SET Username = ?, UserName = ?, Email = ?, PasswordHash = ?, StoreID = ?, RoleID = ?, IsActive = ? WHERE UserID = ?";
    dbExecute($pdo, $sql, array($usernameInput, $userName, $email, $passwordHash, $storeIDInput, $roleID, $isActive, $userID), 'UsersAPI', 'UpdateRow', $username, $storeID);
    echo json_encode(array('success' => true));
    exit;
}
if ($action === 'delete') {
    $userID = isset($_POST['UserID']) ? trim($_POST['UserID']) : '';
    $sql = "DELETE FROM Users WHERE UserID = ?";
    dbExecute($pdo, $sql, array($userID), 'UsersAPI', 'DeleteRow', $username, $storeID);
    echo json_encode(array('success' => true));
    exit;
}
echo json_encode(array('error' => 'Invalid action'));
exit;
