<?php
header("Content-Type: application/json; charset=UTF-8");
require_once "db.php"; // PDO MSSQL 連線

$action = $_GET['action'] ?? '';

function generateTransactionID($pdo, $storeID) {
    // 取得流水號
    $stmt = $pdo->query("SELECT NEXT VALUE FOR TransactionSeq AS Seq");
    $seq = $stmt->fetchColumn();
    $seqStr = str_pad($seq, 8, "0", STR_PAD_LEFT);
    return $storeID . $seqStr;
}

if ($action === 'list') {
    $page     = intval($_GET['page'] ?? 1);
    $pageSize = 10;
    $startRow = ($page - 1) * $pageSize + 1;
    $endRow   = $page * $pageSize;
    $keyword  = $_GET['keyword'] ?? '';
    $like     = "%".$keyword."%";

    // 總筆數
    $countSql = "SELECT COUNT(*) 
                 FROM Transactions t
                 JOIN Customers c ON t.CustomerID=c.CustomerID
                 WHERE c.Name LIKE ? OR c.Phone LIKE ?";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute([$like,$like]);
    $totalCount = $countStmt->fetchColumn();

    // 分頁查詢
    $sql = "WITH TxCTE AS (
                SELECT t.TransactionID, t.StoreID, t.TransactionDate,
                       c.Name AS CustomerName, s.Name AS SalespersonName,
                       t.TotalAmount, t.ActualPayment, t.TotalPointsEarned,
                       ROW_NUMBER() OVER (ORDER BY t.TransactionDate DESC) AS RowNum
                FROM Transactions t
                JOIN Customers c ON t.CustomerID=c.CustomerID
                JOIN Staffs s ON t.SalespersonID=s.StaffID
                WHERE c.Name LIKE ? OR c.Phone LIKE ?
            )
            SELECT * FROM TxCTE WHERE RowNum BETWEEN ? AND ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$like,$like,$startRow,$endRow]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "totalCount"=>$totalCount,
        "pageSize"=>$pageSize,
        "page"=>$page,
        "records"=>$records
    ]);
}

elseif ($action === 'view') {
    $id = $_GET['id'];
    // Master
    $stmt = $pdo->prepare("SELECT * FROM Transactions WHERE TransactionID=?");
    $stmt->execute([$id]);
    $master = $stmt->fetch(PDO::FETCH_ASSOC);

    // Slave
    $stmt = $pdo->prepare("SELECT d.*, p.ProductName, p.Category 
                           FROM TransactionDetails d
                           JOIN Products p ON d.ProductID=p.ProductID
                           WHERE TransactionID=?");
    $stmt->execute([$id]);
    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["master"=>$master,"details"=>$details]);
}

elseif ($action === 'save') {
    $pdo->beginTransaction();
    try {
        $TransactionID = $_POST['TransactionID'] ?? '';
        $StoreID       = $_POST['StoreID'];
        $CustomerID    = $_POST['CustomerID'];
        $SalespersonID = $_POST['SalespersonID'];
        $ExamID        = $_POST['ExamID'] ?? null;
        $TransactionDate = $_POST['TransactionDate'] ?? date("Y-m-d");

        if ($TransactionID == '') {
            // 新增 Master
            $TransactionID = generateTransactionID($pdo,$StoreID);
            $stmt = $pdo->prepare("INSERT INTO Transactions 
                (TransactionID,StoreID,CustomerID,SalespersonID,ExamID,TransactionDate,
                 TotalAmount,ActualPayment,TotalPointsEarned)
                VALUES (?,?,?,?,?,?,0,0,0)");
            $stmt->execute([$TransactionID,$StoreID,$CustomerID,$SalespersonID,$ExamID,$TransactionDate]);
        } else {
            // 更新 Master (先清除舊明細)
            $pdo->prepare("DELETE FROM TransactionDetails WHERE TransactionID=?")->execute([$TransactionID]);
        }

        // 新增 Slave 明細
        $details = json_decode($_POST['details'],true); // 前端傳 JSON
        $totalAmount=0; $totalPoints=0;
        foreach($details as $d){
            $ProductID = $d['ProductID'];
            $BatchNo   = $d['BatchNo'];
            $Quantity  = $d['Quantity'];
            $UnitPrice = $d['UnitPrice'];
            $SalePrice = $d['SalePrice'];
            $subTotal  = $Quantity * $SalePrice;

            // 商品類別查詢
            $stmt = $pdo->prepare("SELECT Category FROM Products WHERE ProductID=?");
            $stmt->execute([$ProductID]);
            $category = $stmt->fetchColumn();

            // 積分計算邏輯 (範例)
            $points = 0;
            if ($category=="Lens") $points = floor($subTotal/100)*2;
            elseif ($category=="Frame") $points = floor($subTotal/100)*1;
            else $points = floor($subTotal/200)*1;

            $stmt = $pdo->prepare("INSERT INTO TransactionDetails
                (TransactionID,ProductID,BatchNo,Quantity,UnitPrice,SalePrice,PointsEarned)
                VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$TransactionID,$ProductID,$BatchNo,$Quantity,$UnitPrice,$SalePrice,$points]);

            $totalAmount += $subTotal;
            $totalPoints += $points;
        }

        // 更新 Master 合計
        $stmt = $pdo->prepare("UPDATE Transactions 
                               SET TotalAmount=?, ActualPayment=?, TotalPointsEarned=?, ModifiedAt=GETDATE()
                               WHERE TransactionID=?");
        $stmt->execute([$totalAmount,$_POST['ActualPayment'],$totalPoints,$TransactionID]);

        $pdo->commit();
        echo json_encode(["message"=>"交易儲存成功","TransactionID"=>$TransactionID]);
    } catch(Exception $e){
        $pdo->rollBack();
        echo json_encode(["message"=>"錯誤: ".$e->getMessage()]);
    }
}

elseif ($action === 'delete') {
    $id = $_GET['id'];
    $pdo->beginTransaction();
    try {
        $pdo->prepare("DELETE FROM TransactionDetails WHERE TransactionID=?")->execute([$id]);
        $pdo->prepare("DELETE FROM Transactions WHERE TransactionID=?")->execute([$id]);
        $pdo->commit();
        echo json_encode(["message"=>"交易刪除成功"]);
    } catch(Exception $e){
        $pdo->rollBack();
        echo json_encode(["message"=>"錯誤: ".$e->getMessage()]);
    }
}
