<?php
session_start();
require_once 'db_config.php';
header('Content-Type: application/json');

$store_id = $_SESSION['user']['store_id'];

try {
    // 功能 A：低庫存預警 (假設庫存 <= 5 即預警，實務上可在 products 加設安全庫存欄位)
    $lowStockSql = "SELECT p.sku, p.name, i.stock_qty, p.unit 
                    FROM products p
                    JOIN inventory i ON p.id = i.product_id
                    WHERE i.store_id = ? AND i.stock_qty <= 5 AND p.is_active = 1
                    ORDER BY i.stock_qty ASC";
    $stmt1 = $pdo->prepare($lowStockSql);
    $stmt1->execute(array($store_id));
    $lowStock = $stmt1->fetchAll();

    // 功能 B：本月銷量排行榜 (Top 5)
    $salesTopSql = "SELECT p.name, SUM(oi.qty) as total_qty, SUM(oi.subtotal) as total_sales
                    FROM sales_order_items oi
                    JOIN sales_orders o ON oi.order_id = o.id
                    JOIN products p ON oi.product_id = p.id
                    WHERE o.store_id = ? AND MONTH(o.order_date) = MONTH(CURRENT_DATE())
                    GROUP BY p.id
                    ORDER BY total_qty DESC LIMIT 5";
    $stmt2 = $pdo->prepare($salesTopSql);
    $stmt2->execute(array($store_id));
    $topSales = $stmt2->fetchAll();

    echo json_encode(array(
        'status' => 'success',
        'low_stock' => $lowStock,
        'top_sales' => $topSales
    ));

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
}
