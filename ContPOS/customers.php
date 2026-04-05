<?php
require 'db.php'; // PDO 連線設定

// 分頁設定
$limit = 10;
$pagenum = isset($_GET['pagenum']) ? (int)$_GET['pagenum'] : 1;
$start = ($pagenum - 1) * $limit + 1;
$end   = $pagenum * $limit;

// 搜尋條件
$search = isset($_GET['search']) ? $_GET['search'] : '';

// 新增客戶
if (isset($_POST['action']) && $_POST['action'] == 'add') {
    $sql = "INSERT INTO Customers (CustomerID, StoreID, Name, Gender, Occupation, City, District, PostalCode, Address, Phone, Mobile, Email, LineID, FacebookID, AcceptPromotion, Birthday, BirthMonth, Points, TotalSpent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST['customerid'], $_POST['storeid'], $_POST['name'], $_POST['gender'], $_POST['occupation'],
        $_POST['city'], $_POST['district'], $_POST['postalcode'], $_POST['address'], $_POST['phone'],
        $_POST['mobile'], $_POST['email'], $_POST['lineid'], $_POST['facebookid'], $_POST['acceptpromotion'],
        $_POST['birthday'], $_POST['birthmonth']
    ]);
}

// 更新客戶
if (isset($_POST['action']) && $_POST['action'] == 'update') {
    $sql = "UPDATE Customers SET StoreID=?, Name=?, Gender=?, Occupation=?, City=?, District=?, PostalCode=?, Address=?, Phone=?, Mobile=?, Email=?, LineID=?, FacebookID=?, AcceptPromotion=?, Birthday=?, BirthMonth=? WHERE CustomerID=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST['storeid'], $_POST['name'], $_POST['gender'], $_POST['occupation'], $_POST['city'], $_POST['district'], $_POST['postalcode'],
        $_POST['address'], $_POST['phone'], $_POST['mobile'], $_POST['email'], $_POST['lineid'], $_POST['facebookid'],
        $_POST['acceptpromotion'], $_POST['birthday'], $_POST['birthmonth'], $_POST['customerid']
    ]);
}

// 刪除客戶
if (isset($_POST['action']) && $_POST['action'] == 'delete') {
    $sql = "DELETE FROM Customers WHERE CustomerID=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_POST['customerid']]);
}

// 查詢客戶 (ROW_NUMBER 分頁)
$sql = "WITH CustList AS (
            SELECT c.*,
                   ROW_NUMBER() OVER (ORDER BY c.CustomerID) AS RowNum
            FROM Customers c
            WHERE c.CustomerID LIKE ? OR c.Name LIKE ? OR c.Mobile LIKE ? OR c.Email LIKE ?
        )
        SELECT * FROM CustList WHERE RowNum BETWEEN ? AND ?";
