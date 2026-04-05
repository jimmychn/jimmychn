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
        $where .= " AND (StoreID LIKE ? OR StoreName LIKE ? OR CITY LIKE ? OR AREA LIKE ? OR Address LIKE ?)";
        $like = '%' . $search . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    $sqlCount = "SELECT COUNT(*) FROM Stores" . $where;
    $countStmt = dbExecute($pdo, $sqlCount, $params, 'StoresAPI', 'CountRows', $username, $storeID);
    $total = $countStmt->fetchColumn();

    $sql = "WITH StoreList AS (
                SELECT StoreID, StoreName, TAXID, TITLE, ZIPCode, CITY, AREA, Address, ManagerID, TEL, FAX, IsActive,
                       ROW_NUMBER() OVER (ORDER BY StoreID ASC) AS RowNum
                FROM Stores
                " . $where . "
            )
            SELECT StoreID, StoreName, TAXID, TITLE, ZIPCode, CITY, AREA, Address, ManagerID, TEL, FAX, IsActive
            FROM StoreList
            WHERE RowNum BETWEEN ? AND ?";

    $params[] = $startRow;
    $params[] = $endRow;
    $stmt = dbExecute($pdo, $sql, $params, 'StoresAPI', 'ListRows', $username, $storeID);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(array('total' => intval($total), 'rows' => $rows));
    exit;
}

if ($action === 'view') {
    $storeID = isset($_REQUEST['StoreID']) ? trim($_REQUEST['StoreID']) : '';
    $sql = "SELECT StoreID, StoreName, TAXID, TITLE, ZIPCode, CITY, AREA, Address, ManagerID, TEL, FAX, IsActive FROM Stores WHERE StoreID = ?";
    $stmt = dbExecute($pdo, $sql, array($storeID), 'StoresAPI', 'ViewRow', $username, $storeID);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(array('row' => $row));
    exit;
}

if ($action === 'insert') {
    $storeID = isset($_POST['StoreID']) ? trim($_POST['StoreID']) : '';
    $storeName = isset($_POST['StoreName']) ? trim($_POST['StoreName']) : '';
    $taxId = isset($_POST['TAXID']) ? trim($_POST['TAXID']) : '';
    $title = isset($_POST['TITLE']) ? trim($_POST['TITLE']) : '';
    $zipCode = isset($_POST['ZIPCode']) ? trim($_POST['ZIPCode']) : '';
    $city = isset($_POST['CITY']) ? trim($_POST['CITY']) : '';
    $area = isset($_POST['AREA']) ? trim($_POST['AREA']) : '';
    $address = isset($_POST['Address']) ? trim($_POST['Address']) : '';
    $managerID = isset($_POST['ManagerID']) ? trim($_POST['ManagerID']) : '';
    $tel = isset($_POST['TEL']) ? trim($_POST['TEL']) : '';
    $fax = isset($_POST['FAX']) ? trim($_POST['FAX']) : '';
    $isActive = isset($_POST['IsActive']) && $_POST['IsActive'] === '1' ? 1 : 0;

    $sql = "INSERT INTO Stores (StoreID, StoreName, TAXID, TITLE, ZIPCode, CITY, AREA, Address, ManagerID, TEL, FAX, IsActive)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $params = array($storeID, $storeName, $taxId, $title, $zipCode, $city, $area, $address, $managerID, $tel, $fax, $isActive);
    dbExecute($pdo, $sql, $params, 'StoresAPI', 'InsertRow', $username, $storeID);
    echo json_encode(array('success' => true));
    exit;
}

if ($action === 'update') {
    $storeID = isset($_POST['StoreID']) ? trim($_POST['StoreID']) : '';
    $storeName = isset($_POST['StoreName']) ? trim($_POST['StoreName']) : '';
    $taxId = isset($_POST['TAXID']) ? trim($_POST['TAXID']) : '';
    $title = isset($_POST['TITLE']) ? trim($_POST['TITLE']) : '';
    $zipCode = isset($_POST['ZIPCode']) ? trim($_POST['ZIPCode']) : '';
    $city = isset($_POST['CITY']) ? trim($_POST['CITY']) : '';
    $area = isset($_POST['AREA']) ? trim($_POST['AREA']) : '';
    $address = isset($_POST['Address']) ? trim($_POST['Address']) : '';
    $managerID = isset($_POST['ManagerID']) ? trim($_POST['ManagerID']) : '';
    $tel = isset($_POST['TEL']) ? trim($_POST['TEL']) : '';
    $fax = isset($_POST['FAX']) ? trim($_POST['FAX']) : '';
    $isActive = isset($_POST['IsActive']) && $_POST['IsActive'] === '1' ? 1 : 0;

    $sql = "UPDATE Stores SET StoreName = ?, TAXID = ?, TITLE = ?, ZIPCode = ?, CITY = ?, AREA = ?, Address = ?, ManagerID = ?, TEL = ?, FAX = ?, IsActive = ? WHERE StoreID = ?";
    $params = array($storeName, $taxId, $title, $zipCode, $city, $area, $address, $managerID, $tel, $fax, $isActive, $storeID);
    dbExecute($pdo, $sql, $params, 'StoresAPI', 'UpdateRow', $username, $storeID);
    echo json_encode(array('success' => true));
    exit;
}

if ($action === 'delete') {
    $storeID = isset($_POST['StoreID']) ? trim($_POST['StoreID']) : '';
    $sql = "DELETE FROM Stores WHERE StoreID = ?";
    dbExecute($pdo, $sql, array($storeID), 'StoresAPI', 'DeleteRow', $username, $storeID);
    echo json_encode(array('success' => true));
    exit;
}

if ($action === 'lookups') {
    $zipStmt = dbExecute($pdo, "SELECT ZIP, CITY, AREA FROM ZIP ORDER BY ZIP", array(), 'StoresAPI', 'LookupZIP', $username, $storeID);
    $zipList = $zipStmt->fetchAll(PDO::FETCH_ASSOC);

    $mgrStmt = dbExecute($pdo, "SELECT StaffID, Name FROM Staffs WHERE Active = 1 ORDER BY Name", array(), 'StoresAPI', 'LookupManagers', $username, $storeID);
    $managers = $mgrStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(array('zipList' => $zipList, 'managers' => $managers));
    exit;
}

if ($action === 'lookupStores') {
    $stmt = dbExecute($pdo, "SELECT StoreID, StoreName FROM Stores WHERE IsActive = 1 ORDER BY StoreID", array(), 'StoresAPI', 'LookupStores', $username, $storeID);
    echo json_encode(array('stores' => $stmt->fetchAll(PDO::FETCH_ASSOC)));
    exit;
}

echo json_encode(array('error' => 'Invalid action'));
exit;
