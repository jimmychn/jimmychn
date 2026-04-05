<?php
session_start();
require 'db.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => '無效的請求']);
    exit;
}

$store = trim($_POST['store'] ?? '');
$username = trim($_POST['username'] ?? '');

if (empty($store) || empty($username)) {
    echo json_encode(['status' => 'error', 'message' => '請選擇門市並輸入帳號']);
    exit;
}

// 根據新版設計，Email 以獨立欄位存在於 Users 中
$sql = "SELECT Username, Email 
        FROM Users
        WHERE StoreID = ? AND Username = ? AND IsActive = 1";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$store, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 若查無帳號或未綁定信箱，為避免帳號列舉攻擊(Account Enumeration)，給予模糊回覆
    if (!$user || empty($user['Email'])) {
        echo json_encode(['status' => 'error', 'message' => '查無此帳號，或該帳號尚未綁定電子郵件信箱。']);
        exit;
    }

    $email = $user['Email'];

    // 產生打碼隱私信箱格式，避免洩漏完整資訊給嘗試帳號的人
    function maskEmail($email) {
        if (strpos($email, '@') !== false) {
            $parts = explode('@', $email);
            $first = $parts[0];
            $domain = $parts[1];
            
            $len = strlen($first);
            if ($len > 2) {
                // 取字首字尾，中間換成三個星號： a***n
                $first = substr($first, 0, 1) . '***' . substr($first, -1);
            } elseif ($len == 2) {
                $first = substr($first, 0, 1) . '*';
            }
            return $first . '@' . $domain;
        }
        return $email;
    }

    $maskedEmail = maskEmail($email);

    // ============================================
    // [TODO] 預留未來 SMTP 寄信實作區塊
    // ============================================
    /*
     * 這裡替換為真實的寄信服務 (如 PHPMailer)
     * $mail = new PHPMailer(true);
     * $mail->isSMTP();
     * $mail->Host = 'smtp.example.com';
     * ...
     * $mail->addAddress($email);
     * $mail->Subject = "密碼重設通知信";
     * $mail->Body = "請點擊以下連結重設... Token: ". bin2hex(random_bytes(16));
     * $mail->send();
     */

    // 模擬成功回覆結果，符合標準規格書要求
    echo json_encode([
        'status' => 'success', 
        'message' => '系統已發送重設密碼連結至<br><b class="text-primary">' . $maskedEmail . '</b>。<br>請務必於 <b>30 分鐘</b> 內點選信中連結變更設定！'
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => '資料庫連線異常，請聯絡系統管理員。']);
    exit;
}
?>
