<?php
$pageTitles = [
  'dashboard.php'=>'Dashboard','users.php'=>'User Management','subscriptions.php'=>'Subscriptions',
  'payments.php'=>'Payment Management','requests.php'=>'Emergency Requests',
  'ambulances.php'=>'Ambulance Fleet','drivers.php'=>'Driver Management',
  'hospitals.php'=>'Hospital Partners','reports.php'=>'Reports & Analytics',
];
$currentTitle = $pageTitles[basename($_SERVER['PHP_SELF'])] ?? 'Admin Panel';
?>
<header class="topbar">
  <div class="topbar-left">
    <button id="sidebar-toggle" style="background:none;border:none;cursor:pointer;color:var(--text);font-size:1.2rem;">☰</button>
    <span class="page-title-topbar"><?=$currentTitle?></span>
  </div>
  <div class="topbar-right">
    <button class="theme-toggle" onclick="toggleTheme()">🌙</button>
    <a href="../index.php" target="_blank" class="btn btn-ghost btn-sm" style="border:1px solid var(--border);">🌐 Website</a>
  </div>
</header>
