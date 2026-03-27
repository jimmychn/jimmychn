<?php
header("Content-Type: application/json; charset=UTF-8");
require_once "db.php"; // 使用你的 PDO MSSQL 連線

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'save') {
    $ExamID      = isset($_POST['ExamID']) ? intval($_POST['ExamID']) : 0;
    $CustomerID  = $_POST['CustomerID'];
    $ExamDate    = $_POST['ExamDate'];
    $Examiner    = $_POST['Examiner'];
    $SphereRight = $_POST['SphereRight'];
    $CylinderRight = $_POST['CylinderRight'];
    $AxisRight   = $_POST['AxisRight'];
    $BaseCurveRight = $_POST['BaseCurveRight'];
    $PdRight     = $_POST['PdRight'];
    $AddRight    = $_POST['AddRight'];
    $SphereLeft  = $_POST['SphereLeft'];
    $CylinderLeft= $_POST['CylinderLeft'];
    $AxisLeft    = $_POST['AxisLeft'];
    $BaseCurveLeft = $_POST['BaseCurveLeft'];
    $PdLeft      = $_POST['PdLeft'];
    $AddLeft     = $_POST['AddLeft'];
    $Notes       = $_POST['Notes'];

    if ($ExamID > 0) {
        // 更新
        $sql = "UPDATE EyeExamRecords
                SET CustomerID=?, ExamDate=?, Examiner=?,
                    SphereRight=?, CylinderRight=?, AxisRight=?, BaseCurveRight=?, PdRight=?, AddRight=?,
                    SphereLeft=?, CylinderLeft=?, AxisLeft=?, BaseCurveLeft=?, PdLeft=?, AddLeft=?,
                    Notes=?, ModifiedAt=GETDATE()
                WHERE ExamID=?";
        $stmt = $pdo->prepare($sql);
        $ok = $stmt->execute([
            $CustomerID, $ExamDate, $Examiner,
            $SphereRight, $CylinderRight, $AxisRight, $BaseCurveRight, $PdRight, $AddRight,
            $SphereLeft, $CylinderLeft, $AxisLeft, $BaseCurveLeft, $PdLeft, $AddLeft,
            $Notes, $ExamID
        ]);
        echo json_encode(["message" => $ok ? "更新成功" : "更新失敗"]);
    } else {
        // 新增
        $sql = "INSERT INTO EyeExamRecords
                (CustomerID, ExamDate, Examiner,
                 SphereRight, CylinderRight, AxisRight, BaseCurveRight, PdRight, AddRight,
                 SphereLeft, CylinderLeft, AxisLeft, BaseCurveLeft, PdLeft, AddLeft,
                 Notes)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $pdo->prepare($sql);
        $ok = $stmt->execute([
            $CustomerID, $ExamDate, $Examiner,
            $SphereRight, $CylinderRight, $AxisRight, $BaseCurveRight, $PdRight, $AddRight,
            $SphereLeft, $CylinderLeft, $AxisLeft, $BaseCurveLeft, $PdLeft, $AddLeft,
            $Notes
        ]);
        echo json_encode(["message" => $ok ? "新增成功" : "新增失敗"]);
    }
    exit;
} 

// 檢視單筆紀錄
if ($action === 'view' && $id > 0) {
    $sql = "SELECT * FROM EyeExamRecords WHERE ExamID=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($row);
    exit;
}

// 刪除紀錄
if ($action === 'delete' && $id > 0) {
    $sql = "DELETE FROM EyeExamRecords WHERE ExamID=?";
    $stmt = $pdo->prepare($sql);
    $ok = $stmt->execute([$id]);
    echo json_encode(["message" => $ok ? "刪除成功" : "刪除失敗"]);
    exit;
}

if ($action === 'list') {
    $page     = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $pageSize = 10;
    $startRow = ($page - 1) * $pageSize + 1;
    $endRow   = $page * $pageSize;

    $keyword  = isset($_GET['keyword']) ? $_GET['keyword'] : '';
    $like     = "%".$keyword."%";

    // 先算總筆數
    $countSql = "SELECT COUNT(*) AS totalCount
                 FROM EyeExamRecords e
                 JOIN Customers c ON e.CustomerID = c.CustomerID
                 WHERE c.Name LIKE ? OR c.Phone LIKE ?";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute([$like, $like]);
    $totalCount = $countStmt->fetchColumn();

    // 分頁查詢
    $sql = "WITH ExamCTE AS (
                SELECT e.ExamID, e.CustomerID, c.Name AS CustomerName, e.ExamDate, e.Examiner,
                       e.SphereRight, e.CylinderRight, e.AxisRight, e.BaseCurveRight, e.PdRight, e.AddRight,
                       e.SphereLeft, e.CylinderLeft, e.AxisLeft, e.BaseCurveLeft, e.PdLeft, e.AddLeft,
                       e.Notes,
                       ROW_NUMBER() OVER (ORDER BY e.ExamDate DESC) AS RowNum
                FROM EyeExamRecords e
                JOIN Customers c ON e.CustomerID = c.CustomerID
                WHERE c.Name LIKE ? OR c.Phone LIKE ?
            )
            SELECT * FROM ExamCTE
            WHERE RowNum BETWEEN ? AND ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$like, $like, $startRow, $endRow]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "totalCount" => $totalCount,
        "pageSize"   => $pageSize,
        "page"       => $page,
        "records"    => $records
    ]);
    exit;
}

echo json_encode(["message" => "未知的操作"]);
