<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>門市管理</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    .table thead { background: #d9edf7; }
    .table tbody tr:nth-of-type(odd) { background: #f9fbfd; }
    .table tbody tr:hover { background: #eef7fd; }
    .column-toggle-panel { max-width: 320px; }
    .cursor-pointer { cursor: pointer; }
    .modal-body .form-label { font-weight: 600; }
    th[data-key="actions"], td[data-key="actions"] {
      position: sticky;
      right: 0;
      background: #ffffff;
      z-index: 3;
      width: 210px;
      min-width: 210px;
      max-width: 210px;
    }
    td[data-key="actions"] {
      display: flex;
      justify-content: flex-end;
      align-items: center;
      gap: .25rem;
      white-space: nowrap;
      padding: .5rem .75rem;
    }
    td[data-key="actions"] .btn {
      padding: .35rem .55rem;
      font-size: .8rem;
    }
  </style>
</head>
<body class="bg-light">

<div class="container-fluid p-4">
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start mb-4">
    <div>
      <h3 class="mb-1">門市管理</h3>
    </div>
  </div>

  <div class="row align-items-center mb-3">
    <div class="col-md-5 mb-2 mb-md-0">
      <div class="input-group">
        <input type="text" id="searchInput" class="form-control" placeholder="搜尋門市代號、名稱、縣市、區域、地址">
        <button class="btn btn-primary" id="btnSearch">搜尋</button>
      </div>
    </div>
    <div class="col-md-7 text-md-end">
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
    <table class="table table-bordered table-striped" id="storesTable">
      <thead>
        <tr>
          <th data-key="StoreID">門市代號</th>
          <th data-key="StoreName">門市名稱</th>
          <th data-key="TAXID">統一編號</th>
          <th data-key="TITLE">抬頭</th>
          <th data-key="ZIPCode">郵遞區號</th>
          <th data-key="CITY">縣市</th>
          <th data-key="AREA">區域</th>
          <th data-key="Address">地址</th>
          <th data-key="ManagerID">經理代號</th>
          <th data-key="TEL">電話</th>
          <th data-key="FAX">傳真</th>
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

<div class="modal fade" id="storeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <form id="storeForm">
        <input type="hidden" id="formAction" value="">
        <div class="modal-header">
          <h5 class="modal-title" id="storeModalTitle">門市資料</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="關閉"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label">門市代號</label>
              <input type="text" id="StoreID" name="StoreID" class="form-control" required maxlength="3">
            </div>
            <div class="col-md-3">
              <label class="form-label">門市名稱</label>
              <input type="text" id="StoreName" name="StoreName" class="form-control" required maxlength="100">
            </div>
            <div class="col-md-3">
              <label class="form-label">統一編號</label>
              <input type="text" id="TAXID" name="TAXID" class="form-control" maxlength="20">
            </div>
            <div class="col-md-3">
              <label class="form-label">抬頭</label>
              <input type="text" id="TITLE" name="TITLE" class="form-control" maxlength="80">
            </div>
            <div class="col-md-3">
              <label class="form-label">郵遞區號</label>
              <select id="ZIPCode" name="ZIPCode" class="form-select"></select>
            </div>
            <div class="col-md-3">
              <label class="form-label">縣市</label>
              <input type="text" id="CITY" name="CITY" class="form-control" readonly>
            </div>
            <div class="col-md-3">
              <label class="form-label">區域</label>
              <input type="text" id="AREA" name="AREA" class="form-control" readonly>
            </div>
            <div class="col-md-3">
              <label class="form-label">經理代號</label>
              <select id="ManagerID" name="ManagerID" class="form-select"></select>
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
              <label class="form-label">傳真</label>
              <input type="text" id="FAX" name="FAX" class="form-control" maxlength="20">
            </div>
            <div class="col-md-3">
              <label class="form-label">是否啟用</label>
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
  { key: 'TAXID', label: '統一編號' },
  { key: 'TITLE', label: '抬頭' },
  { key: 'ZIPCode', label: '郵遞區號' },
  { key: 'CITY', label: '縣市' },
  { key: 'AREA', label: '區域' },
  { key: 'Address', label: '地址' },
  { key: 'ManagerID', label: '經理代號' },
  { key: 'TEL', label: '電話' },
  { key: 'FAX', label: '傳真' },
  { key: 'IsActive', label: '啟用' }
];
var visibleColumns = loadVisibleColumns();

function loadVisibleColumns() {
  var saved = localStorage.getItem('stores_columns');
  if (saved) {
    try {
      return JSON.parse(saved);
    } catch (e) {
      return {};
    }
  }
  var obj = {};
  for (var i = 0; i < columns.length; i++) {
    obj[columns[i].key] = true;
  }
  return obj;
}

function saveVisibleColumns() {
  localStorage.setItem('stores_columns', JSON.stringify(visibleColumns));
}

function buildColumnToggles() {
  var html = '';
  for (var i = 0; i < columns.length; i++) {
    var col = columns[i];
    html += '<div class="col-6 col-md-4">';
    html += '<div class="form-check">';
    html += '<input class="form-check-input" type="checkbox" id="colToggle_' + col.key + '" data-key="' + col.key + '" ' + (visibleColumns[col.key] ? 'checked' : '') + '>';
    html += '<label class="form-check-label" for="colToggle_' + col.key + '">' + col.label + '</label>';
    html += '</div></div>';
  }
  $('#columnCheckboxArea').html(html);
  $('#columnCheckboxArea input[type=checkbox]').on('change', function () {
    var key = $(this).data('key');
    visibleColumns[key] = $(this).is(':checked');
    saveVisibleColumns();
    applyColumnVisibility();
  });
}

function applyColumnVisibility() {
  $('#storesTable thead th').each(function () {
    var key = $(this).data('key');
    if (!key) return;
    $(this).toggle(visibleColumns[key] !== false);
  });
  $('#storesTable tbody tr').each(function () {
    $(this).find('td').each(function () {
      var key = $(this).data('key');
      if (!key) return;
      $(this).toggle(visibleColumns[key] !== false);
    });
  });
}

function buildPagination() {
  var totalPages = Math.ceil(totalRows / pageSize);
  var html = '';
  var pageWindow = 5;
  var startPage = Math.max(1, currentPage - Math.floor(pageWindow / 2));
  var endPage = Math.min(totalPages, startPage + pageWindow - 1);
  if (endPage - startPage < pageWindow - 1) {
    startPage = Math.max(1, endPage - pageWindow + 1);
  }

  html += '<li class="page-item ' + (currentPage <= 1 ? 'disabled' : '') + '">';
  html += '<a class="page-link" href="#" data-page="' + (currentPage - 1) + '">上一頁</a></li>';

  for (var p = startPage; p <= endPage; p++) {
    html += '<li class="page-item ' + (p === currentPage ? 'active' : '') + '">';
    html += '<a class="page-link" href="#" data-page="' + p + '">' + p + '</a></li>';
  }

  html += '<li class="page-item ' + (currentPage >= totalPages ? 'disabled' : '') + '">';
  html += '<a class="page-link" href="#" data-page="' + (currentPage + 1) + '">下一頁</a></li>';

  $('#pagination').html(html);
  $('#pageInfo').text('第 ' + currentPage + ' 頁 / 共 ' + totalPages + ' 頁');
}

function loadStores(page) {
  currentPage = page || 1;
  var keyword = $('#searchInput').val();
  $.getJSON('Stores_api.php', { action: 'list', page: currentPage, pageSize: pageSize, search: keyword }, function (resp) {
    totalRows = resp.total || 0;
    var html = '';
    if (resp.rows && resp.rows.length > 0) {
      for (var i = 0; i < resp.rows.length; i++) {
        var row = resp.rows[i];
        html += '<tr>';
        html += '<td data-key="StoreID">' + escapeHtml(row.StoreID) + '</td>';
        html += '<td data-key="StoreName">' + escapeHtml(row.StoreName) + '</td>';
        html += '<td data-key="TAXID">' + escapeHtml(row.TAXID) + '</td>';
        html += '<td data-key="TITLE">' + escapeHtml(row.TITLE) + '</td>';
        html += '<td data-key="ZIPCode">' + escapeHtml(row.ZIPCode) + '</td>';
        html += '<td data-key="CITY">' + escapeHtml(row.CITY) + '</td>';
        html += '<td data-key="AREA">' + escapeHtml(row.AREA) + '</td>';
        html += '<td data-key="Address">' + escapeHtml(row.Address) + '</td>';
        html += '<td data-key="ManagerID">' + escapeHtml(row.ManagerID) + '</td>';
        html += '<td data-key="TEL">' + escapeHtml(row.TEL) + '</td>';
        html += '<td data-key="FAX">' + escapeHtml(row.FAX) + '</td>';
        html += '<td data-key="IsActive">' + (row.IsActive == 1 ? '啟用' : '停用') + '</td>';
        html += '<td data-key="actions" class="text-end">'
             + '<button class="btn btn-sm btn-primary" onclick="openViewModal(\'' + row.StoreID + '\')">檢視</button>'
             + '<button class="btn btn-sm btn-warning" onclick="openEditModal(\'' + row.StoreID + '\')">編輯</button>'
             + '<button class="btn btn-sm btn-danger" onclick="deleteStore(\'' + row.StoreID + '\')">刪除</button>'
             + '</td>';
        html += '</tr>';
      }
    } else {
      html += '<tr><td colspan="13" class="text-center">目前沒有資料</td></tr>';
    }
    $('#storesTable tbody').html(html);
    applyColumnVisibility();
    buildPagination();
  }).fail(function () {
    alert('無法載入門市資料，請稍後再試。');
  });
}

function escapeHtml(text) {
  if (text === null || text === undefined) return '';
  return $('<div>').text(text).html();
}

function loadLookups() {
  $.getJSON('Stores_api.php', { action: 'lookups' }, function (resp) {
    var zipOptions = '<option value="">-- 請選郵遞區號 --</option>';
    if (resp.zipList) {
      for (var i = 0; i < resp.zipList.length; i++) {
        var zip = resp.zipList[i];
        zipOptions += '<option value="' + escapeHtml(zip.ZIP) + '" data-city="' + escapeHtml(zip.CITY) + '" data-area="' + escapeHtml(zip.AREA) + '">' + escapeHtml(zip.ZIP) + ' ' + escapeHtml(zip.CITY) + ' ' + escapeHtml(zip.AREA) + '</option>';
      }
    }
    $('#ZIPCode').html(zipOptions);

    var mgrOptions = '<option value="">-- 請選經理 --</option>';
    if (resp.managers) {
      for (var j = 0; j < resp.managers.length; j++) {
        var mgr = resp.managers[j];
        mgrOptions += '<option value="' + escapeHtml(mgr.StaffID) + '">' + escapeHtml(mgr.StaffID) + ' - ' + escapeHtml(mgr.Name) + '</option>';
      }
    }
    $('#ManagerID').html(mgrOptions);
  });
}

function openAddModal() {
  $('#storeModalTitle').text('新增門市');
  $('#formAction').val('insert');
  $('#saveButton').show().text('新增');
  $('#StoreID').prop('readonly', false);
  $('#storeForm')[0].reset();
  $('#CITY').val('');
  $('#AREA').val('');
  $('#storeModal').modal('show');
}

function openEditModal(storeID) {
  $.getJSON('Stores_api.php', { action: 'view', StoreID: storeID }, function (resp) {
    if (!resp.row) {
      alert('找不到門市資料');
      return;
    }
    $('#storeModalTitle').text('編輯門市 ' + storeID);
    $('#formAction').val('update');
    $('#StoreID').val(resp.row.StoreID).prop('readonly', true);
    $('#StoreName').val(resp.row.StoreName);
    $('#TAXID').val(resp.row.TAXID);
    $('#TITLE').val(resp.row.TITLE);
    $('#ZIPCode').val(resp.row.ZIPCode).trigger('change');
    $('#CITY').val(resp.row.CITY);
    $('#AREA').val(resp.row.AREA);
    $('#Address').val(resp.row.Address);
    $('#ManagerID').val(resp.row.ManagerID);
    $('#TEL').val(resp.row.TEL);
    $('#FAX').val(resp.row.FAX);
    $('#IsActive').val(resp.row.IsActive == 1 ? '1' : '0');
    $('#saveButton').show().text('更新');
    $('#storeModal').modal('show');
  }).fail(function () {
    alert('無法取得門市資料');
  });
}

function openViewModal(storeID) {
  $.getJSON('Stores_api.php', { action: 'view', StoreID: storeID }, function (resp) {
    if (!resp.row) {
      alert('找不到門市資料');
      return;
    }
    $('#storeModalTitle').text('檢視門市 ' + storeID);
    $('#formAction').val('view');
    $('#StoreID').val(resp.row.StoreID).prop('readonly', true);
    $('#StoreName').val(resp.row.StoreName);
    $('#TAXID').val(resp.row.TAXID);
    $('#TITLE').val(resp.row.TITLE);
    $('#ZIPCode').val(resp.row.ZIPCode).trigger('change');
    $('#CITY').val(resp.row.CITY);
    $('#AREA').val(resp.row.AREA);
    $('#Address').val(resp.row.Address);
    $('#ManagerID').val(resp.row.ManagerID);
    $('#TEL').val(resp.row.TEL);
    $('#FAX').val(resp.row.FAX);
    $('#IsActive').val(resp.row.IsActive == 1 ? '1' : '0');
    $('#saveButton').hide();
    $('#storeModal').modal('show');
  }).fail(function () {
    alert('無法取得門市資料');
  });
}

function deleteStore(storeID) {
  if (!confirm('確定要刪除門市 ' + storeID + ' 嗎？')) {
    return;
  }
  $.post('Stores_api.php?action=delete', { StoreID: storeID }, function (resp) {
    if (resp.success) {
      loadStores(currentPage);
    } else {
      alert('刪除失敗');
    }
  }, 'json').fail(function () {
    alert('刪除失敗，請稍後再試。');
  });
}

function exportCsv() {
  var rows = [];
  var headers = [];
  $('#storesTable thead th:visible').each(function () {
    var key = $(this).data('key');
    if (!key || key === 'actions') return;
    headers.push($(this).text().trim());
  });
  rows.push(headers.join(','));
  $('#storesTable tbody tr').each(function () {
    var cells = [];
    $(this).find('td:visible').each(function () {
      var key = $(this).data('key');
      if (!key || key === 'actions') return;
      cells.push('"' + $(this).text().trim().replace(/"/g, '""') + '"');
    });
    if (cells.length > 0) {
      rows.push(cells.join(','));
    }
  });
  var csv = rows.join('\n');
  var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  var link = document.createElement('a');
  var url = URL.createObjectURL(blob);
  link.setAttribute('href', url);
  link.setAttribute('download', 'Stores_export_page_' + currentPage + '.csv');
  link.style.visibility = 'hidden';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

$(function () {
  buildColumnToggles();
  loadLookups();
  loadStores(1);

  $('#btnSearch').on('click', function () {
    loadStores(1);
  });

  $('#searchInput').on('keypress', function (e) {
    if (e.which === 13) {
      e.preventDefault();
      loadStores(1);
    }
  });

  $('#btnAdd').on('click', function () {
    openAddModal();
  });

  $('#btnExport').on('click', function () {
    exportCsv();
  });

  $('#pagination').on('click', 'a.page-link', function (e) {
    e.preventDefault();
    var page = parseInt($(this).data('page'), 10);
    if (!isNaN(page) && page >= 1) {
      loadStores(page);
    }
  });

  $('#ZIPCode').on('change', function () {
    var selected = $(this).find('option:selected');
    $('#CITY').val(selected.data('city') || '');
    $('#AREA').val(selected.data('area') || '');
  });

  $('#storeForm').on('submit', function (e) {
    e.preventDefault();
    var action = $('#formAction').val();
    if (action === 'view') {
      $('#storeModal').modal('hide');
      return;
    }
    var data = {
      StoreID: $('#StoreID').val().trim(),
      StoreName: $('#StoreName').val().trim(),
      TAXID: $('#TAXID').val().trim(),
      TITLE: $('#TITLE').val().trim(),
      ZIPCode: $('#ZIPCode').val().trim(),
      CITY: $('#CITY').val().trim(),
      AREA: $('#AREA').val().trim(),
      Address: $('#Address').val().trim(),
      ManagerID: $('#ManagerID').val().trim(),
      TEL: $('#TEL').val().trim(),
      FAX: $('#FAX').val().trim(),
      IsActive: $('#IsActive').val()
    };
    var url = 'Stores_api.php?action=' + (action === 'insert' ? 'insert' : 'update');
    $.post(url, data, function (resp) {
      if (resp.success) {
        $('#storeModal').modal('hide');
        loadStores(currentPage);
      } else {
        alert('儲存失敗，請檢查資料。');
      }
    }, 'json').fail(function () {
      alert('儲存失敗，請稍後再試。');
    });
  });
});
</script>
</body>
</html>

