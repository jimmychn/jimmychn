<?php
require 'db.php'; // PDO 連線設定

$table = "Users"; // 資料表名稱
$pk    = "UserID"; // 主鍵欄位

// 分頁設定
$limit = 10;
$pagenum = isset($_GET['pagenum']) ? (int)$_GET['pagenum'] : 1;
$start = ($pagenum - 1) * $limit + 1;
$end   = $pagenum * $limit;

// 新增
if (isset($_POST['action']) && $_POST['action'] == 'add') {
	if ($_POST['password']==='') {
		$sql = "INSERT INTO Users (UserID, Username, StoreID, RoleID, IsActive, Email)
				VALUES (?, ?, ?, ?, 1, ?)";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([$_POST['UserID'], $_POST['UserName'], $_POST['Store'], $_POST['Role'], $_POST['Email']]);	
	} else {
		$hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
		$sql = "INSERT INTO Users (UserID, Username, PasswordHash, StoreID, RoleID, IsActive, Email)
				VALUES (?, ?, ?, ?, ?, 1, ?)";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([$_POST['UserID'], $_POST['UserName'], $hash, $_POST['Store'], $_POST['Role'], $_POST['Email']]);	
	}
}

// 更新
if (isset($_POST['action']) && $_POST['action'] == 'update') {
	if ($_POST['password']==='') {
		$sql = "UPDATE $table SET UserName=?, StoreID=?, RoleID=?, IsActive=?, Email=? WHERE $pk=?";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([$_POST['UserName'], $_POST['Store'], $_POST['Role'], $_POST['IsActive'], $_POST['Email'], $_POST['UserID']]);
	} else {
		$hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
		$sql = "UPDATE $table SET UserName=?, PasswordHash=?, StoreID=?, RoleID=?, IsActive=?, Email=? WHERE $pk=?";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([$_POST['UserName'], $hash, $_POST['Store'], $_POST['Role'], $_POST['IsActive'], $_POST['Email'], $_POST['UserID']]);
	}		
}

// 刪除
if (isset($_POST['action']) && $_POST['action'] == 'delete') {
    $sql = "DELETE FROM $table WHERE $pk=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_POST['UserID']]);
}

// 查詢 (Search)
if (isset($_POST['action']) && $_POST['action'] == 'search') {
    $conditions = [];
    $params = [];

    if (!empty($_POST['UserID'])) {
        $conditions[] = "UserID LIKE ?";
        $params[] = $_POST['UserID']."%";
    }
    if (!empty($_POST['UserName'])) {
        $conditions[] = "UserName LIKE ?";
        $params[] = "%".$_POST['UserName']."%";
    }
    if (!empty($_POST['Store'])) {
        $conditions[] = "StoreID LIKE ?";
        $params[] = "%".$_POST['Store']."%";
    }
    if (!empty($_POST['Email'])) {
        $conditions[] = "Email LIKE ?";
        $params[] = "%".$_POST['Email']."%";
    }

    $sql = "SELECT * FROM $table";
    if (count($conditions) > 0) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {

	// 搜尋條件
	$search = isset($_GET['search']) ? $_GET['search'] : '';

	// 使用 ROW_NUMBER() 分頁查詢
	$sql = "WITH UserList AS (
				SELECT u.*,
					   ROW_NUMBER() OVER (ORDER BY u.$pk) AS RowNum
				FROM $table u
				WHERE u.$pk LIKE ? OR u.UserName LIKE ? OR u.Email LIKE ?
			)
			SELECT * FROM UserList WHERE RowNum BETWEEN ? AND ?";
	$stmt = $pdo->prepare($sql);
	$stmt->execute([
		'%'.$search.'%', '%'.$search.'%', '%'.$search.'%', $start, $end
	]);
	$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
	//print_r($users);
	
	// 計算總筆數
	$countSql = "SELECT COUNT(*) FROM $table WHERE $pk LIKE ? OR UserName LIKE ? OR Email LIKE ?";
	$countStmt = $pdo->prepare($countSql);
	$countStmt->execute(['%'.$search.'%', '%'.$search.'%', '%'.$search.'%']);
	$totalRows = $countStmt->fetchColumn();
	$totalPages = ceil($totalRows / $limit);

}
// 查詢門市清單
$stmt = $pdo->query("SELECT * FROM Stores WHERE IsActive=1 ORDER BY StoreID");
$stores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 查詢角色清單
$stmt = $pdo->query("SELECT * FROM Roles ORDER BY RoleID");
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <title>User 管理</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style> .CheckShow {display: block;} </style>
</head>
<body class="bg-light">
<div class="container mt-4">
  <h3>User 管理</h3>
  <!-- 操作按鈕 -->
  <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#userModal" onclick="openAddModal()">新增使用者</button>
  <button type="button" class="btn btn-info mb-3" data-bs-toggle="modal" data-bs-target="#userModal" onclick="openSearchModal()">查詢使用者</button>

  <!-- 使用者列表 -->
  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>使用者代號</th>
        <th>帳號</th>
        <th>門市</th>
        <th>角色</th>
        <th>啟用</th>
        <th>操作</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $u): ?>
      <tr>
        <td><?= $u['UserID'] ?></td>
        <td><?= $u['UserName'] ?></td>
        <td><?= $u['StoreID'] ?></td>
        <td><?= $u['RoleID'] ?></td>
        <td><?= $u['IsActive'] ?></td>
        <td>
          <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#userModal"
            onclick='openEditModal(<?= json_encode($u, JSON_UNESCAPED_UNICODE) ?>)'>編輯</button>
          <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#userModal"
            onclick='openViewModal(<?= json_encode($u, JSON_UNESCAPED_UNICODE) ?>)'>檢視</button>
          <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#userModal"
            onclick='openDeleteModal(<?= json_encode($u, JSON_UNESCAPED_UNICODE) ?>)'>刪除</button>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- 分頁 -->
  <nav>
    <ul class="pagination">
      <li class="page-item <?= ($pagenum <= 1)?'disabled':'' ?>">
        <a class="page-link" href="?page=users&pagenum=<?= max(1, $pagenum-1) ?>">上一頁</a>
      </li>
      <?php for ($i=1; $i <= ceil(count($users)/$limit); $i++): ?>
        <li class="page-item <?= ($i==$pagenum)?'active':'' ?>">
          <a class="page-link" href="?page=users&pagenum=<?= $i ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>
      <li class="page-item <?= ($pagenum >= ceil(count($users)/$limit))?'disabled':'' ?>">
        <a class="page-link" href="?page=users&pagenum=<?= min(ceil(count($users)/$limit), $pagenum+1) ?>">下一頁</a>
      </li>
    </ul>
  </nav>
