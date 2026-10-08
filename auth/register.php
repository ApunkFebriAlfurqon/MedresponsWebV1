<?php
require_once '../includes/helpers.php';
startSession();
if (isUserLoggedIn()) { header('Location: ../user/dashboard.php'); exit(); }

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = clean($_POST['full_name'] ?? '');
    $email    = sanitizeEmail($_POST['email'] ?? '');
    $phone    = clean($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (!$name)   $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (!$phone)  $errors[] = 'Phone number is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';
    if (db()->count('users', 'email = ?', [$email])) $errors[] = 'This email is already registered.';

    if (empty($errors)) {
        $id = db()->insert('users', [
            'full_name' => $name,
            'email'     => $email,
            'phone'     => $phone,
            'password'  => password_hash($password, PASSWORD_DEFAULT),
        ]);
        createNotification($id, null, 'Welcome to RapidAid!', 'Your account has been created. Start by choosing a subscription plan.', 'success');
        $_SESSION['user_id']   = $id;
        $_SESSION['user_name'] = $name;
        header('Location: ../user/dashboard.php'); exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Register — RapidAid</title>
  <link rel="stylesheet" href="../assets/css/main.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-left">
    <div style="max-width:400px;text-align:center;">
      <div style="font-size:4rem;margin-bottom:20px;">🚑</div>
      <h2 style="font-family:'Syne',sans-serif;font-size:1.8rem;color:white;margin-bottom:12px;">Join RapidAid Today</h2>
      <p style="color:rgba(255,255,255,0.55);line-height:1.8;">Protect yourself and your family with Indonesia's fastest emergency ambulance service. Registration is free.</p>
      <div style="margin-top:32px;padding:24px;background:rgba(229,62,62,0.08);border:1px solid rgba(229,62,62,0.2);border-radius:16px;">
        <div style="font-family:'Syne',sans-serif;font-size:1.5rem;color:var(--red);font-weight:800;margin-bottom:4px;">FREE</div>
        <div style="color:rgba(255,255,255,0.6);font-size:0.875rem;">Account Registration</div>
        <div style="margin-top:12px;color:rgba(255,255,255,0.5);font-size:0.8rem;">Subscription plans start from Rp 25.000/month</div>
      </div>
    </div>
  </div>
  <div class="auth-right" style="max-width:520px;">
    <div class="auth-logo">Rapid<span>Aid</span></div>
    <h1 class="auth-title">Create Account</h1>
    <p class="auth-subtitle">Fill in your details to get started</p>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <?php foreach($errors as $e): ?><div>• <?= clean($e) ?></div><?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form class="auth-form" method="POST">
      <div class="form-group">
        <label class="form-label">Full Name</label>
        <div class="input-group">
          <i class="fas fa-user input-icon"></i>
          <input type="text" name="full_name" class="form-control with-icon" placeholder="Your full name" value="<?= clean($_POST['full_name'] ?? '') ?>" required />
        </div>
      </div>
      <div class="row cols-2">
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <div class="input-group">
            <i class="fas fa-envelope input-icon"></i>
            <input type="email" name="email" class="form-control with-icon" placeholder="you@example.com" value="<?= clean($_POST['email'] ?? '') ?>" required />
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Phone Number</label>
          <div class="input-group">
            <i class="fas fa-phone input-icon"></i>
            <input type="tel" name="phone" class="form-control with-icon" placeholder="08xxxxxxxxxx" value="<?= clean($_POST['phone'] ?? '') ?>" required />
          </div>
        </div>
      </div>
      <div class="row cols-2">
        <div class="form-group">
          <label class="form-label">Password</label>
          <div class="input-group">
            <i class="fas fa-lock input-icon"></i>
            <input type="password" id="password" name="password" class="form-control with-icon" placeholder="Min. 6 characters" required />
            <button type="button" class="password-toggle" onclick="togglePassword('password',this)"><i class="fas fa-eye"></i></button>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Confirm Password</label>
          <div class="input-group">
            <i class="fas fa-lock input-icon"></i>
            <input type="password" id="confirm" name="confirm_password" class="form-control with-icon" placeholder="Repeat password" required />
            <button type="button" class="password-toggle" onclick="togglePassword('confirm',this)"><i class="fas fa-eye"></i></button>
          </div>
        </div>
      </div>
      <label class="form-check">
        <input type="checkbox" required />
        <span style="font-size:0.8rem;">I agree to the <a href="#" style="color:var(--red);">Terms of Service</a> and <a href="#" style="color:var(--red);">Privacy Policy</a></span>
      </label>
      <button type="submit" class="btn btn-primary w-full btn-lg">Create Account →</button>
    </form>
    <p class="auth-footer-text" style="margin-top:20px;">Already have an account? <a href="login.php">Sign in</a></p>
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
