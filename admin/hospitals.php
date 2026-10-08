<?php
require_once '../includes/helpers.php';
requireAdminLogin();
$action = $_GET['action']??'list'; $id=(int)($_GET['id']??0);
if($_SERVER['REQUEST_METHOD']==='POST'){
    $data=['name'=>clean($_POST['name']),'address'=>clean($_POST['address']),'city'=>clean($_POST['city']),'phone'=>clean($_POST['phone']),'email'=>sanitizeEmail($_POST['email']??''),'emergency_capacity'=>(int)($_POST['capacity']??0),'specializations'=>clean($_POST['specializations']??''),'status'=>$_POST['status'],'latitude'=>$_POST['latitude']??null,'longitude'=>$_POST['longitude']??null];
    if($action==='add'){db()->insert('hospitals',$data);setFlash('success','Hospital added!');}
    elseif($action==='edit'&&$id){db()->update('hospitals',$data,'id=?',[$id]);setFlash('success','Hospital updated!');}
    header('Location: hospitals.php');exit();
}
if($action==='delete'&&$id){db()->update('hospitals',['status'=>'inactive'],'id=?',[$id]);setFlash('success','Hospital deactivated.');header('Location: hospitals.php');exit();}
$hospitals=db()->fetchAll("SELECT * FROM hospitals ORDER BY name");
$flash=getFlash();
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Hospitals — RapidAid Admin</title>
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
          <h3 class="card-title">Hospital Partners (<?=count($hospitals)?>)</h3>
          <div style="display:flex;gap:8px;"><input type="text" id="search" placeholder="Search..." class="form-control" style="width:180px;padding:8px 12px;"/>
          <button class="btn btn-primary btn-sm" onclick="openModal('hosp-modal')">+ Add Hospital</button></div>
        </div>
        <div class="table-wrap">
          <table id="hosp-table">
            <thead><tr><th>#</th><th>Name</th><th>City</th><th>Phone</th><th>ER Capacity</th><th>Specializations</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach($hospitals as $i=>$h): ?>
              <tr>
                <td><?=$i+1?></td>
                <td><div style="font-weight:600;">🏥 <?=clean($h['name'])?></div><div style="font-size:0.75rem;color:var(--text-muted);"><?=clean($h['address'])?></div></td>
                <td><?=clean($h['city'])?></td>
                <td><?=clean($h['phone'])?></td>
                <td><strong><?=$h['emergency_capacity']?></strong> beds</td>
                <td style="font-size:0.8rem;max-width:150px;"><?=clean($h['specializations']??'—')?></td>
                <td><?=statusBadge($h['status'])?></td>
                <td><div style="display:flex;gap:6px;">
                  <button class="btn btn-sm btn-ghost" style="border:1px solid var(--border);" onclick="openEditModal(<?=htmlspecialchars(json_encode($h))?>)">✏️</button>
                  <a href="?action=delete&id=<?=$h['id']?>" class="btn btn-sm btn-danger" onclick="return confirm('Deactivate this hospital?')">🗑️</a>
                </div></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal-overlay" id="hosp-modal">
  <div class="modal">
    <div class="modal-header"><h3 class="modal-title" id="modal-title">Add Hospital</h3><button class="modal-close" onclick="closeModal('hosp-modal')">✕</button></div>
    <form method="POST" action="?action=add" id="hosp-form">
      <div class="modal-body" style="display:flex;flex-direction:column;gap:14px;">
        <div class="form-group"><label class="form-label">Hospital Name *</label><input type="text" name="name" id="f-name" class="form-control" required/></div>
        <div class="form-group"><label class="form-label">Address *</label><textarea name="address" id="f-address" class="form-control" rows="2" required></textarea></div>
        <div class="row cols-2">
          <div class="form-group"><label class="form-label">City</label><input type="text" name="city" id="f-city" class="form-control"/></div>
          <div class="form-group"><label class="form-label">Phone</label><input type="text" name="phone" id="f-phone" class="form-control"/></div>
        </div>
        <div class="row cols-2">
          <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" id="f-email" class="form-control"/></div>
          <div class="form-group"><label class="form-label">ER Capacity (beds)</label><input type="number" name="capacity" id="f-capacity" class="form-control" min="0"/></div>
        </div>
        <div class="row cols-2">
          <div class="form-group"><label class="form-label">Latitude</label><input type="text" name="latitude" id="f-latitude" class="form-control" placeholder="-7.2575"/></div>
          <div class="form-group"><label class="form-label">Longitude</label><input type="text" name="longitude" id="f-longitude" class="form-control" placeholder="112.7521"/></div>
        </div>
        <div class="form-group"><label class="form-label">Specializations</label><input type="text" name="specializations" id="f-specializations" class="form-control" placeholder="Trauma, Cardiology, Neurology"/></div>
        <div class="form-group"><label class="form-label">Status</label><select name="status" id="f-status" class="form-control"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-ghost" onclick="closeModal('hosp-modal')">Cancel</button><button type="submit" class="btn btn-primary">Save Hospital</button></div>
    </form>
  </div>
</div>
<script src="../assets/js/main.js"></script>
<script>
initTableSearch('search','hosp-table');
function openEditModal(h){
  document.getElementById('modal-title').textContent='Edit Hospital';
  document.getElementById('hosp-form').action='?action=edit&id='+h.id;
  ['name','address','city','phone','email','capacity','specializations','latitude','longitude','status'].forEach(f=>{
    const el=document.getElementById('f-'+f); if(el) el.value=h[f]||'';
  });
  openModal('hosp-modal');
}
</script>
</body>
</html>
