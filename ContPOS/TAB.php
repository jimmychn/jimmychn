<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TAB 清單管理</title>
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
    #tabsTable td[data-key="actions"] { width: 180px; white-space: nowrap; }
    .form-label { font-weight: 600; }
  </style>
</head>
<body>
<div class="container-fluid p-2">
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-start mb-4">
    <div>
      <h3 class="mb-1">TAB 清單管理</h3>
      <p class="text-secondary mb-0">管理通用清單資料，提供查詢、新增、編輯與刪除功能。</p>
    </div>
  </div>

  <div class="row align-items-center mb-3">
    <div class="col-md-8 mb-2 mb-md-0">
      <div class="input-group">
        <select id="filterTNo" class="form-select">
          <option value="">全部 T_NO</option>
        </select>
        <input type="text" id="searchInput" class="form-control" placeholder="搜尋 TD_NO 或 TD_NAME">
        <button class="btn btn-primary" id="btnSearch">搜尋</button>
      </div>
    </div>
    <div class="col-md-4 text-md-end">
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
    <table class="table table-bordered table-striped" id="tabsTable">
      <thead>
        <tr>
          <th data-key="T_NO">T_NO</th>
          <th data-key="TD_NO">TD_NO</th>
          <th data-key="TD_NAME">TD_NAME</th>
          <th data-key="SEQ">SEQ</th>
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

<div class="modal fade" id="tabModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form id="tabForm">
        <input type="hidden" id="formAction" name="action" value="">
        <div class="modal-header">
          <h5 class="modal-title" id="tabModalTitle">TAB 資料</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="關閉"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">T_NO</label>
              <select id="T_NO" name="T_NO" class="form-select">
                <option value="">(空白)</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">TD_NO</label>
              <input type="text" id="TD_NO" name="TD_NO" class="form-control" maxlength="20">
            </div>
            <div class="col-md-4">
              <label class="form-label">SEQ</label>
              <input type="number" id="SEQ" name="SEQ" class="form-control" min="0" value="0">
            </div>
            <div class="col-md-12">
              <label class="form-label">TD_NAME</label>
              <input type="text" id="TD_NAME" name="TD_NAME" class="form-control" maxlength="30">
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
  { key: 'T_NO', label: 'T_NO' },
  { key: 'TD_NO', label: 'TD_NO' },
  { key: 'TD_NAME', label: 'TD_NAME' },
  { key: 'SEQ', label: 'SEQ' }
];
var visibleColumns = loadVisibleColumns();

function loadVisibleColumns() {
  var saved = localStorage.getItem('tab_columns');
  if (saved) {
    try { return JSON.parse(saved); } catch (e) { }
  }
  var obj = {};
  columns.forEach(function(col) { obj[col.key] = true; });
  return obj;
}

function saveVisibleColumns() {
  localStorage.setItem('tab_columns', JSON.stringify(visibleColumns));
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
  $('#tabsTable thead th').each(function() {
    var key = $(this).data('key');
    if (!key) return;
    $(this).toggle(visibleColumns[key] !== false);
  });
  $('#tabsTable tbody tr').each(function() {
    $(this).find('td').each(function() {
      var key = $(this).data('key');
      if (!key) return;
      $(this).toggle(visibleColumns[key] !== false);
    });
  });
}

