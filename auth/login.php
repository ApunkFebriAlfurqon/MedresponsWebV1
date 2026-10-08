<?php
require_once '../includes/helpers.php';
startSession();
if (isUserLoggedIn()) { header('Location: ../user/dashboard.php'); exit(); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeEmail($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email && $password) {
        $user = db()->fetchOne("SELECT * FROM users WHERE email = ? AND is_active = 1", [$email]);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            db()->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);
            $redirect = $_GET['redirect'] ?? '../user/dashboard.php';
            header('Location: ' . $redirect); exit();
        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    } else {
        $error = 'Please enter your email and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login — RapidAid</title>
  <link rel="stylesheet" href="../assets/css/main.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-left">
    <div style="max-width:420px;">
      <div style="font-size:3rem;margin-bottom:24px;">🚑</div>
      <h2 style="font-family:'Syne',sans-serif;font-size:2rem;color:white;margin-bottom:16px;">Welcome back to RapidAid</h2>
      <p style="color:rgba(255,255,255,0.55);line-height:1.8;margin-bottom:32px;">Access your dashboard, manage subscriptions, and request emergency assistance — all in one place.</p>
      <div style="display:flex;flex-direction:column;gap:12px;">
        <div style="display:flex;align-items:center;gap:12px;padding:14px 18px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:12px;">
          <span style="font-size:1.1rem;">⚡</span>
          <span style="color:rgba(255,255,255,0.7);font-size:0.875rem;">Request an ambulance in under 30 seconds</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px;padding:14px 18px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:12px;">
          <span style="font-size:1.1rem;">📍</span>
          <span style="color:rgba(255,255,255,0.7);font-size:0.875rem;">Track your ambulance in real-time</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px;padding:14px 18px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:12px;">
          <span style="font-size:1.1rem;">💎</span>
          <span style="color:rgba(255,255,255,0.7);font-size:0.875rem;">Manage your subscription and payment history</span>
        </div>
      </div>
    </div>
  </div>
  <div class="auth-right">
    <div class="auth-logo">Rapid<span>Aid</span></div>
    <h1 class="auth-title">Sign In</h1>
    <p class="auth-subtitle">Enter your credentials to access your account</p>

    <?php if ($error): ?>
    <div class="alert alert-danger"><?= clean($error) ?></div>
    <?php endif; ?>

    <form class="auth-form" method="POST">
      <div class="form-group">
        <label class="form-label">Email Address</label>
        <div class="input-group">
          <i class="fas fa-envelope input-icon"></i>
          <input type="email" name="email" class="form-control with-icon" placeholder="you@example.com" value="<?= clean($_POST['email'] ?? '') ?>" required />
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <div class="input-group">
          <i class="fas fa-lock input-icon"></i>
          <input type="password" name="password" id="password" class="form-control with-icon" placeholder="Your password" required />
          <button type="button" class="password-toggle" onclick="togglePassword('password', this)">
            <i class="fas fa-eye"></i>
          </button>
        </div>
      </div>
      <div style="display:flex;align-items:center;justify-content:space-between;">
        <label class="form-check">
          <input type="checkbox" name="remember" />
          <span>Remember me</span>
        </label>
        <a href="forgot-password.php" style="font-size:0.875rem;color:var(--red);font-weight:600;">Forgot password?</a>
      </div>
      <button type="submit" class="btn btn-primary w-full btn-lg">Sign In →</button>
    </form>

    <div class="auth-divider" style="margin-top:20px;">or continue as</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:16px;">
      <a href="../index.php" class="btn btn-ghost" style="border:1px solid var(--border);">👤 Guest</a>
      <a href="admin-login.php" class="btn btn-ghost" style="border:1px solid var(--border);">🔧 Admin</a>
    </div>
    <p class="auth-footer-text" style="margin-top:24px;">Don't have an account? <a href="register.php">Create one free</a></p>
  </div>
</div>
<script src="../assets/js/main.js"></script>
<script>
function togglePassword(id, btn) {
  const input = document.getElementById(id);
  const isText = input.type === 'text';
  input.type = isText ? 'password' : 'text';
  btn.innerHTML = isText ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
}
</script>
</body>
</html>
