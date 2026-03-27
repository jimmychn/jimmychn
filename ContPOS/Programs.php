<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <title>Programs 管理</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.1/dist/bootstrap-table.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.1/dist/bootstrap-table.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.1/dist/extensions/export/bootstrap-table-export.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/tableexport/5.2.0/js/tableexport.min.js"></script>
  <style>
    /* 表格表頭淺藍底色 */
    .table thead th {
        background-color: #e6f2ff;
    }
  </style>
</head>
<body class="container mt-4">

  <h3>Programs 管理</h3>

  <!-- 查詢區塊 -->
  <div class="mb-3">
    <div class="input-group">
      <input type="text" id="txtSearch" class="form-control" placeholder="關鍵字">
      <select id="ddlActive" class="form-select" style="max-width:150px;">
        <option value="">全部</option>
        <option value="1">啟用</option>
        <option value="0">停用</option>
      </select>
      <button id="btnSearch" class="btn btn-secondary">查詢</button>
      <button id="btnAdd" class="btn btn-primary">新增</button>
      <button id="btnView" class="btn btn-info">檢視</button>
      <button id="btnEdit" class="btn btn-warning">編輯</button>
      <button id="btnDelete" class="btn btn-danger">刪除</button>
    </div>
  </div>

  <!-- Bootstrap Table -->
  <table id="programTable"
		 data-locale="zh-TW"
         data-toggle="table"
         data-pagination="true"
         data-side-pagination="server"
         data-click-to-select="true"
         data-show-export="true"
		 data-show-columns="true" 
		 data-search="true"		 
         data-cookie="true"              
		 data-cookie-id-table="ProgramsTable"
	     data-url="Programs_api.php?action=list"
         class="table table-striped table-bordered">
    <thead>
      <tr>
        <th data-field="state" data-checkbox="true"></th>
        <th data-field="ProgramID" data-sortable="true">ProgramID</th>
        <th data-field="ProgramName" data-sortable="true">ProgramName</th>
        <th data-field="ProgramCode" data-sortable="true">ProgramCode</th>
        <th data-field="Active">Active</th>
        <th data-field="CreatedAt">CreatedAt</th>
        <th data-field="ModifiedAt">ModifiedAt</th>
      </tr>
    </thead>
  </table>

  <!-- Modal 新增/編輯 -->
  <div class="modal fade" id="programModal" tabindex="-1">
    <div class="modal-dialog">
      <form id="programForm" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Program</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="ProgramID" name="ProgramID">
          <div class="mb-3">
            <label class="form-label">ProgramName</label>
            <input type="text" id="ProgramName" name="ProgramName" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">ProgramCode</label>
            <input type="text" id="ProgramCode" name="ProgramCode" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Active</label>
            <select id="Active" name="Active" class="form-select">
              <option value="1">啟用</option>
              <option value="0">停用</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" id="btnSave" class="btn btn-primary">儲存</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">關閉</button>
        </div>
      </form>
    </div>
  </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(function(){
  // 查詢
  $("#btnSearch").click(function(){
    let keyword = $("#txtSearch").val();
    let active = $("#ddlActive").val();
    $('#programTable').bootstrapTable('refresh', {
      query: { keyword: keyword, active: active }
    });
  });

  // 新增
  $("#btnAdd").click(function(){
    $("#programForm")[0].reset();
    $("#ProgramID").val("");
    $("#modalTitle").text("新增 Program");
    $("#btnSave").show();
    $("#ProgramName, #ProgramCode, #Active").prop("disabled", false);
    $("#programModal").modal("show");
  });
  
  // 編輯
  $("#btnEdit").click(function(){
    let selections = $('#programTable').bootstrapTable('getSelections');
    if(selections.length !== 1){
      alert("請選擇一筆資料進行編輯");
      return;
    }
    let row = selections[0];
    $("#ProgramID").val(row.ProgramID);
    $("#ProgramName").val(row.ProgramName);
    $("#ProgramCode").val(row.ProgramCode);
    $("#Active").val(row.Active);
    $("#modalTitle").text("編輯 Program");
    $("#btnSave").show();
    $("#ProgramName, #ProgramCode, #Active").prop("disabled", false);
    $("#programModal").modal("show");
  });
  
  // 檢視
  $("#btnView").click(function(){
    let selections = $('#programTable').bootstrapTable('getSelections');
    if(selections.length !== 1){
      alert("請選擇一筆資料進行檢視");
      return;
    }
    let row = selections[0];
    $("#ProgramID").val(row.ProgramID);
    $("#ProgramName").val(row.ProgramName);
    $("#ProgramCode").val(row.ProgramCode);
    $("#Active").val(row.Active);
    $("#modalTitle").text("檢視 Program");
    $("#btnSave").hide(); // 檢視模式不顯示儲存
    $("#ProgramName, #ProgramCode, #Active").prop("disabled", true); // 欄位唯讀
    $("#programModal").modal("show");
  });
  
  // 儲存 (新增/編輯)
  $("#programForm").submit(function(e){
    e.preventDefault();
    let id = $("#ProgramID").val();
    let action = id ? "update" : "insert";
    $.post("Programs_api.php?action=" + action, $(this).serialize(), function(res){
      let obj = JSON.parse(res);
      if(obj.success){
        $("#programModal").modal("hide");
        $('#programTable').bootstrapTable('refresh');
      } else {
        alert("操作失敗: " + obj.message);
      }
    });
  });
});
</script>
</body>
</html>
