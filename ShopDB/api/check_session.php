<?php
session_start();
header('Content-Type: application/json');

$response = [
    'loggedIn' => false,
    'user' => null
];

if (isset($_SESSION['user'])) {
    $response['loggedIn'] = true;
    $response['user'] = $_SESSION['user']; // 順便傳回userData
}

echo json_encode($response);
?>