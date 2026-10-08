<?php
require_once '../includes/helpers.php';
requireAdminLogin();

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'vehicle_number' => clean($_POST['vehicle_number']),
        'vehicle_type'   => $_POST['vehicle_type'],
        'make'           => clean($_POST['make']),
        'model'          => clean($_POST['model']),
        'year'           => (int)$_POST['year'],
        'color'          => clean($_POST['color']),
        'driver_id'      => $_POST['driver_id'] ?: null,
        'status'         => $_POST['status'],
        'equipment'      => clean($_POST['equipment']??''),
    ];
    if ($action === 'add') {
        db()->insert('ambulances', $data);
        setFlash('success','Ambulance added successfully!');
    } elseif ($action === 'edit' && $id) {
        db()->update('ambulances', $data, 'id=?', [$id]);
        setFlash('success','Ambulance updated successfully!');
    }
    header('Location: ambulances.php'); exit();
}

if ($action === 'delete' && $id) {
    db()->update('ambulances',['status'=>'inactive','driver_id'=>null],'id=?',[$id]);
    setFlash('success','Ambulance removed.');
    header('Location: ambulances.php'); exit();
}

$ambulances = db()->fetchAll("SELECT a.*,d.full_name as driver_name FROM ambulances a LEFT JOIN drivers d ON a.driver_id=d.id ORDER BY a.created_at DESC");
$drivers = db()->fetchAll("SELECT * FROM drivers WHERE status != 'inactive' ORDER BY full_name");
$editItem = $id ? db()->fetchOne("SELECT * FROM ambulances WHERE id=?",[$id]) : null;
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Ambulances — RapidAid Admin</title>
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
      
      <!-- Stats Row -->
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">
        <?php foreach(['available'=>['Available','green','✅'],'on_duty'=>['On Duty','red','🚑'],'maintenance'=>['Maintenance','','⚙️'],'inactive'=>['Inactive','','']] as $s=>[$l,$c,$i]): ?>
        <div class="stat-card <?=$c?>"><div class="stat-icon <?=$c?>"><?=$i?></div><div><div class="stat-label"><?=$l?></div><div class="stat-value"><?=db()->count('ambulances',"status='$s'")?></div></div></div>
        <?php endforeach; ?>
      </div>

      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Ambulance Fleet (<?=count($ambulances)?>)</h3>
          <div style="display:flex;gap:8px;">
            <input type="text" id="search" placeholder="Search..." class="form-control" style="width:180px;padding:8px 12px;"/>
            <button class="btn btn-primary btn-sm" onclick="openModal('amb-modal')">+ Add Ambulance</button>
          </div>
        </div>
        <div class="table-wrap">
          <table id="amb-table">
            <thead><tr><th>#</th><th>Vehicle No.</th><th>Type</th><th>Make/Model</th><th>Year</th><th>Driver</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach($ambulances as $i=>$a): ?>
              <tr>
                <td><?=$i+1?></td>
                <td><strong><?=clean($a['vehicle_number'])?></strong></td>
                <td><span class="badge badge-info"><?=$a['vehicle_type']?></span></td>
                <td><?=clean($a['make'])?> <?=clean($a['model'])?></td>
                <td><?=$a['year']?></td>
                <td><?=clean($a['driver_name']??'—')?></td>
                <td><?=statusBadge($a['status'])?></td>
                <td>
                  <div style="display:flex;gap:6px;">
                    <a href="?action=edit&id=<?=$a['id']?>" class="btn btn-sm btn-ghost" style="border:1px solid var(--border);" onclick="openEditModal(<?=htmlspecialchars(json_encode($a))?>);return false;">✏️</a>
                    <a href="?action=delete&id=<?=$a['id']?>" class="btn btn-sm btn-danger" onclick="return confirm('Remove this ambulance?')">🗑️</a>
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

<!-- Add/Edit Modal -->
<div class="modal-overlay" id="amb-modal">
  <div class="modal" style="max-width:580px;">
    <div class="modal-header"><h3 class="modal-title" id="modal-title">Add Ambulance</h3><button class="modal-close" onclick="closeModal('amb-modal')">✕</button></div>
    <form method="POST" action="?action=add" id="amb-form">
      <div class="modal-body" style="display:flex;flex-direction:column;gap:16px;">
        <div class="row cols-2">
          <div class="form-group"><label class="form-label">Vehicle Number *</label><input type="text" name="vehicle_number" id="f-vehicle_number" class="form-control" placeholder="B 1234 AID" required/></div>
          <div class="form-group"><label class="form-label">Type</label>
            <select name="vehicle_type" id="f-vehicle_type" class="form-control">
              <option value="BLS">BLS - Basic Life Support</option>
              <option value="ALS">ALS - Advanced Life Support</option>
              <option value="neonatal">Neonatal</option>
              <option value="bariatric">Bariatric</option>
            </select>
          </div>
        </div>
        <div class="row cols-3">
          <div class="form-group"><label class="form-label">Make</label><input type="text" name="make" id="f-make" class="form-control" placeholder="Toyota"/></div>
          <div class="form-group"><label class="form-label">Model</label><input type="text" name="model" id="f-model" class="form-control" placeholder="HiAce"/></div>
          <div class="form-group"><label class="form-label">Year</label><input type="number" name="year" id="f-year" class="form-control" placeholder="2023" min="2000" max="2030"/></div>
        </div>
        <div class="row cols-2">
          <div class="form-group"><label class="form-label">Color</label><input type="text" name="color" id="f-color" class="form-control" value="White"/></div>
          <div class="form-group"><label class="form-label">Status</label>
            <select name="status" id="f-status" class="form-control">
              <option value="available">Available</option>
              <option value="on_duty">On Duty</option>
              <option value="maintenance">Maintenance</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
        <div class="form-group"><label class="form-label">Assign Driver</label>
          <select name="driver_id" id="f-driver_id" class="form-control">
            <option value="">— No driver —</option>
            <?php foreach($drivers as $d): ?><option value="<?=$d['id']?>"><?=clean($d['full_name'])?> (<?=$d['employee_id']?>)</option><?php endforeach; ?>
          </select>
        </div>
        <div class="form-group"><label class="form-label">Equipment Notes</label><textarea name="equipment" id="f-equipment" class="form-control" rows="2" placeholder="Defibrillator, oxygen tank, stretcher..."></textarea></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-ghost" onclick="closeModal('amb-modal')">Cancel</button><button type="submit" class="btn btn-primary">Save Ambulance</button></div>
    </form>
  </div>
</div>

<script src="../assets/js/main.js"></script>
<script>
initTableSearch('search','amb-table');
function openEditModal(data) {
  document.getElementById('modal-title').textContent = 'Edit Ambulance';
  document.getElementById('amb-form').action = '?action=edit&id=' + data.id;
  ['vehicle_number','vehicle_type','make','model','year','color','status','equipment'].forEach(f => {
    const el = document.getElementById('f-'+f);
    if(el) el.value = data[f] || '';
  });
  const dr = document.getElementById('f-driver_id');
  if(dr) dr.value = data.driver_id || '';
  openModal('amb-modal');
}
</script>
</body>
</html>
