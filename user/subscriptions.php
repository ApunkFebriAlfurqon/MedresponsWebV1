<?php
require_once '../includes/helpers.php';
requireUserLogin();
$user = currentUser();
$subscription = getUserActiveSubscription($user['id']);
$plans = db()->fetchAll("SELECT * FROM subscription_plans WHERE is_active=1 ORDER BY price");
$history = db()->fetchAll(
    "SELECT s.*,sp.name as plan_name,sp.price FROM subscriptions s JOIN subscription_plans sp ON s.plan_id=sp.id WHERE s.user_id=? ORDER BY s.created_at DESC",
    [$user['id']]);
$planIcons = ['basic'=>'🛡️','premium'=>'⚡','family'=>'👨‍👩‍👧‍👦'];
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Subscription — RapidAid</title>
  <link rel="stylesheet" href="../assets/css/main.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>
<div class="dashboard-wrapper">
  <?php include '_sidebar.php'; ?>
  <div class="main-content">
    <?php include '_topbar.php'; ?>
    <div class="content-area">
      <!-- Current Subscription -->
      <?php if($subscription): ?>
      <div style="background:linear-gradient(135deg,<?=$subscription['color']?>,<?=$subscription['color']?>cc);border-radius:20px;padding:28px;margin-bottom:28px;position:relative;overflow:hidden;">
        <div style="position:absolute;right:-30px;top:-30px;width:160px;height:160px;background:rgba(255,255,255,0.08);border-radius:50%;"></div>
        <div style="position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
          <div>
            <p style="color:rgba(255,255,255,0.7);font-size:0.8rem;margin-bottom:4px;">ACTIVE SUBSCRIPTION</p>
            <h2 style="color:white;font-size:1.5rem;margin-bottom:8px;">💎 <?=clean($subscription['plan_name'])?> Plan</h2>
            <p style="color:rgba(255,255,255,0.7);font-size:0.875rem;">Active until: <strong style="color:white;"><?=formatDate($subscription['end_date'])?></strong></p>
            <?php
            $daysLeft = max(0, floor((strtotime($subscription['end_date'])-time())/86400));
            ?>
            <p style="color:rgba(255,255,255,0.7);font-size:0.8rem;margin-top:4px;">
              <?= $daysLeft ?> days remaining
              <?php if($daysLeft <= 7): ?><span style="background:rgba(255,255,255,0.2);padding:2px 8px;border-radius:99px;font-size:0.72rem;margin-left:6px;">⚠️ Expiring Soon</span><?php endif; ?>
            </p>
          </div>
          <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="?action=renew&plan_id=<?=$subscription['plan_id']?>" class="btn" style="background:white;color:var(--red);">🔄 Renew</a>
            <button onclick="openModal('cancel-modal')" class="btn btn-secondary btn-sm">Cancel Plan</button>
          </div>
        </div>
      </div>
      <?php else: ?>
      <div class="alert alert-warning mb-24">You don't have an active subscription. Subscribe now to get priority ambulance dispatch and live tracking.</div>
      <?php endif; ?>

      <!-- Plans -->
      <div class="mb-24"><h3 class="section-title" style="font-size:1.3rem;margin-bottom:4px;">Available Plans</h3><p style="color:var(--text-muted);font-size:0.875rem;">Choose the plan that best fits your needs.</p></div>
      <div class="pricing-grid" style="margin-bottom:36px;">
        <?php foreach($plans as $plan):
          $features = json_decode($plan['features'],true)??[];
          $isCurrent = $subscription && $subscription['plan_id']==$plan['id'];
        ?>
        <div class="pricing-card <?=$plan['is_popular']?'popular':''?>">
          <?php if($plan['is_popular']): ?><div class="popular-badge">Most Popular</div><?php endif; ?>
          <div class="pricing-icon" style="background:<?=$plan['is_popular']?'rgba(229,62,62,0.2)':'rgba(229,62,62,0.08)'?>"><?=$planIcons[$plan['slug']]??'📦'?></div>
          <h3><?=clean($plan['name'])?></h3>
          <p style="font-size:0.82rem;color:<?=$plan['is_popular']?'rgba(255,255,255,0.5)':'var(--text-muted)'?>"><?=$plan['max_members']>1?'Up to '.$plan['max_members'].' members':'Per person'?></p>
          <div class="price-amount"><?=formatRupiah($plan['price'])?></div>
          <div class="price-period">/month</div>
          <ul class="pricing-features">
            <?php foreach($features as $f): ?><li><span class="check">✓</span><?=clean($f)?></li><?php endforeach; ?>
          </ul>
          <?php if($isCurrent): ?>
          <button class="btn w-full" style="background:var(--bg-card2);color:var(--text-muted);cursor:not-allowed;">✓ Current Plan</button>
          <?php else: ?>
          <a href="payment.php?plan_id=<?=$plan['id']?>" class="btn w-full <?=$plan['is_popular']?'btn-primary':'btn-outline'?>">
            <?=$subscription ? 'Upgrade to '.$plan['name'] : 'Get '.$plan['name']?>
          </a>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Subscription History -->
      <?php if(!empty($history)): ?>
      <div class="card">
        <div class="card-header"><h3 class="card-title">Subscription History</h3></div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Plan</th><th>Start</th><th>End</th><th>Status</th></tr></thead>
            <tbody>
              <?php foreach($history as $h): ?>
              <tr>
                <td><strong><?=clean($h['plan_name'])?></strong> — <?=formatRupiah($h['price'])?>/mo</td>
                <td><?=formatDate($h['start_date'])?></td>
                <td><?=formatDate($h['end_date'])?></td>
                <td><?=statusBadge($h['status'])?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Cancel Modal -->
<div class="modal-overlay" id="cancel-modal">
  <div class="modal" style="max-width:420px;">
    <div class="modal-header"><h3 class="modal-title">Cancel Subscription</h3><button class="modal-close" onclick="closeModal('cancel-modal')">✕</button></div>
    <div class="modal-body">
      <p style="color:var(--text-muted);">Are you sure you want to cancel your subscription? Your plan remains active until the end of the billing period. No refunds are provided.</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('cancel-modal')">Keep Plan</button>
      <a href="?action=cancel" class="btn btn-danger">Yes, Cancel</a>
    </div>
  </div>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>
