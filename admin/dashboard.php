<?php
require_once '../includes/helpers.php';
requireAdminLogin();

$totalUsers       = db()->count('users');
$activeSubscribers = db()->count('subscriptions',"status='active' AND end_date >= CURDATE()");
$totalAmbulances  = db()->count('ambulances');
$availableAmbs    = db()->count('ambulances',"status='available'");
$activeRequests   = db()->count('emergency_requests',"status NOT IN ('completed','cancelled')");
$monthRevenue     = db()->fetchOne("SELECT IFNULL(SUM(amount),0) as total FROM payments WHERE status='paid' AND MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")['total'];
$recentRequests   = db()->fetchAll("SELECT er.*,u.full_name as user_name,d.full_name as driver_name FROM emergency_requests er LEFT JOIN users u ON er.user_id=u.id LEFT JOIN drivers d ON er.driver_id=d.id ORDER BY er.created_at DESC LIMIT 8");
$recentPayments   = db()->fetchAll("SELECT p.*,u.full_name as user_name,sp.name as plan_name FROM payments p JOIN users u ON p.user_id=u.id LEFT JOIN subscriptions s ON p.subscription_id=s.id LEFT JOIN subscription_plans sp ON s.plan_id=sp.id ORDER BY p.created_at DESC LIMIT 6");
// Monthly requests for chart
$monthlyData = db()->fetchAll("SELECT DATE_FORMAT(created_at,'%b') as month, COUNT(*) as count FROM emergency_requests WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY DATE_FORMAT(created_at,'%Y-%m') ORDER BY DATE_FORMAT(created_at,'%Y-%m')");
$monthlyRevData = db()->fetchAll("SELECT DATE_FORMAT(created_at,'%b') as month, SUM(amount) as total FROM payments WHERE status='paid' AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY DATE_FORMAT(created_at,'%Y-%m') ORDER BY DATE_FORMAT(created_at,'%Y-%m')");
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Admin Dashboard — RapidAid</title>
  <link rel="stylesheet" href="../assets/css/main.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="dashboard-wrapper">
  <?php include '_sidebar.php'; ?>
  <div class="main-content">
    <?php include '_topbar.php'; ?>
    <div class="content-area">
      <!-- Welcome -->
      <div style="background:linear-gradient(135deg,#0f1729,#1a2235);border:1px solid var(--border);border-radius:20px;padding:24px 28px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
        <div>
          <h2 style="color:white;margin-bottom:4px;">Good <?=date('H')<12?'Morning':( date('H')<18?'Afternoon':'Evening')?>, <?=clean($_SESSION['admin_name'])?>! 👋</h2>
          <p style="color:rgba(255,255,255,0.5);font-size:0.875rem;"><?=date('l, d F Y')?> · Here's what's happening today.</p>
        </div>
        <div style="display:flex;gap:10px;">
          <a href="requests.php?status=pending" class="btn btn-primary btn-sm">🚨 <?=db()->count('emergency_requests',"status='pending'")?> Pending Requests</a>
          <a href="payments.php?status=pending" class="btn btn-ghost btn-sm" style="border:1px solid var(--border);">💰 <?=db()->count('payments',"status='pending'")?> Pending Payments</a>
        </div>
      </div>

      <!-- Stats -->
      <div class="stats-grid">
        <div class="stat-card red"><div class="stat-icon red"><i class="fas fa-users"></i></div><div><div class="stat-label">Total Users</div><div class="stat-value" data-count="<?=$totalUsers?>"><?=$totalUsers?></div></div></div>
        <div class="stat-card teal"><div class="stat-icon teal"><i class="fas fa-gem"></i></div><div><div class="stat-label">Active Subscribers</div><div class="stat-value" data-count="<?=$activeSubscribers?>"><?=$activeSubscribers?></div></div></div>
        <div class="stat-card blue"><div class="stat-icon blue"><i class="fas fa-ambulance"></i></div><div><div class="stat-label">Ambulances</div><div class="stat-value" data-count="<?=$totalAmbulances?>"><?=$totalAmbulances?></div><div class="stat-change"><?=$availableAmbs?> available</div></div></div>
        <div class="stat-card green"><div class="stat-icon green"><i class="fas fa-fire"></i></div><div><div class="stat-label">Active Requests</div><div class="stat-value" data-count="<?=$activeRequests?>"><?=$activeRequests?></div></div></div>
        <div class="stat-card red"><div class="stat-icon red"><i class="fas fa-wallet"></i></div><div><div class="stat-label">Monthly Revenue</div><div class="stat-value" style="font-size:1rem;"><?=formatRupiah($monthRevenue)?></div></div></div>
        <div class="stat-card teal"><div class="stat-icon teal"><i class="fas fa-check"></i></div><div><div class="stat-label">Completed Today</div><div class="stat-value" data-count="<?=db()->count('emergency_requests',"status='completed' AND DATE(completed_at)=CURDATE()")?>"><?=db()->count('emergency_requests',"status='completed' AND DATE(completed_at)=CURDATE()")?></div></div></div>
      </div>

      <!-- Charts -->
      <div style="display:grid;grid-template-columns:3fr 2fr;gap:24px;margin-bottom:24px;">
        <div class="card">
          <div class="card-header"><h3 class="card-title">Emergency Requests (6 months)</h3></div>
          <div class="card-body"><canvas id="reqChart" height="100"></canvas></div>
        </div>
        <div class="card">
          <div class="card-header"><h3 class="card-title">Fleet Status</h3></div>
          <div class="card-body" style="display:flex;flex-direction:column;align-items:center;">
            <canvas id="fleetChart" height="160" style="max-width:200px;"></canvas>
            <div style="margin-top:16px;display:flex;gap:16px;flex-wrap:wrap;justify-content:center;font-size:0.8rem;">
              <div style="display:flex;align-items:center;gap:6px;"><span style="width:12px;height:12px;background:#38a169;border-radius:2px;"></span>Available (<?=$availableAmbs?>)</div>
              <div style="display:flex;align-items:center;gap:6px;"><span style="width:12px;height:12px;background:#d69e2e;border-radius:2px;"></span>On Duty (<?=db()->count('ambulances',"status='on_duty'")?>)</div>
              <div style="display:flex;align-items:center;gap:6px;"><span style="width:12px;height:12px;background:#718096;border-radius:2px;"></span>Maintenance (<?=db()->count('ambulances',"status='maintenance'")?>)</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Requests -->
      <div style="display:grid;grid-template-columns:3fr 2fr;gap:24px;">
        <div class="card">
          <div class="card-header"><h3 class="card-title">Recent Emergency Requests</h3><a href="requests.php" class="btn btn-ghost btn-sm">View All →</a></div>
          <div class="table-wrap">
            <table>
              <thead><tr><th>Code</th><th>User</th><th>Driver</th><th>Priority</th><th>Status</th><th>Time</th></tr></thead>
              <tbody>
                <?php foreach($recentRequests as $r): ?>
                <tr>
                  <td><code style="font-size:0.75rem;background:var(--bg-card2);padding:2px 7px;border-radius:6px;"><?=clean($r['request_code'])?></code></td>
                  <td><?=clean($r['user_name'])?></td>
                  <td><?=clean($r['driver_name']??'—')?></td>
                  <td><span style="font-weight:600;text-transform:capitalize;font-size:0.8rem;color:<?=$r['priority']==='critical'?'var(--red)':($r['priority']==='high'?'#d69e2e':'var(--teal)')?>"><?=$r['priority']?></span></td>
                  <td><?=statusBadge($r['status'])?></td>
                  <td style="font-size:0.78rem;color:var(--text-muted);"><?=timeAgo($r['created_at'])?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <div class="card">
          <div class="card-header"><h3 class="card-title">Recent Payments</h3><a href="payments.php" class="btn btn-ghost btn-sm">View All →</a></div>
          <div style="padding:8px;display:flex;flex-direction:column;gap:8px;">
            <?php foreach($recentPayments as $p): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:var(--bg-card2);border-radius:10px;">
              <div>
                <div style="font-size:0.875rem;font-weight:600;color:var(--text);"><?=clean($p['user_name'])?></div>
                <div style="font-size:0.75rem;color:var(--text-muted);"><?=clean($p['plan_name']??'Service')?> · <?=timeAgo($p['created_at'])?></div>
              </div>
              <div style="text-align:right;">
                <div style="font-weight:700;color:var(--red);font-size:0.9rem;"><?=formatRupiah($p['amount'])?></div>
                <?=statusBadge($p['status'])?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="../assets/js/main.js"></script>
