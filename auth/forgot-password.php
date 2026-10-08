<?php
require_once '../includes/helpers.php';
startSession();
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeEmail($_POST['email'] ?? '');
    $user = db()->fetchOne("SELECT * FROM users WHERE email=?", [$email]);
    if ($user) {
        $token = bin2hex(random_bytes(32));
        db()->update('users', ['reset_token'=>$token, 'reset_token_expiry'=>date('Y-m-d H:i:s', strtotime('+1 hour'))], 'id=?', [$user['id']]);
        $success = "Password reset link sent to $email. (Demo: token = $token)";
    } else {
        $error = 'No account found with that email address.';
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Forgot Password — RapidAid</title>
<link rel="stylesheet" href="../assets/css/main.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/></head>
<body>
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#0a0f1e,#0f1729);padding:20px;">
  <div style="width:100%;max-width:420px;">
    <div style="text-align:center;margin-bottom:28px;">
      <div style="font-family:'Syne',sans-serif;font-size:1.6rem;color:white;font-weight:800;">Rapid<span style="color:var(--red);">Aid</span></div>
    </div>
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:20px;padding:36px;">
      <h2 style="font-size:1.3rem;color:var(--text);margin-bottom:8px;">Forgot Password</h2>
      <p style="color:var(--text-muted);font-size:0.875rem;margin-bottom:24px;">Enter your email and we'll send you a reset link.</p>
      <?php if($success): ?><div class="alert alert-success"><?=$success?></div><?php endif; ?>
      <?php if($error): ?><div class="alert alert-danger"><?=$error?></div><?php endif; ?>
      <form method="POST" style="display:flex;flex-direction:column;gap:16px;">
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <div class="input-group"><i class="fas fa-envelope input-icon"></i>
          <input type="email" name="email" class="form-control with-icon" placeholder="you@example.com" required/></div>
        </div>
        <button type="submit" class="btn btn-primary w-full">Send Reset Link</button>
      </form>
      <div style="margin-top:20px;text-align:center;"><a href="login.php" style="color:var(--red);font-size:0.875rem;">← Back to Login</a></div>
    </div>
  </div>
</div>
<script src="../assets/js/main.js"></script>
</body></html>
