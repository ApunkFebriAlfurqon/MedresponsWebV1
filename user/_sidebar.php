<?php
$unreadCount = isUserLoggedIn() ? getUnreadCount($user['id'] ?? 0) : 0;
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <div class="logo-icon" style="width:34px;height:34px;background:var(--red);border-radius:10px;display:flex;align-items:center;justify-content:center;">🚑</div>
    <div class="logo-text">Rapid<span>Aid</span></div>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section-label">Main Menu</div>
    <a href="dashboard.php" class="sidebar-link <?= $currentPage==='dashboard.php'?'active':'' ?>"><i class="fas fa-home"></i> Dashboard</a>
    <a href="request-ambulance.php" class="sidebar-link <?= $currentPage==='request-ambulance.php'?'active':'' ?>"><i class="fas fa-ambulance"></i> Request Ambulance</a>
    <a href="emergency-history.php" class="sidebar-link <?= $currentPage==='emergency-history.php'?'active':'' ?>"><i class="fas fa-history"></i> Emergency History</a>
    <div class="nav-section-label">Account</div>
    <a href="subscriptions.php" class="sidebar-link <?= $currentPage==='subscriptions.php'?'active':'' ?>"><i class="fas fa-gem"></i> Subscription</a>
    <a href="payments.php" class="sidebar-link <?= $currentPage==='payments.php'?'active':'' ?>"><i class="fas fa-credit-card"></i> Payments</a>
    <a href="notifications.php" class="sidebar-link <?= $currentPage==='notifications.php'?'active':'' ?>">
      <i class="fas fa-bell"></i> Notifications
      <?php if ($unreadCount > 0): ?><span class="badge-num"><?= $unreadCount ?></span><?php endif; ?>
    </a>
    <a href="profile.php" class="sidebar-link <?= $currentPage==='profile.php'?'active':'' ?>"><i class="fas fa-user"></i> My Profile</a>
  </nav>
  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="sidebar-user-avatar"><?= strtoupper(substr($user['full_name'],0,1)) ?></div>
      <div>
        <div class="sidebar-user-name"><?= clean($user['full_name']) ?></div>
        <div class="sidebar-user-role">Member</div>
      </div>
    </div>
    <a href="../auth/logout.php" class="sidebar-link" style="margin-top:8px;"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
  </div>
</aside>
