<?php
require_once("db.php"); // 連線設定

$action = isset($_POST['action']) ? $_POST['action'] : '';
$page   = isset($_POST['page']) ? intval($_POST['page']) : 1;
$pageSize = 10; // 每頁筆數

if ($action == 'list') {
    try {
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $keyword = isset($_POST['keyword']) ? trim($_POST['keyword']) : '';
        $pageSize = 10;
        $offset = ($page - 1) * $pageSize;

        $where = "";
        if ($keyword !== '') {
            $where = "WHERE ProgramName LIKE :kw OR ProgramCode LIKE :kw";
        }

        $sql = "
            WITH Data AS (
                SELECT 
                    ProgramID, ProgramName, ProgramCode,
                    ROW_NUMBER() OVER (ORDER BY ProgramID DESC) AS RowNum
                FROM Programs
                $where
            )
            SELECT * FROM Data
            WHERE RowNum BETWEEN :start AND :end;
        ";

        $stmt = $pdo->prepare($sql);
        if ($keyword !== '') {
            $stmt->bindValue(':kw', "%$keyword%", PDO::PARAM_STR);
        }
        $stmt->bindValue(':start', $offset + 1, PDO::PARAM_INT);
        $stmt->bindValue(':end', $offset + $pageSize, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 總筆數
        $countSql = "SELECT COUNT(*) AS Total FROM Programs " . ($keyword!=='' ? "WHERE ProgramName LIKE :kw OR ProgramCode LIKE :kw" : "");
        $countStmt = $pdo->prepare($countSql);
        if ($keyword !== '') {
            $countStmt->bindValue(':kw', "%$keyword%", PDO::PARAM_STR);
        }
        $countStmt->execute();
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['Total'];
        $totalPages = ceil($total / $pageSize);

        echo json_encode([
            "success" => true,
            "data" => $rows,
            "page" => $page,
            "totalPages" => $totalPages,
            "message" => "查詢成功"
        ]);
    } catch (Exception $e) {
        echo json_encode([
            "success" => false,
            "data" => [],
            "page" => 1,
            "totalPages" => 0,
            "message" => "查詢失敗: ".$e->getMessage()
        ]);
    }
}

// 新增
if ($action == 'create') {
    parse_str($_POST['data'], $form);
    $sql = "INSERT INTO Programs (ProgramName, ProgramCode) VALUES (:name, :code)";
    $stmt = $pdo->prepare($sql);
    $ok = $stmt->execute([
        ':name' => $form['ProgramName'],
        ':code' => $form['ProgramCode']
    ]);
    echo json_encode([ "success"=>$ok, "message"=>$ok?"":"DB Error" ]);
}

// 檢視
if ($action == 'view') {
    $id = intval($_POST['id']);
    $sql = "SELECT ProgramID, ProgramName, ProgramCode FROM Programs WHERE ProgramID=:id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([ ':id'=>$id ]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        echo json_encode([ "success"=>true, "data"=>$row ]);
    } else {
        echo json_encode([ "success"=>false, "message"=>"Not Found" ]);
    }
}

// 更新
if ($action == 'update') {
    parse_str($_POST['data'], $form);
    $sql = "UPDATE Programs SET ProgramName=:name, ProgramCode=:code WHERE ProgramID=:id";
    $stmt = $pdo->prepare($sql);
    $ok = $stmt->execute([
        ':name' => $form['ProgramName'],
        ':code' => $form['ProgramCode'],
        ':id'   => $form['ProgramID']
    ]);
    echo json_encode([ "success"=>$ok, "message"=>$ok?"":"DB Error" ]);
}

// 刪除
if ($action == 'delete') {
    $id = intval($_POST['id']);
    $sql = "DELETE FROM Programs WHERE ProgramID=:id";
    $stmt = $pdo->prepare($sql);
    $ok = $stmt->execute([ ':id'=>$id ]);
    echo json_encode([ "success"=>$ok, "message"=>$ok?"":"DB Error" ]);
}

if ($action == 'lookupRoles') {
    try {
        $sql = "SELECT RoleID, RoleName FROM Roles ORDER BY RoleID";
        $stmt = $pdo->query($sql);
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "roles" => $roles]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "roles" => [], "message" => $e->getMessage()]);
    }
}

?>
