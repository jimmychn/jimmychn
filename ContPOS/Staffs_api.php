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
        $where .= " AND (S.StaffID LIKE ? OR S.Name LIKE ? OR S.StoreID LIKE ? OR St.StoreName LIKE ? OR S.Position LIKE ? OR S.TEL LIKE ? OR S.Mobile LIKE ? OR S.Email LIKE ? )";
        $like = '%' . $search . '%';
        for ($i = 0; $i < 8; $i++) { $params[] = $like; }
    }

    $sqlCount = "SELECT COUNT(*) FROM Staffs S LEFT JOIN Stores St ON S.StoreID = St.StoreID " . $where;
    $countStmt = dbExecute($pdo, $sqlCount, $params, 'StaffsAPI', 'CountRows', $username, $storeID);
    $total = $countStmt->fetchColumn();

    $sql = "WITH StaffList AS (
                SELECT S.StaffID, S.StoreID, St.StoreName, S.Name, S.Gender, S.TEL, S.Mobile, S.Email,
                       S.Active, S.LineID, S.FacebookID, S.Birthday, S.ZIPCode, S.CITY, S.AREA, S.Address, S.Position,
                       ROW_NUMBER() OVER (ORDER BY S.StaffID ASC) AS RowNum
                FROM Staffs S
                LEFT JOIN Stores St ON S.StoreID = St.StoreID
                " . $where . "
            )
            SELECT StaffID, StoreID, StoreName, Name, Gender, TEL, Mobile, Email, Active, LineID, FacebookID, Birthday, ZIPCode, CITY, AREA, Address, Position
            FROM StaffList
            WHERE RowNum BETWEEN ? AND ?";

    $params[] = $startRow;
    $params[] = $endRow;
    $stmt = dbExecute($pdo, $sql, $params, 'StaffsAPI', 'ListRows', $username, $storeID);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(array('total' => intval($total), 'rows' => $rows));
    exit;
}

if ($action === 'view') {
    $staffID = isset($_REQUEST['StaffID']) ? trim($_REQUEST['StaffID']) : '';
    $sql = "SELECT S.StaffID, S.StoreID, St.StoreName, S.Name, S.Gender, S.TEL, S.Mobile, S.Email, S.LineID, S.FacebookID, S.Birthday, S.ZIPCode, S.CITY, S.AREA, S.Address, S.Active, S.Position
            FROM Staffs S
            LEFT JOIN Stores St ON S.StoreID = St.StoreID
            WHERE S.StaffID = ?";
    $stmt = dbExecute($pdo, $sql, array($staffID), 'StaffsAPI', 'ViewRow', $username, $storeID);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(array('row' => $row));
    exit;
}

if ($action === 'check') {
    $field = isset($_GET['field']) ? $_GET['field'] : '';
    $value = isset($_GET['value']) ? trim($_GET['value']) : '';
    if ($field === 'StaffID') {
        $stmt = dbExecute($pdo, "SELECT COUNT(*) AS cnt FROM Staffs WHERE StaffID = ?", array($value), 'StaffsAPI', 'CheckID', $username, $storeID);
        $cnt = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
        if ($cnt > 0) {
            echo json_encode(array('status'=>'error','field'=>$field,'message'=>$field.' 已存在'));
        } else {
            echo json_encode(array('status'=>'ok'));
        }
    } else {
        echo json_encode(array('status'=>'error','message'=>'未知欄位'));    }
    exit;
}

function convertBirthdayMonth($birthday) {
    if ($birthday === '') return null;
    $timestamp = strtotime($birthday);
    if ($timestamp === false) return null;
    return intval(date('n', $timestamp));
}

if ($action === 'insert') {
    $staffID = isset($_REQUEST['StaffID']) ? trim($_REQUEST['StaffID']) : '';
    $storeID = isset($_REQUEST['StoreID']) ? trim($_REQUEST['StoreID']) : '';
    $name = isset($_REQUEST['Name']) ? trim($_REQUEST['Name']) : '';
    $gender = isset($_REQUEST['Gender']) ? trim($_REQUEST['Gender']) : '';
    $position = isset($_REQUEST['Position']) ? trim($_REQUEST['Position']) : '';
    $zipCode = isset($_REQUEST['ZIPCode']) ? trim($_REQUEST['ZIPCode']) : '';
    $city = isset($_REQUEST['CITY']) ? trim($_REQUEST['CITY']) : '';
    $area = isset($_REQUEST['AREA']) ? trim($_REQUEST['AREA']) : '';
    $address = isset($_REQUEST['Address']) ? trim($_REQUEST['Address']) : '';
    $tel = isset($_REQUEST['TEL']) ? trim($_REQUEST['TEL']) : '';
    $mobile = isset($_REQUEST['Mobile']) ? trim($_REQUEST['Mobile']) : '';
    $email = isset($_REQUEST['Email']) ? trim($_REQUEST['Email']) : '';
    $lineID = isset($_REQUEST['LineID']) ? trim($_REQUEST['LineID']) : '';
    $facebookID = isset($_REQUEST['FacebookID']) ? trim($_REQUEST['FacebookID']) : '';
    $birthday = isset($_REQUEST['Birthday']) ? trim($_REQUEST['Birthday']) : '';
    $active = isset($_REQUEST['Active']) && $_REQUEST['Active'] === '1' ? 1 : 0;
    $birthMonth = convertBirthdayMonth($birthday);

    $sql = "INSERT INTO Staffs (StaffID, StoreID, Name, Gender, Position, ZIPCode, CITY, AREA, Address, TEL, Mobile, Email, LineID, FacebookID, Birthday, BirthMonth, Active, CreatedAt, ModifiedAt)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, GETDATE(), GETDATE())";
    dbExecute($pdo, $sql, array($staffID, $storeID, $name, $gender, $position, $zipCode, $city, $area, $address, $tel, $mobile, $email, $lineID, $facebookID, $birthday !== '' ? $birthday : null, $birthMonth, $active), 'StaffsAPI', 'InsertRow', $username, $storeID);
    echo json_encode(array('success' => true));
    exit;
}

