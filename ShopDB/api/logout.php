<?php
session_start();
session_destroy();

// 禁止瀏覽器快取
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 跳轉回最頂層的 index.php 或 login.php
header("Location: ../index.html"); 
exit;
?>