<?php
/**
 * 檔案：api/get_internal_statement.php
 * 功能：計算門市間調撥產生的內部帳務 (以產品進價/成本計算)
 */
session_start();
require_once 'db_config.php';
header('Content-Type: application/json');

$from = $_GET['from_store'];
$to = $_GET['to_store'];

$sql = "SELECT t.transfer_no, t.transfer_date, p.name, i.qty, i.price, (i.qty * i.price) as subtotal
        FROM stock_transfers t
        JOIN stock_transfer_items i ON t.id = i.transfer_id
        JOIN products p ON i.product_id = p.id
        WHERE t.from_store_id = ? AND t.to_store_id = ?
        ORDER BY t.transfer_date ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$from, $to]);
$details = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['status' => 'success', 'data' => $details]);
