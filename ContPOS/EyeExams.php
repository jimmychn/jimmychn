<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <title>眼科檢查紀錄管理</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
	<h2>眼科檢查紀錄管理</h2>

	<form id="searchForm" class="mb-3">
	  <div class="input-group">
		<input type="text" id="keyword" class="form-control" placeholder="輸入姓名或電話查詢">
		<button type="submit" class="btn btn-primary">查詢</button>
		<button type="button" id="btnAdd" class="btn btn-success">新增紀錄</button>
	  </div>
	</form>

	<table class="table table-bordered">
	  <thead>
		<tr>
		  <th>客戶</th>
		  <th>檢查日期</th>
		  <th>右眼球面</th>
		  <th>左眼球面</th>
		  <th>驗光人員</th>
		  <th>操作</th>
		</tr>
	  </thead>
	  <tbody id="examTableBody"></tbody>
	</table>

	<nav>
	  <ul class="pagination" id="pagination"></ul>
	</nav>
	<!-- 共用表單 Modal -->
	<div class="modal fade" id="eyeExamModal" tabindex="-1" aria-hidden="true">
	  <div class="modal-dialog modal-xl">
		<div class="modal-content">
		  <form id="eyeExamForm">
			<div class="modal-header">
			  <h5 class="modal-title" id="modalTitle">新增眼科檢查紀錄</h5>
			  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body">
			  <input type="hidden" id="ExamID" name="ExamID">
			  <input type="hidden" id="CustomerID" name="CustomerID">

			  <div class="mb-3">
				<label class="form-label">客戶姓名</label>
				<input type="text" id="customerName" class="form-control" readonly>
				<button type="button" id="btnSelectCustomer" class="btn btn-info mt-2">選擇客戶</button>
			  </div>

			  <div class="mb-3">
				<label class="form-label">檢查日期</label>
				<input type="date" id="ExamDate" name="ExamDate" class="form-control" required>
			  </div>

			  <h5>右眼</h5>
			  <div class="row mb-3">
				<div class="col"><label>球面</label><input type="text" id="SphereRight" name="SphereRight" class="form-control"></div>
				<div class="col"><label>散光</label><input type="text" id="CylinderRight" name="CylinderRight" class="form-control"></div>
				<div class="col"><label>軸位</label><input type="text" id="AxisRight" name="AxisRight" class="form-control"></div>
				<div class="col"><label>角膜曲率</label><input type="text" id="BaseCurveRight" name="BaseCurveRight" class="form-control"></div>
				<div class="col"><label>瞳距</label><input type="text" id="PdRight" name="PdRight" class="form-control"></div>
				<div class="col"><label>加入度</label><input type="text" id="AddRight" name="AddRight" class="form-control"></div>
			  </div>

			  <h5>左眼</h5>
			  <div class="row mb-3">
				<div class="col"><label>球面</label><input type="text" id="SphereLeft" name="SphereLeft" class="form-control"></div>
				<div class="col"><label>散光</label><input type="text" id="CylinderLeft" name="CylinderLeft" class="form-control"></div>
				<div class="col"><label>軸位</label><input type="text" id="AxisLeft" name="AxisLeft" class="form-control"></div>
				<div class="col"><label>角膜曲率</label><input type="text" id="BaseCurveLeft" name="BaseCurveLeft" class="form-control"></div>
				<div class="col"><label>瞳距</label><input type="text" id="PdLeft" name="PdLeft" class="form-control"></div>
				<div class="col"><label>加入度</label><input type="text" id="AddLeft" name="AddLeft" class="form-control"></div>
			  </div>

			  <div class="mb-3">
				<label class="form-label">驗光人員</label>
				<select id="Examiner" name="Examiner" class="form-select"></select>
			  </div>

			  <div class="mb-3">
				<label class="form-label">備註</label>
				<textarea id="Notes" name="Notes" class="form-control"></textarea>
			  </div>
			</div>
			<div class="modal-footer">
			  <button type="submit" class="btn btn-primary">儲存</button>
			  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
			</div>
		  </form>
		</div>
	  </div>
	</div>
	<!-- 客戶選擇 Modal -->
	<div class="modal fade" id="customerDialog" tabindex="-1" aria-hidden="true">
	  <div class="modal-dialog modal-xl">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title">選擇客戶</h5>
			<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
		  </div>
		  <div class="modal-body">
			<input type="text" id="searchCustomer" class="form-control mb-3" placeholder="輸入姓名或電話查詢">
			<table class="table table-bordered">
			  <thead>
				<tr>
				  <th>CustomerID</th>
				  <th>姓名</th>
				  <th>電話</th>
				  <th>地址</th>
				  <th>操作</th>
				</tr>
			  </thead>
			  <tbody id="customerList"></tbody>
			</table>
		  </div>
		</div>
	  </div>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function(){
    let eyeExamModal = new bootstrap.Modal($("#eyeExamModal")[0]);
    let customerDialog = new bootstrap.Modal($("#customerDialog")[0]);
    let currentPage = 1;
    let currentKeyword = "";
    let pageSize = 10;

    // 載入列表
	function loadList(page, keyword){
		$.getJSON("eye_exam_api.php", {action:"list", page:page, keyword:keyword}, function(res){
			$("#examTableBody").empty();
			$.each(res.records, function(i,row){
				$("#examTableBody").append(`
					<tr>
					  <td>${row.CustomerName}</td>
					  <td>${row.ExamDate}</td>
					  <td>${row.SphereRight||""}</td>
					  <td>${row.SphereLeft||""}</td>
					  <td>${row.Examiner||""}</td>
					  <td>
						<button class="btn btn-sm btn-info btnView" data-id="${row.ExamID}">檢視</button>
						<button class="btn btn-sm btn-warning btnEdit" data-id="${row.ExamID}">編輯</button>
						<button class="btn btn-sm btn-danger btnDelete" data-id="${row.ExamID}">刪除</button>
					  </td>
					</tr>
				`);
			});

			// 分頁按鈕
			renderPagination(res.page, res.pageSize, res.totalCount);
		});
	}

    // 分頁按鈕
	function renderPagination(page, pageSize, totalCount){
		$("#pagination").empty();
		let totalPages = Math.ceil(totalCount / pageSize);
		for(let i=1;i<=totalPages;i++){
			$("#pagination").append(`
				<li class="page-item ${i===page?'active':''}">
				  <a class="page-link" href="#">${i}</a>
				</li>
			`);
		}
	}

    // 分頁點擊事件
    $("#pagination").on("click",".page-link",function(e){
        e.preventDefault();
        let page = parseInt($(this).text());
        currentPage = page;
        loadList(currentPage, currentKeyword);
    });

    // 初始化
    loadList(currentPage, currentKeyword);

    // 查詢
    $("#searchForm").submit(function(e){
        e.preventDefault();
        currentKeyword = $("#keyword").val();
        currentPage = 1;
        loadList(currentPage, currentKeyword);
    });

    // 新增
    $("#btnAdd").click(function(){
        $("#eyeExamForm")[0].reset();
        $("#ExamID").val("");
        $("#modalTitle").text("新增眼科檢查紀錄");
        eyeExamModal.show();
    });

    // 儲存
    $("#eyeExamForm").submit(function(e){
        e.preventDefault();
        $.post("eye_exam_api.php?action=save", $(this).serialize(), function(res){
            alert(res.message);
            eyeExamModal.hide();
            loadList(currentPage, currentKeyword);
        },"json");
    });

    // 編輯
    $("#examTableBody").on("click",".btnEdit",function(){
        let id=$(this).data("id");
        $.getJSON("eye_exam_api.php",{action:"view",id:id},function(data){
            $.each(data,function(k,v){
                $("#"+k).val(v);
            });
            $("#modalTitle").text("編輯眼科檢查紀錄");
            eyeExamModal.show();
        });
    });

    // 檢視
    $("#examTableBody").on("click",".btnView",function(){
        let id=$(this).data("id");
        $.getJSON("eye_exam_api.php",{action:"view",id:id},function(data){
            $.each(data,function(k,v){
                $("#"+k).val(v);
            });
            $("#modalTitle").text("檢視眼科檢查紀錄");
            eyeExamModal.show();
        });
    });

    // 刪除
    $("#examTableBody").on("click",".btnDelete",function(){
        if(confirm("確定刪除？")){
            let id=$(this).data("id");
            $.getJSON("eye_exam_api.php",{action:"delete",id:id},function(res){
                alert(res.message);
                loadList(currentPage, currentKeyword);
            });
        }
    });

    // 客戶選擇
    $("#btnSelectCustomer").click(function(){
        customerDialog.show();
        loadCustomers("");
    });
    $("#searchCustomer").keyup(function(){
        loadCustomers($(this).val());
    });
    function loadCustomers(keyword){
        $.getJSON("customers_api.php",{action:"search",keyword:keyword},function(data){
            $("#customerList").empty();
            $.each(data,function(i,c){
                $("#customerList").append(`
                    <tr>
                      <td>${c.CustomerID}</td>
                      <td>${c.Name}</td>
                      <td>${c.Phone}</td>
                      <td>${c.Address}</td>
                      <td><button class="btn btn-sm btn-primary btnChoose" data-id="${c.CustomerID}" data-name="${c.Name}">選擇</button></td>
                    </tr>
                `);
            });
        });
    }
    $("#customerList").on("click",".btnChoose",function(){
        $("#CustomerID").val($(this).data("id"));
        $("#customerName").val($(this).data("name"));
        customerDialog.hide();
    });

    // 載入驗光人員
    function loadStaffs(storeId){
        $.getJSON("staffs_api.php",{action:"list",store:storeId},function(data){
            $("#Examiner").empty();
            $.each(data,function(i,s){
                $("#Examiner").append(`<option value="${s.Name}">${s.Name}</option>`);
            });
        });
    }
    // 假設目前門市代號
    loadStaffs($_SESSION("StoreID"));
});
</script>
</body>
</html>
