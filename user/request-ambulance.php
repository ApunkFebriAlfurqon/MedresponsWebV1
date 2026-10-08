<?php
require_once '../includes/helpers.php';
requireUserLogin();
$user = currentUser();
$hospitals = db()->fetchAll("SELECT * FROM hospitals WHERE status='active' ORDER BY name");
$subscription = getUserActiveSubscription($user['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = generateRequestCode();
    $id = db()->insert('emergency_requests', [
        'request_code'        => $code,
        'user_id'             => $user['id'],
        'pickup_address'      => clean($_POST['pickup_address']),
        'pickup_latitude'     => $_POST['lat'] ?? null,
        'pickup_longitude'    => $_POST['lng'] ?? null,
        'destination_address' => clean($_POST['destination'] ?? ''),
        'patient_name'        => clean($_POST['patient_name']),
        'patient_age'         => (int)($_POST['patient_age'] ?? 0),
        'condition_description'=> clean($_POST['condition']),
        'hospital_id'         => $_POST['hospital_id'] ?: null,
        'priority'            => $_POST['priority'] ?? 'medium',
        'status'              => 'pending',
    ]);
    // auto-assign first available ambulance
    $amb = db()->fetchOne("SELECT * FROM ambulances WHERE status='available' LIMIT 1");
    if ($amb) {
        db()->update('emergency_requests',
            ['ambulance_id'=>$amb['id'],'driver_id'=>$amb['driver_id'],'status'=>'accepted','assigned_at'=>date('Y-m-d H:i:s')],
            'id=?',[$id]);
        db()->update('ambulances',['status'=>'on_duty'],'id=?',[$amb['id']]);
    }
    createNotification($user['id'],null,'Emergency Request Submitted',"Your request $code has been submitted and is being processed.",'success');
    header("Location: track-request.php?id=$id"); exit();
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Request Ambulance — RapidAid</title>
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
      <div style="max-width:700px;margin:0 auto;">
        <div style="background:linear-gradient(135deg,var(--red),#c53030);border-radius:20px;padding:24px 28px;margin-bottom:28px;">
          <h2 style="color:white;margin-bottom:4px;">🚨 Emergency Ambulance Request</h2>
          <p style="color:rgba(255,255,255,0.7);font-size:0.875rem;">Fill in the details below. Our dispatch system will assign the nearest ambulance immediately.</p>
        </div>
        <?php if (!$subscription): ?>
        <div class="alert alert-warning" style="margin-bottom:20px;">⚠️ You don't have an active subscription. <a href="subscriptions.php" style="color:var(--red);font-weight:600;">Subscribe now</a> for priority dispatch and live tracking.</div>
        <?php endif; ?>
        <form method="POST" class="card">
          <div class="card-body" style="display:flex;flex-direction:column;gap:20px;">
            <div id="map" style="height:250px;border-radius:14px;overflow:hidden;background:var(--bg-card2);display:flex;align-items:center;justify-content:center;color:var(--text-muted);">📍 Loading map...</div>
            <input type="hidden" name="lat" id="lat"/><input type="hidden" name="lng" id="lng"/>
            <div class="form-group">
              <label class="form-label">Pickup Address <span style="color:var(--red);">*</span></label>
              <input type="text" name="pickup_address" id="pickup_address" class="form-control" placeholder="Enter your current location" required/>
              <button type="button" onclick="getLocation()" class="btn btn-ghost btn-sm" style="margin-top:8px;border:1px solid var(--border);">📍 Use My Current Location</button>
              <small id="location-status" role="status" aria-live="polite" style="display:block;margin-top:8px;color:var(--text-muted);">Your coordinates are sent to OpenStreetMap Nominatim only when you request an address lookup.</small>
              <div id="location-feedback" hidden role="status" aria-live="polite" style="margin-top:10px;padding:12px 14px;border-radius:10px;align-items:center;justify-content:space-between;gap:12px;">
                <span id="location-feedback-message"></span>
                <button type="button" id="location-retry" class="btn btn-sm" onclick="retryLocationLookup()" hidden style="display:none;">Try Again</button>
              </div>
              <div id="location-api-data" hidden style="margin-top:12px;padding:14px;border:1px solid var(--border);border-radius:12px;background:var(--bg-card2);">
                <strong style="display:block;margin-bottom:8px;">📡 Location data from API</strong>
                <div id="location-api-details" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:8px;font-size:0.82rem;color:var(--text-muted);"></div>
                <small style="display:block;margin-top:10px;color:var(--text-light);">Source: OpenStreetMap Nominatim</small>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Destination / Hospital (Optional)</label>
              <select name="hospital_id" class="form-control">
                <option value="">— Auto select nearest hospital —</option>
                <?php foreach($hospitals as $h): ?>
                <option value="<?=$h['id']?>"><?=clean($h['name'])?> — <?=clean($h['city'])?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Destination Address</label>
              <input type="text" name="destination" class="form-control" placeholder="Hospital or destination address"/>
            </div>
            <div class="row cols-2">
              <div class="form-group">
                <label class="form-label">Patient Name <span style="color:var(--red);">*</span></label>
                <input type="text" name="patient_name" class="form-control" placeholder="Patient full name" value="<?=clean($user['full_name'])?>" required/>
              </div>
              <div class="form-group">
                <label class="form-label">Patient Age</label>
                <input type="number" name="patient_age" class="form-control" placeholder="Age" min="0" max="120"/>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Condition Description <span style="color:var(--red);">*</span></label>
              <textarea name="condition" class="form-control" rows="3" placeholder="Describe the emergency condition (e.g. chest pain, accident, difficulty breathing)..." required></textarea>
            </div>
            <div class="form-group">
              <label class="form-label">Priority Level</label>
              <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;">
                <?php foreach(['low'=>['Low','🟢'],'medium'=>['Medium','🟡'],'high'=>['High','🟠'],'critical'=>['Critical','🔴']] as $v=>[$l,$ic]): ?>
                <label style="cursor:pointer;text-align:center;padding:12px 8px;border-radius:10px;border:2px solid var(--border);transition:all .2s;" class="priority-label">
                  <input type="radio" name="priority" value="<?=$v?>" style="display:none;" <?=$v==='high'?'checked':''?>>
                  <div style="font-size:1.3rem;"><?=$ic?></div>
                  <div style="font-size:0.78rem;font-weight:600;margin-top:4px;"><?=$l?></div>
                </label>
                <?php endforeach; ?>
              </div>
            </div>
            <button type="submit" class="btn btn-primary btn-lg w-full" style="font-size:1rem;">🚨 Submit Emergency Request</button>
            <p style="text-align:center;color:var(--text-muted);font-size:0.8rem;">By submitting, you confirm this is a genuine emergency. False requests may result in account suspension.</p>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="../assets/js/main.js"></script>
<script>
let map, marker;
let activeLocationLookup = 0;
let retryLocationLookupAction = null;
window.addEventListener('load', () => {
  map = L.map('map').setView([-7.2575, 112.7521], 13);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(map);
  map.on('click', e => resolvePickupAddress(e.latlng.lat, e.latlng.lng));
});
function getLocation() {
  if (!navigator.geolocation) {
    retryLocationLookupAction = getLocation;
    return setLocationFeedback('error', 'Geolocation is not supported by this browser.');
  }
  setLocationFeedback('loading', 'Getting your current location…');
  navigator.geolocation.getCurrentPosition(p => {
    resolvePickupAddress(p.coords.latitude, p.coords.longitude);
  }, () => {
    retryLocationLookupAction = getLocation;
    setLocationFeedback('error', 'Could not get your location. Check permission and try again.');
  });
}
async function resolvePickupAddress(lat, lng) {
  const lookupId = ++activeLocationLookup;
  const addressInput = document.getElementById('pickup_address');
  const apiData = document.getElementById('location-api-data');
  const coordinates = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
  retryLocationLookupAction = () => resolvePickupAddress(lat, lng);
  setMarker(lat, lng);
  addressInput.value = coordinates;
  apiData.hidden = true;
  setLocationFeedback('loading', 'Looking up a readable address…');

  try {
    const response = await fetch('../api/reverse-geocode.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ lat, lng })
    });
    let result;
    try {
      result = await response.json();
    } catch (error) {
      throw new Error('The server returned an unreadable response.');
    }
    if (!response.ok || !result.success) throw new Error(result.message || 'Address lookup failed.');
    if (lookupId !== activeLocationLookup) return;
    addressInput.value = result.address;
    renderLocationApiData(result.details);
    setLocationFeedback('success', 'Address found. Please check it before submitting your request.');
  } catch (error) {
    if (lookupId !== activeLocationLookup) return;
    setLocationFeedback('error', `${error.message || 'Address lookup failed.'} Coordinates are kept as the pickup location.`);
  }
}
function setLocationFeedback(state, message) {
  const feedback = document.getElementById('location-feedback');
  const messageElement = document.getElementById('location-feedback-message');
  const retryButton = document.getElementById('location-retry');
  const status = document.getElementById('location-status');
  const styles = {
    loading: { icon: 'fa-circle-notch fa-spin', color: 'var(--blue)', background: 'rgba(49,130,206,0.1)' },
    success: { icon: 'fa-check-circle', color: '#38a169', background: 'rgba(56,161,105,0.1)' },
    error: { icon: 'fa-exclamation-circle', color: 'var(--red)', background: 'rgba(229,62,62,0.1)' }
  }[state];
  feedback.hidden = false;
  feedback.style.display = 'flex';
  feedback.style.color = styles.color;
  feedback.style.background = styles.background;
  const icon = document.createElement('i');
  icon.className = `fas ${styles.icon}`;
  icon.setAttribute('aria-hidden', 'true');
  messageElement.replaceChildren(icon, document.createTextNode(` ${message}`));
  retryButton.hidden = state !== 'error';
  retryButton.style.display = state === 'error' ? 'inline-flex' : 'none';
  status.textContent = state === 'success' ? 'Address lookup succeeded.' :
    state === 'error' ? 'Address lookup failed.' : 'Address lookup in progress.';
}
function retryLocationLookup() {
  if (typeof retryLocationLookupAction === 'function') retryLocationLookupAction();
}
function renderLocationApiData(details) {
  const panel = document.getElementById('location-api-data');
  const container = document.getElementById('location-api-details');
  const fields = [
    ['Area', details.area],
    ['City / district', details.city],
    ['Province / region', details.region],
    ['Postal code', details.postcode],
    ['Country', details.country],
    ['Coordinates', `${Number(details.latitude).toFixed(5)}, ${Number(details.longitude).toFixed(5)}`]
  ];
  container.replaceChildren();
  fields.filter(([, value]) => value).forEach(([label, value]) => {
    const item = document.createElement('div');
    const heading = document.createElement('strong');
    const content = document.createElement('span');
    heading.textContent = `${label}: `;
    content.textContent = value;
    item.append(heading, content);
    container.append(item);
  });
  panel.hidden = false;
}
function setMarker(lat, lng) {
  if (marker) map.removeLayer(marker);
  marker = L.marker([lat,lng]).addTo(map).bindPopup('Pickup location').openPopup();
  map.setView([lat,lng], 15);
  document.getElementById('lat').value = lat;
  document.getElementById('lng').value = lng;
}
document.querySelectorAll('.priority-label').forEach(label => {
  label.querySelector('input').addEventListener('change', () => {
    document.querySelectorAll('.priority-label').forEach(l => l.style.borderColor = 'var(--border)');
    label.style.borderColor = 'var(--red)';
    label.style.background = 'rgba(229,62,62,0.08)';
  });
});
document.querySelector('[name="priority"][value="high"]').dispatchEvent(new Event('change'));
</script>
</body>
</html>
