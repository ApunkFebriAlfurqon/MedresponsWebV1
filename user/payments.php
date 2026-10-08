<?php
require_once '../includes/helpers.php';
requireUserLogin();
$user = currentUser();
$payments = db()->fetchAll(
    "SELECT p.*,sp.name as plan_name FROM payments p
     LEFT JOIN subscriptions s ON p.subscription_id=s.id
     LEFT JOIN subscription_plans sp ON s.plan_id=sp.id
     WHERE p.user_id=? ORDER BY p.created_at DESC", [$user['id']]);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Payments — RapidAid</title>
  <link rel="stylesheet" href="../assets/css/main.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>
<div class="dashboard-wrapper">
  <?php include '_sidebar.php'; ?>
  <div class="main-content">
    <?php include '_topbar.php'; ?>
    <div class="content-area">
      <?php if(isset($_GET['success'])): ?>
      <div class="alert alert-success mb-16" data-auto-dismiss>✅ Payment submitted successfully! It will be confirmed within 1 business day.</div>
      <?php endif; ?>
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Payment History</h3>
          <button class="btn btn-ghost btn-sm" style="border:1px solid var(--border);" onclick="exportTableCSV('pay-table','payments.csv')">⬇ Export CSV</button>
        </div>
        <div class="table-wrap">
          <table id="pay-table">
            <thead><tr><th>Invoice</th><th>Plan</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
            <tbody>
              <?php if(empty($payments)): ?>
              <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted);">No payment records found.</td></tr>
              <?php else: foreach($payments as $p): ?>
              <tr>
                <td><code style="font-size:0.78rem;background:var(--bg-card2);padding:3px 8px;border-radius:6px;"><?=clean($p['invoice_number'])?></code></td>
                <td><?=clean($p['plan_name']??'Service')?></td>
                <td style="font-weight:700;color:var(--red);"><?=formatRupiah($p['amount'])?></td>
                <td><?=ucfirst(str_replace('_',' ',$p['payment_method']))?></td>
                <td><?=statusBadge($p['status'])?></td>
                <td><?=formatDateTime($p['created_at'])?></td>
                <td>
                  <button class="btn btn-sm btn-ghost" style="border:1px solid var(--border);" onclick="openModal('inv-<?=$p['id']?>')">🧾 Invoice</button>
                </td>
              </tr>
              <!-- Invoice Modal -->
              <div class="modal-overlay" id="inv-<?=$p['id']?>">
                <div class="modal">
                  <div class="modal-header"><h3 class="modal-title">Invoice <?=clean($p['invoice_number'])?></h3><button class="modal-close" onclick="closeModal('inv-<?=$p['id']?>')">✕</button></div>
                  <div class="modal-body" id="invoice-content-<?=$p['id']?>">
                    <div style="text-align:center;margin-bottom:20px;">
                      <div style="font-family:'Syne',sans-serif;font-size:1.4rem;font-weight:800;color:var(--red);">RapidAid</div>
                      <div style="font-size:0.75rem;color:var(--text-muted);">PT RapidAid Indonesia</div>
                    </div>
                    <div style="border:1px dashed var(--border);border-radius:10px;padding:16px;margin-bottom:16px;">
                      <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:0.875rem;"><span style="color:var(--text-muted);">Invoice #</span><span><?=clean($p['invoice_number'])?></span></div>
                      <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:0.875rem;"><span style="color:var(--text-muted);">Plan</span><span><?=clean($p['plan_name']??'Service')?></span></div>
                      <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:0.875rem;"><span style="color:var(--text-muted);">Method</span><span><?=ucfirst(str_replace('_',' ',$p['payment_method']))?></span></div>
                      <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:0.875rem;"><span style="color:var(--text-muted);">Date</span><span><?=formatDateTime($p['created_at'])?></span></div>
                      <div style="display:flex;justify-content:space-between;font-size:1rem;font-weight:700;border-top:1px solid var(--border);padding-top:8px;margin-top:8px;"><span>Total</span><span style="color:var(--red);"><?=formatRupiah($p['amount'])?></span></div>
                    </div>
                    <div style="text-align:center;"><?=statusBadge($p['status'])?></div>
                  </div>
                  <div class="modal-footer">
                    <button class="btn btn-ghost" onclick="closeModal('inv-<?=$p['id']?>')">Close</button>
                    <button class="btn btn-primary" onclick="window.print()">🖨 Print</button>
                  </div>
                </div>
              </div>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>
