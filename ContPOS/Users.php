<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <title>使用者管理</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    table thead th {
      background-color: #e6f2ff !important;
    }
    table tbody tr:nth-child(even) {
      background-color: #f9f9f9 !important;
    }
    table tbody tr:hover {
      background-color: #d9edf7 !important;
    }
  </style>
</head>
<body>
<div class="container mt-4">
    <h2>使用者管理</h2>
    <div class="row mb-3">
 	    <div class="input-group">
            <input type="text" id="searchUser" class="form-control" placeholder="搜尋使用者代號或帳號">
            <button id="btnSearch" class="btn btn-primary">查詢</button>
            <button id="btnAdd" class="btn btn-success">新增使用者</button>
        </div>
    </div>
    <table id="userTable" class="table table-bordered table-striped table-hover">
        <thead class="table-primary">
            <tr>
                <th>使用者代號</th>
                <th>登入帳號</th>
                <th>門市代號</th>
                <th>員工姓名</th>
                <th>角色</th>
                <th>是否啟用</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody id="userTableBody"></tbody>
    </table>
	
    <!--div id="pagination" class="mt-3 text-center"></div-->
    <ul id="pagination" class="pagination"></ul>
</div>
	
<!-- 新增使用者 Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="userForm">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">使用者資料</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="action" id="formAction" value="create">

		    <label>選擇員工 (可輸入)</label>
		    <div class="input-group">
		      <input type="text" id="staffInput" class="form-control" placeholder="輸入員工編號或姓名開頭">
		      <select id="staffSelect" class="form-select mt-2"></select>
			</div>
			
			<label>使用者代號</label>
			<input type="text" name="UserID" id="UserID" class="form-control">
			<div class="invalid-feedback">此代號已存在</div>

			<label>使用者名稱</label>
			<input type="text" name="Username" id="Username" class="form-control">

          <label>門市代號</label>
          <input type="text" name="StoreID" id="StoreID" class="form-control">

          <label>角色</label>
          <select name="RoleID" id="RoleID" class="form-select"></select>

          <label>密碼</label>
          <input type="password" name="Password" id="Password" class="form-control">

          <label>是否啟用</label>
          <select name="IsActive" id="IsActive" class="form-select">
            <option value="1">啟用</option>
            <option value="0">停用</option>
          </select>
        </div>
        <div class="modal-footer">
          <button type="submit" id="modalSaveBtn" class="btn btn-primary">儲存</button>
          <button type="button" id="modalDeleteBtn" class="btn btn-danger d-none">刪除</button>
        </div>
      </form>
    </div>
  </div>
</div>



