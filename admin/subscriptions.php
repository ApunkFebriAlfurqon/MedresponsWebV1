<?php
require_once '../includes/helpers.php';
requireAdminLogin();
$status = clean($_GET['status'] ?? '');
$where = $status ? "s.status = ?" : "1";
$params = $status ? [$status] : [];
$subs = db()->fetchAll(
    "SELECT s.*,u.full_name,u.email,u.phone,sp.name as plan_name,sp.price,sp.color
     FROM subscriptions s
     JOIN users u ON s.user_id=u.id
     JOIN subscription_plans sp ON s.plan_id=sp.id
     WHERE $where ORDER BY s.created_at DESC LIMIT 200", $params);
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Subscriptions — RapidAid Admin</title>
<link rel="stylesheet" href="../assets/css/main.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/></head>
<body>
<div class="dashboard-wrapper">
<?php include '_sidebar.php'; ?>
<div class="main-content">
<?php include '_topbar.php'; ?>
<div class="content-area">
<?php if($flash): ?><div class="alert alert-<?=$flash['type']?> mb-16" data-auto-dismiss><?=$flash['message']?></div><?php endif; ?>
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">
  <div class="stat-card teal"><div class="stat-icon teal"><i class="fas fa-gem"></i></div><div><div class="stat-label">Active</div><div class="stat-value"><?=db()->count('subscriptions',"status='active' AND end_date>=CURDATE()")?></div></div></div>
  <div class="stat-card red"><div class="stat-icon red"><i class="fas fa-clock"></i></div><div><div class="stat-label">Pending</div><div class="stat-value"><?=db()->count('subscriptions',"status='pending'")?></div></div></div>
  <div class="stat-card blue"><div class="stat-icon blue"><i class="fas fa-ban"></i></div><div><div class="stat-label">Expired</div><div class="stat-value"><?=db()->count('subscriptions',"status='expired'")?></div></div></div>
  <div class="stat-card green"><div class="stat-icon green"><i class="fas fa-list"></i></div><div><div class="stat-label">Total All</div><div class="stat-value"><?=db()->count('subscriptions')?></div></div></div>
</div>
<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;">
  <?php foreach([''=> 'All','active'=>'Active','pending'=>'Pending','expired'=>'Expired','cancelled'=>'Cancelled'] as $v=>$l): ?>
  <a href="?status=<?=$v?>" class="btn btn-sm <?=$status===$v?'btn-primary':''?>" style="<?=$status!==$v?'border:1px solid var(--border);':''?>"><?=$l?></a>
  <?php endforeach; ?>
</div>
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Subscriptions (<?=count($subs)?>)</h3>
    <div style="display:flex;gap:8px;"><input type="text" id="search" placeholder="Search..." class="form-control" style="width:200px;padding:8px 12px;"/>
    <button class="btn btn-ghost btn-sm" style="border:1px solid var(--border);" onclick="exportTableCSV('sub-table','subscriptions.csv')">⬇ Export</button></div>
  </div>
  <div class="table-wrap">
    <table id="sub-table">
      <thead><tr><th>#</th><th>User</th><th>Plan</th><th>Price</th><th>Start</th><th>End</th><th>Days Left</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach($subs as $i=>$s):
          $daysLeft = max(0,floor((strtotime($s['end_date'])-time())/86400));
        ?>
        <tr>
          <td><?=$i+1?></td>
          <td><div style="font-weight:600;font-size:0.875rem;"><?=clean($s['full_name'])?></div><div style="font-size:0.72rem;color:var(--text-muted);"><?=clean($s['email'])?></div></td>
          <td><span style="font-weight:700;color:<?=clean($s['color'])?>;"><?=clean($s['plan_name'])?></span></td>
          <td><?=formatRupiah($s['price'])?>/mo</td>
          <td><?=formatDate($s['start_date'])?></td>
          <td><?=formatDate($s['end_date'])?></td>
          <td><span style="color:<?=$daysLeft<=7?'var(--red)':'var(--teal)'?>;font-weight:600;"><?=$daysLeft?> days<?=$daysLeft<=7?' ⚠️':''?></span></td>
          <td><?=statusBadge($s['status'])?></td>
          <td>
            <?php if($s['status']==='pending'): ?>
            <a href="?action=activate&id=<?=$s['id']?>" class="btn btn-sm btn-teal" onclick="return confirm('Activate this subscription?')">Activate</a>
            <?php elseif($s['status']==='active'): ?>
            <a href="?action=expire&id=<?=$s['id']?>" class="btn btn-sm btn-ghost" style="border:1px solid var(--border);" onclick="return confirm('Expire this subscription?')">Expire</a>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
</div></div></div>
<?php
if(isset($_GET['action'])&&isset($_GET['id'])){
  $sid=(int)$_GET['id'];
  if($_GET['action']==='activate') db()->update('subscriptions',['status'=>'active'],'id=?',[$sid]);
  elseif($_GET['action']==='expire') db()->update('subscriptions',['status'=>'expired'],'id=?',[$sid]);
  setFlash('success','Subscription updated.');
  header('Location: subscriptions.php'); exit();
}
?>
<script src="../assets/js/main.js"></script>
<script>initTableSearch('search','sub-table');</script>
</body></html>
