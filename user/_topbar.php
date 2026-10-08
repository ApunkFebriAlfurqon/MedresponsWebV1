<?php
$unreadCount = isUserLoggedIn() ? getUnreadCount($user['id'] ?? 0) : 0;
$pageTitles = [
  'dashboard.php' => 'Dashboard',
  'request-ambulance.php' => 'Request Ambulance',
  'emergency-history.php' => 'Emergency History',
  'track-request.php' => 'Track Request',
  'subscriptions.php' => 'Subscription',
  'payments.php' => 'Payment History',
  'notifications.php' => 'Notifications',
  'profile.php' => 'My Profile',
];
$currentTitle = $pageTitles[basename($_SERVER['PHP_SELF'])] ?? 'Dashboard';
?>
<header class="topbar">
  <div class="topbar-left">
    <button id="sidebar-toggle" style="background:none;border:none;cursor:pointer;color:var(--text);font-size:1.2rem;">☰</button>
    <span class="page-title-topbar"><?= $currentTitle ?></span>
  </div>
  <div class="topbar-right">
    <button class="theme-toggle" onclick="toggleTheme()">🌙</button>
    <div class="notif-btn" onclick="window.location='notifications.php'" title="Notifications">
      <i class="fas fa-bell"></i>
      <?php if ($unreadCount > 0): ?><div class="notif-dot"></div><?php endif; ?>
    </div>
    <a href="request-ambulance.php" class="btn btn-primary btn-sm">🚨 SOS</a>
  </div>
</header>
