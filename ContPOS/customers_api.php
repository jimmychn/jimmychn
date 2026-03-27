<?php
header("Content-Type: application/json; charset=UTF-8");
require_once "db.php";

$action  = isset($_GET['action']) ? $_GET['action'] : '';
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
$page    = isset($_GET['page']) ? intval($_GET['page']) : 1;
$pageSize= 10;
$startRow= ($page - 1) * $pageSize + 1;
$endRow  = $page * $pageSize;

if ($action === 'search') {
    $sql = "WITH CustCTE AS (
                SELECT CustomerID, Name, Phone, Address,
                       ROW_NUMBER() OVER (ORDER BY Name ASC) AS RowNum
                FROM Customers
                WHERE Name LIKE ? OR Phone LIKE ?
            )
            SELECT * FROM CustCTE
            WHERE RowNum BETWEEN ? AND ?";
    $stmt = $pdo->prepare($sql);
    $like = "%".$keyword."%";
    $stmt->execute([$like, $like, $startRow, $endRow]);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($customers);
    exit;
}

echo json_encode(["message" => "未知的操作"]);
