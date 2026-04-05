<?php
// 1. 基本設定
$uploadDir = 'uploads/invoices/';
$vendorId = $_POST['vendor_id']; // 假設從前端選擇廠商後傳入
$dateStr = date('Ymd');
$serial = "0001"; // 實際開發需從資料庫抓取當日最大流水號+1

// 2. 檔案命名處理
$originalExt = pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION);
$newFileName = "{$vendorId}_{$dateStr}_{$serial}.{$originalExt}";
$targetPath = $uploadDir . $newFileName;

if (move_uploaded_file($_FILES['import_file']['tmp_name'], $targetPath)) {
    echo "檔案上傳成功: {$newFileName}<br>";
    analyzeFile($targetPath, $vendorId);
}

// 3. 分析檔案與產生處理邏輯
function analyzeFile($filePath, $vendorId) {
    // 開啟檔案 (Tab 分隔)
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        
        // 讀取第一列 Header
        $headers = fgetcsv($handle, 0, "\t");
        
        // 這裡應該根據 $vendorId 從資料庫抓取 mapping
        // 暫以你提供的檔案欄位索引為例
        $colMap = [
            'store_id' => 0,      // Ship To
            'doc_date' => 3,      // SO Document Date
            'ref_no'   => 5,      // Delivery No
            'barcode'  => 9,      // EAN / UPC
            'qty'      => 11      // Shipped Quantity
        ];

        while (($row = fgetcsv($handle, 0, "\t")) !== FALSE) {
            // 讀取欄位資料並去除可能存在的特殊空白 (如檔案結尾的空白字元)
            $storeId  = trim($row[$colMap['store_id']]);
            $barcode  = trim($row[$colMap['barcode']]);
            $qty      = intval($row[$colMap['qty']]);
            $refNo    = trim($row[$colMap['ref_no']]);
            
            if (empty($barcode)) continue;

            // --- 邏輯 A: 檢查商品是否存在 ---
            // SELECT * FROM product_table WHERE barcode = '$barcode'
            // 如果不存在 -> INSERT INTO product_table ...

            // --- 邏輯 B: 檢查價格 (假設已取得商品資料 $product) ---
            $listPrice = 0;   // 定價
            $discount  = 0.9; // 假設折扣 (實際應從廠商合約抓取)
            $costPrice = 0;   // 實際進價 (應由定價 * 折扣，或商品表最後進價)
            $isGift    = ($qty > 0 && $costPrice == 0) ? 1 : 0; // 簡單判斷是否為贈品

            // --- 邏輯 C: 產生 SQL 或 執行 Insert ---
            echo "處理門市: {$storeId} | 條碼: {$barcode} | 數量: {$qty} ";
            
            if ($costPrice <= 0 && !$isGift) {
                echo " <span style='color:red;'>[警告: 價格為0，不可結案]</span>";
            } else {
                echo " <span style='color:green;'>[正常]</span>";
            }
            echo "<br>";

            /* 實際 SQL 執行範例:
            INSERT INTO purchase_detail (vendor_id, store_id, barcode, qty, cost_price, is_gift, ref_no)
            VALUES ('$vendorId', '$storeId', '$barcode', $qty, $costPrice, $isGift, '$refNo');
            */
        }
        fclose($handle);
    }
}

// 假設從進貨單單頭已取得：廠商代號 $vendor_id, 門市對照 $store_id
function processIncomingFile($filePath, $vendorId) {
    $handle = fopen($filePath, "r");
    $header = fgetcsv($handle, 0, "\t"); // 跳過標題列
    
    $rowCount = 0;
    while (($row = fgetcsv($handle, 0, "\t")) !== FALSE) {
        $rowCount++;
        // 1. 欄位清洗與提取
        $rawStoreId = trim($row[0]);  // Ship To (門市)
        $rawBarcode = trim($row[9]);  // EAN/UPC (條碼) - trim 會移除末尾空白
        $description = trim($row[6]); // Material Description (規格)
        $brandName = trim($row[8]);   // Brand Description (品牌)
        $qty = intval($row[11]);      // Shipped Quantity (數量)

        //因為這檔案的條碼欄位末端帶有 \xA0 (Non-breaking space)。普通的 trim() 可能修不掉。
		//而 $barcode = preg_replace('/[^\d]/', '', $row[9]); (只保留數字) 
		//這 $barcode = trim($row[9], " \t\n\r\0\x0B\xA0"); (才能濾掉不需要的字元)
		$barcode = trim($row[9], " \t\n\r\0\x0B\xA0"); 
        
        if (empty($rawBarcode)) continue;

        // 2. 商品編號邏輯 (建議從品牌+規格提取前綴，或由您定義)
        // 這裡暫以 Brand Code 或簡化名稱作為 product_id
        $productId = trim($row[7]); 

        // 3. 檢查並自動建立商品資料 (product_table)
        checkAndCreateProduct($productId, $rawBarcode, $description, $brandName, $vendorId);

        // 4. 取得價格 (邏輯：若價格為0且非贈品，需標記異常)
        $priceInfo = getProductPrice($productId, $rawBarcode, $vendorId);
        $costPrice = $priceInfo['cost'];
        $isGift = ($costPrice == 0) ? 1 : 0; 

        // 5. 產生匯入紀錄或直接寫入暫存表
        saveToPurchaseDetail([
            'store_id'   => $rawStoreId,
            'product_id' => $productId,
            'barcode'    => $Barcode,
            'qty'        => $qty,
            'cost'       => $costPrice,
            'is_gift'    => $isGift,
            'can_close'  => ($costPrice > 0 || $isGift) ? 1 : 0 // 是否可結案
        ]);
    }
    fclose($handle);
}

