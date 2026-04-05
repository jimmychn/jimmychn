CREATE TABLE `functions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `GID` int(11) DEFAULT '0',
  `FID` int(11) DEFAULT '0',
  `GroupName` varchar(50) NOT NULL,
  `FuncName` varchar(50) DEFAULT NULL,
  `URL` varchar(255) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL COMMENT 'Font Awesome Class',
  PRIMARY KEY (`id`)
);

INSERT INTO `functions` (`GID`, `FID`, `GroupName`, `FuncName`, `URL`, `icon`) VALUES
-- 儀表板
(0,0,'儀表板', '', 'dashboard.html', 'fa-chart-line'),
-- 營運中心
(1,0,'營運中心', NULL, NULL, 'fa-gauge'),
(1,1,'營運中心', '銷貨管理', 'sales_order_list.html', 'fa-cart-shopping'),
(1,2,'營運中心', '進貨管理', 'purchase_list.html', 'fa-truck-ramp-box'),
(1,3,'營運中心', '調撥管理', 'transfer_list.html', 'fa-right-left'),

-- 基本資料
(2,0,'基本資料', NULL, NULL, 'fa-folder-open'),
(2,1,'基本資料', '門市管理', 'store_list.html', 'fa-shop'),
(2,2,'基本資料', '客戶管理', 'customer_list.html', 'fa-users'),
(2,3,'基本資料', '供應商管理', 'supplier_list.html', 'fa-handshake'),
(2,4,'基本資料', '產品管理', 'product_list.html', 'fa-box-archive'),

-- 庫存管理
(3,0,'庫存管理', NULL, NULL, 'fa-boxes-stacked'),
(3,1,'庫存管理', '門市庫存', 'global_inventory.html', 'fa-warehouse'),
(3,2,'庫存管理', '門市調撥', 'stock_transfer.html', 'fa-truck-moving'),
(3,3,'庫存管理', '庫存盤點', 'stock_adjustment.html', 'fa-clipboard-check'),

-- 財務對帳
(4,0,'財務對帳', NULL, NULL, 'fa-file-invoice-dollar'),
(4,1,'財務對帳', '應收帳款 (客戶)', 'customer_report.html', 'fa-money-bill-trend-up'),
(4,2,'財務對帳', '應付帳款 (廠商)', 'supplier_report.html', 'fa-money-bill-transfer'),
(4,3,'財務對帳', '門市內部對帳', 'internal_report.html', 'fa-building-columns'),

-- 系統管理
(4,0,'系統管理', NULL, NULL, 'fa-gears'),
(4,1,'系統管理', '帳號與黑名單', 'system_config.html', 'fa-user-gear'),
(4,2,'系統管理', '權限勾選牆', 'permission_setup.html', 'fa-shield-halved'),

-- 安全登出
(5,0,'安全登出', NULL, 'api/logout.php', 'fa-power-off');


-- 1. 登入嘗試紀錄 (用於計算錯誤次數與記錄裝置資訊)
CREATE TABLE `login_attempts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ip_address` VARCHAR(45) NOT NULL,
  `username` VARCHAR(50),
  `browser` VARCHAR(255),
  `device` VARCHAR(50),
  `status` ENUM('success', 'fail') NOT NULL,
  `attempt_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`ip_address`),
  INDEX (`attempt_time`)
);

-- 2. IP 黑名單 (管理封鎖狀態)
CREATE TABLE `ip_blacklist` (
  `ip_address` VARCHAR(45) PRIMARY KEY,
  `block_type` ENUM('temporary', 'permanent') NOT NULL,
  `unlock_at` DATETIME NULL, -- 臨時封鎖的到期時間
  `reason` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `stores` (
  `store_id` VARCHAR(20) NOT NULL PRIMARY KEY,
  `store_name` VARCHAR(50) NOT NULL, -- 門市名稱
  `address` VARCHAR(255),
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `roles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `role_name` VARCHAR(50) NOT NULL, -- 如：Admin, Manager, Staff
  `description` VARCHAR(100)
);

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `store_id` INT NOT NULL,          -- 所屬門市
  `role_id` INT NOT NULL,           -- 角色ID
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL, -- 存放 password_hash() 後的字串
  `real_name` VARCHAR(50),          -- 真實姓名
  `is_active` TINYINT(1) DEFAULT 1,
  `last_login` DATETIME,
  FOREIGN KEY (`store_id`) REFERENCES `stores`(`id`),
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`)
);