$stmt = $pdo->prepare($sql);
$stmt->execute(['%'.$search.'%', '%'.$search.'%', '%'.$search.'%', '%'.$search.'%', $start, $end]);
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 計算總筆數
$countSql = "SELECT COUNT(*) FROM Customers WHERE CustomerID LIKE ? OR Name LIKE ? OR Mobile LIKE ? OR Email LIKE ?";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute(['%'.$search.'%', '%'.$search.'%', '%'.$search.'%', '%'.$search.'%']);
$totalRows = $countStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// 查表清單
$stores = $pdo->query("SELECT StoreID, StoreName FROM Stores ORDER BY StoreID")->fetchAll(PDO::FETCH_ASSOC);
$occupations = $pdo->query("SELECT TD_NO, TD_NAME FROM T_CODE WHERE T_NO='Occupation' ORDER BY TD_SEQ")->fetchAll(PDO::FETCH_ASSOC);
$cities = $pdo->query("SELECT DISTINCT CITY FROM ZIP ORDER BY CITY")->fetchAll(PDO::FETCH_ASSOC);
$zips = $pdo->query("SELECT ZIP, CITY, AREA FROM ZIP ORDER BY CITY, AREA")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <title>客戶管理</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light">
<div class="container mt-4">
  <h3>客戶管理</h3>
  <!-- 新增客戶按鈕 -->
  <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#customerModal" onclick="openAddModal()">新增客戶</button>

  <!-- 客戶列表 -->
  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>客戶編號</th><th>姓名</th><th>性別</th><th>手機</th><th>Email</th><th>操作</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($customers as $c): ?>
      <tr>
        <td><?= $c['CustomerID'] ?></td>
        <td><?= $c['Name'] ?></td>
        <td><?= $c['Gender']=='M'?'男':'女' ?></td>
        <td><?= $c['Mobile'] ?></td>
        <td><?= $c['Email'] ?></td>
        <td>
          <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#customerModal"
            onclick='openEditModal(<?= json_encode($c, JSON_UNESCAPED_UNICODE) ?>)'>編輯</button>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="customerid" value="<?= $c['CustomerID'] ?>">
            <input type="hidden" name="action" value="delete">
            <button type="submit" class="btn btn-danger btn-sm">刪除</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- 分頁 -->
  <nav>
    <ul class="pagination">
      <li class="page-item <?= ($pagenum <= 1)?'disabled':'' ?>">
        <a class="page-link" href="?page=customers&pagenum=<?= max(1, $pagenum-1) ?>&search=<?= urlencode($search) ?>">上一頁</a>
      </li>
      <?php for ($i=1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= ($i==$pagenum)?'active':'' ?>">
          <a class="page-link" href="?page=customers&pagenum=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>
      <li class="page-item <?= ($pagenum >= $totalPages)?'disabled':'' ?>">
        <a class="page-link" href="?page=customers&pagenum=<?= min($totalPages, $pagenum+1) ?>&search=<?= urlencode($search) ?>">下一頁</a>
      </li>
    </ul>
  </nav>
</div>

<!-- 共用客戶表單 Modal -->
<div class="modal fade" id="customerModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" id="customerForm">
        <input type="hidden" name="action" id="formAction" value="">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">客戶資料</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body row g-3">
           <!-- 客戶編號 -->
          <div class="col-md-4">
            <label>客戶編號</label>
            <input type="text" name="customerid" id="customerid" class="form-control" required>
          </div>
          <!-- 門市 -->
          <div class="col-md-4">
            <label>門市</label>
            <select name="storeid" id="storeid" class="form-select">
              <?php foreach($stores as $s): ?>
                <option value="<?= $s['StoreID'] ?>"><?= $s['StoreID'] ?> - <?= $s['StoreName'] ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <!-- 姓名 -->
          <div class="col-md-4">
            <label>姓名</label>
            <input type="text" name="name" id="name" class="form-control" required>
          </div>

          <!-- 性別 & 職業 -->
          <div class="col-md-3">
            <label>性別</label>
            <select name="gender" id="gender" class="form-select">
              <option value="M">男</option>
              <option value="F">女</option>
            </select>
          </div>
          <div class="col-md-3">
            <label>職業</label>
            <select name="occupation" id="occupation" class="form-select">
              <?php foreach($occupations as $o): ?>
                <option value="<?= $o['TD_NO'] ?>"><?= $o['TD_NAME'] ?></option>
              <?php endforeach; ?>
            </select>
          </div>
		  <!-- ZIP 快選 -->
          <div class="col-md-4">
            <label>ZIP 快選</label>
            <select id="zipSelect" class="form-select">
              <option value="">--選擇郵遞區號--</option>
              <?php foreach($zips as $z): ?>
                <option value="<?= $z['ZIP'] ?>" data-city="<?= $z['CITY'] ?>" data-area="<?= $z['AREA'] ?>">
                  <?= $z['ZIP'] ?> <?= $z['CITY'] ?> <?= $z['AREA'] ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- 縣市/區域/郵遞區號 -->
          <div class="col-md-3">
            <label>縣市</label>
            <select name="city" id="citySelect" class="form-select">
              <?php foreach($cities as $c): ?>
                <option value="<?= $c['CITY'] ?>"><?= $c['CITY'] ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label>區域</label>
            <select name="district" id="districtSelect" class="form-select"></select>
          </div>
          <div class="col-md-2">
            <label>郵遞區號</label>
            <input type="text" name="postalcode" id="postalCode" class="form-control" readonly>
          </div>

          <!-- 地址 -->
          <div class="col-md-7">
            <label>地址</label>
            <input type="text" name="address" id="address" class="form-control">
          </div>

          <!-- 聯絡方式 -->
          <div class="col-md-4"><label>電話</label><input type="text" name="phone" id="phone" class="form-control"></div>
          <div class="col-md-4"><label>手機</label><input type="text" name="mobile" id="mobile" class="form-control"></div>
          <div class="col-md-4"><label>Email</label><input type="text" name="email" id="email" class="form-control"></div>
          <div class="col-md-4"><label>LINE ID</label><input type="text" name="lineid" id="lineid" class="form-control"></div>
          <div class="col-md-4"><label>Facebook ID</label><input type="text" name="facebookid" id="facebookid" class="form-control"></div>

          <!-- 其他 -->
          <div class="col-md-4">
            <label>生日</label>
            <input type="date" name="birthday" id="birthday" class="form-control">
          </div>
          <div class="col-md-2">
            <label>生日月份</label>
            <input type="number" name="birthmonth" id="birthmonth" class="form-control" min="1" max="12">
          </div>
          <div class="col-md-3">
            <label>是否接受促銷</label>
            <select name="acceptpromotion" id="acceptpromotion" class="form-select">
              <option value="1">是</option>
              <option value="0">否</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">儲存</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openAddModal() {
  $('#modalTitle').text('新增客戶');
  $('#formAction').val('add');
  $('#customerForm')[0].reset();
  $('#customerid').prop('readonly', false);
}

