<?php
require_once '../includes/helpers.php';
requireAdminLogin();
$status=clean($_GET['status']??'');
$where=$status?"p.status=?":'1';
$params=$status?[$status]:[];
$payments=db()->fetchAll(
    "SELECT p.*,u.full_name as user_name,sp.name as plan_name FROM payments p
     JOIN users u ON p.user_id=u.id
     LEFT JOIN subscriptions s ON p.subscription_id=s.id
     LEFT JOIN subscription_plans sp ON s.plan_id=sp.id
     WHERE $where ORDER BY p.created_at DESC LIMIT 100",$params);

if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['approve'])){
    $pid=(int)$_POST['payment_id'];
    $newStatus=$_POST['new_status'];
    db()->update('payments',['status'=>$newStatus,'paid_at'=>$newStatus==='paid'?date('Y-m-d H:i:s'):null],'id=?',[$pid]);
    if($newStatus==='paid'){
        $pay=db()->fetchOne("SELECT * FROM payments WHERE id=?",[$pid]);
        if($pay['subscription_id']) db()->update('subscriptions',['status'=>'active'],'id=?',[$pay['subscription_id']]);
        createNotification($pay['user_id'],null,'Payment Confirmed!','Your payment '.$pay['invoice_number'].' has been confirmed. Subscription is now active.','success');
    }
    setFlash('success','Payment status updated!');
    header('Location: payments.php');exit();
}
$totalRevenue=db()->fetchOne("SELECT IFNULL(SUM(amount),0) as t FROM payments WHERE status='paid'")['t'];
$monthRevenue=db()->fetchOne("SELECT IFNULL(SUM(amount),0) as t FROM payments WHERE status='paid' AND MONTH(created_at)=MONTH(NOW())")['t'];
$flash=getFlash();
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Payments — RapidAid Admin</title>
  <link rel="stylesheet" href="../assets/css/main.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>
<div class="dashboard-wrapper">
  <?php include '_sidebar.php'; ?>
  <div class="main-content">
    <?php include '_topbar.php'; ?>
    <div class="content-area">
      <?php if($flash): ?><div class="alert alert-<?=$flash['type']?> mb-16" data-auto-dismiss><?=$flash['message']?></div><?php endif; ?>
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">
        <div class="stat-card green"><div class="stat-icon green"><i class="fas fa-chart-line"></i></div><div><div class="stat-label">Total Revenue</div><div class="stat-value" style="font-size:1rem;"><?=formatRupiah($totalRevenue)?></div></div></div>
        <div class="stat-card blue"><div class="stat-icon blue"><i class="fas fa-calendar"></i></div><div><div class="stat-label">This Month</div><div class="stat-value" style="font-size:1rem;"><?=formatRupiah($monthRevenue)?></div></div></div>
        <div class="stat-card red"><div class="stat-icon red"><i class="fas fa-clock"></i></div><div><div class="stat-label">Pending</div><div class="stat-value"><?=db()->count('payments',"status='pending'")?></div></div></div>
        <div class="stat-card teal"><div class="stat-icon teal"><i class="fas fa-check-circle"></i></div><div><div class="stat-label">Confirmed</div><div class="stat-value"><?=db()->count('payments',"status='paid'")?></div></div></div>
      </div>
      <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;">
        <?php foreach([''=> 'All','pending'=>'Pending','paid'=>'Paid','failed'=>'Failed'] as $v=>$l): ?>
        <a href="?status=<?=$v?>" class="btn btn-sm <?=$status===$v?'btn-primary':''?>" style="<?=$status!==$v?'border:1px solid var(--border);':''?>"><?=$l?></a>
        <?php endforeach; ?>
      </div>
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Payments (<?=count($payments)?>)</h3>
          <div style="display:flex;gap:8px;">
            <input type="text" id="search" placeholder="Search..." class="form-control" style="width:180px;padding:8px 12px;"/>
            <button class="btn btn-ghost btn-sm" style="border:1px solid var(--border);" onclick="exportTableCSV('pay-table','payments.csv')">⬇ Export</button>
          </div>
        </div>
        <div class="table-wrap">
          <table id="pay-table">
            <thead><tr><th>Invoice</th><th>User</th><th>Plan</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach($payments as $p): ?>
              <tr>
                <td><code style="font-size:0.78rem;background:var(--bg-card2);padding:2px 8px;border-radius:6px;"><?=clean($p['invoice_number'])?></code></td>
                <td><?=clean($p['user_name'])?></td>
                <td><?=clean($p['plan_name']??'Service')?></td>
                <td style="font-weight:700;color:var(--red);"><?=formatRupiah($p['amount'])?></td>
                <td><?=ucfirst(str_replace('_',' ',$p['payment_method']))?></td>
                <td><?=statusBadge($p['status'])?></td>
                <td><?=formatDateTime($p['created_at'])?></td>
                <td>
                  <?php if($p['status']==='pending'): ?>
                  <div style="display:flex;gap:6px;">
                    <form method="POST" style="display:inline;">
                      <input type="hidden" name="approve" value="1"/><input type="hidden" name="payment_id" value="<?=$p['id']?>"/><input type="hidden" name="new_status" value="paid"/>
                      <button type="submit" class="btn btn-sm btn-teal" onclick="return confirm('Confirm this payment?')">✅ Confirm</button>
                    </form>
                    <form method="POST" style="display:inline;">
                      <input type="hidden" name="approve" value="1"/><input type="hidden" name="payment_id" value="<?=$p['id']?>"/><input type="hidden" name="new_status" value="failed"/>
                      <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Mark as failed?')">✕</button>
                    </form>
                  </div>
                  <?php else: ?>
                  <span style="font-size:0.75rem;color:var(--text-muted);"><?=$p['paid_at']?formatDate($p['paid_at']):'—'?></span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="../assets/js/main.js"></script>
<script>initTableSearch('search','pay-table');</script>
</body>
</html>
