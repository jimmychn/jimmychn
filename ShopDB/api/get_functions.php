<?php
/**
 * 檔案：api/get_functions.php
 * 註解：處理門市的新增與修改
 */
session_start();
require_once 'db_config.php';
header('Content-Type: application/json');

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // 依照 GroupName 與 SEQ 排序
    // 注意：這裡假設 GroupName 為空的代表它是單一連結（如安全登出）
    // api/get_functions.php 核心邏輯範例
    //$stmt = $pdo->prepare("
    //    SELECT f.* FROM functions f
    //    JOIN role_permissions rp ON f.permission_id = rp.permission_id
    //    WHERE rp.role_id = ? ORDER BY f.sort_order
    //");
    /*//需要檢查權限的語法
    $role_id = $_SESSION['user']['role_id'];
    $stmt = $pdo->prepare("
        SELECT GroupName, FuncName, URL, Icon FROM functions f
        JOIN role_permissions rp ON f.permission_id = rp.permission_id
        WHERE rp.role_id = ? ORDER BY GID, FID");
    $stmt->execute([$role_id]);
    /*///取得沒檢查權限的語法
    $stmt = $pdo->query("SELECT GroupName, FuncName, URL, Icon FROM functions ORDER BY GID, FID;");
    //*///

    $raw_data = $stmt->fetchAll();

    $menu = array();
    foreach ($raw_data as $row) {
        $group = $row['GroupName'];
        
        // 如果這個項目有 FuncName，代表它是子選單
        if (!empty($row['FuncName'])) {
            $menu[$group]['items'][] = array(
                'name' => $row['FuncName'],
                'url'  => $row['URL'],
                'icon' => $row['Icon']
            );
        } else {
            // 如果只有 GroupName 且 FuncName 為空，初始化該 Group (通常是 SEQ=0 的標題)
            if (!isset($menu[$group])) {
                $menu[$group] = array('group_name' => $group, 'items' => array(), 'root_url' => $row['URL'], 'icon' => $row['Icon']);
            }
        }
    }

    // 將關聯陣列轉回索引陣列，方便前端處理
    echo json_encode(array_values($menu));

} catch (\PDOException $e) {
    echo json_encode(array("error" => $e->getMessage()));
}
?>