function openEditModal(customer) {
  $('#modalTitle').text('編輯客戶 ' + customer.CustomerID);
  $('#formAction').val('update');

  $('#customerid').val(customer.CustomerID).prop('readonly', true);
  $('#storeid').val(customer.StoreID);
  $('#name').val(customer.Name);
  $('#gender').val(customer.Gender);
  $('#occupation').val(customer.Occupation);
  $('#citySelect').val(customer.City).trigger('change');
  setTimeout(function(){
    $('#districtSelect').val(customer.District).trigger('change');
  }, 300);
  $('#postalCode').val(customer.PostalCode);
  $('#address').val(customer.Address);
  $('#phone').val(customer.Phone);
  $('#mobile').val(customer.Mobile);
  $('#email').val(customer.Email);
  $('#lineid').val(customer.LineID);
  $('#facebookid').val(customer.FacebookID);
  $('#acceptpromotion').val(customer.AcceptPromotion);
  $('#birthday').val(customer.Birthday ? customer.Birthday.substr(0,10) : '');
  $('#birthmonth').val(customer.BirthMonth);
}

// ZIP 快選 → 填入 CITY, AREA, ZIP
$('#zipSelect').on('change', function(){
  let city = $(this).find(':selected').data('city');
  let area = $(this).find(':selected').data('area');
  let zip  = $(this).val();
  $('#citySelect').val(city).trigger('change');
  setTimeout(function(){
    $('#districtSelect').val(area).trigger('change');
  }, 300);
  $('#postalCode').val(zip);
});

// 縣市改變 → AJAX 載入區域
$('#citySelect').on('change', function(){
  let city = $(this).val();
  $.getJSON('getDistricts.php', { city: city }, function(data){
    let $districtSelect = $('#districtSelect');
    $districtSelect.empty();
    $.each(data, function(i, d){
      $districtSelect.append(
        $('<option>').val(d.AREA).text(d.AREA).attr('data-zip', d.ZIP)
      );
    });
    if(data.length > 0){
      $('#postalCode').val(data[0].ZIP);
    }
  });
});

// 區域改變 → 更新 ZIP
$('#districtSelect').on('change', function(){
  let zip = $(this).find(':selected').data('zip');
  $('#postalCode').val(zip);
});

// 表單送出後 → 關閉 Modal 並刷新列表
$('#customerForm').on('submit', function(){
  setTimeout(function(){
    $('#customerModal').modal('hide');
    location.reload();
  }, 500);
});
</script>

<!-- Bootstrap JS 必須放在 body 結尾 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
