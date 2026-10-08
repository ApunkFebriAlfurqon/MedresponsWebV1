<?php
require_once '../includes/helpers.php';
requireUserLogin();
$user = currentUser();
// Mark all as read
db()->update('notifications',['is_read'=>1],'user_id=?',[$user['id']]);
$notifications = getUserNotifications($user['id']);
$typeIcons = ['success'=>'✅','warning'=>'⚠️','danger'=>'🚨','info'=>'ℹ️','emergency'=>'🆘'];
$typeColors = ['success'=>'#38a169','warning'=>'#d69e2e','danger'=>'var(--red)','info'=>'var(--blue)','emergency'=>'var(--red)'];
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Notifications — RapidAid</title>
  <link rel="stylesheet" href="../assets/css/main.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>
<div class="dashboard-wrapper">
  <?php include '_sidebar.php'; ?>
  <div class="main-content">
    <?php include '_topbar.php'; ?>
    <div class="content-area">
      <div style="max-width:680px;margin:0 auto;">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">All Notifications</h3>
            <span style="font-size:0.8rem;color:var(--text-muted);"><?=count($notifications)?> total</span>
          </div>
          <div style="padding:12px;display:flex;flex-direction:column;gap:8px;">
            <?php if(empty($notifications)): ?>
            <div style="text-align:center;padding:48px;color:var(--text-muted);">
              <div style="font-size:3rem;margin-bottom:12px;">🔔</div>
              <p>No notifications yet</p>
            </div>
            <?php else: foreach($notifications as $n):
              $ic = $typeIcons[$n['type']]??'ℹ️';
              $cl = $typeColors[$n['type']]??'var(--blue)';
            ?>
            <div style="display:flex;gap:14px;padding:16px;background:var(--bg-card2);border-radius:12px;border-left:4px solid <?=$cl?>;">
              <div style="width:40px;height:40px;border-radius:50%;background:<?=$cl?>22;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;"><?=$ic?></div>
              <div style="flex:1;">
                <div style="font-weight:600;color:var(--text);margin-bottom:4px;"><?=clean($n['title'])?></div>
                <div style="font-size:0.85rem;color:var(--text-muted);"><?=clean($n['message'])?></div>
                <div style="font-size:0.72rem;color:var(--text-light);margin-top:6px;"><?=timeAgo($n['created_at'])?></div>
              </div>
            </div>
            <?php endforeach; endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>
