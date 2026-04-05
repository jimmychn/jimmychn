<?php
require 'db.php'; // PDO 連線設定

// ProductCategories CRUD
if (isset($_POST['action']) && $_POST['action'] == 'add_category') {
    $sql = "INSERT INTO ProductCategories (CategoryName, PointRate, AmountRate) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_POST['CategoryName'], $_POST['PointRate'], $_POST['AmountRate']]);
}
if (isset($_POST['action']) && $_POST['action'] == 'update_category') {
    $sql = "UPDATE ProductCategories SET CategoryName=?, PointRate=?, AmountRate=? WHERE CategoryID=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_POST['CategoryName'], $_POST['PointRate'], $_POST['AmountRate'], $_POST['CategoryID']]);
}
if (isset($_POST['action']) && $_POST['action'] == 'delete_category') {
    $sql = "DELETE FROM ProductCategories WHERE CategoryID=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_POST['CategoryID']]);
}

// MembershipLevels CRUD
if (isset($_POST['action']) && $_POST['action'] == 'add_level') {
    $sql = "INSERT INTO MembershipLevels (LevelName, MinPoints, MinTotalSpent, BonusRate) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_POST['LevelName'], $_POST['MinPoints'], $_POST['MinTotalSpent'], $_POST['BonusRate']]);
}
if (isset($_POST['action']) && $_POST['action'] == 'update_level') {
    $sql = "UPDATE MembershipLevels SET LevelName=?, MinPoints=?, MinTotalSpent=?, BonusRate=? WHERE LevelID=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_POST['LevelName'], $_POST['MinPoints'], $_POST['MinTotalSpent'], $_POST['BonusRate'], $_POST['LevelID']]);
}
if (isset($_POST['action']) && $_POST['action'] == 'delete_level') {
    $sql = "DELETE FROM MembershipLevels WHERE LevelID=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_POST['LevelID']]);
}

// 查詢
$categories = $pdo->query("SELECT * FROM ProductCategories ORDER BY CategoryID")->fetchAll(PDO::FETCH_ASSOC);
$levels = $pdo->query("SELECT * FROM MembershipLevels ORDER BY LevelID")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <title>商品類別與會員等級管理</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light">
<div class="container mt-4">
  <h3>商品類別管理</h3>
  <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="openAddCategory()">新增類別</button>
  <table class="table table-bordered">
    <thead><tr><th>ID</th><th>類別名稱</th><th>積分比率</th><th>金額比率</th><th>操作</th></tr></thead>
    <tbody>
      <?php foreach ($categories as $c): ?>
      <tr>
        <td><?= $c['CategoryID'] ?></td>
        <td><?= $c['CategoryName'] ?></td>
        <td><?= $c['PointRate'] ?></td>
        <td><?= $c['AmountRate'] ?></td>
        <td>
          <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#categoryModal"
            onclick='openEditCategory(<?= json_encode($c, JSON_UNESCAPED_UNICODE) ?>)'>編輯</button>
          <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#categoryModal"
            onclick='openDeleteCategory(<?= json_encode($c, JSON_UNESCAPED_UNICODE) ?>)'>刪除</button>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <h3 class="mt-5">會員等級管理</h3>
  <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#levelModal" onclick="openAddLevel()">新增等級</button>
  <table class="table table-bordered">
    <thead><tr><th>ID</th><th>等級名稱</th><th>最低積分</th><th>最低消費</th><th>加成倍率</th><th>操作</th></tr></thead>
    <tbody>
      <?php foreach ($levels as $l): ?>
      <tr>
        <td><?= $l['LevelID'] ?></td>
        <td><?= $l['LevelName'] ?></td>
        <td><?= $l['MinPoints'] ?></td>
        <td><?= $l['MinTotalSpent'] ?></td>
        <td><?= $l['BonusRate'] ?></td>
        <td>
          <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#levelModal"
            onclick='openEditLevel(<?= json_encode($l, JSON_UNESCAPED_UNICODE) ?>)'>編輯</button>
          <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#levelModal"
            onclick='openDeleteLevel(<?= json_encode($l, JSON_UNESCAPED_UNICODE) ?>)'>刪除</button>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<!-- 商品類別 Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <form method="POST" id="categoryForm">
      <input type="hidden" name="action" id="categoryAction">
      <div class="modal-header"><h5 id="categoryTitle">商品類別</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <input type="hidden" name="CategoryID" id="CategoryID">
        <div class="mb-3"><label>類別名稱</label><input type="text" name="CategoryName" id="CategoryName" class="form-control"></div>
        <div class="mb-3"><label>積分比率</label><input type="text" name="PointRate" id="PointRate" class="form-control"></div>
        <div class="mb-3"><label>金額比率</label><input type="text" name="AmountRate" id="AmountRate" class="form-control"></div>
      </div>
      <div class="modal-footer"><button type="submit" class="btn btn-primary">確認</button><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button></div>
    </form>
  </div></div>
