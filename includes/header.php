<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$user   = currentUser();
$flash  = getFlash();
$notifs = unreadNotifCount();

// Active page for nav highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Pastimes – South Africa's premium second-hand clothing marketplace. Buy, sell, and trade pre-loved fashion.">
  <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' | Pastimes' : 'Pastimes – Pre-Loved Fashion Marketplace' ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>👗</text></svg>">
</head>
<body>

<nav class="navbar" id="navbar">
  <div class="navbar-inner">
    <a href="<?= BASE_URL ?>/index.php" class="navbar-brand">Pastimes</a>

    <div class="navbar-nav">
      <a href="<?= BASE_URL ?>/index.php"    class="<?= $currentPage === 'index.php'   ? 'active' : '' ?>">Home</a>
      <a href="<?= BASE_URL ?>/catalog.php"  class="<?= $currentPage === 'catalog.php' ? 'active' : '' ?>">Catalog</a>
      <?php if ($user): ?>
        <a href="<?= BASE_URL ?>/add_listing.php" class="<?= $currentPage === 'add_listing.php' ? 'active' : '' ?>">+ List Item</a>
        <a href="<?= BASE_URL ?>/dashboard.php"   class="<?= $currentPage === 'dashboard.php'   ? 'active' : '' ?>">Dashboard</a>
        <?php if ($user['role'] === 'admin'): ?>
          <a href="<?= BASE_URL ?>/admin.php" class="<?= $currentPage === 'admin.php' ? 'active' : '' ?>">Admin</a>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <div class="navbar-actions">
      <?php if ($user): ?>
        <a href="<?= BASE_URL ?>/dashboard.php?tab=notifications" class="notif-btn" title="Notifications">
          🔔
          <?php if ($notifs > 0): ?>
            <span class="notif-badge"><?= $notifs > 9 ? '9+' : $notifs ?></span>
          <?php endif; ?>
        </a>
        <a href="<?= BASE_URL ?>/cart.php" class="btn btn-outline btn-sm">🛒 Cart</a>
        <a href="<?= BASE_URL ?>/logout.php" class="btn btn-outline btn-sm">Log Out</a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/login.php"    class="btn btn-outline btn-sm">Log In</a>
        <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary btn-sm">Sign Up</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<main>
<?php if ($flash): ?>
<div class="container" style="padding-top: calc(var(--navbar-h) + 1rem);">
  <div class="flash flash-<?= sanitize($flash['type']) ?>">
    <?= $flash['type'] === 'success' ? '✅' : ($flash['type'] === 'error' ? '❌' : 'ℹ️') ?>
    <?= sanitize($flash['message']) ?>
  </div>
</div>
<?php endif; ?>
