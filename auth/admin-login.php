<?php
require_once '../includes/helpers.php';
startSession();
if (isAdminLoggedIn()) { header('Location: ../admin/dashboard.php'); exit(); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeEmail($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email && $password) {
        $admin = db()->fetchOne("SELECT * FROM admins WHERE email = ? AND status = 'active'", [$email]);
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_role'] = $admin['role'];
            db()->update('admins', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$admin['id']]);
            header('Location: ../admin/dashboard.php'); exit();
        } else {
            $error = 'Invalid credentials. Please try again.';
        }
    } else {
        $error = 'Email and password are required.';
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Login — RapidAid</title>
  <link rel="stylesheet" href="../assets/css/main.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#0a0f1e,#0f1729);padding:20px;">
  <div style="width:100%;max-width:420px;">
    <div style="text-align:center;margin-bottom:32px;">
      <div style="width:64px;height:64px;background:linear-gradient(135deg,var(--red),var(--teal));border-radius:18px;display:flex;align-items:center;justify-content:center;font-size:1.8rem;margin:0 auto 16px;">🔧</div>
      <div class="auth-logo" style="justify-content:center;font-size:1.6rem;">Rapid<span>Aid</span></div>
      <p style="color:rgba(255,255,255,0.5);font-size:0.875rem;margin-top:8px;">Administration Panel</p>
    </div>
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:20px;padding:36px;">
      <h2 style="font-size:1.3rem;color:var(--text);margin-bottom:6px;">Admin Sign In</h2>
      <p style="color:var(--text-muted);font-size:0.875rem;margin-bottom:24px;">Enter your admin credentials</p>

      <?php if ($error): ?>
      <div class="alert alert-danger"><?= clean($error) ?></div>
      <?php endif; ?>

      <form method="POST" style="display:flex;flex-direction:column;gap:16px;">
        <div class="form-group">
          <label class="form-label">Admin Email</label>
          <div class="input-group">
            <i class="fas fa-user-shield input-icon"></i>
            <input type="email" name="email" class="form-control with-icon" placeholder="admin@rapidaid.id" value="<?= clean($_POST['email'] ?? '') ?>" required />
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <div class="input-group">
            <i class="fas fa-key input-icon"></i>
            <input type="password" id="password" name="password" class="form-control with-icon" placeholder="Admin password" required />
            <button type="button" class="password-toggle" onclick="togglePassword('password',this)"><i class="fas fa-eye"></i></button>
          </div>
        </div>
        <button type="submit" class="btn btn-primary w-full" style="padding:14px;">Sign In to Admin Panel</button>
      </form>
      <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border);text-align:center;">
        <a href="../index.php" style="color:var(--text-muted);font-size:0.8rem;">← Back to Website</a>
      </div>
    </div>
    <p style="text-align:center;color:rgba(255,255,255,0.25);font-size:0.75rem;margin-top:16px;">
      Default: admin@rapidaid.id / password
    </p>
  </div>
</div>
<script src="../assets/js/main.js"></script>
<script>
function togglePassword(id, btn) {
  const input = document.getElementById(id);
  input.type = input.type === 'text' ? 'password' : 'text';
  btn.innerHTML = input.type === 'text' ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
}
</script>
</body>
</html>
