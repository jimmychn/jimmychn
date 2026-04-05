<?php
session_start();
// 權限攔截檢查：若尚未登入，導向登入畫面
if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit();
}
$page = isset($_GET['page']) ? $_GET['page'] : 'Store';
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>後台管理系統</title>
  <!-- Bootstrap -->
  <link  href="https://cdn.jsdelivr.net/npm/bootstrap@5/dist/css/bootstrap.min.css" rel="stylesheet">
  <link  href="https://cdn.jsdelivr.net/npm/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet">  <!-- FontAwesome -->
  <link  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    html, body { min-height: 100%; height: 100%; }
    body { background-color: #f8f9fa; min-height: 100vh; padding: 0px; }
    .sidebar { height: calc(100vh - 20px); background-color: #343a40; transition: width .2s ease; }
    .sidebar a { color: #fff; display: flex; align-items: center; padding: 10px; text-decoration: none; }
    .sidebar a:hover { background-color: #495057; }
    .active-link { background-color: #007bff; color: #fff; }
    .menu-icon { display: inline-flex; align-items: center; justify-content: center; width: 1.75rem; text-align: center; }
    .menu-label { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .sidebar .sidebar-toggle-row { padding: .75rem .5rem; }
    .sidebar .sidebar-toggle-row .btn { min-width: 0; }
    .content-area { min-height: calc(100vh - 20px); display: flex; flex-direction: column; overflow: hidden; }
    #moduleWorkspace { flex: 1 1 auto; display: flex; flex-direction: column; overflow: hidden; }
    .module-tabs { margin-bottom: 1rem; flex: 0 0 auto; }
    .tab-content { flex: 1 1 auto; overflow: hidden; }
    .tab-pane { min-height: 0; height: 100%; position: relative; padding: 0; overflow: hidden; }
    .tab-pane iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; }
    body.sidebar-collapsed #desktopSidebar { width: 4rem !important; min-width: 4rem !important; max-width: 4rem !important; overflow: hidden; }
    body.sidebar-collapsed #desktopSidebar .menu-label,
    body.sidebar-collapsed #desktopSidebar .collapse-toggle-icon,
    body.sidebar-collapsed #desktopSidebar .sidebar-toggle-row .btn span {
      display: none !important;
    }
    body.sidebar-collapsed #desktopSidebar a { justify-content: center; padding: .75rem .5rem; }
    body.sidebar-collapsed #desktopSidebar .menu-icon { width: auto; }
    body.sidebar-collapsed .content-area { flex: 0 0 calc(100% - 4rem); max-width: calc(100% - 4rem); }
    /* 手機版 nav-link active 樣式 */
    .nav-link.active-link {
      background-color: #007bff !important;
      color: #fff !important;
    }
    table thead th {
      background-color: #e6f2ff !important;
    }
    table tbody tr:nth-child(even) {
      background-color: #f9f9f9 !important;
    }
    table tbody tr:hover {
      background-color: #d9edf7 !important;
    }
    .module-tabs { margin-bottom: 1rem; }
    .module-tabs .nav-item { position: relative; width: 80px; }
    .module-tabs .nav-link { padding: 0.5rem 1rem 0.5rem 0.5rem; display: block; width: 100%; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; text-align: left; }
    .module-tabs .btn-close { position: absolute; top: 0.25rem; right: 0.25rem; opacity: 0.8; }
    .module-tabs .btn-close:hover { opacity: 1; }
    .module-tabs .btn-close-dark { filter: invert(0); }
    .tab-pane { min-height: calc(100vh - 220px); position: relative; padding: 0; }
    .tab-pane iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; }
  </style>
</head>
<body>
<!-- 手機版漢堡選單 -->
<nav class="navbar navbar-dark bg-dark d-md-none">
  <div class="container-fluid">
    <a class="navbar-brand" href="#"><i class="fas fa-bars me-2"></i> 管理選單</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mobileMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mobileMenu">
      <ul class="navbar-nav">
        <li><a class="nav-link <?= $page=='Customers' ? 'active-link' : '' ?>" href="?page=Customers" onclick="openModuleTab('Customers','客戶管理'); return false;"><i class="fas fa-user me-2"></i> 客戶管理</a></li>
        <li><a class="nav-link <?= $page=='EyeExams' ? 'active-link' : '' ?>" href="?page=EyeExams" onclick="openModuleTab('EyeExams','驗光管理'); return false;"><i class="fas fa-eye me-2"></i> 驗光管理</a></li>
        <li><a class="nav-link <?= $page=='Transactions' ? 'active-link' : '' ?>" href="?page=Transactions" onclick="openModuleTab('Transactions','交易管理'); return false;"><i class="fas fa-file-invoice-dollar me-2"></i> 交易管理</a></li>
        <li class="nav-item">
          <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#mobileSysMenu">
            <span><i class="fas fa-cog me-2"></i> 系統管理</span> <i class="fas fa-caret-down"></i>
          </a>
          <div class="collapse ps-3" id="mobileSysMenu">
            <ul class="navbar-nav">
              <li><a class="nav-link <?= $page=='Stores' ? 'active-link' : '' ?>" href="?page=Stores" onclick="openModuleTab('Stores','門市管理'); return false;">門市管理</a></li>
              <li><a class="nav-link <?= $page=='Staffs' ? 'active-link' : '' ?>" href="?page=Staffs" onclick="openModuleTab('Staffs','員工'); return false;">員工</a></li>
              <li><a class="nav-link <?= $page=='Tabs' ? 'active-link' : '' ?>" href="?page=Tabs" onclick="openModuleTab('Tabs','TAB清單'); return false;">TAB清單</a></li>
              <li><a class="nav-link <?= $page=='Users' ? 'active-link' : '' ?>" href="?page=Users" onclick="openModuleTab('Users','使用者管理'); return false;">使用者管理</a></li>
              <li><a class="nav-link <?= $page=='Roles' ? 'active-link' : '' ?>" href="?page=Roles" onclick="openModuleTab('Roles','權限設定'); return false;">權限設定</a></li>
              <li><a class="nav-link <?= $page=='Rewards' ? 'active-link' : '' ?>" href="?page=Rewards" onclick="openModuleTab('Rewards','積點規則'); return false;">積點規則</a></li>
              <li><a class="nav-link <?= $page=='Systemlogs' ? 'active-link' : '' ?>" href="?page=Systemlogs" onclick="openModuleTab('Systemlogs','系統日誌'); return false;">系統日誌</a></li>
            </ul>
          </div>
        </li>
      </ul>
    </div>
  </div>
</nav>
<!-- 電腦版側邊選單 -->
<div class="container-fluid">
  <div class="row">
    <div class="col-md-2 sidebar d-none d-md-block" id="desktopSidebar">
      <div class="d-flex justify-content-between align-items-center px-3 mt-3 sidebar-toggle-row">
        <div class="d-flex align-items-center text-white menu-label"><i class="fas fa-bars me-2"></i> 管理選單</div>
        <button id="sidebarToggle" class="btn btn-outline-light btn-sm" type="button"><span><i class="fas fa-bars"></i></span></button>
      </div>
      <a href="?page=Customers" class="<?= $page=='Customers' ? 'active-link' : '' ?>"><span class="menu-icon"><i class="fas fa-user"></i></span><span class="menu-label ms-2">客戶管理</span></a>
      <a href="?page=EyeExams" class="<?= $page=='EyeExams' ? 'active-link' : '' ?>"><span class="menu-icon"><i class="fas fa-eye"></i></span><span class="menu-label ms-2">驗光管理</span></a>
      <a href="?page=Transactions" class="<?= $page=='Transactions' ? 'active-link' : '' ?>"><span class="menu-icon"><i class="fas fa-file-invoice-dollar"></i></span><span class="menu-label ms-2">交易管理</span></a>
      <a class="d-flex justify-content-between align-items-center text-white" data-bs-toggle="collapse" href="#desktopSysMenu">
        <span class="d-flex align-items-center"><span class="menu-icon"><i class="fas fa-cog"></i></span><span class="menu-label ms-2">系統管理</span></span>
        <i class="fas fa-caret-down collapse-toggle-icon"></i>
      </a>
      <div class="collapse ps-3" id="desktopSysMenu">
        <a href="?page=Stores" class="<?= $page=='Stores' ? 'active-link' : '' ?>" onclick="openModuleTab('Stores','門市'); return false;"><span class="menu-icon"><i class="fas fa-store"></i></span><span class="menu-label ms-2">門市</span></a>
        <a href="?page=Staffs" class="<?= $page=='Staffs' ? 'active-link' : '' ?>" onclick="openModuleTab('Staffs','員工'); return false;"><span class="menu-icon"><i class="fas fa-users"></i></span><span class="menu-label ms-2">員工</span></a>
        <a href="?page=Tabs" class="<?= $page=='Tabs' ? 'active-link' : '' ?>" onclick="openModuleTab('Tabs','TAB清單'); return false;"><span class="menu-icon"><i class="fas fa-list"></i></span><span class="menu-label ms-2">TAB清單</span></a>
        <a href="?page=Users" class="<?= $page=='Users' ? 'active-link' : '' ?>" onclick="openModuleTab('Users','使用者'); return false;"><span class="menu-icon"><i class="fas fa-user-cog"></i></span><span class="menu-label ms-2">使用者</span></a>
        <a href="?page=Roles" class="<?= $page=='Roles' ? 'active-link' : '' ?>" onclick="openModuleTab('Roles','權限'); return false;"><span class="menu-icon"><i class="fas fa-shield-alt"></i></span><span class="menu-label ms-2">權限</span></a>
        <a href="?page=Rewards" class="<?= $page=='Rewards' ? 'active-link' : '' ?>" onclick="openModuleTab('Rewards','積點'); return false;"><span class="menu-icon"><i class="fas fa-gift"></i></span><span class="menu-label ms-2">積點</span></a>
        <a href="?page=Systemlogs" class="<?= $page=='Systemlogs' ? 'active-link' : '' ?>" onclick="openModuleTab('Systemlogs','日誌'); return false;"><span class="menu-icon"><i class="fas fa-book"></i></span><span class="menu-label ms-2">日誌</span></a>
      </div>
    </div>
    <div class="col-md-10 p-0 content-area">
      <div id="moduleWorkspace">
        <ul class="nav nav-tabs module-tabs" id="moduleTabs" role="tablist"></ul>
        <div class="tab-content border border-top-0 rounded-bottom bg-white" id="moduleTabContent"></div>
      </div>
    </div>
  </div>
</div>

<footer class="bg-light text-center py-3" style="position: fixed; bottom: 0; left: 0; width: 100%; z-index: 100;">
    <small>&copy; <?php echo date("Y"); ?> 眼鏡連鎖店POS管理系統</small>
</footer>
<script>
  var moduleMap = {
    Stores: 'Stores.php',
    Staffs: 'Staffs.php',
    Tabs: 'TAB.php',
    Users: 'Users.php',
    Roles: 'Roles.php',
    Customers: 'Customers.php',
    Rewards: 'CategorieLevel.php',
    EyeExams: 'EyeExams.php',
    Transactions: 'Transactions.php',
    Systemlogs: 'SystemLogs.php'
  };

  function setActiveMenu(pageKey) {
    $('a[href^="?page="]').removeClass('active-link');
    $('a[href="?page=' + pageKey + '"]').addClass('active-link');
    var systemPages = ['Stores', 'Staffs', 'Tabs', 'Users', 'Roles', 'Rewards', 'Systemlogs','Customers','EyeExams','Transactions','Systemlogs'];
    if (systemPages.indexOf(pageKey) !== -1) {
      $('#desktopSysMenu').collapse('show');
      $('#mobileSysMenu').collapse('show');
    } else {
      $('#desktopSysMenu').collapse('hide');
      $('#mobileSysMenu').collapse('hide');
    }
  }

  function activateModuleTab(pageKey) {
    var tabId = 'tab-' + pageKey;
    var paneId = 'pane-' + pageKey;
    $('#moduleTabs .nav-link').removeClass('active');
    $('#moduleTabContent .tab-pane').removeClass('active show');
    $('#' + tabId + ' .nav-link').addClass('active');
    $('#' + paneId).addClass('active show');
    setActiveMenu(pageKey);
    history.replaceState(null, null, '?page=' + pageKey);
  }

  function closeModuleTab(pageKey) {
    var tabId = 'tab-' + pageKey;
    var paneId = 'pane-' + pageKey;
    var tab = $('#' + tabId);
    var pane = $('#' + paneId);
    var isActive = tab.find('.nav-link').hasClass('active');
    tab.remove();
    pane.remove();
    if (isActive) {
      var lastTab = $('#moduleTabs .nav-item').last();
      if (lastTab.length) {
        var nextKey = lastTab.data('page');
        activateModuleTab(nextKey);
      }
    }
  }

  function openModuleTab(pageKey, title) {
    if (!moduleMap[pageKey]) {
      alert('找不到模組：' + pageKey);
      return;
    }
    var tabId = 'tab-' + pageKey;
    var paneId = 'pane-' + pageKey;
    if ($('#' + tabId).length) {
      activateModuleTab(pageKey);
      return;
    }
    var tabHtml = '<li class="nav-item me-1" id="' + tabId + '" data-page="' + pageKey + '">' +
      '<button class="nav-link btn btn-link p-2" type="button" onclick="activateModuleTab(\'' + pageKey + '\')">' + title + '</button>' +
      '<button type="button" class="btn-close btn-close-dark btn-close-sm" aria-label="關閉" onclick="closeModuleTab(\'' + pageKey + '\'); event.stopPropagation();"></button>' +
      '</li>';
    $('#moduleTabs').append(tabHtml);
    var iframeHtml = '<div class="tab-pane active show" id="' + paneId + '"><iframe src="' + moduleMap[pageKey] + '"></iframe></div>';
    $('#moduleTabContent').append(iframeHtml);
    $('#moduleTabs .nav-link').removeClass('active');
    $('#' + tabId + ' .nav-link').addClass('active');
    $('#moduleTabContent .tab-pane').not('#' + paneId).removeClass('active show');
    setActiveMenu(pageKey);
    history.replaceState(null, null, '?page=' + pageKey);
  }

  $(function () {
    $('#sidebarToggle').on('click', function () {
      $('body').toggleClass('sidebar-collapsed');
    });
    var initialPage = '<?php echo $page; ?>';
    if (moduleMap[initialPage]) {
      openModuleTab(initialPage, {
        Stores: '門市',
        Staffs: '員工',
        Tabs: 'TAB清單',
        Users: '使用者',
        Roles: '權限',
        Customers: '客戶',
        Rewards: '積點',
        EyeExams: '驗光',
        Transactions: '交易',
        Systemlogs: '日誌'
      }[initialPage]);
    } else {
      openModuleTab('Stores', '門市');
    }
  });

  $(function(){
    $('#mobileSysMenu').on('show.bs.collapse', function () {
      $(this).prev('a').find('i.fas.fa-caret-down')
        .removeClass('fa-caret-down').addClass('fa-caret-up');
    });
    $('#mobileSysMenu').on('hide.bs.collapse', function () {
      $(this).prev('a').find('i.fas.fa-caret-up')
        .removeClass('fa-caret-up').addClass('fa-caret-down');
    });

    $('#desktopSysMenu').on('show.bs.collapse', function () {
      $(this).prev('a').find('i.fas.fa-caret-down')
        .removeClass('fa-caret-down').addClass('fa-caret-up');
    });
    $('#desktopSysMenu').on('hide.bs.collapse', function () {
      $(this).prev('a').find('i.fas.fa-caret-up')
        .removeClass('fa-caret-up').addClass('fa-caret-down');
    });
  });
</script>
</body>
</html>
