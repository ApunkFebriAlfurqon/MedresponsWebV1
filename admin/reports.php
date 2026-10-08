<?php
require_once '../includes/helpers.php';
requireAdminLogin();

$year = (int)($_GET['year'] ?? date('Y'));
$monthlyRevenue = db()->fetchAll("SELECT MONTH(created_at) as m, SUM(amount) as total FROM payments WHERE status='paid' AND YEAR(created_at)=? GROUP BY MONTH(created_at) ORDER BY m", [$year]);
$monthlyRequests = db()->fetchAll("SELECT MONTH(created_at) as m, COUNT(*) as count FROM emergency_requests WHERE YEAR(created_at)=? GROUP BY MONTH(created_at) ORDER BY m", [$year]);
$planDist = db()->fetchAll("SELECT sp.name, sp.color, COUNT(*) as count FROM subscriptions s JOIN subscription_plans sp ON s.plan_id=sp.id WHERE s.status='active' GROUP BY sp.id");
$totalRevenue = db()->fetchOne("SELECT IFNULL(SUM(amount),0) as t FROM payments WHERE status='paid' AND YEAR(created_at)=?", [$year])['t'];
$totalReq = db()->count('emergency_requests', "YEAR(created_at)=?", [$year]);

// Build month arrays
$months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
$revByMonth = array_fill(0, 12, 0);
$reqByMonth = array_fill(0, 12, 0);
foreach ($monthlyRevenue as $r) $revByMonth[$r['m']-1] = (float)$r['total'];
foreach ($monthlyRequests as $r) $reqByMonth[$r['m']-1] = (int)$r['count'];
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Reports — RapidAid Admin</title>
<link rel="stylesheet" href="../assets/css/main.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script></head>
<body>
<div class="dashboard-wrapper">
<?php include '_sidebar.php'; ?>
<div class="main-content">
<?php include '_topbar.php'; ?>
<div class="content-area">

<!-- Year selector -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
  <h2 style="color:var(--text);font-size:1.3rem;">Analytics & Reports — <?=$year?></h2>
  <div style="display:flex;gap:8px;">
    <?php for($y=date('Y');$y>=date('Y')-3;$y--): ?>
    <a href="?year=<?=$y?>" class="btn btn-sm <?=$y==$year?'btn-primary':''?>" style="<?=$y!=$year?'border:1px solid var(--border);':''?>"><?=$y?></a>
    <?php endfor; ?>
    <button class="btn btn-ghost btn-sm" style="border:1px solid var(--border);" onclick="window.print()">🖨 Print</button>
  </div>
</div>

<!-- KPI Cards -->
<div class="stats-grid" style="margin-bottom:28px;">
  <div class="stat-card green"><div class="stat-icon green"><i class="fas fa-coins"></i></div><div><div class="stat-label">Revenue <?=$year?></div><div class="stat-value" style="font-size:1rem;"><?=formatRupiah($totalRevenue)?></div></div></div>
  <div class="stat-card red"><div class="stat-icon red"><i class="fas fa-ambulance"></i></div><div><div class="stat-label">Total Requests <?=$year?></div><div class="stat-value"><?=$totalReq?></div></div></div>
  <div class="stat-card teal"><div class="stat-icon teal"><i class="fas fa-check"></i></div><div><div class="stat-label">Completed</div><div class="stat-value"><?=db()->count('emergency_requests',"status='completed' AND YEAR(created_at)=?",[$year])?></div></div></div>
  <div class="stat-card blue"><div class="stat-icon blue"><i class="fas fa-users"></i></div><div><div class="stat-label">New Users <?=$year?></div><div class="stat-value"><?=db()->count('users',"YEAR(created_at)=?",[$year])?></div></div></div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;margin-bottom:24px;">
  <!-- Revenue Chart -->
  <div class="card">
    <div class="card-header"><h3 class="card-title">Monthly Revenue (<?=$year?>)</h3></div>
    <div class="card-body"><canvas id="revChart" height="90"></canvas></div>
  </div>
  <!-- Plan Distribution -->
  <div class="card">
    <div class="card-header"><h3 class="card-title">Active Plan Distribution</h3></div>
    <div class="card-body" style="display:flex;flex-direction:column;align-items:center;gap:16px;">
      <canvas id="planChart" height="160" style="max-width:180px;"></canvas>
      <div style="width:100%;display:flex;flex-direction:column;gap:8px;">
        <?php foreach($planDist as $p): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;font-size:0.82rem;">
          <span style="display:flex;align-items:center;gap:8px;"><span style="width:10px;height:10px;border-radius:2px;background:<?=clean($p['color'])?>"></span><?=clean($p['name'])?></span>
          <strong><?=$p['count']?> subscribers</strong>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<!-- Requests Chart -->
