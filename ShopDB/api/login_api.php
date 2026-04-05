<?php
session_start();
require_once 'db_config.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$ip = $_SERVER['REMOTE_ADDR'];
$ua = $_SERVER['HTTP_USER_AGENT'];

// --- 0. 內網 IP 豁免檢查 (127.0.0.1, 192.168.* 等) ---
$is_internal = (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false);

// --- 1. 黑名單檢查 ---
if (!$is_internal) {
    $stmt = $pdo->prepare("SELECT * FROM ip_blacklist WHERE ip_address = ?");
    $stmt->execute([$ip]);
    $block = $stmt->fetch();
    
    if ($block) {
        if ($block['block_type'] == 'permanent') {
            exit(json_encode(['status' => 'error', 'message' => '此 IP 已被永久封鎖，請聯絡管理員']));
        }
        if ($block['block_type'] == 'temporary' && strtotime($block['unlock_at']) > time()) {
            exit(json_encode(['status' => 'error', 'message' => '登入錯誤過多，請於 ' . $block['unlock_at'] . ' 後再試']));
        }
        // 臨時封鎖已過期，自動移除
        $pdo->prepare("DELETE FROM ip_blacklist WHERE ip_address = ?")->execute([$ip]);
    }
}

/*//==================以自訂 POST JSON 資料調用
// --- 2. 驗證碼檢查 ---
if (!isset($input['captcha']) || $input['captcha'] !== $_SESSION['captcha']) {
	exit(json_encode(['status' => 'error', 'message' => '驗證碼錯誤captcha']));
}
$user = isset($input['username']) ? $input['username'] : '';
$pass = isset($input['password']) ? $input['password'] : '';
/*///==================以自訂 POST FormData 資料調用
if (!isset($_REQUEST['captcha']) || $_REQUEST['captcha'] !== $_SESSION['captcha']) {
	exit(json_encode(['status' => 'error', 'message' => '驗證碼錯誤！'.$_REQUEST['captcha']]));
}
$user = isset($_REQUEST['username']) ? $_REQUEST['username'] : '';
$pass = isset($_REQUEST['password']) ? $_REQUEST['password'] : '';
//*///=================

// 裝置/瀏覽器解析 (簡單示範)
$browser = strpos($ua, 'Chrome') ? 'Chrome' : (strpos($ua, 'Firefox') ? 'Firefox' : 'Other');
$device = strpos($ua, 'Mobi') ? 'Mobile' : 'PC';

// --- 3. 驗證帳密 ---
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$user]);
$userData = $stmt->fetch();

if ($userData && password_verify($pass, $userData['password'])) {
    // 登入成功
    $_SESSION['user'] = $userData; // userData 存入 Session

    // 2. 查詢權限並存入 Session
    $stmt = $pdo->prepare("SELECT p.perm_key            //功能權限代碼
                            FROM role_permissions rp
                            JOIN permissions p ON rp.perm_id = p.id
                            WHERE rp.role_id = ?");
    $stmt->execute([$userData['role_id']]);
    $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $_SESSION['permissions'] = $permissions; // 權限陣列存入 Session


    $pdo->prepare("INSERT INTO login_attempts (ip_address, username, browser, device, status) VALUES (?, ?, ?, ?, 'success')")->execute([$ip, $user, $browser, $device]);
    echo json_encode(['status' => 'success']);
} else {
    // 登入失敗：記錄並計算次數
    $pdo->prepare("INSERT INTO login_attempts (ip_address, username, browser, device, status) VALUES (?, ?, ?, ?, 'fail')")->execute([$ip, $user, $browser, $device]);
   
    if (!$is_internal) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND status = 'fail' AND attempt_time > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        $stmt->execute([$ip]);
        $fail_count = $stmt->fetchColumn();

        if ($fail_count >= 5) {
            $pdo->prepare("INSERT INTO ip_blacklist (ip_address, block_type, reason) VALUES (?, 'permanent', '錯誤5次永久封鎖')")->execute([$ip]);
        } elseif ($fail_count >= 3) {
            $unlock_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $pdo->prepare("INSERT INTO ip_blacklist (ip_address, block_type, unlock_at, reason) VALUES (?, 'temporary', ?, '錯誤3次暫時封鎖') ON DUPLICATE KEY UPDATE unlock_at = ?")->execute([$ip, $unlock_at, $unlock_at]);
        }
    }
    echo json_encode(['status' => 'error', 'message' => '帳號或密碼錯誤！'.password_hash($pass, PASSWORD_DEFAULT)]);
}