/**
 * 處理廠商進貨檔匯入 (Tab CSV 格式)
 */
function processVendorImport($filePath, $vendorId) {
    global $db; // 假設這是您的資料庫連線物件

    $handle = fopen($filePath, "r");
    // 讀取第一列 Header
    $headers = fgetcsv($handle, 0, "\t");

    // 建立一個陣列來儲存處理結果，用於前端顯示
    $importResults = [];

    while (($row = fgetcsv($handle, 0, "\t")) !== FALSE) {
        if (empty($row[0])) continue; // 跳過空行

        // 1. 欄位提取 (對應大陸眼鏡格式)
        $storeId   = trim($row[0]);  // Ship To
        $rawBarcode = trim($row[9], " \t\n\r\0\x0B\xA0"); // 清理條碼與特殊空白
        $description = trim($row[6]); // Material Description
        $brandName  = trim($row[8]);  // Brand Description
        $productId  = trim($row[7]);  // 廠商商品編號 (Brand Code)
        $qty        = intval($row[11]);
        $refNo      = trim($row[5]);  // Delivery No (出貨單號)

        // 2. 檢查商品表並自動建檔
        // 複合主鍵檢查: product_id + barcode
        $product = $db->query("SELECT id_type, list_price FROM product_table WHERE product_id = ? AND barcode = ?", [$productId, $rawBarcode]);

        if (!$product) {
            // 自動建檔邏輯
            $db->execute("INSERT INTO product_table (product_id, barcode, product_name, brand, vendor_id, id_type) 
                          VALUES (?, ?, ?, ?, ?, 'BARCODE')", 
                          [$productId, $rawBarcode, $description, $brandName, $vendorId]);
            $listPrice = 0;
            $idType = 'BARCODE';
        } else {
            $listPrice = $product['list_price'];
            $idType = $product['id_type'];
        }

        // 3. 價格與贈品檢查邏輯
        // 從系統取得該廠商的議價折扣 (假設為 0.6)
        $discount = 0.6; 
        $costPrice = ($listPrice > 0) ? ($listPrice * $discount) : 0;
        
        // 贈品判斷：如果系統算出來進價為 0，且該項目在 Excel 中或是人工標記為贈品
        $isGift = ($costPrice == 0) ? 1 : 0;

        // 4. 判斷是否允許結案
        $canClose = true;
        if ($costPrice <= 0 && $isGift == 0) {
            $canClose = false; // 價格為0且不是贈品，不可結案
        }

        // 5. 準備寫入進貨單單身 (Purchase Detail)
        // 這裡 identifier_code 統一使用 $rawBarcode (因為隱形眼鏡選用條碼管理)
        $importResults[] = [
            'store_id'   => $storeId,
            'product_id' => $productId,
            'identifier' => $rawBarcode,
            'qty'        => $qty,
            'cost_price' => $costPrice,
            'is_gift'    => $isGift,
            'ref_no'     => $refNo,
            'can_close'  => $canClose
        ];
    }
    fclose($handle);

    return $importResults;
}

// --- 執行範例 ---
/*
$results = processVendorImport('uploads/CONTINENTAL_20260401_0001.CSV', 'V001');
foreach ($results as $item) {
    if (!$item['can_close']) {
        echo "警告：條碼 {$item['identifier']} 價格異常，此單據將無法結案！<br>";
    }
}
*/

<?php
/**
 * 處理大陸眼鏡進貨 CSV 並依門市自動拆單
 */
