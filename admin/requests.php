<?php
require_once '../includes/helpers.php';
requireAdminLogin();
$status=clean($_GET['status']??'');
$where=$status?"er.status=?":"1";
$params=$status?[$status]:[];
$requests=db()->fetchAll(
    "SELECT er.*,u.full_name as user_name,u.phone as user_phone,d.full_name as driver_name,a.vehicle_number,h.name as hospital_name
     FROM emergency_requests er
     JOIN users u ON er.user_id=u.id
     LEFT JOIN drivers d ON er.driver_id=d.id
     LEFT JOIN ambulances a ON er.ambulance_id=a.id
     LEFT JOIN hospitals h ON er.hospital_id=h.id
     WHERE $where ORDER BY er.created_at DESC LIMIT 100",$params);

// Handle assignment POST
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['assign'])){
    $reqId=(int)$_POST['req_id'];
    $ambId=$_POST['ambulance_id']?:null;
    $drvId=$_POST['driver_id']?:null;
    $hospId=$_POST['hospital_id']?:null;
    $newStatus=$_POST['new_status']??'accepted';
    db()->update('emergency_requests',['ambulance_id'=>$ambId,'driver_id'=>$drvId,'hospital_id'=>$hospId,'status'=>$newStatus,'assigned_at'=>date('Y-m-d H:i:s')],'id=?',[$reqId]);
    if($ambId) db()->update('ambulances',['status'=>'on_duty'],'id=?',[$ambId]);
    setFlash('success','Request updated!');
    header('Location: requests.php');exit();
}
$ambulances=db()->fetchAll("SELECT * FROM ambulances WHERE status='available'");
$drivers=db()->fetchAll("SELECT * FROM drivers WHERE status='available'");
$hospitals=db()->fetchAll("SELECT * FROM hospitals WHERE status='active'");
$flash=getFlash();
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Emergency Requests — RapidAid Admin</title>
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
      <!-- Filter tabs -->
      <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;">
        <?php foreach([''=> 'All', 'pending'=>'Pending','accepted'=>'Accepted','on_the_way'=>'On The Way','arrived'=>'Arrived','completed'=>'Completed','cancelled'=>'Cancelled'] as $v=>$l): ?>
        <a href="?status=<?=$v?>" class="btn btn-sm <?=$status===$v?'btn-primary':''?>" style="<?=$status!==$v?'border:1px solid var(--border);':''?>"><?=$l?> (<?=db()->count('emergency_requests',$v?"status='$v'":'1')?>)</a>
        <?php endforeach; ?>
      </div>
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Emergency Requests (<?=count($requests)?>)</h3>
          <input type="text" id="search" placeholder="Search..." class="form-control" style="width:200px;padding:8px 12px;"/>
        </div>
        <div class="table-wrap">
          <table id="req-table">
            <thead><tr><th>Code</th><th>User</th><th>Pickup Address</th><th>Priority</th><th>Ambulance</th><th>Driver</th><th>Hospital</th><th>Status</th><th>Time</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach($requests as $r): ?>
              <tr>
                <td><code style="font-size:0.75rem;background:var(--bg-card2);padding:2px 8px;border-radius:6px;"><?=clean($r['request_code'])?></code></td>
                <td><div style="font-size:0.875rem;"><?=clean($r['user_name'])?></div><div style="font-size:0.72rem;color:var(--text-muted);"><?=clean($r['user_phone'])?></div></td>
                <td style="max-width:160px;font-size:0.8rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?=clean($r['pickup_address'])?>"><?=clean($r['pickup_address'])?></td>
                <td><span style="font-weight:700;text-transform:capitalize;font-size:0.8rem;color:<?=$r['priority']==='critical'?'var(--red)':($r['priority']==='high'?'#d69e2e':'var(--teal)')?>"><?=$r['priority']?></span></td>
                <td><?=clean($r['vehicle_number']??'—')?></td>
                <td><?=clean($r['driver_name']??'—')?></td>
                <td style="font-size:0.8rem;"><?=clean($r['hospital_name']??'—')?></td>
                <td><?=statusBadge($r['status'])?></td>
                <td style="font-size:0.75rem;color:var(--text-muted);white-space:nowrap;"><?=timeAgo($r['created_at'])?></td>
                <td>
                  <?php if(!in_array($r['status'],['completed','cancelled'])): ?>
                  <button class="btn btn-sm btn-primary" onclick='openAssignModal(<?=htmlspecialchars(json_encode($r))?>)'>Manage</button>
                  <?php else: ?>
                  <span style="font-size:0.75rem;color:var(--text-muted);">Closed</span>
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

<!-- Assign Modal -->
<div class="modal-overlay" id="assign-modal">
  <div class="modal">
    <div class="modal-header"><h3 class="modal-title" id="assign-title">Manage Request</h3><button class="modal-close" onclick="closeModal('assign-modal')">✕</button></div>
    <form method="POST">
      <input type="hidden" name="assign" value="1"/>
      <input type="hidden" name="req_id" id="f-req_id"/>
      <div class="modal-body" style="display:flex;flex-direction:column;gap:14px;">
        <div class="form-group"><label class="form-label">Assign Ambulance</label>
          <select name="ambulance_id" id="f-ambulance_id" class="form-control">
            <option value="">— No ambulance —</option>
            <?php foreach($ambulances as $a): ?><option value="<?=$a['id']?>"><?=clean($a['vehicle_number'])?> (<?=$a['vehicle_type']?>)</option><?php endforeach; ?>
          </select>
        </div>
        <div class="form-group"><label class="form-label">Assign Driver</label>
          <select name="driver_id" id="f-driver_id" class="form-control">
            <option value="">— No driver —</option>
            <?php foreach($drivers as $d): ?><option value="<?=$d['id']?>"><?=clean($d['full_name'])?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="form-group"><label class="form-label">Assign Hospital</label>
          <select name="hospital_id" id="f-hospital_id" class="form-control">
            <option value="">— No hospital —</option>
            <?php foreach($hospitals as $h): ?><option value="<?=$h['id']?>"><?=clean($h['name'])?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="form-group"><label class="form-label">Update Status</label>
          <select name="new_status" id="f-new_status" class="form-control">
            <?php foreach(['pending','accepted','on_the_way','arrived','completed','cancelled'] as $s): ?>
            <option value="<?=$s?>"><?=ucfirst(str_replace('_',' ',$s))?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-ghost" onclick="closeModal('assign-modal')">Cancel</button><button type="submit" class="btn btn-primary">Update Request</button></div>
    </form>
  </div>
</div>
<script src="../assets/js/main.js"></script>
<script>
initTableSearch('search','req-table');
function openAssignModal(r){
  document.getElementById('assign-title').textContent='Manage Request: '+r.request_code;
  document.getElementById('f-req_id').value=r.id;
  if(r.ambulance_id) document.getElementById('f-ambulance_id').value=r.ambulance_id;
  if(r.driver_id) document.getElementById('f-driver_id').value=r.driver_id;
  if(r.hospital_id) document.getElementById('f-hospital_id').value=r.hospital_id;
  document.getElementById('f-new_status').value=r.status;
  openModal('assign-modal');
}
</script>
</body>
</html>
