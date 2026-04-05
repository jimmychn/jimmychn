<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POS 系統登入</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- 引入 CryptoJS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div id="alertBox"></div>
      
      <div class="card shadow">
        <div class="card-header text-center">
          <h4>POS 系統登入</h4>
        </div>
        <div class="card-body">
          <form id="loginForm">
            <div class="mb-3">
              <label class="form-label">門市</label>
              <select class="form-select" id="store" name="store" required>
                <option value="">請選擇門市</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">帳號</label>
              <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
              <label class="form-label">密碼</label>
              <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
              <label class="form-label">圖形驗證碼</label>
              <div class="input-group">
                <input type="text" class="form-control" id="captcha" name="captcha" required>
                <span class="input-group-text p-0">
                  <img src="captcha.php" alt="驗證碼" id="captchaImg" style="cursor: pointer;" onclick="this.src='captcha.php?'+Date.now();" title="點擊重新產生驗證碼">
                </span>
              </div>
            </div>
            <button type="submit" class="btn btn-primary w-100" id="loginBtn">登入</button>
            <div class="text-end mt-2">
                <a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal" class="text-decoration-none text-muted"><small>忘記密碼？</small></a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 忘記密碼 Modal -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="forgotPasswordModalLabel">忘記密碼</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="forgotForm">
            <div class="mb-3">
                <label class="form-label">請選擇所屬門市</label>
                <select class="form-select" id="forgotStore" required>
                  <option value="">請選擇門市</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">請輸入您的帳號</label>
                <input type="text" class="form-control" id="forgotUsername" required>
            </div>
            <div id="forgotAlert"></div>
            <button type="submit" class="btn btn-warning w-100" id="forgotBtn">發送重設密碼連結</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
  loadLookups();
    $('#loginForm').submit(function(e) {
        e.preventDefault();
        
        var plainPassword = $('#password').val();
        var hashedPass = CryptoJS.SHA256(plainPassword).toString();
        
        var loginData = {
            store: $('#store').val(),
            username: $('#username').val(),
            password: hashedPass, // 傳送 SHA256 加密後的密碼
            captcha: $('#captcha').val()
        };

        $('#loginBtn').prop('disabled', true).text('驗證中...');
        $('#alertBox').empty();
        
        $.ajax({
            url: 'Login_api.php',
            type: 'POST',
            dataType: 'json',
            data: loginData,
            success: function(response) {
                if(response.status === 'success') {
                    window.location.href = response.url;
                } else {
                    var remainingText = response.remaining !== undefined ? '<br>還剩 ' + response.remaining + ' 次機會' : '';
                    if(response.remaining === 0) {
                         remainingText = '<br>該 IP 已被永久鎖定。';
                    }
                    if(response.isBlocked) {
                         remainingText = '';
                    }
                    $('#alertBox').html('<div class="alert alert-danger text-center">' + response.message + remainingText + '</div>');
                    // 重新整理驗證碼
                    $('#captchaImg').attr('src', 'captcha.php?' + Date.now());
                    $('#captcha').val('');
                    $('#password').val('');
                    $('#loginBtn').prop('disabled', false).text('登入');
                }
            },
            error: function() {
                $('#alertBox').html('<div class="alert alert-danger text-center">系統發生連接錯誤！請稍後再試。</div>');
                $('#loginBtn').prop('disabled', false).text('登入');
            }
        });
    });

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
        $('#store').html(options);
        $('#forgotStore').html(options);
      }, 'json').fail(function() {
        alert('無法載入門市清單');
      });
    };

    $('#forgotForm').submit(function(e) {
        e.preventDefault();
        var store = $('#forgotStore').val();
        var username = $('#forgotUsername').val();
        $('#forgotBtn').prop('disabled', true).text('發送中...');
        $('#forgotAlert').html('');
        
        $.ajax({
            url: 'forgot_password_api.php',
            type: 'POST',
            dataType: 'json',
            data: { store: store, username: username },
            success: function(response) {
                if(response.status === 'success') {
                    $('#forgotAlert').html('<div class="alert alert-success">' + response.message + '</div>');
                    // 關閉 Modal
                    setTimeout(function() {
                        $('#forgotPasswordModal').modal('hide');
                        $('#forgotBtn').prop('disabled', false).text('發送重設密碼連結');
                        $('#forgotAlert').html('');
                    }, 5000);
                } else {
                    $('#forgotAlert').html('<div class="alert alert-danger">' + response.message + '</div>');
                    $('#forgotBtn').prop('disabled', false).text('發送重設密碼連結');
                }
            },
            error: function() {
                $('#forgotAlert').html('<div class="alert alert-danger">信件發送發生異常！請稍後再試。</div>');
                $('#forgotBtn').prop('disabled', false).text('發送重設密碼連結');
            }
        });
    });
});
</script>

</body>
</html>
