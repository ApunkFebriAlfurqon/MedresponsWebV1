<?php
require_once '../includes/helpers.php';
requireUserLogin();
$user = currentUser();
$subscription = getUserActiveSubscription($user['id']);
$recentRequests = db()->fetchAll(
    "SELECT er.*, a.vehicle_number, d.full_name as driver_name, h.name as hospital_name
     FROM emergency_requests er
     LEFT JOIN ambulances a ON er.ambulance_id = a.id
     LEFT JOIN drivers d ON er.driver_id = d.id
     LEFT JOIN hospitals h ON er.hospital_id = h.id
     WHERE er.user_id = ? ORDER BY er.created_at DESC LIMIT 5",
    [$user['id']]
);
$recentPayments = db()->fetchAll(
    "SELECT p.*, sp.name as plan_name FROM payments p
     LEFT JOIN subscriptions s ON p.subscription_id = s.id
     LEFT JOIN subscription_plans sp ON s.plan_id = sp.id
     WHERE p.user_id = ? ORDER BY p.created_at DESC LIMIT 4",
    [$user['id']]
);
$notifications = getUserNotifications($user['id'], false);
$unreadCount   = getUnreadCount($user['id']);
$totalRequests = db()->count('emergency_requests','user_id = ?',[$user['id']]);
$totalPayments = db()->fetchOne("SELECT SUM(amount) as total FROM payments WHERE user_id = ? AND status = 'paid'", [$user['id']]);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Dashboard — RapidAid</title>
  <link rel="stylesheet" href="../assets/css/main.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>