<div class="card" style="margin-bottom:24px;">
  <div class="card-header"><h3 class="card-title">Monthly Emergency Requests (<?=$year?>)</h3></div>
  <div class="card-body"><canvas id="reqChart" height="70"></canvas></div>
</div>

<!-- Top performers table -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
  <div class="card">
    <div class="card-header"><h3 class="card-title">Top Drivers by Trips</h3></div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Driver</th><th>Total Trips</th><th>Rating</th></tr></thead>
        <tbody>
          <?php $topDrivers=db()->fetchAll("SELECT d.full_name,d.rating,COUNT(er.id) as trips FROM drivers d LEFT JOIN emergency_requests er ON er.driver_id=d.id GROUP BY d.id ORDER BY trips DESC LIMIT 5");
          foreach($topDrivers as $d): ?>
          <tr><td><?=clean($d['full_name'])?></td><td><strong><?=$d['trips']?></strong></td><td>⭐ <?=number_format($d['rating'],1)?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="card">
    <div class="card-header"><h3 class="card-title">Top Hospitals by Referrals</h3></div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Hospital</th><th>Referrals</th></tr></thead>
        <tbody>
          <?php $topHosp=db()->fetchAll("SELECT h.name,COUNT(er.id) as refs FROM hospitals h LEFT JOIN emergency_requests er ON er.hospital_id=h.id GROUP BY h.id ORDER BY refs DESC LIMIT 5");
          foreach($topHosp as $h): ?>
          <tr><td>🏥 <?=clean($h['name'])?></td><td><strong><?=$h['refs']?></strong></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</div></div></div>
<script src="../assets/js/main.js"></script>
<script>
const months = <?=json_encode($months)?>;
const rev = <?=json_encode(array_values($revByMonth))?>;
const reqs = <?=json_encode(array_values($reqByMonth))?>;

new Chart(document.getElementById('revChart'), {
  type:'line', data:{labels:months, datasets:[{label:'Revenue (Rp)',data:rev,borderColor:'var(--red)',backgroundColor:'rgba(229,62,62,0.1)',fill:true,tension:0.4,pointBackgroundColor:'var(--red)',pointRadius:4}]},
  options:{responsive:true,plugins:{legend:{display:false}},scales:{x:{grid:{color:'rgba(255,255,255,0.04)'},ticks:{color:'#718096'}},y:{grid:{color:'rgba(255,255,255,0.04)'},ticks:{color:'#718096',callback:v=>'Rp '+v.toLocaleString()}}}}
});

new Chart(document.getElementById('reqChart'), {
  type:'bar', data:{labels:months, datasets:[{label:'Requests',data:reqs,backgroundColor:'rgba(56,178,172,0.6)',borderColor:'var(--teal)',borderWidth:2,borderRadius:6}]},
  options:{responsive:true,plugins:{legend:{display:false}},scales:{x:{grid:{color:'rgba(255,255,255,0.04)'},ticks:{color:'#718096'}},y:{grid:{color:'rgba(255,255,255,0.04)'},ticks:{color:'#718096'}}}}
});

const planNames = <?=json_encode(array_column($planDist,'name'))?>;
const planCounts = <?=json_encode(array_column($planDist,'count'))?>;
const planColors = <?=json_encode(array_column($planDist,'color'))?>;
if(planCounts.length) new Chart(document.getElementById('planChart'),{
  type:'doughnut', data:{labels:planNames,datasets:[{data:planCounts,backgroundColor:planColors,borderWidth:0}]},
  options:{responsive:true,plugins:{legend:{display:false}},cutout:'65%'}
});
</script>
</body></html>
