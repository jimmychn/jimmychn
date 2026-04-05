<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>員工管理</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    body { background-color: #f8f9fa; }
    .table thead { background: #d9edf7; }
    .table tbody tr:nth-child(even) { background: #f9fbfd; }
    .table tbody tr:hover { background: #eef7fd; }
    .column-toggle-panel { max-width: 320px; }
    #staffsTable td[data-key="actions"] { width: 180px; white-space: nowrap; }
    .form-label { font-weight: 800; }
  </style>
</head>
<body>

<div class="container-fluid p-2">
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start mb-4">
    <div>
      <h3 class="mb-1">員工管理</h3>
      <p class="text-secondary mb-0">管理員工資料，提供查詢、新增、編輯與刪除功能。</p>
    </div>
  </div>

  <div class="row align-items-center mb-3">
    <div class="col-md-6 mb-2 mb-md-0">
      <div class="input-group">
        <input type="text" id="searchInput" class="form-control" placeholder="搜尋員工代號、姓名、門市、職位、電話或 Email">
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
    <table class="table table-bordered table-striped" id="staffsTable">
      <thead>
        <tr>
          <th data-key="StaffID">員工代號</th>
          <th data-key="StoreID">門市代號</th>
          <th data-key="StoreName">門市名稱</th>
          <th data-key="Name">姓名</th>
          <th data-key="Gender">性別</th>
          <th data-key="Position">職位</th>
          <th data-key="TEL">電話</th>
          <th data-key="Mobile">手機</th>
          <th data-key="Email">Email</th>
          <th data-key="Active">在職</th>
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

<div class="modal fade" id="staffModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <form id="staffForm">
        <input type="hidden" id="formAction" name="action" value="">
        <div class="modal-header">
          <h5 class="modal-title" id="staffModalTitle">員工資料</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="關閉"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">門市</label>
              <select id="StoreID" name="StoreID" class="form-select" required></select>
            </div>
            <div class="col-md-3">
              <label class="form-label">員工代號</label>
              <input type="text" id="StaffID" name="StaffID" class="form-control" required maxlength="10">
            </div>
            <div class="col-md-3">
              <label class="form-label">姓名</label>
              <input type="text" id="Name" name="Name" class="form-control" required maxlength="50">
            </div>
            <div class="col-md-2">
              <label class="form-label">性別</label>
              <select id="Gender" name="Gender" class="form-select">
                <option value="">未指定</option>
                <option value="M">男</option>
                <option value="F">女</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">郵遞區號</label>
              <select id="ZIPCode" name="ZIPCode" class="form-select"></select>
            </div>
            <div class="col-md-3">
              <label class="form-label">生日</label>
              <input type="date" id="Birthday" name="Birthday" class="form-control">
            </div>
            <div class="col-md-3">
              <label class="form-label">職位</label>
              <input type="text" id="Position" name="Position" class="form-control" maxlength="50">
            </div>
            <div class="col-md-3">
              <label class="form-label">停用/在職</label>
              <select id="Active" name="Active" class="form-select">
                <option value="1">在職</option>
                <option value="0">離職</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">縣市</label>
              <input type="text" id="CITY" name="CITY" class="form-control" readonly>
            </div>
            <div class="col-md-3">
              <label class="form-label">區域</label>
              <input type="text" id="AREA" name="AREA" class="form-control" readonly>
            </div>
            <div class="col-md-6">
              <label class="form-label">地址</label>
              <input type="text" id="Address" name="Address" class="form-control" maxlength="200">
            </div>
            <div class="col-md-3">
              <label class="form-label">電話</label>
              <input type="text" id="TEL" name="TEL" class="form-control" maxlength="20">
            </div>
            <div class="col-md-3">
              <label class="form-label">手機</label>
              <input type="text" id="Mobile" name="Mobile" class="form-control" maxlength="20">
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" id="Email" name="Email" class="form-control" maxlength="100">
            </div>
            <div class="col-md-3">
              <label class="form-label">LINE ID</label>
              <input type="text" id="LineID" name="LineID" class="form-control" maxlength="50">
            </div>
            <div class="col-md-3">
              <label class="form-label">Facebook ID</label>
              <input type="text" id="FacebookID" name="FacebookID" class="form-control" maxlength="50">
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
  { key: 'StaffID', label: '員工代號' },
  { key: 'StoreID', label: '門市代號' },
  { key: 'Name', label: '姓名' },
  { key: 'Gender', label: '性別' },
  { key: 'Position', label: '職位' },
  { key: 'TEL', label: '電話' },
  { key: 'Mobile', label: '手機' },
  { key: 'Email', label: 'Email' },
  { key: 'Active', label: '在職' }
];
var visibleColumns = loadVisibleColumns();

function loadVisibleColumns() {
  var saved = localStorage.getItem('staffs_columns');
  if (saved) {
    try { return JSON.parse(saved); } catch (e) { }
  }
  var obj = {};
  columns.forEach(function(col) { obj[col.key] = true; });
  return obj;
}

function saveVisibleColumns() {
  localStorage.setItem('staffs_columns', JSON.stringify(visibleColumns));
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
  $('#staffsTable thead th').each(function() {
    var key = $(this).data('key');
    if (!key) return;
    $(this).toggle(visibleColumns[key] !== false);
  });
  $('#staffsTable tbody tr').each(function() {
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

function loadStaffs(page) {
  currentPage = page || 1;
  var keyword = $('#searchInput').val();
  $.getJSON('Staffs_api.php', { action: 'list', page: currentPage, pageSize: pageSize, search: keyword }, function(resp) {
    totalRows = resp.total || 0;
    var html = '';
    if (resp.rows && resp.rows.length > 0) {
      resp.rows.forEach(function(row) {
        html += '<tr>';
        html += '<td data-key="StaffID">' + escapeHtml(row.StaffID) + '</td>';
        html += '<td data-key="StoreID">' + escapeHtml(row.StoreID) + '</td>';
        html += '<td data-key="StoreName">' + escapeHtml(row.StoreName) + '</td>';
        html += '<td data-key="Name">' + escapeHtml(row.Name) + '</td>';
        html += '<td data-key="Gender">' + escapeHtml(row.Gender) + '</td>';
        html += '<td data-key="Position">' + escapeHtml(row.Position) + '</td>';
        html += '<td data-key="TEL">' + escapeHtml(row.TEL) + '</td>';
        html += '<td data-key="Mobile">' + escapeHtml(row.Mobile) + '</td>';
        html += '<td data-key="Email">' + escapeHtml(row.Email) + '</td>';
        html += '<td data-key="Active">' + (row.Active == 1 ? '在職' : '離職') + '</td>';
        html += '<td data-key="actions" class="text-end">';
        html += '<button class="btn btn-sm btn-primary ms-1" onclick="openViewModal(\'' + row.StaffID + '\')">檢視</button>';
        html += '<button class="btn btn-sm btn-warning ms-1" onclick="openEditModal(\'' + row.StaffID + '\')">編輯</button>';
        html += '<button class="btn btn-sm btn-danger ms-1" onclick="deleteStaff(\'' + row.StaffID + '\')">刪除</button>';
        html += '</td>';
        html += '</tr>';
      });
    } else {
      html += '<tr><td colspan="11" class="text-center">目前沒有資料</td></tr>';
    }
    $('#staffsTable tbody').html(html);
    applyColumnVisibility();
    buildPagination();
  }).fail(function() {
    alert('無法載入員工資料，請稍後再試。');
  });
}

function loadLookups() {
  $.getJSON('Staffs_api.php', { action: 'lookups' }, function(resp) {
    var storeOptions = '<option value="">-- 請選門市 --</option>';
    if (resp.stores) {
      resp.stores.forEach(function(store) {
        storeOptions += '<option value="' + escapeHtml(store.StoreID) + '" data-name="' + escapeHtml(store.StoreName) + '">' + escapeHtml(store.StoreID) + ' - ' + escapeHtml(store.StoreName) + '</option>';
      });
    }
    $('#StoreID').html(storeOptions);

    var zipOptions = '<option value="">-- 請選郵遞區號 --</option>';
    if (resp.zipList) {
      resp.zipList.forEach(function(zip) {
        zipOptions += '<option value="' + escapeHtml(zip.ZIP) + '" data-city="' + escapeHtml(zip.CITY) + '" data-area="' + escapeHtml(zip.AREA) + '">' + escapeHtml(zip.ZIP) + ' ' + escapeHtml(zip.CITY) + ' ' + escapeHtml(zip.AREA) + '</option>';
      });
    }
    $('#ZIPCode').html(zipOptions);
  }).fail(function() {
    alert('無法載入參照資料');
  });
}

function openAddModal() {
  $('#staffModalTitle').text('新增員工');
  $('#formAction').val('insert');
  $('#saveButton').show().text('新增');
  $('#staffForm')[0].reset();
  $('#StaffID').prop('readonly', false);
  $('#Gender').val('');
  $('#Position').val('');
  $('#Active').val('1');
  $('#saveButton').removeClass('d-none');
  $('#staffModal').modal('show');
}

function openEditModal(staffID) {
  $.getJSON('Staffs_api.php', { action: 'view', StaffID: staffID }, function(resp) {
    if (!resp.row) { alert('找不到員工資料'); return; }

    var row = resp.row;
    $('#staffModalTitle').text('編輯員工 ' + staffID);
    $('#formAction').val('update');
    $('#StaffID').val(row.StaffID).prop('readonly', true);
    $('#StoreID').val(row.StoreID).prop('readonly', true);
    $('#Name').val(row.Name).prop('readonly', false);
    $('#Gender').val(row.Gender || '').prop('disabled', false);
    $('#Position').val(row.Position || '').prop('readonly', false);
    $('#Active').val(row.Active == 1 ? '1' : '0').prop('disabled', false);
    $('#ZIPCode').val(row.ZIPCode).trigger('change').prop('disabled', false);
    $('#CITY').val(row.CITY || '').prop('readonly', false);
    $('#AREA').val(row.AREA || '').prop('readonly', false);
    $('#Address').val(row.Address || '').prop('readonly', false);
    $('#TEL').val(row.TEL || '').prop('readonly', false);
    $('#Mobile').val(row.Mobile || '').prop('readonly', false);
    $('#Email').val(row.Email || '').prop('readonly', false);
    $('#LineID').val(row.LineID || '').prop('readonly', false);
    $('#FacebookID').val(row.FacebookID || '').prop('readonly', false);
    $('#Birthday').val(row.Birthday || '').prop('readonly', false);
    $('#saveButton').show().text('更新');
    $('#staffModal').modal('show');
  }).fail(function() {
    alert('無法取得員工資料');
  });
}

function openViewModal(staffID) {
  $.getJSON('Staffs_api.php', { action: 'view', StaffID: staffID }, function(resp) {
    if (!resp.row) { alert('找不到員工資料'); return; }

    var row = resp.row;
    $('#staffModalTitle').text('檢視員工 ' + staffID);
    $('#formAction').val('view');
    $('#StaffID').val(row.StaffID).prop('readonly', true);
    $('#StoreID').val(row.StoreID).prop('disabled', true);
    $('#Name').val(row.Name).prop('readonly', true);
    $('#Gender').val(row.Gender || '').prop('disabled', true);
    $('#Position').val(row.Position || '').prop('readonly', true);
    $('#Active').val(row.Active == 1 ? '1' : '0').prop('disabled', true);
    $('#ZIPCode').val(row.ZIPCode).trigger('change').prop('disabled', true);
    $('#CITY').val(row.CITY || '');
    $('#AREA').val(row.AREA || '');
    $('#Address').val(row.Address || '').prop('readonly', true);
    $('#TEL').val(row.TEL || '').prop('readonly', true);
    $('#Mobile').val(row.Mobile || '').prop('readonly', true);
    $('#Email').val(row.Email || '').prop('readonly', true);
    $('#LineID').val(row.LineID || '').prop('readonly', true);
    $('#FacebookID').val(row.FacebookID || '').prop('readonly', true);
    $('#Birthday').val(row.Birthday || '').prop('readonly', true);
    $('#saveButton').hide();
    $('#staffModal').modal('show');
  }).fail(function() {
    alert('無法取得員工資料');
  });
}

function resetModalState() {
  $('#StoreID, #Gender, #Active, #ZIPCode').prop('disabled', false);
  $('#Name, #Address, #TEL, #Mobile, #Email, #LineID, #FacebookID, #Birthday, #Position').prop('readonly', false);
  $('#saveButton').show();
}

function deleteStaff(staffID) {
  if (!confirm('確定要刪除員工 ' + staffID + ' 嗎？')) return;
  $.post('Staffs_api.php', { action: 'delete', StaffID: staffID }, function(resp) {
    if (resp.success) {
      loadStaffs(currentPage);
    } else {
      alert('刪除失敗');
    }
  }, 'json').fail(function() {
    alert('刪除失敗，請稍後再試。');
  });
}

function exportCsv() {
  var rows = [];
  var headers = [];
  $('#staffsTable thead th:visible').each(function() {
    var key = $(this).data('key');
    if (!key || key === 'actions') return;
    headers.push($(this).text().trim());
  });
  rows.push(headers.join(','));
  $('#staffsTable tbody tr').each(function() {
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
  var url = URL.createObjectURL(blob);
  var link = document.createElement('a');
  link.href = url;
  link.download = 'staffs_export.csv';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
}

$(document).ready(function() {
  buildColumnToggles();
  applyColumnVisibility();
  loadLookups();
  loadStaffs(1);

  $('#searchInput').on('keypress', function(e) {
    if (e.which === 13) { e.preventDefault(); $('#btnSearch').click(); }
  });

  $('#btnSearch').click(function() { currentPage = 1; loadStaffs(1); });
  $('#btnExport').click(exportCsv);
  $('#btnAdd').click(function() { resetModalState(); openAddModal(); });

  $('#ZIPCode').change(function() {
    var selected = $(this).find(':selected');
    $('#CITY').val(selected.data('city') || '');
    $('#AREA').val(selected.data('area') || '');
  });

  $('#staffForm').submit(function(e) {
    e.preventDefault();
    var action = $('#formAction').val();
    if (action !== 'insert' && action !== 'update') return;
    //var data = $(this).serialize();
    var data = {
      action: action,
      StoreID: $('#StoreID').val().trim(),
      StaffID: $('#StaffID').val().trim(),
      Name: $('#Name').val().trim(),
      Gender: $('#Gender').val().trim(),
      Position: $('#Position').val().trim(),
      Active: $('#Active').val().trim(),
      ZIPCode: $('#ZIPCode').val().trim(),
      CITY: $('#CITY').val().trim(),
      AREA: $('#AREA').val().trim(),
      Address: $('#Address').val().trim(),
      TEL: $('#TEL').val().trim(),
      Mobile: $('#Mobile').val().trim(),
      Email: $('#Email').val().trim(),
      LineID: $('#LineID').val().trim(),
      FacebookID: $('#FacebookID').val().trim(),
      Birthday: $('#Birthday').val().trim(),
      IsActive: $('#IsActive').val()
    };

    $.post('Staffs_api.php', data, function(resp) {
      if (resp.success) {
        $('#staffModal').modal('hide');
        loadStaffs(currentPage);
      } else {
        alert('儲存失敗');
      }
    }, 'json').fail(function() {
      alert('儲存失敗，請稍後再試。');
    });
  });

  $('#pagination').on('click', 'a.page-link', function(e) {
    e.preventDefault();
    var page = parseInt($(this).data('page'));
    if (page >= 1) loadStaffs(page);
  });

  makeModalDraggable('#staffModal');
});

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
      $dialog.css({
        left: startLeft + (e.pageX - startX) + 'px',
        top: startTop + (e.pageY - startY) + 'px'
      });
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
</script>
</body>
</html>