</div>

<!-- 會員等級 Modal -->
<div class="modal fade" id="levelModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <form method="POST" id="levelForm">
      <input type="hidden" name="action" id="levelAction">
      <div class="modal-header"><h5 id="levelTitle">會員等級</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <input type="hidden" name="LevelID" id="LevelID">
        <div class="mb-3"><label>等級名稱</label><input type="text" name="LevelName" id="LevelName" class="form-control"></div>
        <div class="mb-3"><label>最低積分</label><input type="text" name="MinPoints" id="MinPoints" class="form-control"></div>
        <div class="mb-3"><label>最低消費</label><input type="text" name="MinTotalSpent" id="MinTotalSpent" class="form-control"></div>
        <div class="mb-3"><label>加成倍率</label><input type="text" name="BonusRate" id="BonusRate" class="form-control"></div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">確認</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
      </div>
    </form>
  </div>
</div>
</div>
	  


<script>
// 商品類別控制
function openAddCategory() {
  $('#categoryTitle').text('新增商品類別');
  $('#categoryAction').val('add_category');
  $('#categoryForm')[0].reset();
  $('#CategoryID').val('');
}
function openEditCategory(c) {
  $('#categoryTitle').text('編輯商品類別 ' + c.CategoryID);
  $('#categoryAction').val('update_category');
  $('#CategoryID').val(c.CategoryID);
  $('#CategoryName').val(c.CategoryName);
  $('#PointRate').val(c.PointRate);
  $('#AmountRate').val(c.AmountRate);
}
function openDeleteCategory(c) {
  $('#categoryTitle').text('刪除商品類別 ' + c.CategoryID);
  $('#categoryAction').val('delete_category');
  $('#CategoryID').val(c.CategoryID);
  $('#CategoryName').val(c.CategoryName).prop('readonly', true);
  $('#PointRate').val(c.PointRate).prop('readonly', true);
  $('#AmountRate').val(c.AmountRate).prop('readonly', true);
}

// 會員等級控制
function openAddLevel() {
  $('#levelTitle').text('新增會員等級');
  $('#levelAction').val('add_level');
  $('#levelForm')[0].reset();
  $('#LevelID').val('');
}
function openEditLevel(l) {
  $('#levelTitle').text('編輯會員等級 ' + l.LevelID);
  $('#levelAction').val('update_level');
  $('#LevelID').val(l.LevelID);
  $('#LevelName').val(l.LevelName);
  $('#MinPoints').val(l.MinPoints);
  $('#MinTotalSpent').val(l.MinTotalSpent);
  $('#BonusRate').val(l.BonusRate);
}
function openDeleteLevel(l) {
  $('#levelTitle').text('刪除會員等級 ' + l.LevelID);
  $('#levelAction').val('delete_level');
  $('#LevelID').val(l.LevelID);
  $('#LevelName').val(l.LevelName).prop('readonly', true);
  $('#MinPoints').val(l.MinPoints).prop('readonly', true);
  $('#MinTotalSpent').val(l.MinTotalSpent).prop('readonly', true);
  $('#BonusRate').val(l.BonusRate).prop('readonly', true);
}

// 表單送出後 → 關閉 Modal 並刷新列表
$('#categoryForm, #levelForm').on('submit', function(){
  setTimeout(function(){
    $('.modal').modal('hide');
    location.reload();
  }, 500);
});
</script>

<!-- Bootstrap JS 必須放在 body 結尾 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
	  