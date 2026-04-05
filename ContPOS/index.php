<?php
session_start();
$page = isset($_GET['page']) ? $_GET['page'] : 'Store';
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>後台管理系統</title>
  <!-- Bootstrap -->
  <link  href="https://cdn.jsdelivr.net/npm/bootstrap@5/dist/css/bootstrap.min.css" rel="stylesheet">
  <link  href="https://cdn.jsdelivr.net/npm/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet">  <!-- FontAwesome -->
  <link  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    html, body { min-height: 100%; height: 100%; }
    body { background-color: #f8f9fa; min-height: 100vh; padding-bottom: 70px; }
    .sidebar { height: calc(100vh - 70px); background-color: #343a40; transition: width .2s ease; }
    .sidebar a { color: #fff; display: flex; align-items: center; padding: 10px; text-decoration: none; }
    .sidebar a:hover { background-color: #495057; }
    .active-link { background-color: #007bff; color: #fff; }
    .menu-icon { display: inline-flex; align-items: center; justify-content: center; width: 1.75rem; text-align: center; }
    .menu-label { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .sidebar .sidebar-toggle-row { padding: .75rem .5rem; }
    .sidebar .sidebar-toggle-row .btn { min-width: 0; }
    .content-area { min-height: calc(100vh - 70px); display: flex; flex-direction: column; overflow: hidden; }
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
<?php include 'header.php'; ?>
  <div id="moduleWorkspace">
    <ul class="nav nav-tabs module-tabs" id="moduleTabs" role="tablist"></ul>
    <div class="tab-content border border-top-0 rounded-bottom bg-white" id="moduleTabContent"></div>
  </div>

  <script>
    var moduleMap = {
      Store: 'Stores.php',
      Staff: 'Staffs.php',
      Tab: 'TAB.php',
      Users: 'Users.php',
      Roles: 'Roles.php',
      Customers: 'Customers_crud.php',
      Rewards: 'CategorieLevel_crud.php',
      EyeExam: 'EyeExamRecords_crud.php',
      Transactions: 'Transactions.php',
      Systemlogs: 'SystemLogs.php'
    };

    function setActiveMenu(pageKey) {
      $('a[href^="?page="]').removeClass('active-link');
      $('a[href="?page=' + pageKey + '"]').addClass('active-link');
      var systemPages = ['Store', 'Staff', 'Tab', 'Users', 'Roles', 'Rewards', 'Systemlogs'];
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
          Store: '門市',
          Staff: '員工',
          Tab: 'TAB清單',
          Users: '使用者',
          Roles: '權限',
          Customers: '客戶',
          Rewards: '積點',
          EyeExam: '驗光',
          Transactions: '交易',
          Systemlogs: '日誌'
        }[initialPage]);
      } else {
        openModuleTab('Store', '門市');
      }
    });
  </script>
<?php include 'footer.php'; ?>

