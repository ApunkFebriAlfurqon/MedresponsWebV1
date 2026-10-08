<?php
require_once '../includes/helpers.php';
requireUserLogin();
$user = currentUser();
$success = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $data = [
        'full_name'               => clean($_POST['full_name']??''),
        'phone'                   => clean($_POST['phone']??''),
        'address'                 => clean($_POST['address']??''),
        'date_of_birth'           => $_POST['dob']??null,
        'gender'                  => $_POST['gender']??null,
        'blood_type'              => $_POST['blood_type']??null,
        'emergency_contact_name'  => clean($_POST['ec_name']??''),
        'emergency_contact_phone' => clean($_POST['ec_phone']??''),
    ];
    if (!empty($_POST['new_password'])) {
        if (!password_verify($_POST['current_password']??'', $user['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($_POST['new_password']) < 6) {
            $error = 'New password must be at least 6 characters.';
        } else {
            $data['password'] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        }
    }
    if (!isset($error)) {
        db()->update('users', $data, 'id=?', [$user['id']]);
        $_SESSION['user_name'] = $data['full_name'];
        $user = currentUser();
        $success = 'Profile updated successfully!';
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>My Profile — RapidAid</title>
  <link rel="stylesheet" href="../assets/css/main.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>
<div class="dashboard-wrapper">
  <?php include '_sidebar.php'; ?>
  <div class="main-content">
    <?php include '_topbar.php'; ?>
    <div class="content-area">
      <div style="max-width:720px;margin:0 auto;">
        <?php if($success): ?><div class="alert alert-success mb-16"><?=$success?></div><?php endif; ?>
        <?php if(isset($error)): ?><div class="alert alert-danger mb-16"><?=$error?></div><?php endif; ?>
        <form method="POST">
          <div class="card" style="margin-bottom:20px;">
            <div class="card-header"><h3 class="card-title">Personal Information</h3></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:18px;">
              <!-- Avatar -->
              <div style="display:flex;align-items:center;gap:16px;">
                <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,var(--red),var(--teal));display:flex;align-items:center;justify-content:center;color:white;font-family:'Syne',sans-serif;font-size:1.8rem;font-weight:800;flex-shrink:0;"><?=strtoupper(substr($user['full_name'],0,1))?></div>
                <div>
                  <div style="font-weight:700;color:var(--text);"><?=clean($user['full_name'])?></div>
                  <div style="font-size:0.8rem;color:var(--text-muted);"><?=clean($user['email'])?></div>
                  <div style="font-size:0.75rem;color:var(--text-light);margin-top:2px;">Member since <?=formatDate($user['created_at'])?></div>
                </div>
              </div>
              <div class="row cols-2">
                <div class="form-group"><label class="form-label">Full Name</label><input type="text" name="full_name" class="form-control" value="<?=clean($user['full_name'])?>" required/></div>
                <div class="form-group"><label class="form-label">Phone Number</label><input type="tel" name="phone" class="form-control" value="<?=clean($user['phone'])?>"/></div>
              </div>
              <div class="form-group"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2"><?=clean($user['address']??'')?></textarea></div>
              <div class="row cols-3">
                <div class="form-group"><label class="form-label">Date of Birth</label><input type="date" name="dob" class="form-control" value="<?=$user['date_of_birth']??''?>"/></div>
                <div class="form-group"><label class="form-label">Gender</label>
                  <select name="gender" class="form-control">
                    <option value="">Select</option>
                    <option value="male" <?=$user['gender']==='male'?'selected':''?>>Male</option>
                    <option value="female" <?=$user['gender']==='female'?'selected':''?>>Female</option>
                    <option value="other" <?=$user['gender']==='other'?'selected':''?>>Other</option>
                  </select>
                </div>
                <div class="form-group"><label class="form-label">Blood Type</label>
                  <select name="blood_type" class="form-control">
                    <option value="">Unknown</option>
                    <?php foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bt): ?>
                    <option value="<?=$bt?>" <?=$user['blood_type']===$bt?'selected':''?>><?=$bt?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
          </div>
          <div class="card" style="margin-bottom:20px;">
            <div class="card-header"><h3 class="card-title">Emergency Contact</h3></div>
            <div class="card-body">
              <div class="row cols-2">
                <div class="form-group"><label class="form-label">Contact Name</label><input type="text" name="ec_name" class="form-control" value="<?=clean($user['emergency_contact_name']??'')?>" placeholder="Contact person name"/></div>
                <div class="form-group"><label class="form-label">Contact Phone</label><input type="tel" name="ec_phone" class="form-control" value="<?=clean($user['emergency_contact_phone']??'')?>" placeholder="08xxxxxxxxxx"/></div>
              </div>
            </div>
          </div>
          <div class="card" style="margin-bottom:20px;">
            <div class="card-header"><h3 class="card-title">Change Password</h3></div>
            <div class="card-body">
              <div class="row cols-3">
                <div class="form-group"><label class="form-label">Current Password</label><input type="password" name="current_password" class="form-control" placeholder="Current password"/></div>
                <div class="form-group"><label class="form-label">New Password</label><input type="password" name="new_password" class="form-control" placeholder="New password (min. 6)"/></div>
                <div class="form-group"><label class="form-label">Confirm New</label><input type="password" name="confirm_new" class="form-control" placeholder="Confirm new password"/></div>
              </div>
            </div>
          </div>
          <button type="submit" class="btn btn-primary">💾 Save Changes</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>
