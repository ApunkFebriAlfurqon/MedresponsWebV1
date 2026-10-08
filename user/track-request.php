<?php
require_once '../includes/helpers.php';
requireUserLogin();
$user = currentUser();
$id = (int)($_GET['id'] ?? 0);
$req = db()->fetchOne(
    "SELECT er.*, a.vehicle_number, a.latitude as amb_lat, a.longitude as amb_lng,
            d.full_name as driver_name, d.phone as driver_phone, d.rating as driver_rating,
            h.name as hospital_name, h.address as hospital_address
     FROM emergency_requests er
     LEFT JOIN ambulances a ON er.ambulance_id = a.id
     LEFT JOIN drivers d ON er.driver_id = d.id
     LEFT JOIN hospitals h ON er.hospital_id = h.id
     WHERE er.id = ? AND er.user_id = ?", [$id, $user['id']]);
if (!$req) { header('Location: emergency-history.php'); exit(); }

$statusSteps = ['pending'=>0,'accepted'=>1,'on_the_way'=>2,'arrived'=>3,'completed'=>4,'cancelled'=>-1];
$currentStep = $statusSteps[$req['status']] ?? 0;
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Track Request — RapidAid</title>
  <link rel="stylesheet" href="../assets/css/main.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
</head>
<body>
<div class="dashboard-wrapper">
  <?php include '_sidebar.php'; ?>
  <div class="main-content">
    <?php include '_topbar.php'; ?>
    <div class="content-area">
      <div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start;">
        <div>
          <!-- Status Tracker -->
          <div class="card" style="margin-bottom:20px;">
            <div class="card-header">
              <h3 class="card-title">Request #<?= clean($req['request_code']) ?></h3>
              <?= statusBadge($req['status']) ?>
            </div>
            <div class="card-body">
              <div style="display:flex;gap:0;position:relative;">
                <?php
                $steps = [
                  ['pending','Pending','🕐'],
                  ['accepted','Accepted','✅'],
                  ['on_the_way','On The Way','🚑'],
                  ['arrived','Arrived','📍'],
                  ['completed','Completed','🏥'],
                ];
                foreach($steps as $i=>[$sv,$sl,$si]):
                  $done = $i < $currentStep;
                  $active = $i === $currentStep;
                ?>
                <div style="flex:1;text-align:center;position:relative;">
                  <?php if($i>0): ?>
                  <div style="position:absolute;top:19px;left:-50%;right:50%;height:3px;background:<?=$done?'var(--teal)':'var(--border)'?>;z-index:0;"></div>
                  <?php endif; ?>
                  <div style="width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 8px;font-size:1.1rem;z-index:1;position:relative;background:<?=$active?'var(--red)':($done?'var(--teal)':'var(--border)')?>;">
                    <?= $si ?>
                  </div>
                  <div style="font-size:0.75rem;font-weight:600;color:<?=$active?'var(--red)':($done?'var(--teal)':'var(--text-muted)')?>;"><?=$sl?></div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <!-- Map -->
          <div class="card">
            <div class="card-header"><h3 class="card-title">Live Tracking</h3>
              <span style="font-size:0.8rem;color:var(--text-muted);" id="eta-label">Calculating ETA...</span>
            </div>
            <div id="tracking-map" style="height:380px;border-radius:0 0 16px 16px;"></div>
          </div>
        </div>

        <!-- Info Panel -->
        <div style="display:flex;flex-direction:column;gap:16px;">
          <!-- Request Info -->
          <div class="card">
            <div class="card-header"><h3 class="card-title">Request Details</h3></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:12px;font-size:0.875rem;">
              <div><span style="color:var(--text-muted);">Patient:</span> <strong><?= clean($req['patient_name']) ?></strong></div>
              <div><span style="color:var(--text-muted);">Pickup:</span> <?= clean($req['pickup_address']) ?></div>
              <?php if($req['hospital_name']): ?>
              <div><span style="color:var(--text-muted);">Hospital:</span> <?= clean($req['hospital_name']) ?></div>
              <?php endif; ?>
              <div><span style="color:var(--text-muted);">Priority:</span> <span style="text-transform:capitalize;font-weight:600;color:var(--red);"><?= $req['priority'] ?></span></div>
              <div><span style="color:var(--text-muted);">Submitted:</span> <?= formatDateTime($req['created_at']) ?></div>
              <?php if($req['condition_description']): ?>
              <div><span style="color:var(--text-muted);">Condition:</span> <?= clean($req['condition_description']) ?></div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Driver Info -->
          <?php if($req['driver_name']): ?>
          <div class="card">
            <div class="card-header"><h3 class="card-title">Driver & Ambulance</h3></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:12px;">
              <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,var(--red),var(--teal));display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:1.1rem;flex-shrink:0;"><?= strtoupper(substr($req['driver_name'],0,1)) ?></div>
                <div>
                  <div style="font-weight:700;color:var(--text);"><?= clean($req['driver_name']) ?></div>
                  <div style="font-size:0.8rem;color:var(--text-muted);">⭐ <?= $req['driver_rating'] ?? '4.9' ?> · EMT Certified</div>
                </div>
              </div>
              <?php if($req['driver_phone']): ?>
              <a href="tel:<?= clean($req['driver_phone']) ?>" class="btn btn-teal w-full btn-sm">📞 Call Driver</a>
              <?php endif; ?>
              <?php if($req['vehicle_number']): ?>
              <div style="background:var(--bg-card2);border-radius:10px;padding:12px;text-align:center;">
                <div style="font-size:0.75rem;color:var(--text-muted);margin-bottom:4px;">Ambulance</div>
                <div style="font-family:'Syne',sans-serif;font-size:1.1rem;font-weight:800;color:var(--red);"><?= clean($req['vehicle_number']) ?></div>
              </div>
              <?php endif; ?>
            </div>
          </div>
          <?php endif; ?>

          <!-- Cancel button if pending -->
          <?php if(in_array($req['status'],['pending','accepted'])): ?>
          <form method="POST" action="../api/cancel-request.php">
            <input type="hidden" name="id" value="<?= $req['id'] ?>"/>
            <button type="submit" class="btn btn-outline w-full" onclick="return confirm('Cancel this emergency request?')">✕ Cancel Request</button>
          </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="../assets/js/main.js"></script>