<div class="dashboard-wrapper">
  <!-- Sidebar -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      <div class="logo-icon" style="width:34px;height:34px;background:var(--red);border-radius:10px;display:flex;align-items:center;justify-content:center;">🚑</div>
      <div class="logo-text">Rapid<span>Aid</span></div>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-section-label">Main Menu</div>
      <a href="dashboard.php" class="sidebar-link active"><i class="fas fa-home"></i> Dashboard</a>
      <a href="request-ambulance.php" class="sidebar-link"><i class="fas fa-ambulance"></i> Request Ambulance</a>
      <a href="emergency-history.php" class="sidebar-link"><i class="fas fa-history"></i> Emergency History</a>
      <div class="nav-section-label">Account</div>
      <a href="subscriptions.php" class="sidebar-link"><i class="fas fa-gem"></i> Subscription</a>
      <a href="payments.php" class="sidebar-link"><i class="fas fa-credit-card"></i> Payments</a>
      <a href="notifications.php" class="sidebar-link">
        <i class="fas fa-bell"></i> Notifications
        <?php if ($unreadCount > 0): ?><span class="badge-num"><?= $unreadCount ?></span><?php endif; ?>
      </a>
      <a href="profile.php" class="sidebar-link"><i class="fas fa-user"></i> My Profile</a>
    </nav>
    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="sidebar-user-avatar"><?= strtoupper(substr($user['full_name'],0,1)) ?></div>
        <div>
          <div class="sidebar-user-name"><?= clean($user['full_name']) ?></div>
          <div class="sidebar-user-role"><?= $subscription ? 'Member' : 'Free Account' ?></div>
        </div>
      </div>
      <a href="../auth/logout.php" class="sidebar-link" style="margin-top:8px;"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
    </div>
  </aside>

  <div class="main-content">
    <!-- Topbar -->
    <header class="topbar">
      <div class="topbar-left">
        <button id="sidebar-toggle" style="background:none;border:none;cursor:pointer;color:var(--text);font-size:1.2rem;display:none;">☰</button>
        <span class="page-title-topbar">Dashboard</span>
      </div>
      <div class="topbar-right">
        <button class="theme-toggle" onclick="toggleTheme()">🌙</button>
        <div class="notif-btn" onclick="window.location='notifications.php'">
          <i class="fas fa-bell"></i>
          <?php if ($unreadCount > 0): ?><div class="notif-dot"></div><?php endif; ?>
        </div>
        <a href="request-ambulance.php" class="btn btn-primary btn-sm">🚨 SOS</a>
      </div>
    </header>

    <div class="content-area">
      <!-- Welcome Card -->
      <div style="background:linear-gradient(135deg,var(--red),#c53030);border-radius:20px;padding:28px 32px;margin-bottom:24px;position:relative;overflow:hidden;">
        <div style="position:absolute;right:-20px;top:-20px;width:180px;height:180px;background:rgba(255,255,255,0.06);border-radius:50%;"></div>
        <div style="position:absolute;right:60px;bottom:-40px;width:120px;height:120px;background:rgba(255,255,255,0.04);border-radius:50%;"></div>
        <div style="position:relative;z-index:1;display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:20px;">
          <div>
            <p style="color:rgba(255,255,255,0.7);font-size:0.875rem;margin-bottom:6px;">Welcome back,</p>
            <h2 style="color:white;font-size:1.6rem;margin-bottom:10px;"><?= clean($user['full_name']) ?> 👋</h2>
            <?php if ($subscription): ?>
              <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,0.15);padding:6px 14px;border-radius:99px;">
                <span style="color:white;font-size:0.8rem;font-weight:600;">💎 <?= clean($subscription['plan_name']) ?> Member</span>
              </div>
              <p style="color:rgba(255,255,255,0.65);font-size:0.8rem;margin-top:8px;">Active until: <?= formatDate($subscription['end_date']) ?></p>
            <?php else: ?>
              <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,0.15);padding:6px 14px;border-radius:99px;">
                <span style="color:white;font-size:0.8rem;">Free Account — <a href="subscriptions.php" style="color:#ffd700;font-weight:600;">Upgrade Now</a></span>
              </div>
            <?php endif; ?>
          </div>
          <a href="request-ambulance.php" class="btn" style="background:white;color:var(--red);font-weight:700;box-shadow:0 4px 20px rgba(0,0,0,0.2);">🚑 Request Ambulance</a>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card red">
          <div class="stat-icon red"><i class="fas fa-ambulance"></i></div>
          <div>
            <div class="stat-label">Total Requests</div>
            <div class="stat-value"><?= $totalRequests ?></div>
          </div>
        </div>
        <div class="stat-card teal">
          <div class="stat-icon teal"><i class="fas fa-check-circle"></i></div>
          <div>
            <div class="stat-label">Completed</div>
            <div class="stat-value"><?= db()->count('emergency_requests',"user_id = ? AND status = 'completed'",[$user['id']]) ?></div>
          </div>
        </div>
        <div class="stat-card blue">
          <div class="stat-icon blue"><i class="fas fa-gem"></i></div>
          <div>
            <div class="stat-label">Subscription</div>
            <div class="stat-value" style="font-size:1rem;"><?= $subscription ? clean($subscription['plan_name']) : 'None' ?></div>
          </div>
        </div>
        <div class="stat-card green">
          <div class="stat-icon green"><i class="fas fa-wallet"></i></div>
          <div>
            <div class="stat-label">Total Paid</div>
            <div class="stat-value" style="font-size:1.1rem;"><?= formatRupiah($totalPayments['total'] ?? 0) ?></div>
          </div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;flex-wrap:wrap;">
        <!-- Recent Requests -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Recent Emergency Requests</h3>
            <a href="emergency-history.php" class="btn btn-ghost btn-sm">View All →</a>
          </div>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Code</th><th>Date</th><th>Status</th><th>Driver</th><th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($recentRequests)): ?>
                <tr><td colspan="5" style="text-align:center;padding:32px;color:var(--text-muted);">No emergency requests yet.<br/><a href="request-ambulance.php" style="color:var(--red);">Request your first ambulance</a></td></tr>
                <?php else: ?>
                <?php foreach ($recentRequests as $req): ?>
                <tr>
                  <td><code style="font-size:0.8rem;background:var(--bg-card2);padding:3px 8px;border-radius:6px;"><?= clean($req['request_code']) ?></code></td>
                  <td><?= formatDateTime($req['created_at']) ?></td>
                  <td><?= statusBadge($req['status']) ?></td>
                  <td><?= clean($req['driver_name'] ?? 'Unassigned') ?></td>
                  <td><a href="track-request.php?id=<?= $req['id'] ?>" class="btn btn-sm btn-ghost" style="border:1px solid var(--border);">Track</a></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Notifications Panel -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Notifications</h3>
            <?php if ($unreadCount > 0): ?><span class="badge badge-danger"><?= $unreadCount ?> new</span><?php endif; ?>
          </div>
          <div style="padding:12px;display:flex;flex-direction:column;gap:8px;max-height:400px;overflow-y:auto;">
            <?php if (empty($notifications)): ?>
            <div style="text-align:center;padding:24px;color:var(--text-muted);font-size:0.875rem;">No notifications yet</div>
            <?php else: ?>
            <?php foreach ($notifications as $n): ?>
            <div style="padding:12px;background:<?= !$n['is_read'] ? 'var(--bg-card2)' : 'transparent' ?>;border-radius:10px;border-left:3px solid <?= $n['type'] === 'success' ? '#38a169' : ($n['type'] === 'danger' ? 'var(--red)' : ($n['type'] === 'warning' ? '#d69e2e' : 'var(--blue)')) ?>;">
              <div style="font-size:0.85rem;font-weight:600;color:var(--text);margin-bottom:3px;"><?= clean($n['title']) ?></div>
              <div style="font-size:0.775rem;color:var(--text-muted);"><?= clean($n['message']) ?></div>
              <div style="font-size:0.7rem;color:var(--text-light);margin-top:6px;"><?= timeAgo($n['created_at']) ?></div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Payment History -->
      <div class="card" style="margin-top:24px;">
        <div class="card-header">
          <h3 class="card-title">Recent Payments</h3>
          <a href="payments.php" class="btn btn-ghost btn-sm">View All →</a>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Invoice</th><th>Plan</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
              <?php if (empty($recentPayments)): ?>
              <tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-muted);">No payments yet</td></tr>
              <?php else: ?>
              <?php foreach ($recentPayments as $p): ?>
              <tr>
                <td><span style="font-size:0.8rem;"><?= clean($p['invoice_number']) ?></span></td>
                <td><?= clean($p['plan_name'] ?? 'N/A') ?></td>
                <td style="font-weight:700;"><?= formatRupiah($p['amount']) ?></td>
                <td><?= ucfirst(str_replace('_',' ',$p['payment_method'])) ?></td>
                <td><?= statusBadge($p['status']) ?></td>
                <td><?= formatDate($p['created_at']) ?></td>
              </tr>
              <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="../assets/js/main.js"></script>
<script>
document.getElementById('sidebar-toggle').style.display = 'flex';
</script>
</body>
</html>
