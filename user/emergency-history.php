<?php
require_once '../includes/helpers.php';
requireUserLogin();
$user = currentUser();
$page = max(1,(int)($_GET['page']??1));
$limit = 10; $offset = ($page-1)*$limit;
$status = $_GET['status'] ?? '';
$where = "er.user_id = ?";
$params = [$user['id']];
if ($status) { $where .= " AND er.status = ?"; $params[] = $status; }
$total = db()->count('emergency_requests er', $where, $params);
$requests = db()->fetchAll(
    "SELECT er.*, d.full_name as driver_name, h.name as hospital_name, a.vehicle_number
     FROM emergency_requests er
     LEFT JOIN drivers d ON er.driver_id=d.id
     LEFT JOIN hospitals h ON er.hospital_id=h.id
     LEFT JOIN ambulances a ON er.ambulance_id=a.id
     WHERE $where ORDER BY er.created_at DESC LIMIT $limit OFFSET $offset", $params);
$totalPages = ceil($total/$limit);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Emergency History — RapidAid</title>
  <link rel="stylesheet" href="../assets/css/main.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>
<div class="dashboard-wrapper">
  <?php include '_sidebar.php'; ?>
  <div class="main-content">
    <?php include '_topbar.php'; ?>
    <div class="content-area">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Emergency History (<?=$total?>)</h3>
          <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <input type="text" id="search-input" placeholder="Search..." class="form-control" style="width:180px;padding:8px 12px;"/>
            <select class="form-control" style="width:140px;padding:8px 12px;" onchange="location='?status='+this.value">
              <option value="">All Status</option>
              <?php foreach(['pending','accepted','on_the_way','arrived','completed','cancelled'] as $s): ?>
              <option value="<?=$s?>" <?=$status===$s?'selected':''?>><?=ucfirst(str_replace('_',' ',$s))?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn btn-ghost btn-sm" style="border:1px solid var(--border);" onclick="exportTableCSV('req-table','emergency_history.csv')">⬇ Export</button>
          </div>
        </div>
        <div class="table-wrap">
          <table id="req-table">
            <thead><tr><th>#</th><th>Code</th><th>Date</th><th>Pickup Address</th><th>Patient</th><th>Driver</th><th>Hospital</th><th>Priority</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
              <?php if(empty($requests)): ?>
              <tr><td colspan="10" style="text-align:center;padding:40px;color:var(--text-muted);">No emergency requests found. <a href="request-ambulance.php" style="color:var(--red);">Make your first request →</a></td></tr>
              <?php else: foreach($requests as $i=>$r): ?>
              <tr>
                <td><?= $offset+$i+1 ?></td>
                <td><code style="font-size:0.78rem;background:var(--bg-card2);padding:3px 8px;border-radius:6px;"><?=clean($r['request_code'])?></code></td>
                <td style="white-space:nowrap;"><?=formatDateTime($r['created_at'])?></td>
                <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?=clean($r['pickup_address'])?>"><?=clean($r['pickup_address'])?></td>
                <td><?=clean($r['patient_name'])?></td>
                <td><?=clean($r['driver_name']??'—')?></td>
                <td><?=clean($r['hospital_name']??'—')?></td>
                <td><span style="font-weight:600;text-transform:capitalize;color:<?=$r['priority']==='critical'?'var(--red)':($r['priority']==='high'?'#d69e2e':'var(--teal)')?>"><?=$r['priority']?></span></td>
                <td><?=statusBadge($r['status'])?></td>
                <td><a href="track-request.php?id=<?=$r['id']?>" class="btn btn-sm btn-ghost" style="border:1px solid var(--border);">View</a></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
        <?php if($totalPages>1): ?>
        <div style="padding:16px 24px;display:flex;gap:8px;justify-content:center;">
          <?php for($p=1;$p<=$totalPages;$p++): ?>
          <a href="?page=<?=$p?>&status=<?=urlencode($status)?>" class="btn btn-sm" style="<?=$p===$page?'background:var(--red);color:white;':'border:1px solid var(--border);'?>"><?=$p?></a>
          <?php endfor; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<script src="../assets/js/main.js"></script>
<script>initTableSearch('search-input','req-table');</script>
</body>
</html>