CREATE TABLE `permissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `module` VARCHAR(50) NOT NULL,    -- 模組：products, orders, users
  `action` VARCHAR(50) NOT NULL,    -- 動作：view, add, edit, delete, print
  `perm_key` VARCHAR(100) UNIQUE    -- 組合鍵：products_view, orders_print
);

CREATE TABLE `role_permissions` (
  `role_id` INT NOT NULL,
  `perm_id` INT NOT NULL,
  PRIMARY KEY (`role_id`, `perm_id`),
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`),
  FOREIGN KEY (`perm_id`) REFERENCES `permissions`(`id`)
);

-- 1. 銷貨單頭 (Sales Orders) 
CREATE TABLE `sales_orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_no` VARCHAR(20) NOT NULL UNIQUE, -- 單號，如 SO20231027001
  `store_id` VARCHAR(20) NOT NULL,        -- 門市代碼
  `customer_id` INT NOT NULL,             -- 客戶ID
  `order_date` DATE NOT NULL,             -- 單據日期
  `total_amount` DECIMAL(12,2) DEFAULT 0, -- 總金額
  `discount_amount` DECIMAL(12,2) DEFAULT 0,	-- 折扣
  `final_amount` DECIMAL(12,2) DEFAULT 0,		-- 實際支付金額
  `status` TINYINT(1) DEFAULT 1,
  `remark` TEXT,                          -- 備註
  `created_by` INT NOT NULL,              -- 建立者(User ID)
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`order_no`),
  INDEX (`store_id`)
);

-- 2. 銷貨單身 (Sales Order Items)
CREATE TABLE `sales_order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,                -- 關聯單頭的 id
  `product_id` INT NOT NULL,              -- 產品 ID
  `qty` INT NOT NULL,                     -- 銷售數量
  `price` DECIMAL(12,2) NOT NULL,         -- 銷售單價
  `subtotal` DECIMAL(12,2) NOT NULL,      -- 小計
  FOREIGN KEY (`order_id`) REFERENCES `sales_orders`(`id`) ON DELETE CASCADE
);

-- 2. 建立銷貨退回單頭 (Sales Returns)
CREATE TABLE `sales_returns` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `return_no` VARCHAR(20) NOT NULL UNIQUE,
  `origin_order_no` VARCHAR(20) NOT NULL, -- 關聯的原銷貨單號
  `store_id` VARCHAR(20) NOT NULL,
  `total_return_amount` DECIMAL(12,2) DEFAULT 0,
  `reason` VARCHAR(255),
  `created_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. 銷貨退回明細 (Return Items)
CREATE TABLE `sales_return_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `return_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `qty` INT NOT NULL, -- 退回數量
  `price` DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (`return_id`) REFERENCES `sales_returns`(`id`)
);


