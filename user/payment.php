<?php
require_once '../includes/helpers.php';
requireUserLogin();
$user = currentUser();
$planId = (int)($_GET['plan_id']??0);
$plan = $planId ? db()->fetchOne("SELECT * FROM subscription_plans WHERE id=? AND is_active=1",[$planId]) : null;
if (!$plan) { header('Location: subscriptions.php'); exit(); }
$features = json_decode($plan['features'],true)??[];

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $method  = clean($_POST['payment_method']??'bank_transfer');
    $channel = clean($_POST['payment_channel']??'');
    // Create subscription
    $subId = db()->insert('subscriptions',[
        'user_id'    => $user['id'],
        'plan_id'    => $plan['id'],
        'status'     => 'pending',
        'start_date' => date('Y-m-d'),
        'end_date'   => date('Y-m-d', strtotime('+'.$plan['duration_days'].' days')),
    ]);
    $invoice = generateInvoiceNumber();
    $payId = db()->insert('payments',[
        'invoice_number'  => $invoice,
        'user_id'         => $user['id'],
        'subscription_id' => $subId,
        'amount'          => $plan['price'],
        'payment_method'  => $method,
        'payment_channel' => $channel,
        'status'          => 'pending',
    ]);
    // Handle proof upload
    if (!empty($_FILES['proof']['name'])) {
        $path = uploadFile($_FILES['proof'],'payments');
        if ($path) {
            db()->update('payments',['proof_image'=>$path,'status'=>'paid','paid_at'=>date('Y-m-d H:i:s')],'id=?',[$payId]);
            db()->update('subscriptions',['status'=>'active'],'id=?',[$subId]);
        }
    }
    createNotification($user['id'],null,'Payment Submitted',"Invoice $invoice submitted for ".$plan['name'].' plan.','info');
    header("Location: payments.php?success=1"); exit();
}
$planIcons=['basic'=>'🛡️','premium'=>'⚡','family'=>'👨‍👩‍👧‍👦'];
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Payment — RapidAid</title>
  <link rel="stylesheet" href="../assets/css/main.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>
<div class="dashboard-wrapper">
  <?php include '_sidebar.php'; ?>
  <div class="main-content">
    <?php include '_topbar.php'; ?>
    <div class="content-area">
      <div style="max-width:680px;margin:0 auto;display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start;">
        <div>
          <div class="card">
            <div class="card-header"><h3 class="card-title">Complete Payment</h3></div>
            <div class="card-body">
              <form method="POST" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:20px;">
                <div>
                  <p class="form-label" style="margin-bottom:12px;">Payment Method</p>
                  <div style="display:flex;flex-direction:column;gap:10px;">
                    <?php foreach(['bank_transfer'=>['🏦','Bank Transfer'],'e_wallet'=>['📱','E-Wallet'],'credit_card'=>['💳','Credit Card']] as $v=>[$ic,$lbl]): ?>
                    <label style="display:flex;align-items:center;gap:14px;padding:16px;border-radius:12px;border:2px solid var(--border);cursor:pointer;transition:all .2s;" class="method-label">
                      <input type="radio" name="payment_method" value="<?=$v?>" style="display:none;" <?=$v==='bank_transfer'?'checked':''?>>
                      <span style="font-size:1.4rem;"><?=$ic?></span>
                      <span style="font-weight:600;color:var(--text);"><?=$lbl?></span>
                    </label>
                    <?php endforeach; ?>
                  </div>
                </div>
                <div class="form-group" id="bank-details">
                  <label class="form-label">Bank Account</label>
                  <select name="payment_channel" class="form-control">
                    <option>BCA — 1234567890 (PT RapidAid)</option>
                    <option>Mandiri — 0987654321 (PT RapidAid)</option>
                    <option>BNI — 1122334455 (PT RapidAid)</option>
                    <option>BRI — 5544332211 (PT RapidAid)</option>
                  </select>
                  <div style="background:var(--bg-card2);border-radius:10px;padding:14px;margin-top:10px;font-size:0.8rem;color:var(--text-muted);">
                    📌 Transfer the exact amount of <strong style="color:var(--red);"><?=formatRupiah($plan['price'])?></strong> and upload the proof below.
                  </div>
                </div>
                <div class="form-group">
                  <label class="form-label">Upload Payment Proof</label>
                  <input type="file" name="proof" class="form-control" accept="image/*"/>
                  <p style="font-size:0.75rem;color:var(--text-muted);margin-top:4px;">Upload screenshot or photo of transfer receipt (JPG/PNG, max 5MB)</p>
                </div>
                <button type="submit" class="btn btn-primary btn-lg">✅ Submit Payment</button>
              </form>
            </div>
          </div>
        </div>
        <!-- Order Summary -->
        <div class="card" style="position:sticky;top:80px;">
          <div class="card-header"><h3 class="card-title">Order Summary</h3></div>
          <div class="card-body" style="display:flex;flex-direction:column;gap:14px;">
            <div style="text-align:center;padding:20px;background:var(--bg-card2);border-radius:12px;">
              <div style="font-size:2rem;margin-bottom:8px;"><?=$planIcons[$plan['slug']]??'📦'?></div>
              <div style="font-family:'Syne',sans-serif;font-size:1.2rem;font-weight:800;color:var(--text);"><?=clean($plan['name'])?></div>
              <div style="font-family:'Syne',sans-serif;font-size:1.8rem;font-weight:800;color:var(--red);margin-top:8px;"><?=formatRupiah($plan['price'])?></div>
              <div style="font-size:0.8rem;color:var(--text-muted);">/<?=$plan['duration_days']?> days</div>
            </div>
            <div style="display:flex;flex-direction:column;gap:8px;">
              <?php foreach($features as $f): ?>
              <div style="display:flex;align-items:center;gap:8px;font-size:0.82rem;color:var(--text-muted);"><span style="color:var(--teal);">✓</span><?=clean($f)?></div>
              <?php endforeach; ?>
            </div>
            <div style="border-top:1px solid var(--border);padding-top:12px;">
              <div style="display:flex;justify-content:space-between;font-size:0.875rem;margin-bottom:6px;"><span style="color:var(--text-muted);">Subtotal</span><span><?=formatRupiah($plan['price'])?></span></div>
              <div style="display:flex;justify-content:space-between;font-size:0.875rem;margin-bottom:6px;"><span style="color:var(--text-muted);">Tax (0%)</span><span>Rp 0</span></div>
              <div style="display:flex;justify-content:space-between;font-weight:700;color:var(--red);"><span>Total</span><span><?=formatRupiah($plan['price'])?></span></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="../assets/js/main.js"></script>
<script>
document.querySelectorAll('.method-label').forEach(label => {
  label.querySelector('input').addEventListener('change', () => {
    document.querySelectorAll('.method-label').forEach(l => { l.style.borderColor='var(--border)'; l.style.background=''; });
    label.style.borderColor='var(--red)'; label.style.background='rgba(229,62,62,0.06)';
  });
});
document.querySelector('[name="payment_method"][value="bank_transfer"]').dispatchEvent(new Event('change'));
</script>
</body>
</html>
