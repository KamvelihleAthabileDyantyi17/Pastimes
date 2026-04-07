<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$pageTitle = 'Create Account';
include __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
  <div class="auth-bg"></div>
  <div class="auth-card">
    <div class="auth-logo">Pastimes</div>
    <div class="auth-tagline">South Africa's Thrift Marketplace</div>

    <h1 class="auth-title">Create an account</h1>
    <p class="auth-subtitle">Join the community and start trading today.</p>

    <form id="register-form" action="<?= BASE_URL ?>/process/register_process.php" method="POST" class="form-stack" novalidate>
      <div class="form-group">
        <label class="form-label" for="username">Username</label>
        <input type="text" id="username" name="username" class="form-control"
               placeholder="e.g. thriftqueen" maxlength="50" autocomplete="username"
               value="<?= isset($_GET['username']) ? sanitize($_GET['username']) : '' ?>">
      </div>

      <div class="form-group">
        <label class="form-label" for="email">Email Address</label>
        <input type="email" id="email" name="email" class="form-control"
               placeholder="you@example.com" maxlength="100" autocomplete="email"
               value="<?= isset($_GET['email']) ? sanitize($_GET['email']) : '' ?>">
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <input type="password" id="password" name="password" class="form-control"
               placeholder="At least 8 characters" autocomplete="new-password">
      </div>

      <div class="form-group">
        <label class="form-label" for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" class="form-control"
               placeholder="Repeat your password" autocomplete="new-password">
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%; margin-top:0.5rem;">
        Create Account →
      </button>
    </form>

    <div class="auth-divider"><span>Already have an account?</span></div>
    <a href="<?= BASE_URL ?>/login.php" class="btn btn-outline" style="width:100%;">Log In</a>
  </div>
</div>

<script src="<?= BASE_URL ?>/js/validation.js"></script>
<?php include __DIR__ . '/includes/footer.php'; ?>
