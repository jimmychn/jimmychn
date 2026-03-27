# ContNew 眼鏡門市 POS 系統

本專案為 PHP + MSSQL 的眼鏡門市管理系統，包含門市、員工、客戶、交易、檢查紀錄、系統日誌等模組。

## 專案架構

- `ContPOS/`：後台管理系統入口與 CRUD 模組檔案
- `Admin/`：管理介面相關靜態頁面與資源
- `css/`, `js/`, `img/`, `lib/`：前端樣式、腳本與資源
- `templates/`, `templates_c/`：Smarty 模板與編譯頁面
- `upload/`：上傳檔案儲存目錄
- `vendor/`：Composer 相依套件
- `composer.json`, `composer.lock`：PHP 相依套件設定
- `.vscode/`：VS Code 工作區設定

## 目前環境

- PHP 5.6 相容
- MSSQL 2014
- Bootstrap 5 + jQuery
- Smarty 模板引擎

## 還原步驟

1. 將專案從 GitHub clone 到新電腦
2. 安裝 PHP 與對應 MSSQL driver
3. 匯入資料庫結構與內容（若有 `DB.sql` 或線上備份）
4. 更新 `config.php` 中的資料庫連線設定
5. 若使用 Composer，執行：
   ```bash
   composer install
   ```
6. 開啟 VS Code，若需要可安裝 PHP 和前端相關擴充元件

## VS Code 設定

目前專案根目錄有 `.vscode/settings.json`，目前內容如下：

- `editor.mouseWheelZoom`: `true`

如果要備份額外擴充元件清單，可自行在 VS Code 中導出或使用 `extensions.json`。

## 注意事項

- 本專案當前尚未包含 `README.md` 與 `Agent.md`，已新增初版
- 若要推上 GitHub，請新增遠端 repository 並執行 `git remote add origin <your-url>`
- 建議在推送之前檢查 `.gitignore`，排除 `templates_c/`、上傳檔案與第三方憑證
