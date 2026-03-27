<?php
$password = "admin"; // 你要設定的密碼
$hash = password_hash($password, PASSWORD_BCRYPT);
echo "密碼: $password\n";
echo "雜湊: $hash\n";
?>