if ($action === 'update') {
    $staffID = isset($_REQUEST['StaffID']) ? trim($_REQUEST['StaffID']) : '';
    $storeID = isset($_REQUEST['StoreID']) ? trim($_REQUEST['StoreID']) : '';
    $name = isset($_REQUEST['Name']) ? trim($_REQUEST['Name']) : '';
    $gender = isset($_REQUEST['Gender']) ? trim($_REQUEST['Gender']) : '';
    $position = isset($_REQUEST['Position']) ? trim($_REQUEST['Position']) : '';
    $zipCode = isset($_REQUEST['ZIPCode']) ? trim($_REQUEST['ZIPCode']) : '';
    $city = isset($_REQUEST['CITY']) ? trim($_REQUEST['CITY']) : '';
    $area = isset($_REQUEST['AREA']) ? trim($_REQUEST['AREA']) : '';
    $address = isset($_REQUEST['Address']) ? trim($_REQUEST['Address']) : '';
    $tel = isset($_REQUEST['TEL']) ? trim($_REQUEST['TEL']) : '';
    $mobile = isset($_REQUEST['Mobile']) ? trim($_REQUEST['Mobile']) : '';
    $email = isset($_REQUEST['Email']) ? trim($_REQUEST['Email']) : '';
    $lineID = isset($_REQUEST['LineID']) ? trim($_REQUEST['LineID']) : '';
    $facebookID = isset($_REQUEST['FacebookID']) ? trim($_REQUEST['FacebookID']) : '';
    $birthday = isset($_REQUEST['Birthday']) ? trim($_REQUEST['Birthday']) : '';
    $active = isset($_REQUEST['Active']) && $_REQUEST['Active'] === '1' ? 1 : 0;
    $birthMonth = convertBirthdayMonth($birthday);

    $sql = "UPDATE Staffs SET StoreID = ?, Name = ?, Gender = ?, Position = ?, ZIPCode = ?, CITY = ?, AREA = ?, Address = ?, TEL = ?, Mobile = ?, Email = ?, LineID = ?, FacebookID = ?, Birthday = ?, BirthMonth = ?, Active = ?, ModifiedAt = GETDATE() WHERE StaffID = ?";
    dbExecute($pdo, $sql, array($storeID, $name, $gender, $position, $zipCode, $city, $area, $address, $tel, $mobile, $email, $lineID, $facebookID, $birthday !== '' ? $birthday : null, $birthMonth, $active, $staffID), 'StaffsAPI', 'UpdateRow', $username, $storeID);
    echo json_encode(array('success' => true));
    exit;
}

if ($action === 'delete') {
    $staffID = isset($_REQUEST['StaffID']) ? trim($_REQUEST['StaffID']) : '';
    $sql = "DELETE FROM Staffs WHERE StaffID = ?";
    dbExecute($pdo, $sql, array($staffID), 'StaffsAPI', 'DeleteRow', $username, $storeID);
    echo json_encode(array('success' => true));
    exit;
}

if ($action === 'lookups') {
    $zipStmt = dbExecute($pdo, "SELECT ZIP, CITY, AREA FROM ZIP ORDER BY ZIP", array(), 'StaffsAPI', 'LookupZIP', $username, $storeID);
    $storesStmt = dbExecute($pdo, "SELECT StoreID, StoreName FROM Stores WHERE IsActive = 1 ORDER BY StoreID", array(), 'StaffsAPI', 'LookupStores', $username, $storeID);

    echo json_encode(array(
        'zipList' => $zipStmt->fetchAll(PDO::FETCH_ASSOC),
        'stores' => $storesStmt->fetchAll(PDO::FETCH_ASSOC)
    ));
    exit;
}

if ($action === 'lookupByStore') {
    $selectedStore = isset($_REQUEST['StoreID']) ? trim($_REQUEST['StoreID']) : '';
    $stmt = dbExecute($pdo, "SELECT StaffID, Name, Email FROM Staffs WHERE StoreID = ? AND Active = 1 ORDER BY Name", array($selectedStore), 'StaffsAPI', 'LookupByStore', $username, $storeID);
    echo json_encode(array('staffs' => $stmt->fetchAll(PDO::FETCH_ASSOC)));
    exit;
}

echo json_encode(array('error' => 'Invalid action'));
exit;
