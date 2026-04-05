<?php
session_start();
require 'db.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => '無效的請求']);
    exit;
}

$clientIp = $_SERVER['REMOTE_ADDR'];
function isInternalIP($ip) {
    // 包含 localhost (127.0.0.1, ::1) 以及 RFC 1918 規範的 10.x.x.x, 192.168.x.x, 172.16~31.x.x
    return $ip === '127.0.0.1' || $ip === '::1' || preg_match('/^(192\.168|10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.)/', $ip);
}
$isInternal = isInternalIP($clientIp);

$store = isset($_REQUEST['store']) ? trim($_REQUEST['store']) : '';
$username = isset($_REQUEST['username']) ? trim($_REQUEST['username']) : '';
$password = isset($_REQUEST['password']) ? $_REQUEST['password'] : '';  // 前端已做 SHA256
$captcha = isset($_REQUEST['captcha']) ? trim($_REQUEST['captcha']) : '';

if (empty($username) || empty($password) || empty($store) || empty($captcha)) {
    echo json_encode(['status' => 'error', 'message' => '請填寫完整登入資訊']);
    exit;
}

// 1. 先查封鎖狀態
$stmt = $pdo->prepare("SELECT FailedCount, BlockUntil, IsBlocked FROM LoginAttempts WHERE IP=?");
$stmt->execute([$clientIp]);
$attempt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$isInternal && $attempt) {
    if ($attempt['IsBlocked'] == 1) {
        echo json_encode(['status' => 'error', 'message' => '您的 IP 已被永久禁止登入！', 'isBlocked' => true, 'remaining' => 0]);
        exit;
    }
    if ($attempt['BlockUntil'] && strtotime($attempt['BlockUntil']) > time()) {
        echo json_encode(['status' => 'error', 'message' => '您的 IP 登入錯誤過度，暫時禁止登入至 ' . $attempt['BlockUntil'], 'isBlocked' => true, 'remaining' => 0]);
        exit;
    }
    // 若 BlockUntil 時間已過，則允許繼續驗證，後續若成功會清除紀錄
}

// 寫入失敗日誌與防護層的 Helper
function failLogin($msg) {
    global $pdo, $clientIp, $isInternal, $attempt, $username, $store;

    try {
        // 先寫入登入日誌 (無論內外網)
        $pdo->prepare("INSERT INTO LoginLogs (Username, StoreID, IP, Result, AttemptTime) VALUES (?, ?, ?, 'Failure', GETDATE())")
            ->execute([$username, $store, $clientIp]);
    } catch (PDOException $e) {
        // 若表不存在或失敗，忽略，避免中斷主流程
    }

    if ($isInternal) {
        echo json_encode(['status' => 'error', 'message' => $msg]);
        exit;
    }

    $failedCount = ($attempt ? (int)$attempt['FailedCount'] : 0) + 1;
    $blockUntil = null;
    $isBlocked = 0;

    if ($failedCount >= 5) {
        $isBlocked = 1;
    } elseif ($failedCount >= 3) {
        $blockUntil = date("Y-m-d H:i:s", strtotime("+1 hour"));
    }

    try {
        if ($attempt) {
            $sql = "UPDATE LoginAttempts SET FailedCount=?, BlockUntil=?, IsBlocked=? WHERE IP=?";
            $pdo->prepare($sql)->execute([$failedCount, $blockUntil, $isBlocked, $clientIp]);
        } else {
            $sql = "INSERT INTO LoginAttempts (IP, FailedCount, BlockUntil, IsBlocked) VALUES (?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([$clientIp, $failedCount, $blockUntil, $isBlocked]);
        }
    } catch (PDOException $e) { }

    $remaining = 5 - $failedCount;
    if ($remaining < 0) $remaining = 0;

    echo json_encode([
        'status' => 'error', 
        'message' => $msg, 
        'remaining' => $remaining,
        'isBlocked' => ($isBlocked === 1)
    ]);
    exit;
}

// 2. 檢查圖形驗證碼
if (empty($_SESSION['captcha']) || strtolower($captcha) !== strtolower($_SESSION['captcha'])) {
    failLogin('圖形驗證碼錯誤，請重新輸入！');
}

// 3. 檢查帳號與密碼 (使用 password_verify 比對 前端 SHA256 字串 與 資料庫內的 BCRYPT)
$sql = "SELECT UserID, PasswordHash, RoleID, StoreID 
        FROM Users 
        WHERE Username=? AND StoreID=? AND IsActive=1";
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username, $store]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['PasswordHash'])) {
        // 成功
        $_SESSION['UserID'] = $user['UserID'];
        $_SESSION['RoleID'] = $user['RoleID'];
        $_SESSION['StoreID'] = $user['StoreID'];

        // 登入成功後，立刻刷新驗證碼，防止重放攻擊 (Replay Attack)
        unset($_SESSION['captcha']);

        // 清除該 IP 失敗紀錄
        if (!$isInternal) {
            $pdo->prepare("DELETE FROM LoginAttempts WHERE IP=?")->execute([$clientIp]);
        }
        // 寫入成功日誌
        $pdo->prepare("INSERT INTO LoginLogs (Username, StoreID, IP, Result, AttemptTime) VALUES (?, ?, ?, 'Success', GETDATE())")
            ->execute([$username, $store, $clientIp]);

        echo json_encode(['status' => 'success', 'message' => '登入成功', 'url' => 'index.php']);
        exit;
    } else {
        failLogin('帳號密碼錯誤或門市帶入有誤！');
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => '資料庫連線或查詢錯誤：' . $e->getMessage()]);
    exit;
}
