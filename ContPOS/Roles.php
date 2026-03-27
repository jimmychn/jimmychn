<?php
// 模組功能.php
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>Programs 管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="container mt-4">

    <h3>Programs 管理</h3>
	<div class="input-group mb-3">
		<input type="text" class="form-control" id="txtSearch" placeholder="輸入關鍵字查詢 (名稱/代號)">
		<button class="btn btn-secondary" id="btnSearch">查詢</button>
		<button class="btn btn-primary" id="btnAdd">新增程式</button>
	</div>

    <!-- 資料表 -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ProgramID</th>
                <th>ProgramName</th>
                <th>ProgramCode</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody id="dataBody"></tbody>
    </table>

    <!-- 分頁區塊 -->
    <div class="d-flex justify-content-between align-items-center">
        <div id="pageInfo"></div>
        <nav>
            <ul class="pagination" id="pagination"></ul>
        </nav>
    </div>

	<!-- 共用 Modal -->
	<div class="modal fade" id="mainModal" tabindex="-1">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="modalTitle">操作</h5>
			<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
		  </div>
		  <div class="modal-body">
			<form id="formProgram">
			  <input type="hidden" name="ProgramID" id="ProgramID">
			  <div class="mb-3">
				<label class="form-label">ProgramName</label>
				<input type="text" class="form-control" name="ProgramName" id="ProgramName" required>
			  </div>
			  <div class="mb-3">
				<label class="form-label">ProgramCode</label>
				<input type="text" class="form-control" name="ProgramCode" id="ProgramCode" required>
			  </div>
			</form>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">關閉</button>
			<button type="button" class="btn btn-primary" id="modalSave">儲存</button>
		  </div>
		</div>
	  </div>
	</div>
</div>
<script>
let searchKeyword = "";
let currentPage = 1;
let totalPages = 1;

// 查詢按鈕事件
$(document).on("click", "#btnSearch", function(){
    searchKeyword = $("#txtSearch").val();
    loadData(1); // 查詢時從第 1 頁開始
});

// 載入資料 (標準函式宣告)
function loadData(page) {
    $.post("roles_api.php", { action:"list", page:page, keyword:searchKeyword }, function(res){
        let obj = JSON.parse(res);
        let rows = obj.data;
        currentPage = obj.page;
        totalPages = obj.totalPages;

        let html = "";
        $.each(rows, function(i,row){
            html += "<tr>";
            html += "<td>"+row.ProgramID+"</td>";
            html += "<td>"+row.ProgramName+"</td>";
            html += "<td>"+row.ProgramCode+"</td>";
            html += "<td>";
            html += "<button class='btn btn-sm btn-info btnView' data-id='"+row.ProgramID+"'>檢視</button> ";
            html += "<button class='btn btn-sm btn-warning btnEdit' data-id='"+row.ProgramID+"'>編輯</button> ";
            html += "<button class='btn btn-sm btn-danger btnDelete' data-id='"+row.ProgramID+"'>刪除</button>";
            html += "</td>";
            html += "</tr>";
        });
        $("#dataBody").html(html);

        renderPagination();
    });
}

// 分頁按鈕
function renderPagination() {
    let html = "";
    html += "<li class='page-item'><a class='page-link' href='#' data-page='1'>首頁</a></li>";
    html += "<li class='page-item'><a class='page-link' href='#' data-page='"+(currentPage-1)+"'>上頁</a></li>";

    let start = Math.max(1, currentPage-2);
    let end = Math.min(totalPages, currentPage+2);
    for (let i=start; i<=end; i++) {
        html += "<li class='page-item "+(i==currentPage?"active":"")+"'><a class='page-link' href='#' data-page='"+i+"'>"+i+"</a></li>";
    }

    html += "<li class='page-item'><a class='page-link' href='#' data-page='"+(currentPage+1)+"'>下頁</a></li>";
    html += "<li class='page-item'><a class='page-link' href='#' data-page='"+totalPages+"'>末頁</a></li>";

    $("#pagination").html(html);
    $("#pageInfo").text("第 "+currentPage+" 頁 / 共 "+totalPages+" 頁");
}

// 分頁點擊事件
$(document).on("click", "#pagination a", function(e){
    e.preventDefault();
    let page = parseInt($(this).data("page"));
    if (page>=1 && page<=totalPages) {
        loadData(page);
    }
});

// 新增
$("#btnAdd").click(function(){
    $("#modalTitle").text("新增 Program");
    $("#ProgramID").val("");
    $("#ProgramName").val("").prop("readonly", false);
    $("#ProgramCode").val("").prop("readonly", false);
    $("#modalSave").show().text("儲存").off("click").on("click", function(){
        let formData = $("#formProgram").serialize();
        $.post("roles_api.php", { action:"create", data:formData }, function(res){
            let obj = JSON.parse(res);
            if (obj.success) {
                $("#mainModal").modal("hide");
                loadData(currentPage);
            } else {
                alert("新增失敗: " + obj.message);
            }
        });
    });
    $("#mainModal").modal("show");
});

// 檢視 (唯讀)
$(document).on("click", ".btnView", function(){
    let id = $(this).data("id");
    $.post("roles_api.php", { action:"view", id:id }, function(res){
        let obj = JSON.parse(res);
        if (obj.success) {
            $("#modalTitle").text("檢視 Program");
            $("#ProgramID").val(obj.data.ProgramID);
            $("#ProgramName").val(obj.data.ProgramName).prop("readonly", true);
            $("#ProgramCode").val(obj.data.ProgramCode).prop("readonly", true);
            $("#modalSave").hide(); // 檢視不用儲存
            $("#mainModal").modal("show");
        } else {
            alert("查詢失敗: " + obj.message);
        }
    });
});

// 編輯
$(document).on("click", ".btnEdit", function(){
    let id = $(this).data("id");
    $.post("roles_api.php", { action:"view", id:id }, function(res){
        let obj = JSON.parse(res);
        if (obj.success) {
            $("#modalTitle").text("編輯 Program");
            $("#ProgramID").val(obj.data.ProgramID);
            $("#ProgramName").val(obj.data.ProgramName).prop("readonly", false);
            $("#ProgramCode").val(obj.data.ProgramCode).prop("readonly", false);
            $("#modalSave").show().text("更新").off("click").on("click", function(){
                let formData = $("#formProgram").serialize();
                $.post("roles_api.php", { action:"update", data:formData }, function(res){
                    let obj = JSON.parse(res);
                    if (obj.success) {
                        $("#mainModal").modal("hide");
                        loadData(currentPage);
                    } else {
                        alert("更新失敗: " + obj.message);
                    }
                });
            });
            $("#mainModal").modal("show");
        } else {
            alert("查詢失敗: " + obj.message);
        }
    });
});

// 刪除
$(document).on("click", ".btnDelete", function(){
    if (!confirm("確定要刪除這筆資料嗎？")) return;
    let id = $(this).data("id");
    $.post("roles_api.php", { action:"delete", id:id }, function(res){
        let obj = JSON.parse(res);
        if (obj.success) {
            loadData(currentPage);
        } else {
            alert("刪除失敗: " + obj.message);
        }
    });
});



// 初始化
$(function(){
    loadData();
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