function loadLookups() {
  $.getJSON('TAB_api.php', { action: 'lookups' }, function(resp) {
    var filterOptions = '<option value="">全部 T_NO</option>';
    var formOptions = '<option value="">(空白)</option>';
    if (resp.tnoList) {
      resp.tnoList.forEach(function(item) {
        filterOptions += '<option value="' + escapeHtml(item.T_NO) + '">' + escapeHtml(item.T_NO) + ' ' + escapeHtml(item.T_NAME) + '</option>';
        formOptions += '<option value="' + escapeHtml(item.T_NO) + '">' + escapeHtml(item.T_NO) + ' ' + escapeHtml(item.T_NAME) + '</option>';
      });
    }
    $('#filterTNo').html(filterOptions);
    $('#T_NO').html(formOptions);
  }).fail(function() {
    console.warn('無法載入 T_NO 清單');
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

function loadTabs(page) {
  currentPage = page || 1;
  var keyword = $('#searchInput').val();
  var tnoFilter = $('#filterTNo').val();
  $.getJSON('TAB_api.php', { action: 'list', page: currentPage, pageSize: pageSize, T_NO: tnoFilter, search: keyword }, function(resp) {
    totalRows = resp.total || 0;
    var html = '';
    if (resp.rows && resp.rows.length > 0) {
      resp.rows.forEach(function(row) {
        html += '<tr>';
        html += '<td data-key="T_NO">' + escapeHtml(row.T_NO) + '</td>';
        html += '<td data-key="TD_NO">' + escapeHtml(row.TD_NO) + '</td>';
        html += '<td data-key="TD_NAME">' + escapeHtml(row.TD_NAME) + '</td>';
        html += '<td data-key="SEQ">' + escapeHtml(row.SEQ) + '</td>';
        html += '<td data-key="actions" class="text-end">';
        html += '<button type="button" class="btn btn-sm btn-primary ms-1 view-tab" data-tno="' + escapeHtml(row.T_NO) + '" data-tdno="' + escapeHtml(row.TD_NO) + '">檢視</button>';
        html += '<button type="button" class="btn btn-sm btn-warning ms-1 edit-tab" data-tno="' + escapeHtml(row.T_NO) + '" data-tdno="' + escapeHtml(row.TD_NO) + '">編輯</button>';
        html += '<button type="button" class="btn btn-sm btn-danger ms-1 delete-tab" data-tno="' + escapeHtml(row.T_NO) + '" data-tdno="' + escapeHtml(row.TD_NO) + '">刪除</button>';
        html += '</td>';
        html += '</tr>';
      });
    } else {
      html += '<tr><td colspan="5" class="text-center">目前沒有資料</td></tr>';
    }
    $('#tabsTable tbody').html(html);
    applyColumnVisibility();
    buildPagination();
  }).fail(function() {
    alert('無法載入 TAB 資料，請稍後再試。');
  });
}

function openAddModal() {
  $('#tabModalTitle').text('新增 TAB 資料');
  $('#formAction').val('insert');
  $('#saveButton').show().text('新增');
  $('#tabForm')[0].reset();
  $('#T_NO, #TD_NO').prop('readonly', false).prop('disabled', false);
  $('#T_NO').val($('#filterTNo').val() || '');
  $('#SEQ').val('0');
  $('#saveButton').removeClass('d-none');
  $('#tabModal').modal('show');
}

function openEditModal(tno, tdno) {
  $.getJSON('TAB_api.php', { action: 'view', T_NO: tno, TD_NO: tdno }, function(resp) {
    if (!resp.row) { alert('找不到資料'); return; }
    $('#tabModalTitle').text('編輯 TAB 資料');
    $('#formAction').val('update');
    $('#T_NO').val(resp.row.T_NO).prop('disabled', false).prop('readonly', false);
    $('#TD_NO').val(resp.row.TD_NO).prop('disabled', false).prop('readonly', false);
    $('#TD_NAME').val(resp.row.TD_NAME || '').prop('disabled', false).prop('readonly', false);
    $('#SEQ').val(resp.row.SEQ || 0).prop('disabled', false).prop('readonly', false);
    $('#saveButton').show().text('更新');
    $('#tabModal').modal('show');
  }).fail(function() {
    alert('無法取得資料');
  });
}

function openViewModal(tno, tdno) {
  $.getJSON('TAB_api.php', { action: 'view', T_NO: tno, TD_NO: tdno }, function(resp) {
    if (!resp.row) { alert('找不到資料'); return; }
    $('#tabModalTitle').text('檢視 TAB 資料');
    $('#formAction').val('view');
    $('#T_NO').val(resp.row.T_NO).prop('disabled', true);
    $('#TD_NO').val(resp.row.TD_NO).prop('readonly', true);
    $('#TD_NAME').val(resp.row.TD_NAME || '').prop('readonly', true);
    $('#SEQ').val(resp.row.SEQ || 0).prop('readonly', true);
    $('#saveButton').hide();
    $('#tabModal').modal('show');
  }).fail(function() {
    alert('無法取得資料');
  });
}

function deleteTab(tno, tdno) {
  if (!confirm('確定要刪除 T_NO=' + tno + '，TD_NO=' + tdno + ' 的資料嗎？')) return;
  $.post('TAB_api.php', { action: 'delete', T_NO: tno, TD_NO: tdno }, function(resp) {
    if (resp.success) {
      loadTabs(currentPage);
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
  $('#tabsTable thead th:visible').each(function() {
    var key = $(this).data('key');
    if (!key || key === 'actions') return;
    headers.push($(this).text().trim());
  });
  rows.push(headers.join(','));
  $('#tabsTable tbody tr').each(function() {
    var cells = [];
    $(this).find('td:visible').each(function() {
      var key = $(this).data('key');
      if (!key || key === 'actions') return;
      cells.push('"' + $(this).text().trim().replace(/"/g, '""') + '"');
    });
    if (cells.length > 0) rows.push(cells.join(','));
  });
  var blob = new Blob([rows.join('\n')], { type: 'text/csv;charset=utf-8;' });
  var url = URL.createObjectURL(blob);
  var link = document.createElement('a');
  link.href = url;
  link.download = 'TAB_export.csv';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
}

$(document).ready(function() {
  buildColumnToggles();
  applyColumnVisibility();
  loadLookups();
  loadTabs(1);

  $('#searchInput').on('keypress', function(e) {
    if (e.which === 13) { e.preventDefault(); $('#btnSearch').click(); }
  });

  $('#btnSearch').click(function() { currentPage = 1; loadTabs(1); });
  $('#filterTNo').change(function() { currentPage = 1; loadTabs(1); });
  $('#btnExport').click(exportCsv);
  $('#btnAdd').click(openAddModal);

  $('#tabsTable').on('click', '.view-tab', function() {
    openViewModal($(this).data('tno'), $(this).data('tdno'));
  });

  $('#tabsTable').on('click', '.edit-tab', function() {
    openEditModal($(this).data('tno'), $(this).data('tdno'));
  });

  $('#tabsTable').on('click', '.delete-tab', function() {
    deleteTab($(this).data('tno'), $(this).data('tdno'));
  });

  $('#tabForm').submit(function(e) {
    e.preventDefault();
    var action = $('#formAction').val();
    if (action !== 'insert' && action !== 'update') return;
    $.post('TAB_api.php', $(this).serialize(), function(resp) {
      if (resp.success) {
        $('#tabModal').modal('hide');
        loadTabs(currentPage);
      } else {
        alert(resp.message || '儲存失敗');
      }
    }, 'json').fail(function() {
      alert('儲存失敗，請稍後再試。');
    });
  });

  $('#pagination').on('click', 'a.page-link', function(e) {
    e.preventDefault();
    var page = parseInt($(this).data('page'));
    if (page >= 1) loadTabs(page);
  });

  makeModalDraggable();
});

function makeModalDraggable() {
  var isDragging = false;
  var startX = 0;
  var startY = 0;
  var startLeft = 0;
  var startTop = 0;
  var $dialog = null;

  $('#tabModal .modal-header').css('cursor', 'move').on('mousedown', function(e) {
    $dialog = $(this).closest('.modal-dialog');
    isDragging = true;
    startX = e.pageX;
    startY = e.pageY;
    var offset = $dialog.offset();
    startLeft = offset.left;
    startTop = offset.top;
    $dialog.css({ position: 'absolute', margin: 0, left: startLeft + 'px', top: startTop + 'px' });
    $('body').on('mousemove.tabDrag', function(e) {
      if (!isDragging) return;
      $dialog.css({
        left: startLeft + (e.pageX - startX) + 'px',
        top: startTop + (e.pageY - startY) + 'px'
      });
    }).on('mouseup.tabDrag', function() {
      isDragging = false;
      $('body').off('.tabDrag');
    });
    e.preventDefault();
  });

  $('#tabModal').on('show.bs.modal', function() {
    var $dialog = $(this).find('.modal-dialog');
    $dialog.css({ position: '', left: '', top: '', margin: '' });
  });

  $('#tabModal').on('hidden.bs.modal', function() {
    if ($dialog) {
      $dialog.css({ position: '', left: '', top: '', margin: '' });
      $dialog = null;
    }
  });
}
</script>
</body>
</html>