<script>
const userLat = <?= $req['pickup_latitude'] ?: -7.2575 ?>;
const userLng = <?= $req['pickup_longitude'] ?: 112.7521 ?>;
const ambLat  = <?= $req['amb_lat'] ?: -7.2700 ?>;
const ambLng  = <?= $req['amb_lng'] ?: 112.7400 ?>;

const map = L.map('tracking-map').setView([userLat, userLng], 14);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'© OpenStreetMap'}).addTo(map);

const userIcon = L.divIcon({html:'<div style="font-size:24px;">📍</div>',iconSize:[30,30],className:''});
const ambIcon  = L.divIcon({html:'<div style="font-size:24px;animation:none;">🚑</div>',iconSize:[30,30],className:''});

L.marker([userLat, userLng], {icon:userIcon}).addTo(map).bindPopup('<b>Your Location</b>').openPopup();
const ambMarker = L.marker([ambLat, ambLng], {icon:ambIcon}).addTo(map).bindPopup('Ambulance');

// Simulate movement
let aLat = ambLat, aLng = ambLng;
const dLat = (userLat - ambLat)/40, dLng = (userLng - ambLng)/40;
let steps = 0, etaMin = 8;
const sim = setInterval(() => {
  if(steps >= 40){clearInterval(sim); document.getElementById('eta-label').textContent='Ambulance arrived!'; return;}
  aLat += dLat; aLng += dLng; steps++;
  ambMarker.setLatLng([aLat, aLng]);
  etaMin = Math.max(0, Math.round(8 - steps/5));
  document.getElementById('eta-label').textContent = etaMin > 0 ? `ETA: ~${etaMin} min` : 'Ambulance arriving...';
}, 2000);
</script>
</body>
</html>