<script>
//$(function(){
$(document).ready(function(){
    let currentPage = 1;
    let pageSize = 10;
	let currentSearch = '';
	let currentKeyword = "";

	function loadUsers(page){
		//$.getJSON("user_api.php",{action:"list",page:page,keyword:keyword},function(res){
		$.getJSON('user_api.php?action=list&page=' + page + '&pageSize=' + pageSize + '&search=' + encodeURIComponent(currentSearch), function(res){
			let rows = '';
			$.each(res.data, function(i, user){
				rows += `<tr>
					<td>${user.UserID}</td>
					<td>${user.Username}</td>
					<td>${user.StoreID}</td>
					<td>${user.Name || ''}</td>
					<td>${user.RoleName || ''}</td>
					<td>${user.IsActive == 1 ? '啟用' : '停用'}</td>
					<td>
						<button class="btn btn-sm btn-info btnView" data-id="${user.UserID}">檢視</button>
						<button class="btn btn-sm btn-warning btnEdit" data-id="${user.UserID}">編輯</button>
						<button class="btn btn-sm btn-danger btnDelete" data-id="${user.UserID}">刪除</button>
					</td>
				</tr>`;
			});
			$('#userTable tbody').html(rows);

			renderPagination(res.page,res.totalPages);
		});
	}

	// 分頁渲染 (沿用 Staffs.php)
	function renderPagination(page,totalPages){
	  $("#pagination").empty();
	  if(page>1){
		$("#pagination").append(`<li class="page-item"><a class="page-link" href="#" data-page="1">首頁</a></li>`);
		$("#pagination").append(`<li class="page-item"><a class="page-link" href="#" data-page="${page-1}">上頁</a></li>`);
	  }
	  let start=Math.max(1,page-5);
	  let end=Math.min(totalPages,page+5);
	  for(let i=start;i<=end;i++){
		$("#pagination").append(`<li class="page-item ${i===page?'active':''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`);
	  }
	  if(page<totalPages){
		$("#pagination").append(`<li class="page-item"><a class="page-link" href="#" data-page="${page+1}">下頁</a></li>`);
		$("#pagination").append(`<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">末頁</a></li>`);
	  }
	  $("#pagination").append(`<li class="page-item disabled"><span class="page-link">第 ${page} 頁 / 共 ${totalPages} 頁</span></li>`);
	}

	// 分頁點擊事件
	$("#pagination").on("click",".page-link",function(e){
	  e.preventDefault();
	  let page=$(this).data("page");
	  if(page){
		currentPage=page;
		loadList(currentPage,currentSearch);
	  }
	});

    // 載入 Staffs 下拉選單
    $.getJSON('staff_api.php?action=combolist', function(data){
        $.each(data, function(i, staff){
            $('#staffSelect').append(`<option value="${staff.StaffID}" 
                data-name="${staff.Name}" data-store="${staff.StoreID}">
                ${staff.StaffID} - ${staff.Name}
            </option>`);
        });
    });

	// 當輸入框改變時即時查詢
	$('#staffInput').on('keyup', function(){
		let keyword = $(this).val().trim();
		$.getJSON('staff_api.php?action=combolist&keyword=' + encodeURIComponent(keyword), function(data){
		  $('#staffSelect').empty().append('<option value="">-- 不選擇員工，自行輸入 --</option>');
		  $.each(data, function(i, staff){
			$('#staffSelect').append(`<option value="${staff.StaffID}" 
			  data-name="${staff.Name}" data-store="${staff.StoreID}">
			  ${staff.StaffID} - ${staff.Name} (${staff.StoreID})
			</option>`);
		  });
		});
	});


    // 選擇員工 → 自動帶入
    $('#staffSelect').change(function(){
        let selected = $(this).find(':selected');
        if(selected.val() !== ""){
            $('#UserID').val(selected.val());
            $('#Username').val(selected.data('name'));
            $('#StoreID').val(selected.data('store'));
        } else {
            $('#UserID').val('');
            $('#Username').val('');
            $('#StoreID').val('');
        }
    });

	// 載入角色清單
	$.getJSON('user_api.php?action=roles', function(data){
		$.each(data, function(i, role){
		  $('#RoleID').append(`<option value="${role.RoleID}">${role.RoleName}</option>`);
		});
	});

    // 表單送出
	$('#userForm').submit(function(e){
	  e.preventDefault();
	  // 先清除舊的錯誤狀態
	  $('#UserID').removeClass('is-invalid');
	  $('#Username').removeClass('is-invalid');

	  $.post('user_api.php', $(this).serialize(), function(res){
		  console.log('錯誤顯示');
		if(res.status==='success'){
		  alert('儲存成功');
		  $('#userModal').modal('hide');
		  loadList(currentPage,currentKeyword);
		}else{
		  alert('儲存失敗');
		}
	  },'json');
	});

	 // 查詢按鈕
	$('#btnSearch').click(function(){
		currentSearch = $('#searchUser').val().trim();;
		currentPage = 1;
		loadUsers(currentPage);
	});

	// 新增使用者
  $('#btnAdd').click(function(){
    $('#modalTitle').text('新增使用者');
    $('#formAction').val('create');
    $('#userForm')[0].reset();
    $('#UserID').prop('readonly', false);
    $('#Password').prop('required', true);
    $('#modalSaveBtn').removeClass('d-none');
    $('#modalDeleteBtn').addClass('d-none');
    $('#userModal').modal('show');
  });
	
	// 檢視使用者
	$('#userTableBody').on('click','.btnView',function(){
	  let id=$(this).data('id');
	  $.getJSON('user_api.php',{action:'get',UserID:id},function(row){
		// 設定 Modal 標題
		$('#modalTitle').text('檢視使用者');
		$('#formAction').val('');

		// 填入資料並設為唯讀
		$('#UserID').val(row.UserID).prop('readonly', true).removeClass('is-invalid');
		$('#Username').val(row.UserName).prop('readonly', true).removeClass('is-invalid');
		$('#StoreID').val(row.StoreID).prop('readonly', true);
		$('#RoleID').val(row.RoleID).prop('disabled', true);
		$('#IsActive').val(row.IsActive).prop('disabled', true);
		$('#Password').val('').prop('disabled', true);

		// 隱藏儲存、刪除按鈕
		$('#modalSaveBtn').addClass('d-none');
		$('#modalDeleteBtn').addClass('d-none');

		// 關閉即時檢查事件，避免誤觸
		$('#UserID, #Username').off('blur keyup');

		// 顯示 Modal
		$('#userModal').modal('show');
	  });
	});
  
	// 編輯使用者
  $('#userTableBody').on('click','.btnEdit',function(){
    let id=$(this).data('id');
    $.getJSON('user_api.php',{action:'get',UserID:id},function(row){
      $('#modalTitle').text('編輯使用者');
      $('#formAction').val('update');
      $('#UserID').val(row.UserID).prop('readonly', true);
      $('#Username').val(row.UserName).prop('readonly', false);
      $('#StoreID').val(row.StoreID).prop('readonly', false);
      $('#RoleID').val(row.RoleID).prop('disabled', false);
      $('#IsActive').val(row.IsActive).prop('disabled', false);
      $('#Password').val('').prop('disabled', false).prop('required', false);
      $('#modalSaveBtn').removeClass('d-none');
      $('#modalDeleteBtn').addClass('d-none');
      $('#userModal').modal('show');
    });
  });	  
  
  // 刪除使用者
  $('#userTableBody').on('click','.btnDelete',function(){
    let id=$(this).data('id');
    $.getJSON('user_api.php',{action:'get',UserID:id},function(row){
      $('#modalTitle').text('刪除使用者');
      $('#formAction').val('delete');
      $('#UserID').val(row.UserID).prop('readonly', true);
      $('#Username').val(row.UserName).prop('readonly', true);
      $('#StoreID').val(row.StoreID).prop('readonly', true);
      $('#RoleID').val(row.RoleID).prop('disabled', true);
      $('#IsActive').val(row.IsActive).prop('disabled', true);
      $('#Password').val('').prop('disabled', true);
      $('#modalSaveBtn').addClass('d-none');
      $('#modalDeleteBtn').removeClass('d-none');
      $('#userModal').modal('show');
    });
  });
  
  // 刪除按鈕
  $('#modalDeleteBtn').click(function(){
    let id=$('#UserID').val();
    $.post('user_api.php',{action:'delete',UserID:id},function(res){
      if(res.status==='success'){
        alert('刪除成功');
        $('#userModal').modal('hide');
        loadList(currentPage,currentKeyword);
      }else{
        alert('刪除失敗');
      }
    },'json');
  });

  // 即時檢查 UserID
	$('#UserID').on('blur', function(){
		let val = $(this).val().trim();
		if(val!==''){
		  $.getJSON('user_api.php?action=check&field=UserID&value='+encodeURIComponent(val), function(res){
			$('#UserID').removeClass('is-invalid');
			if(res.status==='error'){
			  $('#UserID').addClass('is-invalid');
			}
		  });
		}
	});

	// 即時清除紅框 (keyup 事件)
	$('#UserID').on('keyup', function(){
	  $(this).removeClass('is-invalid');
	});

    loadUsers(currentPage);
});
</script>
</body>
</html>
