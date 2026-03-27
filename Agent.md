# Agent 摘要

## 專案概覽

這是一個 PHP/MSSQL 的眼鏡門市 POS 管理系統專案，包含後台管理與前端展示功能。專案內已建立 `ContPOS/` 管理模組，並且目前正在調整共用 CRUD、tab 工作區呈現與頁面佈局。

## 目前進度

- `Stores.php` / `Stores_api.php`：門市管理 CRUD 功能已實作，操作欄靠右、可匯出 CSV、可搜尋
- `Staffs.php` / `Staffs_api.php`：員工管理 CRUD 已建立，已移除無需的 Role 同步邏輯
- `ContPOS/index.php`：多模組 tab 工作區、側邊選單與手機漢堡選單已調整
- `ContPOS/header.php` / `ContPOS/footer.php`：已同步選單 active 樣式與 footer 固定底部

## 開發重點

- 依照使用者需求保持 CRUD 模組簡潔，避免增加不必要的關聯或同步邏輯
- 盡量維持後台頁面可用高度，避免 iframe 內容底部出現多餘空白
- 針對桌面與手機行為分別處理選單樣式與 active 顯示

## 備註

- 若要從另一台電腦還原專案，可依照 `README.md` 中步驟進行
- 本檔案主要作為專案狀態與開發目標的快速說明
