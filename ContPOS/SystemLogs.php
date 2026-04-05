<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Logs 管理</title>
    <!-- Bootstrap & Bootstrap Table CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5/dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.27.0/dist/bootstrap-table.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

	<style>
		/* 表頭淺藍底色 */
		.bootstrap-table .table thead th {
			background-color: #e6f2ff;
			color: #000;
		}
	</style>
</head>
<body>
<div class="container-fluid mt-3">
    <h3>System Logs</h3>
    <table id="logsTable"
       data-cookie="true"
       data-cookie-id-table="SystemLogsTable"
       data-page-size="10"
       data-pagination="true"
       data-search="true"
	   data-search-on-enter-key="true"
       data-show-columns="true"
	   data-show-export="true"
	   data-show-refresh="true"
       data-side-pagination="server"
       data-sort-name="CreatedAt"
       data-sort-order="desc"
       data-toggle="table"
	   data-toolbar="#toolbar"
       data-url="SystemLogs_api.php?action=list"
       class="table table-bordered table-hover table-striped">
        <thead>
            <tr>
                <th data-field="LogID" data-sortable="true">LogID</th>
                <th data-field="Username" data-sortable="true">Username</th>
                <th data-field="StoreID" data-sortable="true">StoreID</th>
                <th data-field="ModuleName" data-sortable="true">Module</th>
                <th data-field="Activity" data-sortable="true">Activity</th>
                <th data-field="Status" data-sortable="true">Status</th>
                <th data-field="IPAddress">IP</th>
                <th data-field="CreatedAt" data-sortable="true">Created At</th>
                <th data-field="operate" data-formatter="operateFormatter" data-events="operateEvents">操作</th>
            </tr>
        </thead>
    </table>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewLogModal" tabindex="-1" role="dialog" aria-labelledby="viewLogLabel">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="viewLogLabel">檢視 Log 詳細資料</h5>
        <!-- 關閉按鈕放在右上角 -->
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="關閉"></button>
      </div>
      <div class="modal-body">
        <form>
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">LogID</label>
              <input type="text" class="form-control" id="viewLogID" readonly>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Username</label>
              <input type="text" class="form-control" id="viewUsername" readonly>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">StoreID</label>
              <input type="text" class="form-control" id="viewStoreID" readonly>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Module</label>
              <input type="text" class="form-control" id="viewModuleName" readonly>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Activity</label>
              <input type="text" class="form-control" id="viewActivity" readonly>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Status</label>
              <input type="text" class="form-control" id="viewStatus" readonly>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">IP</label>
              <input type="text" class="form-control" id="viewIP" readonly>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">CreatedAt</label>
              <input type="text" class="form-control" id="viewCreatedAt" readonly>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 mb-3">
              <label class="form-label">ErrorMessage</label>
              <textarea class="form-control" id="viewErrorMessage" rows="3" readonly></textarea>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- JS 套件 -->
<script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.27.0/dist/bootstrap-table.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.27.0/dist/bootstrap-table-locale-all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.27.0/dist/extensions/export/bootstrap-table-export.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin@1.29.0/tableExport.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootbox/dist/bootbox.min.js"></script>

<script>
// 操作欄位格式化
function operateFormatter(value, row, index) {
    return '<button class="btn btn-sm btn-info viewLog" data-id="'+ row.LogID +'">View</button>';
}


$.extend($.fn.bootstrapTable.defaults, {
  iconsPrefix: 'fa',
  icons: {
    refresh: 'fa fa-sync',
    columns: 'fa fa-th',
    toggle: 'fa fa-list',
    export: 'fa fa-download'
  }
});

/*
$('#logsTable').bootstrapTable({
  cookie: true,
  cookie-id-table: 'SystemLogsTable',
  page-size: 10,
  pagination: true,
  search: true,
  search-on-enter-key: true,
  show-columns: true
  show-export: true,
  show-refresh: true,
  sidepagination: 'server',
  sort-name: 'CreatedAt',
  sort-order: 'desc',
  toggle: 'table.
  toolbar: '#toolbar'
  url: 'SystemLogs_api.php?action=list',
  iconsPrefix: 'fa',
  icons: {
    refresh: 'fa fa-sync',
    columns: 'fa fa-th',
    toggle: 'fa fa-list',
    export: 'fa fa-download'
  }
});
*/


// 這不是 jQuery，而是 Bootstrap Table 的事件綁定方式
window.operateEvents = {
    'click .viewLog': function (e, value, row, index) {
        $.get('SystemLogs_api.php?action=view&LogID=' + row.LogID, function(data) {
            let log = JSON.parse(data).row;

            // 填入 modal 欄位
            $('#viewLogID').val(log.LogID);
            $('#viewUsername').val(log.Username);
            $('#viewStoreID').val(log.StoreID);
            $('#viewModuleName').val(log.ModuleName);
            $('#viewActivity').val(log.Activity);
            $('#viewStatus').val(log.Status);
            $('#viewIP').val(log.IPAddress);
            $('#viewErrorMessage').val(log.ErrorMessage ?? '');
            $('#viewCreatedAt').val(log.CreatedAt);

            // 顯示 modal
            $('#viewLogModal').modal('show');
        });
    }
};

$.extend($.fn.bootstrapTable.defaults, {
    formatShowingRows: function (pageFrom, pageTo, totalRows) {
        // 取得目前頁數與總頁數
        let currentPage = Math.ceil(pageFrom / this.pageSize);
        let totalPages  = Math.ceil(totalRows / this.pageSize);

        return '第 ' + currentPage + ' 頁，共 ' + totalPages + ' 頁';
    },
    formatRecordsPerPage: function (pageNumber) {
        return pageNumber + ' 筆/頁';
    }
});

</script>
</body>
</html>
