<?php
require_once '../includes/helpers.php';
requireAdminLogin();
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'full_name'      => clean($_POST['full_name']),
        'employee_id'    => clean($_POST['employee_id']),
        'email'          => sanitizeEmail($_POST['email']??''),
        'phone'          => clean($_POST['phone']),
        'license_number' => clean($_POST['license_number']),
        'license_expiry' => $_POST['license_expiry']??null,
        'address'        => clean($_POST['address']??''),
        'status'         => $_POST['status'],
    ];
    if ($action==='add') { db()->insert('drivers',$data); setFlash('success','Driver added!'); }
    elseif ($action==='edit' && $id) { db()->update('drivers',$data,'id=?',[$id]); setFlash('success','Driver updated!'); }
    header('Location: drivers.php'); exit();
}
if ($action==='delete' && $id) { db()->update('drivers',['status'=>'inactive'],'id=?',[$id]); setFlash('success','Driver deactivated.'); header('Location: drivers.php'); exit(); }

$drivers = db()->fetchAll("SELECT d.*,a.vehicle_number FROM drivers d LEFT JOIN ambulances a ON a.driver_id=d.id ORDER BY d.created_at DESC");
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Drivers — RapidAid Admin</title>
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
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Driver Management (<?=count($drivers)?>)</h3>
          <div style="display:flex;gap:8px;">
            <input type="text" id="search" placeholder="Search..." class="form-control" style="width:180px;padding:8px 12px;"/>
            <button class="btn btn-primary btn-sm" onclick="openModal('drv-modal')">+ Add Driver</button>
          </div>
        </div>
        <div class="table-wrap">
          <table id="drv-table">
            <thead><tr><th>#</th><th>Name</th><th>Employee ID</th><th>Phone</th><th>License</th><th>Expiry</th><th>Vehicle</th><th>Rating</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach($drivers as $i=>$d): ?>
              <tr>
                <td><?=$i+1?></td>
                <td><div style="display:flex;align-items:center;gap:8px;"><div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--red),var(--teal));display:flex;align-items:center;justify-content:center;color:white;font-size:0.75rem;font-weight:700;"><?=strtoupper(substr($d['full_name'],0,1))?></div><?=clean($d['full_name'])?></div></td>
                <td><?=clean($d['employee_id'])?></td>
                <td><?=clean($d['phone'])?></td>
                <td><?=clean($d['license_number'])?></td>
                <td style="color:<?=strtotime($d['license_expiry']??'2099-01-01')<time()?'var(--red)':'var(--text-muted)'?>"><?=formatDate($d['license_expiry']??'')?></td>
                <td><?=clean($d['vehicle_number']??'—')?></td>
                <td>⭐ <?=number_format($d['rating'],1)?></td>
                <td><?=statusBadge($d['status'])?></td>
                <td>
                  <div style="display:flex;gap:6px;">
                    <button class="btn btn-sm btn-ghost" style="border:1px solid var(--border);" onclick="openEditModal(<?=htmlspecialchars(json_encode($d))?>)">✏️</button>
                    <a href="?action=delete&id=<?=$d['id']?>" class="btn btn-sm btn-danger" onclick="return confirm('Deactivate this driver?')">🗑️</a>
                  </div>
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
<!-- Driver Modal -->
<div class="modal-overlay" id="drv-modal">
  <div class="modal" style="max-width:560px;">
    <div class="modal-header"><h3 class="modal-title" id="modal-title">Add Driver</h3><button class="modal-close" onclick="closeModal('drv-modal')">✕</button></div>
    <form method="POST" action="?action=add" id="drv-form">
      <div class="modal-body" style="display:flex;flex-direction:column;gap:14px;">
        <div class="row cols-2">
          <div class="form-group"><label class="form-label">Full Name *</label><input type="text" name="full_name" id="f-full_name" class="form-control" required/></div>
          <div class="form-group"><label class="form-label">Employee ID *</label><input type="text" name="employee_id" id="f-employee_id" class="form-control" placeholder="DRV-001"/></div>
        </div>
        <div class="row cols-2">
          <div class="form-group"><label class="form-label">Phone *</label><input type="tel" name="phone" id="f-phone" class="form-control"/></div>
          <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" id="f-email" class="form-control"/></div>
        </div>
        <div class="row cols-2">
          <div class="form-group"><label class="form-label">License Number</label><input type="text" name="license_number" id="f-license_number" class="form-control"/></div>
          <div class="form-group"><label class="form-label">License Expiry</label><input type="date" name="license_expiry" id="f-license_expiry" class="form-control"/></div>
        </div>
        <div class="form-group"><label class="form-label">Address</label><textarea name="address" id="f-address" class="form-control" rows="2"></textarea></div>
        <div class="form-group"><label class="form-label">Status</label>
          <select name="status" id="f-status" class="form-control">
            <option value="available">Available</option><option value="on_duty">On Duty</option>
            <option value="off_duty">Off Duty</option><option value="inactive">Inactive</option>
          </select>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-ghost" onclick="closeModal('drv-modal')">Cancel</button><button type="submit" class="btn btn-primary">Save Driver</button></div>
    </form>
  </div>
</div>
<script src="../assets/js/main.js"></script>
<script>
initTableSearch('search','drv-table');
function openEditModal(d){
  document.getElementById('modal-title').textContent='Edit Driver';
  document.getElementById('drv-form').action='?action=edit&id='+d.id;
  ['full_name','employee_id','phone','email','license_number','license_expiry','address','status'].forEach(f=>{
    const el=document.getElementById('f-'+f); if(el) el.value=d[f]||'';
  });
  openModal('drv-modal');
}
</script>
</body>
</html>