-- 1. 客戶資料表 (Customers)
CREATE TABLE `customers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `store_id` VARCHAR(20) NOT NULL,        -- 所屬門市
  `customer_name` VARCHAR(100) NOT NULL,  -- 公司或客戶名稱
  `contact_person` VARCHAR(50),           -- 聯絡人
  `tel` VARCHAR(20),                      -- 電話/手機
  `address` VARCHAR(255),                 -- 送貨地址
  `is_active` TINYINT(1) DEFAULT 1,       -- 是否往來中
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`customer_name`),
  INDEX (`tel`)
);

-- 1. 產品基本資料 (不含庫存量)
CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sku` VARCHAR(50) NOT NULL UNIQUE,      -- 條碼/品號
  `name` VARCHAR(100) NOT NULL,           -- 品名
  `spec` VARCHAR(100),                    -- 規格/型號
  `unit` VARCHAR(20) DEFAULT '個',         -- 單位 (個, 支, 箱)
  `purchase_price` DECIMAL(12,2) DEFAULT 0, -- 進貨進價 (成本)
  `sales_price` DECIMAL(12,2) DEFAULT 0,    -- 建議售價
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. 門市庫存表 (存放各店實際庫存)
CREATE TABLE `inventory` (
  `product_id` INT NOT NULL,
  `store_id` VARCHAR(20) NOT NULL,
  `batch_no` VARCHAR(20) NULL,
  `stock_qty` INT DEFAULT 0,              -- 該店目前庫存
  `remark` VARCHAR(200) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  PRIMARY KEY (`product_id`, `store_id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
);


-- 門市商品庫存表 (支援多門市、多度數組合)
CREATE TABLE store_stock_serial (
    store_id VARCHAR(20) NOT NULL,       -- 門市代號 (對應 Excel 的 Ship To)
    product_id VARCHAR(50) NOT NULL,     -- 商品編號 (例如：1-DAY-MOIST)
    barcode VARCHAR(50) NOT NULL,        -- 國際條碼 (EAN/UPC，對應不同度數)
    batch_no VARCHAR(20) DEFAULT '',     -- 批號 (如有需要)
    stock_qty INT DEFAULT 0,             -- 現有庫存量
    list_price DECIMAL(10,2) DEFAULT 0,  -- 定價
    cost_price DECIMAL(10,2) DEFAULT 0,  -- 實際進價
    is_gift TINYINT(1) DEFAULT 0,        -- 是否為贈品
    last_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (store_id, product_id, barcode) -- 包含門市的複合主鍵
);

-- 門市批序號庫存表
CREATE TABLE store_stock_ledger (
    store_id VARCHAR(20) NOT NULL,
    product_id VARCHAR(50) NOT NULL,
    identifier_code VARCHAR(50) NOT NULL, -- 條碼、序號或批號（擇一管理）
    stock_qty INT DEFAULT 0,
    -- 以下欄位建議保留在進貨單身(Detail)，庫存表僅更新數量與成本參考
    cost_price DECIMAL(10,2) DEFAULT 0, 
    PRIMARY KEY (store_id, product_id, identifier_code)
);

-- 3. 庫存異動日誌 (查帳用，非常重要！)
CREATE TABLE `stock_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `store_id` VARCHAR(20) NOT NULL,
  `type` ENUM('IN', 'OUT', 'ADJ') NOT NULL, -- 進貨、銷貨、調整
  `qty` INT NOT NULL,                       -- 變動數量
  `relation_no` VARCHAR(50),                -- 關聯單號 (如銷貨單號)
  `remark` VARCHAR(255),
  `created_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 1. 供應商資料表 (Suppliers)
CREATE TABLE `suppliers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `store_id` VARCHAR(20) NOT NULL,
  `supplier_name` VARCHAR(100) NOT NULL,
  `contact_person` VARCHAR(50),
  `tel` VARCHAR(20),
  `address` VARCHAR(255),
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. 進貨單頭 (Purchase Orders)
CREATE TABLE `purchase_orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `purchase_no` VARCHAR(20) NOT NULL UNIQUE, -- 單號如 PO20231027001
  `store_id` VARCHAR(20) NOT NULL,
  `supplier_id` INT NOT NULL,
  `purchase_date` DATE NOT NULL,
  `total_amount` DECIMAL(12,2) DEFAULT 0,
  `status` TINYINT(1) DEFAULT 1,
  `remark` TEXT,
  `created_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. 進貨單身 (Purchase Order Items)
CREATE TABLE `purchase_order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `purchase_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `qty` INT NOT NULL,
  `price` DECIMAL(12,2) NOT NULL, -- 進貨成本價
  `subtotal` DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (`purchase_id`) REFERENCES `purchase_orders`(`id`) ON DELETE CASCADE
);

CREATE TABLE purchase_detail (
    po_id VARCHAR(20) NOT NULL,          -- 進貨單號 (單頭關聯)
    no INT NOT NULL,                     -- 項目編號 (單身唯一流水號，如 1, 2, 3...)
    store_id VARCHAR(20) NOT NULL,       -- 門市代號 (由 Excel 的 Ship To 取得)
    product_id VARCHAR(50) NOT NULL,     -- 商品編號
    identifier_code VARCHAR(50) NOT NULL, -- 條碼/序號/批號 (擇一管理)
    qty INT DEFAULT 0,                   -- 數量
    unit_price DECIMAL(10,2) DEFAULT 0,  -- 定價
    cost_price DECIMAL(10,2) DEFAULT 0,  -- 實際進價 (可由定價*折扣算出)
    is_gift TINYINT(1) DEFAULT 0,        -- 是否為贈品
    PRIMARY KEY (po_id, no)              -- 嚴謹的唯一性判斷
);

-- 1. 進貨退回單頭 (Purchase Returns)
CREATE TABLE `purchase_returns` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `return_no` VARCHAR(20) NOT NULL UNIQUE, -- 退回單號如 PR20231027001
  `origin_purchase_no` VARCHAR(20) NOT NULL, -- 關聯的原進貨單號
  `store_id` VARCHAR(20) NOT NULL,
  `supplier_id` INT NOT NULL,
  `return_date` DATE NOT NULL,
  `total_amount` DECIMAL(12,2) DEFAULT 0,
  `remark` TEXT,
  `created_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. 進貨退回單身 (Purchase Return Items)
CREATE TABLE `purchase_return_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `return_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `qty` INT NOT NULL, -- 退回數量
  `price` DECIMAL(12,2) NOT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (`return_id`) REFERENCES `purchase_returns`(`id`) ON DELETE CASCADE
);

/**
 * 檔案：database_adjustment.sql
 * 功能：建立庫存盤點相關資料表
 */

-- 1. 盤點/調整單頭 (Stock Adjustment Header)
CREATE TABLE `stock_adjustments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `adj_no` VARCHAR(20) NOT NULL UNIQUE, -- 調整單號 ADJ20231027001
  `store_id` VARCHAR(20) NOT NULL,      -- 所屬門市
  `adj_date` DATE NOT NULL,             -- 調整日期
  `reason` VARCHAR(255),                -- 整單備註 (如: 年終大盤點)
  `created_by` INT NOT NULL,            -- 操作人
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. 盤點/調整單身 (Stock Adjustment Items)
CREATE TABLE `stock_adjustment_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `adj_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `book_qty` INT NOT NULL,              -- 調整前的帳面數量
  `actual_qty` INT NOT NULL,            -- 實際清點的數量
  `adj_qty` INT NOT NULL,               -- 調整差異數 (actual - book)
  `item_remark` VARCHAR(100),           -- 單項備註 (如: 報廢、遺失)
  FOREIGN KEY (`adj_id`) REFERENCES `stock_adjustments`(`id`) ON DELETE CASCADE
);

-- 1. 調撥單頭 (Stock Transfer Header)
CREATE TABLE `stock_transfers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `transfer_no` VARCHAR(20) NOT NULL UNIQUE, -- 單號如 TR20231027001
  `from_store_id` VARCHAR(20) NOT NULL,      -- 轉出門市
  `to_store_id` VARCHAR(20) NOT NULL,        -- 轉入門市
  `transfer_date` DATE NOT NULL,
  `status` TINYINT(1) DEFAULT 1,			 -- 確認轉出
  `remark` VARCHAR(255),
  `created_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. 調撥單身 (Stock Transfer Items)
CREATE TABLE `stock_transfer_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `transfer_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `qty` INT NOT NULL,                        -- 調撥數量
  FOREIGN KEY (`transfer_id`) REFERENCES `stock_transfers`(`id`) ON DELETE CASCADE
);
