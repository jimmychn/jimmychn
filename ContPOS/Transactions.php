<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <title>交易管理</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light">
<div class="container mt-4">

	<h2>交易管理</h2>

	<form id="searchForm" class="mb-3">
	  <div class="input-group">
		<input type="text" id="keyword" class="form-control" placeholder="輸入顧客姓名或電話查詢">
		<button type="submit" class="btn btn-primary">查詢</button>
		<button type="button" id="btnAdd" class="btn btn-success">新增交易</button>
	  </div>
	</form>

	<table class="table table-bordered">
	  <thead>
		<tr>
		  <th>日期</th>
		  <th>顧客</th>
		  <th>驗光單號</th>
		  <th>業務員</th>
		  <th>合計金額</th>
		  <th>實際收款</th>
		  <th>積分</th>
		  <th>操作</th>
		</tr>
	  </thead>
	  <tbody id="transactionTableBody"></tbody>
	</table>

	<nav>
	  <ul class="pagination" id="pagination"></ul>
	</nav>
	<!-- Master + Slave 表單 Modal -->
	<div class="modal fade" id="transactionModal" tabindex="-1">
	  <div class="modal-dialog modal-xl">
		<div class="modal-content">
		  <form id="transactionForm">
			<div class="modal-header">
			  <h5 class="modal-title" id="modalTitle">新增交易</h5>
			  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body">
			  <input type="hidden" id="TransactionID" name="TransactionID">
			  <div class="row mb-3">
				<div class="col">
				  <label>門市</label>
				  <select id="StoreID" name="StoreID" class="form-select"></select>
				</div>
				<div class="col">
				  <label>顧客</label>
				  <select id="CustomerID" name="CustomerID" class="form-select"></select>
				</div>
				<div class="col">
				  <label>業務員</label>
				  <select id="SalespersonID" name="SalespersonID" class="form-select"></select>
				</div>
				<div class="col">
				  <label>驗光單號</label>
				  <select id="ExamID" name="ExamID" class="form-select"></select>
				</div>
				<div class="col">
				  <label>日期</label>
				  <input type="date" id="TransactionDate" name="TransactionDate" class="form-control">
				</div>
			  </div>

			  <h5>銷售商品明細</h5>
			  <table class="table table-sm" id="detailsTable">
				<thead>
				  <tr>
					<th>商品</th><th>批號</th><th>數量</th><th>定價</th><th>銷售價</th><th>小計</th><th>積分</th><th>操作</th>
				  </tr>
				</thead>
				<tbody></tbody>
			  </table>
			  <button type="button" id="btnAddDetail" class="btn btn-secondary">新增商品</button>

			  <div class="row mt-3">
				<div class="col">
				  <label>合計金額</label>
				  <input type="text" id="TotalAmount" name="TotalAmount" class="form-control" readonly>
				</div>
				<div class="col">
				  <label>實際收款</label>
				  <input type="text" id="ActualPayment" name="ActualPayment" class="form-control">
				</div>
				<div class="col">
				  <label>積分</label>
				  <input type="text" id="TotalPointsEarned" name="TotalPointsEarned" class="form-control" readonly>
				</div>
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
</div>	
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(function(){
    let transactionModal = new bootstrap.Modal($("#transactionModal")[0]);
    let currentPage = 1;
    let currentKeyword = "";

    // 載入交易列表 (Master)
    function loadList(page, keyword){
        $.getJSON("transactions_api.php",{action:"list",page:page,keyword:keyword},function(res){
            $("#transactionTableBody").empty();
            $.each(res.records,function(i,row){
                $("#transactionTableBody").append(`
                    <tr>
                      <td>${row.TransactionDate}</td>
                      <td>${row.CustomerName}</td>
                      <td>${row.ExamID||""}</td>
                      <td>${row.SalespersonName}</td>
                      <td>${row.TotalAmount}</td>
                      <td>${row.ActualPayment}</td>
                      <td>${row.TotalPointsEarned}</td>
                      <td>
                        <button class="btn btn-sm btn-info btnView" data-id="${row.TransactionID}">檢視</button>
                        <button class="btn btn-sm btn-warning btnEdit" data-id="${row.TransactionID}">編輯</button>
                        <button class="btn btn-sm btn-danger btnDelete" data-id="${row.TransactionID}">刪除</button>
                      </td>
                    </tr>
                `);
            });
            renderPagination(res.page,res.pageSize,res.totalCount);
        });
    }

    // 分頁控制
    function renderPagination(page,pageSize,totalCount){
        $("#pagination").empty();
        let totalPages = Math.ceil(totalCount/pageSize);
        for(let i=1;i<=totalPages;i++){
            $("#pagination").append(`
                <li class="page-item ${i===page?'active':''}">
                  <a class="page-link" href="#">${i}</a>
                </li>
            `);
        }
    }

    // 分頁按鈕事件
    $("#pagination").on("click",".page-link",function(e){
        e.preventDefault();
        currentPage=parseInt($(this).text());
        loadList(currentPage,currentKeyword);
    });

    // 查詢事件
    $("#searchForm").submit(function(e){
        e.preventDefault();
        currentKeyword=$("#keyword").val();
        currentPage=1;
        loadList(currentPage,currentKeyword);
    });

    // 初始化載入
    loadList(currentPage,currentKeyword);
});
</script>
<script>
$(function(){
    let transactionModal = new bootstrap.Modal($("#transactionModal")[0]);

    // 新增交易
    $("#btnAdd").click(function(){
        $("#transactionForm")[0].reset();
        $("#TransactionID").val("");
        $("#detailsTable tbody").empty();
        $("#modalTitle").text("新增交易");
        transactionModal.show();
    });

    // 儲存交易 (Master + Slave)
    $("#transactionForm").submit(function(e){
        e.preventDefault();
        let details=[];
        $("#detailsTable tbody tr").each(function(){
            details.push({
                ProductID:$(this).find(".ProductID").val(),
                BatchNo:$(this).find(".BatchNo").val(),
                Quantity:$(this).find(".Quantity").val(),
                UnitPrice:$(this).find(".UnitPrice").val(),
                SalePrice:$(this).find(".SalePrice").val()
            });
        });
        let formData=$(this).serializeArray();
        let data={};
        $.each(formData,function(i,f){data[f.name]=f.value;});
        data.details=JSON.stringify(details);

        $.post("transactions_api.php?action=save",data,function(res){
            alert(res.message);
            transactionModal.hide();
            loadList(currentPage,currentKeyword);
        },"json");
    });

    // 檢視交易
    $("#transactionTableBody").on("click",".btnView",function(){
        let id=$(this).data("id");
        $.getJSON("transactions_api.php",{action:"view",id:id},function(res){
            $.each(res.master,function(k,v){$("#"+k).val(v);});
            $("#detailsTable tbody").empty();
            $.each(res.details,function(i,d){
                $("#detailsTable tbody").append(`
                    <tr>
                      <td><input type="text" class="form-control ProductID" value="${d.ProductID}" readonly></td>
                      <td><input type="text" class="form-control BatchNo" value="${d.BatchNo||""}" readonly></td>
                      <td><input type="number" class="form-control Quantity" value="${d.Quantity}" readonly></td>
                      <td><input type="text" class="form-control UnitPrice" value="${d.UnitPrice}" readonly></td>
                      <td><input type="text" class="form-control SalePrice" value="${d.SalePrice}" readonly></td>
                      <td>${d.SubTotal}</td>
                      <td>${d.PointsEarned}</td>
                      <td></td>
                    </tr>
                `);
            });
            $("#modalTitle").text("檢視交易");
            transactionModal.show();
        });
    });

    // 編輯交易
    $("#transactionTableBody").on("click",".btnEdit",function(){
        let id=$(this).data("id");
        $.getJSON("transactions_api.php",{action:"view",id:id},function(res){
            $.each(res.master,function(k,v){$("#"+k).val(v);});
            $("#detailsTable tbody").empty();
            $.each(res.details,function(i,d){
                $("#detailsTable tbody").append(`
                    <tr>
                      <td><input type="text" class="form-control ProductID" value="${d.ProductID}"></td>
                      <td><input type="text" class="form-control BatchNo" value="${d.BatchNo||""}"></td>
                      <td><input type="number" class="form-control Quantity" value="${d.Quantity}"></td>
                      <td><input type="text" class="form-control UnitPrice" value="${d.UnitPrice}"></td>
                      <td><input type="text" class="form-control SalePrice" value="${d.SalePrice}"></td>
                      <td>${d.SubTotal}</td>
                      <td>${d.PointsEarned}</td>
                      <td><button type="button" class="btn btn-sm btn-danger btnRemoveDetail">刪除</button></td>
                    </tr>
                `);
            });
            $("#modalTitle").text("編輯交易");
            transactionModal.show();
        });
    });

    // 刪除交易
    $("#transactionTableBody").on("click",".btnDelete",function(){
        if(confirm("確定刪除這筆交易？")){
            let id=$(this).data("id");
            $.getJSON("transactions_api.php",{action:"delete",id:id},function(res){
                alert(res.message);
                loadList(currentPage,currentKeyword);
            });
        }
    });

    // 新增商品明細列
    $("#btnAddDetail").click(function(){
        $("#detailsTable tbody").append(`
            <tr>
              <td><input type="text" class="form-control ProductID"></td>
              <td><input type="text" class="form-control BatchNo"></td>
              <td><input type="number" class="form-control Quantity"></td>
              <td><input type="text" class="form-control UnitPrice"></td>
              <td><input type="text" class="form-control SalePrice"></td>
              <td></td>
              <td></td>
              <td><button type="button" class="btn btn-sm btn-danger btnRemoveDetail">刪除</button></td>
            </tr>
        `);
    });

    // 移除商品明細列
    $("#detailsTable").on("click",".btnRemoveDetail",function(){
        $(this).closest("tr").remove();
    });
});
</script>
</body>
</html>

