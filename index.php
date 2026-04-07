<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$db = getDB();

// Featured listings
$stmt = $db->query("
    SELECT p.*, c.category_name, u.username
    FROM Products p
    JOIN Categories c ON p.category_id = c.category_id
    JOIN Users u ON p.user_id = u.user_id
    WHERE p.status = 'Available'
    ORDER BY p.date_listed DESC
    LIMIT 8
");
$featured = $stmt->fetchAll();

// Stats
$totalListings = $db->query("SELECT COUNT(*) FROM Products WHERE status = 'Available'")->fetchColumn();
$totalUsers    = $db->query("SELECT COUNT(*) FROM Users WHERE role = 'user'")->fetchColumn();
$totalTrades   = $db->query("SELECT COUNT(*) FROM Trades WHERE trade_status = 'Accepted'")->fetchColumn();

$pageTitle = 'Home';
include __DIR__ . '/includes/header.php';
?>

<!-- HERO -->
<section class="hero">
  <div class="hero-bg"></div>
  <div class="container">
    <div class="hero-content">
      <div class="hero-eyebrow">✨ South Africa's Thrift Marketplace</div>
      <h1 class="hero-title">
        Give clothes<br>
        a <span class="gradient-text">second life.</span>
      </h1>
      <p class="hero-subtitle">
        Buy, sell, and trade pre-loved fashion with people in your community.
        Sustainable shopping that's easy, affordable, and stylish.
      </p>
      <div class="hero-actions">
        <a href="<?= BASE_URL ?>/catalog.php" class="btn btn-primary btn-lg">Browse Catalog</a>
        <?php if (!isLoggedIn()): ?>
          <a href="<?= BASE_URL ?>/register.php" class="btn btn-outline btn-lg">Join for Free</a>
        <?php else: ?>
          <a href="<?= BASE_URL ?>/add_listing.php" class="btn btn-gold btn-lg">+ List an Item</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- STATS -->
<div class="container">
  <div class="stats-bar">
    <div class="stat-item text-center">
      <div class="stat-number"><?= number_format($totalListings) ?>+</div>
      <div class="stat-label">Active Listings</div>
    </div>
    <div class="stat-divider"></div>
    <div class="stat-item text-center">
      <div class="stat-number"><?= number_format($totalUsers) ?>+</div>
      <div class="stat-label">Registered Sellers</div>
    </div>
    <div class="stat-divider"></div>
    <div class="stat-item text-center">
      <div class="stat-number"><?= number_format($totalTrades) ?>+</div>
      <div class="stat-label">Successful Trades</div>
    </div>
  </div>

  <!-- CATEGORIES -->
  <div class="section-header">
    <h2 class="section-title">Shop by Category</h2>
    <p class="section-subtitle">Find exactly what you're looking for</p>
  </div>
  <div class="category-chips mb-xl">
    <?php
    $cats = $db->query("SELECT * FROM Categories")->fetchAll();
    $catIcons = ['Vintage'=>'🕰️','Shoes'=>'👟','Clothing'=>'👕','Electronics'=>'📱','Accessories'=>'💍','Other'=>'📦'];
    foreach ($cats as $cat):
      $icon = $catIcons[$cat['category_name']] ?? '🏷️';
    ?>
    <a href="<?= BASE_URL ?>/catalog.php?category=<?= $cat['category_id'] ?>" class="category-chip">
      <?= $icon ?> <?= sanitize($cat['category_name']) ?>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- FEATURED LISTINGS -->
  <div class="section-header flex-between">
    <div>
      <h2 class="section-title">Latest Listings</h2>
      <p class="section-subtitle">Fresh arrivals from the community</p>
    </div>
    <a href="<?= BASE_URL ?>/catalog.php" class="btn btn-outline btn-sm">View All →</a>
  </div>

  <?php if (empty($featured)): ?>
    <div class="empty-state">
      <div class="empty-state-icon">👗</div>
      <h3 class="empty-state-title">No listings yet</h3>
      <p>Be the first to list something!</p>
      <?php if (isLoggedIn()): ?>
        <a href="<?= BASE_URL ?>/add_listing.php" class="btn btn-primary">+ Add Listing</a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary">Join & List</a>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <div class="products-grid mb-xl">
      <?php foreach ($featured as $p): ?>
        <a href="<?= BASE_URL ?>/item.php?id=<?= $p['product_id'] ?>" class="product-card">
          <?php if ($p['image_path'] && file_exists($_SERVER['DOCUMENT_ROOT'] . $p['image_path'])): ?>
            <img src="<?= sanitize($p['image_path']) ?>" alt="<?= sanitize($p['title']) ?>" class="product-card-img">
          <?php else: ?>
            <?= productImageTag('', $p['title']) ?>
          <?php endif; ?>
          <div class="product-card-body">
            <div class="product-card-title"><?= sanitize($p['title']) ?></div>
            <div class="product-card-seller">by @<?= sanitize($p['username']) ?></div>
            <div class="product-card-footer">
              <span class="product-card-price"><?= formatPrice($p['price']) ?></span>
              <span class="badge <?= conditionBadgeClass($p['condition_label']) ?>"><?= sanitize($p['condition_label']) ?></span>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- HOW IT WORKS -->
  <div class="section-header mt-xl">
    <h2 class="section-title">How It Works</h2>
    <p class="section-subtitle">Simple steps to buy, sell, or trade</p>
  </div>
  <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 3rem;">
    <?php
    $steps = [
      ['🔍', 'Browse', 'Explore thousands of pre-loved items across all categories.'],
      ['📸', 'List', 'Upload your items in minutes with photos and descriptions.'],
      ['🤝', 'Trade', 'Offer items from your wardrobe as trade-ins for items you want.'],
      ['🛒', 'Buy', 'Add to cart and checkout securely anytime.'],
    ];
    foreach ($steps as $s): ?>
      <div class="card no-hover" style="padding: 1.75rem; text-align: center;">
        <div style="font-size: 2.5rem; margin-bottom: 1rem;"><?= $s[0] ?></div>
        <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem;"><?= $s[1] ?></h3>
        <p style="color: var(--text-muted); font-size: 0.875rem; line-height: 1.6;"><?= $s[2] ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
