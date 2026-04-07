<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$pageTitle = 'Log In';
include __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
  <div class="auth-bg"></div>
  <div class="auth-card">
    <div class="auth-logo">Pastimes</div>
    <div class="auth-tagline">Welcome back 👋</div>

    <h1 class="auth-title">Log in to your account</h1>
    <p class="auth-subtitle">Enter your credentials to continue.</p>

    <form id="login-form" action="<?= BASE_URL ?>/process/login_process.php" method="POST" class="form-stack" novalidate>
      <div class="form-group">
        <label class="form-label" for="email">Email Address</label>
        <input type="email" id="email" name="email" class="form-control"
               placeholder="you@example.com" autocomplete="email">
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <input type="password" id="password" name="password" class="form-control"
               placeholder="Your password" autocomplete="current-password">
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%; margin-top:0.5rem;">
        Log In →
      </button>
    </form>

    <div class="auth-divider"><span>Don't have an account?</span></div>
    <a href="<?= BASE_URL ?>/register.php" class="btn btn-outline" style="width:100%;">Create an Account</a>

    <p class="text-center mt-md" style="font-size:0.8rem; color: var(--text-muted);">
      Demo admin: <strong style="color:var(--accent-light)">admin@pastimes.co.za</strong> / <strong style="color:var(--accent-light)">Admin@123</strong>
    </p>
  </div>
</div>

<script src="<?= BASE_URL ?>/js/validation.js"></script>
<?php include __DIR__ . '/includes/footer.php'; ?>
