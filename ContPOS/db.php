<?php
// db.php
$serverName = "localhost";
$DBName = "ContPOS";
$DBUser = "test";
$DBPwd = "test1234";

try {
    $dsn = "sqlsrv:Server=$serverName;Database=$DBName";
    $pdo = new PDO($dsn, $DBUser, $DBPwd);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "連線成功！";
} catch (PDOException $e) {
    echo "連線失敗: " . $e->getMessage();
}

$debug = 1;	//偵錯用
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$storeID  = isset($_SESSION['storeID']) ? $_SESSION['storeID'] : '';


function writeSystemLog($pdo, $username, $storeID, $moduleName, $activity, $status, $ip, $errorMessage = null) {
    $sql = "INSERT INTO SystemLogs 
            (Username, StoreID, ModuleName, Activity, Status, IPAddress, ErrorMessage)
            VALUES (:username, :storeID, :moduleName, :activity, :status, :ip, :errorMessage)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':username'    => $username,
        ':storeID'     => $storeID,
        ':moduleName'  => $moduleName,
        ':activity'    => $activity,
        ':status'      => $status,
        ':ip'          => $ip,
        ':errorMessage'=> $errorMessage
    ]);
}

function dbExecute($pdo, $sql, $params, $moduleName, $activity, $username, $storeID, $extraBindings = []) {
	// 偵錯用字串
	$paramsString 			= is_array($params) ? json_encode($params, JSON_UNESCAPED_UNICODE) : (string)$params;
	$extraBindingsString 	= is_array($extraBindings) ? json_encode($extraBindings, JSON_UNESCAPED_UNICODE) : (string)$extraBindings;

    try {
        $stmt = $pdo->prepare($sql);
		// 自動綁定一般參數PDO的位置參數是1-based(而PHP陣列索引是0-based)，所以第一個 ? 要用 bindValue(1, ...)或bindValue($key+1,$val, ...)。
		// $params = [':username' => '榮',':email' => 'test@example.com'];
        foreach ($params as $key => $val) {
            $stmt->bindValue($key+1, $val);
        }
		// 額外綁定需要指定型別的參數
        foreach ($extraBindings as $binding) {
            // $binding = [':param', $value, PDO::PARAM_INT]
            $stmt->bindValue($binding[0], $binding[1], $binding[2]);
        }

        $stmt->execute();

        // 成功紀錄，暫時關閉，偵錯的時候再打開
        if ($GLOBALS['debug']>0)  
			writeSystemLog($pdo, $username, $storeID, $moduleName, $activity, 'Success', $_SERVER['REMOTE_ADDR'], "sql=".$sql.";\nparam=".$paramsString.";\nextraBindings=".$extraBindingsString);
		

        return $stmt;
    } catch (PDOException $e) {
        // 失敗紀錄
        writeSystemLog($pdo, $username, $storeID, $moduleName, $activity, 'Failure', $_SERVER['REMOTE_ADDR'], $e->getMessage()."\nsql=".$sql.";\nparams=".$paramsString.";\nextraBindings=".$extraBindingsString);
        throw $e;
    }
}

?>
