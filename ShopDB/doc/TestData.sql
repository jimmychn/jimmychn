-- 1. 插入門市
INSERT INTO `stores` (store_id,store_name,store_head) VALUES 
('A00','總公司','A00'), 
('A01','公館店','A00'),
('A05','板橋店','A00'),
('A06','興隆店','A00'),
('A07','南港店','A00'),
('A08','內湖店','A00'),
('A09','淡水店','A00'),
('A10','新店店','A00'),
('A12','龍江店','A00'),
('A14','酒泉店','A00'),
('A15','大安店','A00'),
('A18','新莊店','A00'),
('A22','大墩店','A00'),
('A24','民安店','A00'),
('A25','蘆洲店','A00'),
('A26','南勢角店','A00'),
('A27','南港店','A00'),
('A32','大直店','A00'),
('A37','大吉店','A00'),
('A41','土城店','A00'),
('A42','重五店','A00'),
('A47','三峽店','A00');

-- 2. 插入角色
INSERT INTO `roles` (role_name) VALUES ('管理員'), ('一般店員');

-- 3. 插入權限項目
INSERT INTO `permissions` (module, action, perm_key) VALUES 
('products', 'view', 'products_view'),
('products', 'add', 'products_add'),
('orders', 'view', 'orders_view'),
('orders', 'print', 'orders_print'),
('sales', 'add', 'sales_add'),
('sales', 'return', 'sales_return'),
('inventory', 'adj', 'inventory_adj'),
('system', 'config', 'system_config');

-- 2. 綁定管理員角色 (Role ID: 1) 擁有所有權限
INSERT INTO `role_permissions` (`role_id`, `perm_id`) 
SELECT 1, id FROM permissions;

-- 4. 綁定角色與權限 (管理員全開，店員只有查看)
INSERT INTO `role_permissions` (role_id, perm_id) VALUES 
(1, 1), (1, 2), (1, 3), (1, 4), -- 管理員
(2, 1), (2, 3);                 -- 店員


-- 5. 建立測試帳號 (密碼 123456 的 hash)
INSERT INTO `users` (store_id, role_id, username, password, real_name) VALUES 
('STORE01', 1, 'admin', '$2y$10$Di3hF1iKTIdcFEiXEUaSyOZYXH4DzsIdE7GAaIBreA3mLWNZOZkbe', '老王'),
('STORE02', 2, 'user01', '$2y$10$Di3hF1iKTIdcFEiXEUaSyOZYXH4DzsIdE7GAaIBreA3mLWNZOZkbe', '小李');

-- 插入一個測試產品
INSERT INTO `products` (product_id, name, purchase_price, sales_price) VALUES 
('A001', '蘋果手機', 20000, 25000),
('B002', '無線耳機', 3000, 4500);
-- 2. 插入測試資料
INSERT INTO `customers` (store_id, customer_name, contact_person, tel, address) VALUES 
('STORE01', '零售客戶2', '現場', '00000000', '無'),
('STORE01', '台積電2', '張先生', '03-1234567', '新竹科學園區力行二路'),
('STORE01', '鴻海精密2', '郭小姐', '02-2268346', '新北市土城區自由街'),
('STORE01', '鴻海精密3', '郭小姐', '02-2268346', '新北市土城區自由街'),
('STORE01', '鴻海精密4', '郭小姐', '02-2268346', '新北市土城區自由街'),
('STORE01', '鴻海精密5', '郭小姐', '02-2268346', '新北市土城區自由街'),
('STORE01', '鴻海精密6', '郭小姐', '02-2268346', '新北市土城區自由街'),
('STORE01', '鴻海精密7', '郭小姐', '02-2268346', '新北市土城區自由街'),
('STORE01', '鴻海精密8', '郭小姐', '02-2268346', '新北市土城區自由街'),
('STORE01', '鴻海精密9', '郭小姐', '02-2268346', '新北市土城區自由街'),
('STORE01', '鴻海精密10', '郭小姐', '02-2268346', '新北市土城區自由街');

-- 插入測試供應商
INSERT INTO `suppliers` (store_id, supplier_name, contact_person, tel) VALUES 
('STORE01', '大同批發商', '王大同', '02-88889999'),
('STORE01', '聯強國際', '林小姐', '02-22334455');

