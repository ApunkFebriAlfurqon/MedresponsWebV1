<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminRole = $_SESSION['admin_role'] ?? 'admin';
?>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <div class="logo-icon" style="width:34px;height:34px;background:var(--red);border-radius:10px;display:flex;align-items:center;justify-content:center;">🚑</div>
    <div class="logo-text">Rapid<span>Aid</span> <span style="font-size:0.6rem;color:rgba(255,255,255,0.4);font-family:'Plus Jakarta Sans',sans-serif;font-weight:400;">Admin</span></div>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section-label">Overview</div>
    <a href="dashboard.php" class="sidebar-link <?=$currentPage==='dashboard.php'?'active':''?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <div class="nav-section-label">Management</div>
    <a href="users.php" class="sidebar-link <?=$currentPage==='users.php'?'active':''?>"><i class="fas fa-users"></i> Users</a>
    <a href="subscriptions.php" class="sidebar-link <?=$currentPage==='subscriptions.php'?'active':''?>"><i class="fas fa-gem"></i> Subscriptions</a>
    <a href="payments.php" class="sidebar-link <?=$currentPage==='payments.php'?'active':''?>"><i class="fas fa-money-bill-wave"></i> Payments</a>
    <a href="requests.php" class="sidebar-link <?=$currentPage==='requests.php'?'active':''?>"><i class="fas fa-ambulance"></i> Emergency Requests</a>
    <div class="nav-section-label">Fleet</div>
    <a href="ambulances.php" class="sidebar-link <?=$currentPage==='ambulances.php'?'active':''?>"><i class="fas fa-truck-medical"></i> Ambulances</a>
    <a href="drivers.php" class="sidebar-link <?=$currentPage==='drivers.php'?'active':''?>"><i class="fas fa-id-card"></i> Drivers</a>
    <a href="hospitals.php" class="sidebar-link <?=$currentPage==='hospitals.php'?'active':''?>"><i class="fas fa-hospital"></i> Hospitals</a>
    <div class="nav-section-label">Reports</div>
    <a href="reports.php" class="sidebar-link <?=$currentPage==='reports.php'?'active':''?>"><i class="fas fa-chart-bar"></i> Reports & Analytics</a>
  </nav>
  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="sidebar-user-avatar"><?=strtoupper(substr($adminName,0,1))?></div>
      <div>
        <div class="sidebar-user-name"><?=clean($adminName)?></div>
        <div class="sidebar-user-role"><?=ucfirst($adminRole)?></div>
      </div>
    </div>
    <a href="../auth/logout.php?admin=1" class="sidebar-link" style="margin-top:8px;"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
  </div>
</aside>