function processImportAndSplitByStore($filePath, $vendorId) {
    global $db;
    
    // 1. 讀取並按門市分群
    $handle = fopen($filePath, "r");
    fgetcsv($handle, 0, "\t"); // 跳過 Header
    
    $storeGroups = [];
    while (($row = fgetcsv($handle, 0, "\t")) !== FALSE) {
        if (empty($row[0])) continue;
        $storeId = trim($row[0]);
        $storeGroups[$storeId][] = $row;
    }
    fclose($handle);

    $dateStr = date('Ymd');
    $p_count = 1;

    // 2. 遍歷每個門市進行拆單處理
    foreach ($storeGroups as $storeId => $rows) {
        // 生成該門市的 PO 單號 (例如: PO20260401-001)
        $poId = "PO{$dateStr}-" . str_pad($p_count++, 3, '0', STR_PAD_LEFT);
        
        // A. 建立進貨單單頭 (Purchase Order Header)
        // 狀態預設為 'Pending' (待處理)，由門市人員確認後才改為 'Finished'
        $db->execute("INSERT INTO purchase_header (po_id, vendor_id, store_id, create_date, status) VALUES (?, ?, ?, NOW(), 'pending')", 
                     [$poId, $vendorId, $storeId]);

        $lineNo = 1; // 每個 po_id 的單身序號重新計數

        foreach ($rows as $row) {
            $rawBarcode = preg_replace('/[^\d\w]/', '', $row[9]); // 清理條碼
            $productId  = trim($row[7]);
            $description = trim($row[6]);
            $brandName   = trim($row[8]);
            $qty         = intval($row[11]);
            $refNo       = trim($row[5]);

            // B. 檢查並自動建立商品資料 (若不存在)
            $product = $db->query("SELECT list_price, id_type FROM product_table WHERE product_id = ? AND barcode = ?", [$productId, $rawBarcode]);
            
            if (!$product) {
                $db->execute("INSERT INTO product_table (product_id, barcode, product_name, brand, vendor_id, id_type) VALUES (?, ?, ?, ?, ?, 'BARCODE')", 
                             [$productId, $rawBarcode, $description, $brandName, $vendorId]);
                $listPrice = 0;
            } else {
                $listPrice = $product['list_price'];
            }

            // C. 價格與贈品邏輯
            $costPrice = $listPrice * 0.6; // 這裡改為您系統的折扣邏輯
            $isGift = ($costPrice <= 0) ? 1 : 0;

            // D. 寫入進貨單單身 (使用 po_id + no 作為 Primary Key)
            $db->execute("INSERT INTO purchase_detail (po_id, no, store_id, product_id, identifier_code, qty, unit_price, cost_price, is_gift, ref_no) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", 
                          [$poId, $lineNo++, $storeId, $productId, $rawBarcode, $qty, $listPrice, $costPrice, $isGift, $refNo]);
        }
    }
    return true;
}


// 內部工具：檢查商品與條碼是否存在，不存在則新增
function checkAndCreateProduct($pid, $barcode, $desc, $brand, $v_id) {
    global $db;
    // 查詢 product_table 是否已有此 (product_id, barcode)
    // SQL: SELECT count(*) FROM product_table WHERE product_id = ? AND barcode = ?
    
    // 如果沒有，則執行 INSERT
    /*
    INSERT INTO product_table (product_id, barcode, product_name, brand, vendor_id)
    VALUES ('$pid', '$barcode', '$desc', '$brand', '$v_id')
    */
}

/**
 * 處理大陸眼鏡進貨 CSV 並產生 SQL 資料
 */
function processImportToSql($filePath, $poIdPrefix, $vendorId) {
    $handle = fopen($filePath, "r");
    fgetcsv($handle, 0, "\t"); // 跳過 Header

    $lineNo = 1; // 初始化項目編號
    $sqlBatch = [];

    while (($row = fgetcsv($handle, 0, "\t")) !== FALSE) {
        if (empty($row[0])) continue;

        // 1. 提取原始資料
        $storeId   = trim($row[0]);  // Ship To
        $rawBarcode = preg_replace('/[^\d\w]/', '', $row[9]); // 清理條碼
        $productId  = trim($row[7]);  // 廠商商品代碼
        $qty        = intval($row[11]);
        
        // 2. 價格邏輯 (需從商品表抓取，若無則設為 0)
        // 假設 $listPrice 是從系統 product_table 查到的
        $listPrice = getProductListPrice($productId, $rawBarcode); 
        $costPrice = $listPrice * 0.6; // 假設 6 折
        $isGift    = ($costPrice <= 0) ? 1 : 0;

        // 3. 建立 SQL (產生 po_id，您可以按門市拆單，或是同一張單多門市)
        // 這裡示範同一張 po_id，使用 no 作為唯一碼
        $sqlBatch[] = sprintf(
            "INSERT INTO purchase_detail (po_id, no, store_id, product_id, identifier_code, qty, unit_price, cost_price, is_gift) 
             VALUES ('%s', %d, '%s', '%s', '%s', %d, %f, %f, %d);",
            $poIdPrefix, 
            $lineNo++, // 每次執行後遞增
            $storeId,
            $productId,
            $rawBarcode,
            $qty,
            $listPrice,
            $costPrice,
            $isGift
        );
    }
    fclose($handle);
    return $sqlBatch;
}

// 模擬從資料庫取價格
function getProductListPrice($pid, $barcode) {
    // 實務上在此處下 SELECT 語句
    // 如果查不到，回傳 0，這會觸發之後的「不可結案」判斷
    return 1000.0; 
}
?>