<script>
const months = <?=json_encode(array_column($monthlyData,'month'))?>;
const counts = <?=json_encode(array_column($monthlyData,'count'))?>;

const ctx1 = document.getElementById('reqChart').getContext('2d');
new Chart(ctx1, {
  type: 'bar',
  data: { labels: months.length ? months : ['Jan','Feb','Mar','Apr','May','Jun'],
    datasets: [{
      label: 'Requests', data: counts.length ? counts : [12,19,8,25,14,18],
      backgroundColor: 'rgba(229,62,62,0.6)', borderColor:'var(--red)', borderWidth:2, borderRadius:6
    }]
  },
  options: { responsive:true, plugins:{legend:{display:false}}, scales:{
    x:{grid:{color:'rgba(255,255,255,0.04)'},ticks:{color:'#718096'}},
    y:{grid:{color:'rgba(255,255,255,0.04)'},ticks:{color:'#718096'}}
  }}
});

const avail = <?=$availableAmbs?>;
const onDuty = <?=db()->count('ambulances',"status='on_duty'")?>;
const maint = <?=db()->count('ambulances',"status='maintenance'")?>;
const ctx2 = document.getElementById('fleetChart').getContext('2d');
new Chart(ctx2, {
  type: 'doughnut',
  data: { labels:['Available','On Duty','Maintenance'],
    datasets:[{data:[avail||1,onDuty,maint], backgroundColor:['#38a169','#d69e2e','#718096'], borderWidth:0}]
  },
  options:{ responsive:true, plugins:{legend:{display:false}}, cutout:'70%' }
});
</script>
</body>
</html>
