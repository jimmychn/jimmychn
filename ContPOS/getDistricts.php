<?php
require 'db.php'; // 請確認這裡有 PDO 連線設定

header('Content-Type: application/json; charset=utf-8');

$city = $_GET['city'] ?? '';

if ($city === '') {
    echo json_encode([]);
    exit;
}

$sql = "SELECT ZIP, AREA FROM ZIP WHERE CITY=? ORDER BY AREA";
$stmt = $pdo->prepare($sql);
$stmt->execute([$city]);

$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result, JSON_UNESCAPED_UNICODE);
?>
