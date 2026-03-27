<?php
require_once 'db.php'; // PDO 連線

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

switch ($action) {
	case 'list':
		$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
		$pageSize = isset($_GET['pageSize']) ? intval($_GET['pageSize']) : 10;
		$search = isset($_GET['search']) ? trim($_GET['search']) : '';
		$startRow = ($page - 1) * $pageSize + 1;
		$endRow = $page * $pageSize;

		if($search === ''){
			// 顯示全部
			$countSql = "SELECT COUNT(*) AS TotalCount FROM Users";
			$countStmt = $pdo->query($countSql);
			$totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['TotalCount'];

			$sql = "WITH UserList AS (
						SELECT ROW_NUMBER() OVER (ORDER BY U.UserID) AS RowNum,
							   U.UserID, U.Username, U.StoreID, S.Name, R.RoleName, U.IsActive
						FROM Users U
						LEFT JOIN Staffs S ON U.UserID = S.StaffID
						LEFT JOIN Roles R ON U.RoleID = R.RoleID
					)
					SELECT * FROM UserList WHERE RowNum BETWEEN ? AND ?";
			$stmt = $pdo->prepare($sql);
			$stmt->execute(array($startRow, $endRow));
		} else {
			// 有搜尋條件
			$likeSearch = '%' . $search . '%';

			$countSql = "SELECT COUNT(*) AS TotalCount
						 FROM Users U
						 WHERE (U.UserID LIKE ? OR U.Username LIKE ?)";
			$countStmt = $pdo->prepare($countSql);
			$countStmt->execute(array($likeSearch, $likeSearch));
			$totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['TotalCount'];

			$sql = "WITH UserList AS (
						SELECT ROW_NUMBER() OVER (ORDER BY U.UserID) AS RowNum,
							   U.UserID, U.Username, U.StoreID, S.Name, R.RoleName, U.IsActive
						FROM Users U
						LEFT JOIN Staffs S ON U.UserID = S.StaffID
						LEFT JOIN Roles R ON U.RoleID = R.RoleID
						WHERE (U.UserID LIKE ? OR U.Username LIKE ?)
					)
					SELECT * FROM UserList WHERE RowNum BETWEEN ? AND ?";
			$stmt = $pdo->prepare($sql);
			$stmt->execute(array($likeSearch, $likeSearch, $startRow, $endRow));
		}

		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$totalPages = ceil($totalCount / $pageSize);

		echo json_encode(array(
			'data' => $rows,
			'totalCount' => $totalCount,
			'page' => $page,
			'pageSize' => $pageSize,
            "totalPages"=>$totalPages
		));
		break;

	case 'create':
		$UserID = $_POST['UserID'];
		$Username = $_POST['Username'];
		$PasswordHash = password_hash($_POST['Password'], PASSWORD_BCRYPT);
		$StoreID = $_POST['StoreID'];
		$RoleID = $_POST['RoleID'];
		$IsActive = $_POST['IsActive'];

		// 新增
		$stmt = $pdo->prepare("INSERT INTO Users (UserID, Username, PasswordHash, StoreID, RoleID, IsActive) 
							   VALUES (?, ?, ?, ?, ?, ?)");
		$stmt->execute(array($UserID, $Username, $PasswordHash, $StoreID, $RoleID, $IsActive));
		echo json_encode(array('status'=>'success'));
		break;


    case 'update':
        $UserID = $_POST['UserID'];
        $Username = $_POST['Username'];
        $StoreID = $_POST['StoreID'];
        $RoleID = $_POST['RoleID'];
        $IsActive = $_POST['IsActive'];

        $stmt = $pdo->prepare("UPDATE Users SET Username=?, StoreID=?, RoleID=?, IsActive=? WHERE UserID=?");
        $stmt->execute(array($Username, $StoreID, $RoleID, $IsActive, $UserID));
        echo json_encode(array('status' => 'success'));
        break;

    case 'delete':
        $UserID = $_POST['UserID'];
        $stmt = $pdo->prepare("DELETE FROM Users WHERE UserID=?");
        $stmt->execute(array($UserID));
        echo json_encode(array('status' => 'success'));
        break;

	case 'roles':
		$stmt = $pdo->query("SELECT RoleID, RoleName FROM Roles ORDER BY RoleID");
		echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
		break;

	case 'get':
		$UserID = $_GET['UserID'];
		$stmt = $pdo->prepare("SELECT * FROM Users WHERE UserID=?");
		$stmt->execute(array($UserID));
		echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
		break;

	case 'check':
		$field = $_GET['field']; // UserID 或 Username
		$value = $_GET['value'];

		if($field === 'UserID'){
			$stmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM Users WHERE UserID=?");
		} else {
			echo json_encode(array('status'=>'error','message'=>'未知欄位'));
			exit;
		}

		$stmt->execute(array($value));
		$cnt = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

		if($cnt > 0){
			echo json_encode(array('status'=>'error','field'=>$field,'message'=>$field.' 已存在'));
		} else {
			echo json_encode(array('status'=>'ok'));
		}
		break;

   default:
        echo json_encode(array('error' => 'Invalid action'));
}
?>
