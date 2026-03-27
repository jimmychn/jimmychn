<?php
$page = isset($page) ? $page : 'Store';
?>
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
        <li><a class="nav-link <?= $page=='EyeExam' ? 'active-link' : '' ?>" href="?page=EyeExam" onclick="openModuleTab('EyeExam','驗光管理'); return false;"><i class="fas fa-eye me-2"></i> 驗光管理</a></li>
        <li><a class="nav-link <?= $page=='Transactions' ? 'active-link' : '' ?>" href="?page=Transactions" onclick="openModuleTab('Transactions','交易管理'); return false;"><i class="fas fa-file-invoice-dollar me-2"></i> 交易管理</a></li>
        <li class="nav-item">
          <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#mobileSysMenu">
            <span><i class="fas fa-cog me-2"></i> 系統管理</span> <i class="fas fa-caret-down"></i>
          </a>
          <div class="collapse ps-3" id="mobileSysMenu">
            <ul class="navbar-nav">
              <li><a class="nav-link <?= $page=='Store' ? 'active-link' : '' ?>" href="?page=Store" onclick="openModuleTab('Store','門市管理'); return false;">門市管理</a></li>
              <li><a class="nav-link <?= $page=='Staff' ? 'active-link' : '' ?>" href="?page=Staff" onclick="openModuleTab('Staff','員工'); return false;">員工</a></li>
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

<div class="container-fluid">
  <div class="row">
    <div class="col-md-2 sidebar d-none d-md-block" id="desktopSidebar">
        <div class="d-flex justify-content-between align-items-center px-3 mt-3 sidebar-toggle-row">
        <div class="d-flex align-items-center text-white menu-label"><i class="fas fa-bars me-2"></i> 管理選單</div>
        <button id="sidebarToggle" class="btn btn-outline-light btn-sm" type="button"><span><i class="fas fa-bars"></i></span></button>
      </div>
      <a href="?page=Customers" class="<?= $page=='Customers' ? 'active-link' : '' ?>"><span class="menu-icon"><i class="fas fa-user"></i></span><span class="menu-label ms-2">客戶管理</span></a>
      <a href="?page=EyeExam" class="<?= $page=='EyeExam' ? 'active-link' : '' ?>"><span class="menu-icon"><i class="fas fa-eye"></i></span><span class="menu-label ms-2">驗光管理</span></a>
      <a href="?page=Transactions" class="<?= $page=='Transactions' ? 'active-link' : '' ?>"><span class="menu-icon"><i class="fas fa-file-invoice-dollar"></i></span><span class="menu-label ms-2">交易管理</span></a>
      <a class="d-flex justify-content-between align-items-center text-white" data-bs-toggle="collapse" href="#desktopSysMenu">
        <span class="d-flex align-items-center"><span class="menu-icon"><i class="fas fa-cog"></i></span><span class="menu-label ms-2">系統管理</span></span>
        <i class="fas fa-caret-down collapse-toggle-icon"></i>
      </a>
      <div class="collapse ps-3" id="desktopSysMenu">
        <a href="?page=Store" class="<?= $page=='Store' ? 'active-link' : '' ?>" onclick="openModuleTab('Store','門市'); return false;"><span class="menu-icon"><i class="fas fa-store"></i></span><span class="menu-label ms-2">門市</span></a>
        <a href="?page=Staff" class="<?= $page=='Staff' ? 'active-link' : '' ?>" onclick="openModuleTab('Staff','員工'); return false;"><span class="menu-icon"><i class="fas fa-users"></i></span><span class="menu-label ms-2">員工</span></a>
        <a href="?page=Users" class="<?= $page=='Users' ? 'active-link' : '' ?>" onclick="openModuleTab('Users','使用者'); return false;"><span class="menu-icon"><i class="fas fa-user-cog"></i></span><span class="menu-label ms-2">使用者</span></a>
        <a href="?page=Roles" class="<?= $page=='Roles' ? 'active-link' : '' ?>" onclick="openModuleTab('Roles','權限'); return false;"><span class="menu-icon"><i class="fas fa-shield-alt"></i></span><span class="menu-label ms-2">權限</span></a>
        <a href="?page=Rewards" class="<?= $page=='Rewards' ? 'active-link' : '' ?>" onclick="openModuleTab('Rewards','積點'); return false;"><span class="menu-icon"><i class="fas fa-gift"></i></span><span class="menu-label ms-2">積點</span></a>
        <a href="?page=Systemlogs" class="<?= $page=='Systemlogs' ? 'active-link' : '' ?>" onclick="openModuleTab('Systemlogs','日誌'); return false;"><span class="menu-icon"><i class="fas fa-book"></i></span><span class="menu-label ms-2">日誌</span></a>
      </div>
    </div>
    <div class="col-md-10 p-4 content-area">
