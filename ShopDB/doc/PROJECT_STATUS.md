這是一份針對您開發的 PHP 響應式進銷存管理系統 所整理的完整專案盤點文件。您可以將此內容儲存為 PROJECT_STATUS.md。
------------------------------
## 📋 專案開發進度與架構盤點報告
本專案是一個採用 Vanilla PHP (純 PHP) 搭配 Bootstrap 5 開發的響應式進銷存系統。系統核心採用 前後端分離 邏輯，透過 Fetch API 進行 JSON 資料交換，完美支援電腦與手機操作。
------------------------------
## 📂 檔案清單與完成進度## 1. 核心底層 (Core Configuration)

| 檔名 | 類型 | 說明 | 進度 |
|---|---|---|---|
| api/db_config.php | PHP | PDO 資料庫連線設定 (含錯誤例外處理) | ✅ 100% |
| api/stock_functions.php | PHP | 統一庫存異動邏輯 (updateStock)，含日誌自動寫入 | ✅ 100% |

## 2. 權限與驗證 (Auth System)

| 檔名 | 類型 | 說明 | 進度 |
|---|---|---|---|
| login.html | HTML/JS | 響應式登入介面 (支援手機大按鈕) | ✅ 100% |
| api/login_api.php | PHP | 帳密 password_verify 驗證與 Session 寫入 | ✅ 100% |
| api/check_session.php | PHP | 各頁面啟動時的權限門檻檢查 | ✅ 100% |

## 3. 客戶與供應商管理 (Partners)

| 檔名 | 類型 | 說明 | 進度 |
|---|---|---|---|
| customer_list.html | HTML/JS | 客戶清單、分頁、搜尋、停用/恢復切換 | ✅ 100% |
| supplier_list.html | HTML/JS | 供應商清單、分頁、搜尋、資料維護 | ✅ 100% |
| api/get_customers.php | PHP | 客戶資料分頁查詢 API | ✅ 100% |
| api/save_customer.php | PHP | 客戶新增與修改 (一對多門市隔離) | ✅ 100% |
| api/get_suppliers.php | PHP | 供應商清單查詢 API | ✅ 100% |
| api/save_supplier.php | PHP | 供應商資料維護 API | ✅ 100% |
| api/update_customer_status.php | PHP | 客戶軟刪除 (is_active) 狀態切換 | ✅ 100% |

## 4. 產品與多倉庫存 (Inventory)

| 檔名 | 類型 | 說明 | 進度 |
|---|---|---|---|
| product_list.html | HTML/JS | 產品清單、本分店即時庫存顯示、庫存預警顏色 | ✅ 100% |
| stock_adjustment.html | HTML/JS | 庫存盤點作業、手動調整數量、自動計算差異 | ✅ 100% |
| api/get_products.php | PHP | 跨表查詢產品與特定門市在庫數 | ✅ 100% |
| api/save_product.php | PHP | 產品資料維護與初始庫存導入交易 | ✅ 100% |
| api/save_adjustment.php | PHP | 盤點存檔、更新 inventory、產生 ADJ 日誌 | ✅ 100% |
| api/get_stock_logs.php | PHP | 產品庫存異動流水帳 (含操作人姓名) | ✅ 100% |

## 5. 進銷貨與退貨 (Trading)

| 檔名 | 類型 | 說明 | 進度 |
|---|---|---|---|
| sales_order.html | HTML/JS | 新增銷貨單 (自動計算折扣、彈窗選品) | ✅ 100% |
| sales_order_list.html | HTML/JS | 銷貨歷史列表 (含權限過濾退貨按鈕) | ✅ 100% |
| sales_return.html | HTML/JS | 銷貨退回 (一鍵帶入明細、庫存回補) | ✅ 100% |
| purchase_order.html | HTML/JS | 進貨處理 (自動帶入成本價、增加庫存) | ✅ 100% |
| purchase_list.html | HTML/JS | 進貨歷史列表 | ✅ 100% |
| purchase_return.html | HTML/JS | 進貨退回 (採購退出、扣除庫存) | ✅ 100% |
| api/save_order.php | PHP | 銷貨存檔交易 (Transaction)、扣庫存 | ✅ 100% |
| api/save_return.php | PHP | 銷回存檔交易、庫存回補、權限校驗 | ✅ 100% |
| api/save_purchase.php | PHP | 進貨存檔交易、增加庫存 | ✅ 100% |
| api/get_order_details.php | PHP | 抓取單據明細供退貨作業自動填入 | ✅ 100% |

## 6. 報表、列印與管理 (Admin)

| 檔名 | 類型 | 說明 | 進度 |
|---|---|---|---|
| index.html | HTML/JS | 儀表板、Chart.js 銷售走勢圖、多店切換 | ✅ 100% |
| customer_report.html | HTML/JS | 客戶對帳報表 (應收帳款統計) | ✅ 100% |
| supplier_report.html | HTML/JS | 供應商對帳報表 (應付帳款統計) | ✅ 100% |
| print_order.html | HTML/JS | 萬用列印頁 (支援 A4/A5/58/80/110mm) | ✅ 100% |
| user_mgmt.html | HTML/JS | 員工帳號管理、權限角色分配、密碼重設 | ✅ 100% |
| api/get_reports.php | PHP | 低庫存預警與熱銷排行數據 | ✅ 100% |
| api/get_dashboard_chart.php | PHP | 儀表板近七日銷售統計數據 | ✅ 100% |

------------------------------
## ⚠️ 建議增加的功能 (優化方向)

   1. 單據狀態管理：目前單據存檔即生效。建議增加「草稿/待審核/已結案」狀態，防止錯誤單據直接影響庫存。
   2. 批次導入功能：增加 Excel 匯入產品與客戶的功能 (使用 PhpSpreadsheet 庫)，方便初期快速建檔。
   3. 庫存警示主動化：在 index.html 增加自動發送 Email 或 Line 通知的功能，當庫存低於安全水位時自動提醒。
   4. 操作軌跡 (Audit Log)：除了庫存日誌，建議增加使用者操作日誌（記錄誰在哪個時間修改了客戶地址或產品價格）。

------------------------------
## 🛠 維護說明

* 資料一致性：所有影響庫存的操作必須呼叫 api/stock_functions.php 中的 updateStock()，切勿直接操作 inventory 資料表。
* 列印調整：若要修改熱感紙列印格式，請調整 print_order.html 中的 CSS .format-80mm 等寬度定義。
* 權限變更：目前的權限判斷寫在 roles 表，若需增加功能權限，請在 permissions 表新增 key 並對應至 role_permissions。

------------------------------
專案交付狀態：核心功能全數達成。
您可以將這份文件作為專案的驗收參考，祝您的進銷存系統運行順利！後續如有細節調整需求，隨時歡迎提出。

