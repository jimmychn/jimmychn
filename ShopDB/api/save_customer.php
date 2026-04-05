<?php
session_start();
require_once 'db_config.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$store_id = $_SESSION['user']['store_id'];

$id = isset($input['id']) ? $input['id'] : '';
$name = isset($input['customer_name']) ? $input['customer_name'] : '';
$contact = isset($input['contact_person']) ? $input['contact_person'] : '';
$tel = isset($input['tel']) ? $input['tel'] : '';
$address = isset($input['address']) ? $input['address'] : '';

if (empty($name)) {
    echo json_encode(array('status' => 'error', 'message' => '名稱必填'));
    exit;
}

try {
    if (!empty($id)) {
        // 修改
        $sql = "UPDATE customers SET customer_name=?, contact_person=?, tel=?, address=? WHERE id=? AND store_id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($name, $contact, $tel, $address, $id, $store_id));
    } else {
        // 新增
        $sql = "INSERT INTO customers (store_id, customer_name, contact_person, tel, address) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($store_id, $name, $contact, $tel, $address));
    }
    echo json_encode(array('status' => 'success'));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
}
