<?php
require_once '../includes/helpers.php';
requireAdminLogin();
$page=(int)($_GET['page']??1); $limit=15; $offset=($page-1)*$limit;
$search=clean($_GET['q']??'');
$where=$search?"full_name LIKE ? OR email LIKE ? OR phone LIKE ?":'1';
$params=$search?["%$search%","%$search%","%$search%"]:[];
$total=db()->count('users',$where,$params);
$users=db()->fetchAll("SELECT u.*,s.status as sub_status,sp.name as plan_name FROM users u LEFT JOIN subscriptions s ON s.user_id=u.id AND s.status='active' LEFT JOIN subscription_plans sp ON s.plan_id=sp.id WHERE $where GROUP BY u.id ORDER BY u.created_at DESC LIMIT $limit OFFSET $offset",$params);
$totalPages=ceil($total/$limit);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Users — RapidAid Admin</title>
  <link rel="stylesheet" href="../assets/css/main.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>
<div class="dashboard-wrapper">
  <?php include '_sidebar.php'; ?>
  <div class="main-content">
    <?php include '_topbar.php'; ?>
    <div class="content-area">
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">
        <div class="stat-card red"><div class="stat-icon red"><i class="fas fa-users"></i></div><div><div class="stat-label">Total Users</div><div class="stat-value"><?=db()->count('users')?></div></div></div>
        <div class="stat-card teal"><div class="stat-icon teal"><i class="fas fa-user-check"></i></div><div><div class="stat-label">Active</div><div class="stat-value"><?=db()->count('users',"is_active=1")?></div></div></div>
        <div class="stat-card blue"><div class="stat-icon blue"><i class="fas fa-gem"></i></div><div><div class="stat-label">Subscribers</div><div class="stat-value"><?=db()->count('subscriptions',"status='active' AND end_date>=CURDATE()")?></div></div></div>
        <div class="stat-card green"><div class="stat-icon green"><i class="fas fa-user-plus"></i></div><div><div class="stat-label">New This Month</div><div class="stat-value"><?=db()->count('users',"MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")?></div></div></div>
      </div>
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">All Users (<?=$total?>)</h3>
          <form method="GET" style="display:flex;gap:8px;">
            <input type="text" name="q" value="<?=clean($search)?>" placeholder="Search name, email, phone..." class="form-control" style="width:240px;padding:8px 12px;"/>
            <button type="submit" class="btn btn-primary btn-sm">Search</button>
            <?php if($search): ?><a href="users.php" class="btn btn-ghost btn-sm" style="border:1px solid var(--border);">Clear</a><?php endif; ?>
          </form>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>#</th><th>User</th><th>Phone</th><th>Blood Type</th><th>Subscription</th><th>Requests</th><th>Joined</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach($users as $i=>$u): ?>
              <tr>
                <td><?=$offset+$i+1?></td>
                <td>
                  <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--red),var(--teal));display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:0.875rem;flex-shrink:0;"><?=strtoupper(substr($u['full_name'],0,1))?></div>
                    <div><div style="font-weight:600;"><?=clean($u['full_name'])?></div><div style="font-size:0.75rem;color:var(--text-muted);"><?=clean($u['email'])?></div></div>
                  </div>
                </td>
                <td><?=clean($u['phone'])?></td>
                <td><?=$u['blood_type']??'—'?></td>
                <td><?=$u['plan_name']?statusBadge('active').' '.clean($u['plan_name']):'<span style="color:var(--text-muted);font-size:0.8rem;">No plan</span>'?></td>
                <td><?=db()->count('emergency_requests','user_id=?',[$u['id']])?></td>
                <td><?=formatDate($u['created_at'])?></td>
                <td><?=statusBadge($u['is_active']?'active':'expired')?></td>
                <td>
                  <form method="POST" action="api/toggle-user.php" style="display:inline;">
                    <input type="hidden" name="id" value="<?=$u['id']?>"/>
                    <input type="hidden" name="status" value="<?=$u['is_active']?0:1?>"/>
                    <button type="submit" class="btn btn-sm btn-ghost" style="border:1px solid var(--border);"><?=$u['is_active']?'Suspend':'Activate'?></button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php if($totalPages>1): ?>
        <div style="padding:16px 24px;display:flex;gap:8px;justify-content:center;">
          <?php for($p=1;$p<=$totalPages;$p++): ?>
          <a href="?page=<?=$p?>&q=<?=urlencode($search)?>" class="btn btn-sm" style="<?=$p===$page?'background:var(--red);color:white;':'border:1px solid var(--border);'?>"><?=$p?></a>
          <?php endfor; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>
