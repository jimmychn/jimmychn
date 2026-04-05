<?php
/**
 * 檔案：api/get_global_inventory.php
 * 功能：總部專用 - 全門市庫存分布對照表
 */
session_start();
require_once 'db_config.php';
header('Content-Type: application/json');

// 權限檢查：僅限總部人員 (STORE01) 查看
if (!isset($_SESSION['user']) || $_SESSION['user']['store_id'] !== 'STORE01') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => '權限不足，僅限總部檢視']);
    exit;
}

try {
    /**
     * 核心 SQL 邏輯：
     * 使用 SUM(CASE WHEN ...) 將不同門市的庫存橫向展開。
     * 這樣前端畫表格時，每一列就是一個產品，橫向是各店數量。
     */
    $sql = "
        SELECT 
            p.sku, 
            p.name, 
            p.spec,
            SUM(CASE WHEN i.store_id = 'STORE01' THEN i.stock_qty ELSE 0 END) AS stock_taipei,
            SUM(CASE WHEN i.store_id = 'STORE01' THEN i.stock_qty ELSE 0 END) AS stock_taichung, -- 依此類推
            SUM(i.stock_qty) AS global_total
        FROM products p
        LEFT JOIN inventory i ON p.id = i.product_id
        WHERE p.is_active = 1
        GROUP BY p.id
        ORDER BY p.sku ASC
    ";

    $stmt = $pdo->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $data,
        'update_time' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
