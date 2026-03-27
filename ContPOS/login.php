<?php
session_start();
require 'db.php';

$clientIp = $_SERVER['REMOTE_ADDR'];

function isInternalIP($ip) {
    return preg_match('/^(192\.168|10\.)/', $ip);
}

// 讀取登入嘗試紀錄
$stmt = $pdo->prepare("SELECT FailedCount, BlockUntil, IsBlocked FROM LoginAttempts WHERE IP=?");
$stmt->execute([$clientIp]);
$attempt = $stmt->fetch(PDO::FETCH_ASSOC);

// 封鎖檢查 (非內網才限制)
if (!isInternalIP($clientIp)) {
    if ($attempt && $attempt['IsBlocked'] == 1) {
        die("<div class='alert alert-danger text-center'>您的 IP 已被永久禁止登入！</div>");
    }
    if ($attempt && $attempt['BlockUntil'] && strtotime($attempt['BlockUntil']) > time()) {
        die("<div class='alert alert-danger text-center'>您的 IP 登入錯誤超過 3 次，暫時禁止登入至 " . $attempt['BlockUntil'] . "</div>");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $store    = $_POST['store'];
    $captcha  = $_POST['captcha'];

    $error = false;

    // 驗證碼檢查
    if (!isset($_SESSION['captcha']) || strtolower($captcha) !== strtolower($_SESSION['captcha'])) {
        $error = true;
    } else {
        $sql = "SELECT UserID, PasswordHash, RoleID, StoreID 
                FROM Users 
                WHERE Username=? AND StoreID=? AND IsActive=1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $store]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['PasswordHash'])) {
            // 登入成功
            $_SESSION['UserID'] = $user['UserID'];
            $_SESSION['RoleID'] = $user['RoleID'];
            $_SESSION['StoreID'] = $user['StoreID'];

            if (!isInternalIP($clientIp)) {
                $pdo->prepare("DELETE FROM LoginAttempts WHERE IP=?")->execute([$clientIp]);
            }

            // 寫入登入日誌
            $pdo->prepare("INSERT INTO LoginLogs (Username, StoreID, IP, Result) VALUES (?, ?, ?, 'Success')")
                ->execute([$username, $store, $clientIp]);

            header("Location: admin_dashboard.php");
            exit;
        } else {
            $error = true;
        }
    }

    // 登入失敗處理
    if ($error) {
        $failedCount = 1;
        $blockUntil = null;
        $isBlocked = 0;

        if (!isInternalIP($clientIp)) {
            if ($attempt) {
                $failedCount = $attempt['FailedCount'] + 1;

                if ($failedCount >= 6) {
                    $isBlocked = 1; // 永久封鎖
                } elseif ($failedCount >= 3) {
                    $blockUntil = date("Y-m-d H:i:s", strtotime("+1 hour"));
                }

                $sql = "UPDATE LoginAttempts SET FailedCount=?, BlockUntil=?, IsBlocked=? WHERE IP=?";
                $pdo->prepare($sql)->execute([$failedCount, $blockUntil, $isBlocked, $clientIp]);
            } else {
                $sql = "INSERT INTO LoginAttempts (IP, FailedCount, BlockUntil, IsBlocked) VALUES (?, 1, NULL, 0)";
                $pdo->prepare($sql)->execute([$clientIp]);
            }
        }

        // 寫入登入日誌 (失敗)
        $pdo->prepare("INSERT INTO LoginLogs (Username, StoreID, IP, Result) VALUES (?, ?, ?, 'Failure')")
            ->execute([$username, $store, $clientIp]);

        // 顯示錯誤訊息 + 次數提示
        $remaining = 6 - $failedCount;
        if ($remaining > 0) {
            echo "<div class='alert alert-danger text-center'>
                    帳號、密碼或驗證碼錯誤，無法登入！<br>
                    您已錯誤 {$failedCount} 次，還剩 {$remaining} 次機會。
                  </div>";
        } else {
            echo "<div class='alert alert-danger text-center'>
                    帳號、密碼或驗證碼錯誤，無法登入！<br>
                    您已錯誤 {$failedCount} 次，該 IP 已被永久禁止登入。
                  </div>";
        }
    }
}

// 讀取門市清單
$stmt = $pdo->query("SELECT StoreID, StoreName FROM Stores WHERE IsActive=1 ORDER BY StoreID");
$stores = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>



<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <title>POS 系統登入</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card shadow">
        <div class="card-header text-center">
          <h4>POS 系統登入</h4>
        </div>
        <div class="card-body">
          <form method="POST">
            <div class="mb-3">
              <label class="form-label">門市</label>
              <select class="form-select" name="store" required>
                <option value="">請選擇門市</option>
                <?php foreach ($stores as $s): ?>
                  <option value="<?= $s['StoreID'] ?>"><?= $s['StoreID'] ?> - <?= $s['StoreName'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">帳號</label>
              <input type="text" class="form-control" name="username" required>
            </div>
            <div class="mb-3">
              <label class="form-label">密碼</label>
              <input type="password" class="form-control" name="password" required>
            </div>
            <div class="mb-3">
              <label class="form-label">驗證碼</label>
              <div class="input-group">
                <input type="text" class="form-control" name="captcha" required>
                <span class="input-group-text">
                  <img src="captcha.php" alt="驗證碼" onclick="this.src='captcha.php?'+Date.now();">
                </span>
              </div>
            </div>
            <button type="submit" class="btn btn-primary w-100">登入</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
