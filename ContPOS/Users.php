<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>使用者管理</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    body { background-color: #f8f9fa; }
    .table thead { background: #d9edf7; }
    .table tbody tr:nth-child(even) { background: #f9fbfd; }
    .table tbody tr:hover { background: #eef7fd; }
    .column-toggle-panel { max-width: 320px; }
    #usersTable td[data-key="actions"] { width: 180px; white-space: nowrap; }
    .form-label { font-weight: 600; }
  </style>
</head>
<body>
<div class="container-fluid p-2">
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start mb-4">
    <div>
      <h3 class="mb-1">使用者管理</h3>
      <p class="text-secondary mb-0">使用 Stores_api.php 取得門市清單、Roles API 取得角色清單，並從門市員工複製 UserID、UserName。</p>
    </div>
  </div>
  <div class="row align-items-center mb-3">
    <div class="col-md-6 mb-2 mb-md-0">
      <div class="input-group">
        <input type="text" id="searchInput" class="form-control" placeholder="搜尋 UserID、UserName、門市或角色">
        <button class="btn btn-primary" id="btnSearch">搜尋</button>
      </div>
    </div>
    <div class="col-md-6 text-md-end">
      <div class="d-flex justify-content-md-end justify-content-start flex-wrap gap-2">
        <button class="btn btn-success" id="btnAdd">新增</button>
        <button class="btn btn-outline-secondary" id="btnExport">CSV</button>
        <button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#columnSettings" aria-expanded="false" aria-controls="columnSettings">欄位顯示</button>
      </div>
    </div>
  </div>
  <div class="collapse mb-3" id="columnSettings">
    <div class="card card-body column-toggle-panel">
      <div class="row g-2" id="columnCheckboxArea"></div>
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-bordered table-striped" id="usersTable">
      <thead>
        <tr>
          <th data-key="StoreID">門市代號</th>
          <th data-key="StoreName">門市名稱</th>
          <th data-key="UserID">使用者代號</th>
          <th data-key="UserName">使用者名稱</th>
          <th data-key="RoleID">角色代號</th>
          <th data-key="RoleName">角色名稱</th>
          <th data-key="IsActive">啟用</th>
          <th data-key="actions">操作</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
    <div class="text-secondary mb-2 mb-md-0" id="pageInfo">第 1 頁 / 共 0 頁</div>
    <nav><ul class="pagination mb-0" id="pagination"></ul></nav>
  </div>
</div>
<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <form id="userForm">
        <input type="hidden" id="formAction" name="action" value="">
        <input type="hidden" id="OriginalStoreID" name="OriginalStoreID" value="">
        <input type="hidden" id="OriginalUserID" name="OriginalUserID" value="">
        <div class="modal-header">
          <h5 class="modal-title" id="userModalTitle">使用者資料</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="關閉"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">門市</label>
              <select id="StoreID" name="StoreID" class="form-select" required></select>
            </div>
            <div class="col-md-4">
              <label class="form-label">門市員工</label>
              <select id="StaffSourceID" class="form-select">
                <option value="">-- 請先選擇門市 --</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">角色</label>
              <select id="RoleID" name="RoleID" class="form-select" required></select>
            </div>
            <div class="col-md-4">
              <label class="form-label">使用者代號 (UserID)</label>
              <input type="text" id="UserID" name="UserID" class="form-control" readonly maxlength="20">
            </div>
            <div class="col-md-4">
              <label class="form-label">使用者名稱</label>
              <input type="text" id="UserName" name="UserName" class="form-control" required maxlength="100">
            </div>
            <div class="col-md-4">
              <label class="form-label">密碼</label>
              <input type="password" id="Password" name="Password" class="form-control" required maxlength="100">
            </div>
            <div class="col-md-8">
              <label class="form-label">EMail</label>
              <input type="email" id="Email" name="Email" class="form-control" maxlength="100">
            </div>
            <div class="col-md-4">
              <label class="form-label">啟用狀態</label>
              <select id="IsActive" name="IsActive" class="form-select">
                <option value="1">啟用</option>
                <option value="0">停用</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary" id="saveButton">儲存</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
var currentPage = 1;
var pageSize = 10;
var totalRows = 0;
var columns = [
  { key: 'StoreID', label: '門市代號' },
  { key: 'StoreName', label: '門市名稱' },
  { key: 'UserID', label: '使用者代號' },
  { key: 'UserName', label: '使用者名稱' },
  { key: 'RoleID', label: '角色代號' },
  { key: 'RoleName', label: '角色名稱' },
  { key: 'IsActive', label: '啟用' }
];
var visibleColumns = loadVisibleColumns();
function loadVisibleColumns() {
  var saved = localStorage.getItem('users_columns');
  if (saved) {
    try { return JSON.parse(saved); } catch (e) { }
  }
  var obj = {};
  columns.forEach(function(col) { obj[col.key] = true; });
  return obj;
}
function saveVisibleColumns() {
  localStorage.setItem('users_columns', JSON.stringify(visibleColumns));
}
function buildColumnToggles() {
  var html = '';
  columns.forEach(function(col) {
    html += '<div class="col-6 col-md-4">';
    html += '<div class="form-check">';
    html += '<input class="form-check-input" type="checkbox" id="colToggle_' + col.key + '" data-key="' + col.key + '" ' + (visibleColumns[col.key] ? 'checked' : '') + '>';
    html += '<label class="form-check-label" for="colToggle_' + col.key + '">' + col.label + '</label>';
    html += '</div></div>';
  });
  $('#columnCheckboxArea').html(html);
  $('#columnCheckboxArea input[type=checkbox]').on('change', function() {
    var key = $(this).data('key');
    visibleColumns[key] = $(this).is(':checked');
    saveVisibleColumns();
    applyColumnVisibility();
  });
}
function applyColumnVisibility() {
  $('#usersTable thead th').each(function() {
    var key = $(this).data('key');
    if (!key) return;
    $(this).toggle(visibleColumns[key] !== false);
  });
  $('#usersTable tbody tr').each(function() {
    $(this).find('td').each(function() {
      var key = $(this).data('key');
      if (!key) return;
      $(this).toggle(visibleColumns[key] !== false);
    });
  });
}
function buildPagination() {
  var totalPages = Math.max(1, Math.ceil(totalRows / pageSize));
  var html = '';
  var windowSize = 5;
  var start = Math.max(1, currentPage - Math.floor(windowSize / 2));
  var end = Math.min(totalPages, start + windowSize - 1);
  if (end - start < windowSize - 1) { start = Math.max(1, end - windowSize + 1); }
  html += '<li class="page-item ' + (currentPage <= 1 ? 'disabled' : '') + '">';
  html += '<a class="page-link" href="#" data-page="' + (currentPage - 1) + '">上一頁</a></li>';
  for (var p = start; p <= end; p++) {
    html += '<li class="page-item ' + (p === currentPage ? 'active' : '') + '">';
    html += '<a class="page-link" href="#" data-page="' + p + '">' + p + '</a></li>';
  }
  html += '<li class="page-item ' + (currentPage >= totalPages ? 'disabled' : '') + '">';
  html += '<a class="page-link" href="#" data-page="' + (currentPage + 1) + '">下一頁</a></li>';
  $('#pagination').html(html);
  $('#pageInfo').text('第 ' + currentPage + ' 頁 / 共 ' + totalPages + ' 頁');
}
function escapeHtml(text) {
  if (text === null || text === undefined) return '';
  return $('<div>').text(text).html();
}
function loadLookups() {
  $.post('Stores_api.php', { action: 'lookupStores' }, function(resp) {
    var options = '<option value="">-- 請選門市 --</option>';
    if (resp.stores) {
      resp.stores.forEach(function(store) {
        options += '<option value="' + escapeHtml(store.StoreID) + '">' + escapeHtml(store.StoreID) + ' - ' + escapeHtml(store.StoreName) + '</option>';
      });
    }
    $('#StoreID').html(options);
  }, 'json').fail(function() {
    alert('無法載入門市清單');
  });
  $.post('roles_api.php', { action: 'lookupRoles' }, function(resp) {
    var options = '<option value="">-- 請選角色 --</option>';
    if (resp.roles) {
      resp.roles.forEach(function(role) {
        options += '<option value="' + escapeHtml(role.RoleID) + '">' + escapeHtml(role.RoleID) + ' - ' + escapeHtml(role.RoleName) + '</option>';
      });
    }
    $('#RoleID').html(options);
  }, 'json').fail(function() {
    alert('無法載入角色清單');
  });
}
function loadStaffsByStore(storeID, selectedStaffID) {
  $('#StaffSourceID').html('<option value="">讀取中...</option>');
  $.getJSON('Staffs_api.php', { action: 'lookupByStore', StoreID: storeID }, function(resp) {
    var options = '<option value="">-- 請選員工 --</option>';
    if (resp.staffs) {
      resp.staffs.forEach(function(staff) {
        options += '<option value="' + escapeHtml(staff.StaffID) + '" data-name="' + escapeHtml(staff.Name) + '" data-email="' + escapeHtml(staff.Email || '') + '">' + escapeHtml(staff.StaffID) + ' - ' + escapeHtml(staff.Name) + '</option>';
      });
    }
    $('#StaffSourceID').html(options);
    if (selectedStaffID) {
      $('#StaffSourceID').val(selectedStaffID).trigger('change');
    }
  }).fail(function() {
    $('#StaffSourceID').html('<option value="">-- 載入失敗 --</option>');
    alert('無法載入門市員工');
  });
}
function openAddModal() {
  $('#userModalTitle').text('新增使用者');
  $('#formAction').val('insert');
  $('#userForm')[0].reset();
  $('#OriginalStoreID').val('');
  $('#OriginalUserID').val('');
  $('#UserID').prop('readonly', false).val('');
  $('#UserName').prop('readonly', false).val('');
  $('#Email').prop('readonly', false).val('');
  $('#Password').prop('readonly', false).attr('required', true);
  $('#saveButton').show().text('新增');
  $('#StoreID').val('');
  $('#StaffSourceID').html('<option value="">-- 請先選擇門市 --</option>');
  $('#Email').val('');
  $('#IsActive').val('1');
  $('#userModal').modal('show');
}
function openEditModal(storeID, userID) {
  $.getJSON('Users_api.php', { action: 'view', StoreID: storeID, UserID: userID }, function(resp) {
    if (!resp.row) { alert('找不到使用者資料'); return; }
    var row = resp.row;
    $('#userModalTitle').text('編輯使用者 ' + userID);
    $('#formAction').val('update');
    $('#OriginalStoreID').val(row.StoreID);
    $('#OriginalUserID').val(row.UserID);
    $('#UserID').val(row.UserID).prop('readonly', false);
    $('#UserName').val(row.UserName).prop('readonly', false);
    $('#Email').val(row.Email || '').prop('readonly', false);
    $('#RoleID').val(row.RoleID);
    $('#StoreID').val(row.StoreID);
    $('#Email').val(row.Email || '');
    $('#IsActive').val(row.IsActive == 1 ? '1' : '0');
    $('#Password').val('').attr('required', false);
    $('#saveButton').show().text('更新');
    if (row.StoreID) {
      loadStaffsByStore(row.StoreID, row.UserID);
    } else {
      $('#StaffSourceID').html('<option value="">-- 請先選擇門市 --</option>');
    }
    $('#userModal').modal('show');
  }).fail(function() {
    alert('無法取得使用者資料');
  });
}
function openViewModal(storeID, userID) {
  $.getJSON('Users_api.php', { action: 'view', StoreID: storeID, UserID: userID }, function(resp) {
    if (!resp.row) { alert('找不到使用者資料'); return; }
    var row = resp.row;
    $('#userModalTitle').text('檢視使用者 ' + userID);
    $('#formAction').val('view');
    $('#UserID').val(row.UserID).prop('readonly', true);
    $('#UserName').val(row.UserName).prop('readonly', true);
    $('#Email').val(row.Email || '').prop('readonly', true);
    $('#RoleID').val(row.RoleID).prop('disabled', true);
    $('#StoreID').val(row.StoreID).prop('disabled', true);
    $('#StaffSourceID').html('<option value="' + escapeHtml(row.UserID) + '" data-name="' + escapeHtml(row.UserName) + '">' + escapeHtml(row.UserID) + ' - ' + escapeHtml(row.UserName) + '</option>');
    $('#StaffSourceID').prop('disabled', true);
    $('#IsActive').val(row.IsActive == 1 ? '1' : '0').prop('disabled', true);
    $('#Password').val('').prop('readonly', true).attr('required', false);
    $('#saveButton').hide();
    $('#userModal').modal('show');
  }).fail(function() {
    alert('無法取得使用者資料');
  });
}
function deleteUser(storeID, userID) {
  if (!confirm('確定要刪除門市 ' + storeID + ' 的使用者 ' + userID + ' 嗎？')) return;
  $.post('Users_api.php', { action: 'delete', StoreID: storeID, UserID: userID }, function(resp) {
    if (resp.success) {
      loadUsers(currentPage);
    } else {
      alert('刪除失敗');
    }
  }, 'json').fail(function() {
    alert('刪除失敗，請稍後再試。');
  });
}
function loadUsers(page) {
  currentPage = page;
  var search = $('#searchInput').val().trim();
  $.getJSON('Users_api.php', { action: 'list', page: page, pageSize: pageSize, search: search }, function(resp) {
    totalRows = resp.total || 0;
    var html = '';
    if (resp.rows && resp.rows.length) {
      resp.rows.forEach(function(row) {
        html += '<tr>';
        html += '<td data-key="StoreID">' + escapeHtml(row.StoreID) + '</td>';
        html += '<td data-key="StoreName">' + escapeHtml(row.StoreName) + '</td>';
        html += '<td data-key="UserID">' + escapeHtml(row.UserID) + '</td>';
        html += '<td data-key="UserName">' + escapeHtml(row.UserName) + '</td>';
        html += '<td data-key="RoleID">' + escapeHtml(row.RoleID) + '</td>';
        html += '<td data-key="RoleName">' + escapeHtml(row.RoleName) + '</td>';
        html += '<td data-key="IsActive">' + (row.IsActive == 1 ? '啟用' : '停用') + '</td>';
        html += '<td data-key="actions" class="text-end">';
        html += '<button class="btn btn-sm btn-primary me-1 btn-view-user" data-storeid="' + escapeHtml(row.StoreID) + '" data-userid="' + escapeHtml(row.UserID) + '">檢視</button>';
        html += '<button class="btn btn-sm btn-warning me-1 btn-edit-user" data-storeid="' + escapeHtml(row.StoreID) + '" data-userid="' + escapeHtml(row.UserID) + '">編輯</button>';
        html += '<button class="btn btn-sm btn-danger btn-delete-user" data-storeid="' + escapeHtml(row.StoreID) + '" data-userid="' + escapeHtml(row.UserID) + '">刪除</button>';
        html += '</td>';
        html += '</tr>';
      });
    } else {
      html += '<tr><td colspan="9" class="text-center">目前沒有資料</td></tr>';
    }
    $('#usersTable tbody').html(html);
    applyColumnVisibility();
    buildPagination();
  }).fail(function() {
    alert('無法載入使用者資料，請稍後再試。');
  });
}
$(document).ready(function() {
  buildColumnToggles();
  applyColumnVisibility();
  loadLookups();
  loadUsers(1);
  $('#btnSearch').click(function() { loadUsers(1); });
  $('#searchInput').on('keypress', function(e) { if (e.which === 13) { e.preventDefault(); loadUsers(1); }});
  $('#btnAdd').click(function() { resetModalState(); openAddModal(); });
  $('#btnExport').click(function() {
    var rows = [];
    var headers = [];
    $('#usersTable thead th:visible').each(function() {
      var key = $(this).data('key');
      if (!key || key === 'actions') return;
      headers.push($(this).text().trim());
    });
    rows.push(headers.join(','));
    $('#usersTable tbody tr').each(function() {
      var cells = [];
      $(this).find('td:visible').each(function() {
        var key = $(this).data('key');
        if (!key || key === 'actions') return;
        cells.push('"' + $(this).text().trim().replace(/"/g, '""') + '"');
      });
      if (cells.length > 0) rows.push(cells.join(','));
    });
    var csv = rows.join('\n');
    var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    var link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'users_export_page_' + currentPage + '.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(link.href);
  });
  $('#StoreID').change(function() {
    var storeID = $(this).val();
    if (storeID) {
      loadStaffsByStore(storeID);
    } else {
      $('#StaffSourceID').html('<option value="">-- 請先選擇門市 --</option>');
    }
  });
  $('#StaffSourceID').change(function() {
    var selected = $(this).find(':selected');
    if (selected.val()) {
      $('#UserID').val(selected.val());
      $('#UserName').val(selected.data('name') || '');
      $('#Email').val(selected.data('email') || '');
    } else {
      //$('#UserID').val('');
      //$('#UserName').val('');
      //$('#Email').val('');
    }
  });
  $('#userForm').submit(function(e) {
    e.preventDefault();
    var action = $('#formAction').val();
    if (action === 'view') {
      $('#userModal').modal('hide');
      return;
    }
    var data = {
      action: action,
      OriginalStoreID: $('#OriginalStoreID').val().trim(),
      OriginalUserID: $('#OriginalUserID').val().trim(),
      StoreID: $('#StoreID').val(),
      UserID: $('#UserID').val().trim(),
      UserName: $('#UserName').val().trim(),
      Email: $('#Email').val().trim(),
      Password: $('#Password').val(),
      RoleID: $('#RoleID').val(),
      IsActive: $('#IsActive').val()
    };
    if (!data.StoreID || !data.RoleID || !data.UserID || !data.UserName) {
      var msg = "請完整填寫必填欄位(";
      if (!data.StoreID) msg += "門市、";
      if (!data.RoleID) msg += "角色、";
      if (!data.UserID) msg += "使用者代號、";
      if (!data.UserName) msg += "使用者名稱、";
      msg = msg.substring(0, msg.length - 1) + ")";
      alert(msg);
      return;
    }
    $.post('Users_api.php', data, function(resp) {
      if (resp.success) {
        $('#userModal').modal('hide');
        loadUsers(currentPage);
      } else {
        alert(resp.message || '儲存失敗');
      }
    }, 'json').fail(function() {
      alert('儲存失敗，請稍後再試。');
    });
  });
  $('#pagination').on('click', 'a.page-link', function(e) {
    e.preventDefault();
    var page = parseInt($(this).data('page'), 10);
    if (!isNaN(page) && page >= 1) {
      loadUsers(page);
    }
  });
  makeModalDraggable('#userModal');
});
function resetModalState() {
  $('#StoreID, #RoleID, #StaffSourceID, #IsActive').prop('disabled', false);
  $('#UserID, #UserName, #Email, #Password').prop('readonly', false);
  $('#Password').attr('required', true);
  $('#saveButton').show();
}
function makeModalDraggable(modalSelector) {
  var isDragging = false;
  var startX = 0;
  var startY = 0;
  var startLeft = 0;
  var startTop = 0;
  var $dialog = null;
  $(modalSelector).on('show.bs.modal', function () {
    $dialog = $(this).find('.modal-dialog');
    $dialog.css({ position: '', left: '', top: '', margin: '' });
  });
  $(modalSelector + ' .modal-header').css('cursor', 'move').on('mousedown', function (e) {
    $dialog = $(this).closest('.modal-dialog');
    isDragging = true;
    startX = e.pageX;
    startY = e.pageY;
    var offset = $dialog.offset();
    startLeft = offset.left;
    startTop = offset.top;
    $dialog.css({ position: 'absolute', margin: 0, left: startLeft + 'px', top: startTop + 'px' });
    $('body').on('mousemove.modalDrag', function (e) {
      if (!isDragging) return;
      $dialog.css({ left: startLeft + (e.pageX - startX) + 'px', top: startTop + (e.pageY - startY) + 'px' });
    }).on('mouseup.modalDrag', function () {
      isDragging = false;
      $('body').off('.modalDrag');
    });
    e.preventDefault();
  });
  $(modalSelector).on('hidden.bs.modal', function () {
    if ($dialog) {
      $dialog.css({ position: '', left: '', top: '', margin: '' });
      $dialog = null;
    }
  });
}
$(document).on('click', '.btn-view-user', function() { openViewModal($(this).data('storeid'), $(this).data('userid')); });
$(document).on('click', '.btn-edit-user', function() { resetModalState(); openEditModal($(this).data('storeid'), $(this).data('userid')); });
$(document).on('click', '.btn-delete-user', function() { deleteUser($(this).data('storeid'), $(this).data('userid')); });
</script>
</body>
</html>