</div>

<!-- 共用使用者表單 Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" id="userForm">
        <input type="hidden" name="action" id="formAction" value="">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">使用者表單</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>使用者代號</label>
            <input type="text" name="UserID" id="UserID" class="form-control">
          </div>
          <div class="mb-3 CheckShow">
            <label>使用者代號</label>
			<input type="password" name="password" class="form-control" placeholder="密碼">
		  </div>
          <div class="mb-3">
            <label>名稱</label>
            <input type="text" name="UserName" id="UserName" class="form-control">
          </div>
          <div class="mb-3">
            <label>門市</label>
            <select name="Store" class="form-select">
			  <option value=""></option>
              <?php foreach ($stores as $s): ?>
                <option value="<?= $s['StoreID'] ?>" <?php if (isset($u)) echo ($u['StoreID']==$s['StoreID']?'selected':''); ?>>
                  <?= $s['StoreID'] ?> - <?= $s['StoreName'] ?>
                </option>
              <?php endforeach; ?>
            </select>
		  </div>
          <div class="mb-3 CheckShow">
            <label>腳色</label>
            <select name="Role" class="form-select">
              <?php foreach ($roles as $r): ?>
                <option value="<?= $r['RoleID'] ?>" <?php if (isset($u)) echo ($u['RoleID']==$r['RoleID']?'selected':''); ?>>
                  <?= $r['RoleName'] ?>
                </option>
              <?php endforeach; ?>
            </select>
		  </div>
          <div class="mb-3 CheckShow">
            <label>啟用</label>
            <select name="IsActive" class="form-select">
              <option value=""></option>
              <option value="1" <?php if (isset($u)) echo ( $u['IsActive']?'selected':'') ?>>啟用</option>
              <option value="0" <?php if (isset($u)) echo (!$u['IsActive']?'selected':'') ?>>停用</option>
            </select>
		  </div>	
          <div class="mb-3 CheckShow">
            <label>Email</label>
            <input type="text" name="Email" id="Email" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary btn-save">確認</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
function openAddModal() {
  $('#modalTitle').text('新增使用者');
  $('#formAction').val('add');
  $('#userForm')[0].reset();
  $('.CheckShow').show();
  $('input').prop('readonly', false);
  $('.btn-save').show().text('確認');
}

function openEditModal(user) {
  $('#modalTitle').text('編輯使用者 ' + user.UserID);
  $('#formAction').val('update');
  $('.CheckShow').show();
  fillForm(user);
  $('input').prop('readonly', false);
  $('#UserID').prop('readonly', true);
  $('.btn-save').show().text('確認');
}

function openDeleteModal(user) {
  $('#modalTitle').text('刪除使用者 ' + user.UserID);
  $('#formAction').val('delete');
  $('.CheckShow').show();
  fillForm(user);
  $('input').prop('readonly', true);
  $('.btn-save').show().text('確認刪除');
}

function openViewModal(user) {
  $('#modalTitle').text('檢視使用者 ' + user.UserID);
  $('#formAction').val('view');
  $('.CheckShow').show();
  fillForm(user);
  $('input').prop('readonly', true);
  $('.btn-save').hide(); // 檢視模式不顯示確認
}

function openSearchModal() {
  $('#modalTitle').text('查詢使用者');
  $('#formAction').val('search');
  $('.CheckShow').hide();
  $('#userForm')[0].reset();
  $('#UserID').prop('readonly', false);
  $('input').prop('readonly', false);
  $('.btn-save').show().text('確認');
}

function fillForm(user) {
  $('#UserID').val(user.UserID);
  $('#UserName').val(user.UserName);
  $('#Store').val(user.StoreID);
  $('#Role').val(user.RoleID);
  $('#IsActive').val(user.IsActive);
  $('#Email').val(user.Email);
}

// 表單送出後 → 關閉 Modal 並刷新列表
$('#userForm').on('submit', function(){
  let action = $('#formAction').val();
  if (action == 'view') {
    $('#userModal').modal('hide');
    return false;
  }
  setTimeout(function(){
    $('#userModal').modal('hide');
    location.reload();
  }, 500);
});
</script>

<!-- Bootstrap JS 必須放在 body 結尾 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
