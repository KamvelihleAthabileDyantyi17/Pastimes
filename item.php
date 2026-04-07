<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$db = getDB();

$productId = (int)($_GET['id'] ?? 0);
if (!$productId) {
    header('Location: ' . BASE_URL . '/catalog.php');
    exit;
}

$stmt = $db->prepare("
    SELECT p.*, c.category_name, u.username, u.user_id AS seller_id
    FROM Products p
    JOIN Categories c ON p.category_id = c.category_id
    JOIN Users u ON p.user_id = u.user_id
    WHERE p.product_id = ?
");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    setFlash('error', 'Item not found.');
    header('Location: ' . BASE_URL . '/catalog.php');
    exit;
}

$currentUser = currentUser();
$isOwner     = $currentUser && $currentUser['user_id'] == $product['seller_id'];
$isLoggedIn  = isLoggedIn();

// Fetch user's own available items for trade modal
$myItems = [];
if ($isLoggedIn && !$isOwner && $product['status'] === 'Available') {
    $s = $db->prepare("
        SELECT * FROM Products
        WHERE user_id = ? AND status = 'Available' AND product_id != ?
    ");
    $s->execute([$currentUser['user_id'], $productId]);
    $myItems = $s->fetchAll();
}

// Check if already in cart
$inCart = false;
if ($isLoggedIn) {
    $s = $db->prepare("SELECT cart_id FROM Cart WHERE user_id = ? AND product_id = ?");
    $s->execute([$currentUser['user_id'], $productId]);
    $inCart = (bool)$s->fetch();
}

$pageTitle = sanitize($product['title']);
include __DIR__ . '/includes/header.php';
?>

<div class="page-content">
  <div class="container">
    <!-- Breadcrumb -->
    <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.85rem;color:var(--text-muted);margin-bottom:2rem;">
      <a href="<?= BASE_URL ?>/index.php">Home</a> <span>/</span>
      <a href="<?= BASE_URL ?>/catalog.php">Catalog</a> <span>/</span>
      <span style="color:var(--text-secondary);"><?= sanitize($product['title']) ?></span>
    </div>

    <div class="item-layout">
      <!-- Image -->
      <div class="item-img-wrap">
        <?php if ($product['image_path'] && file_exists($_SERVER['DOCUMENT_ROOT'] . $product['image_path'])): ?>
          <img src="<?= sanitize($product['image_path']) ?>" alt="<?= sanitize($product['title']) ?>">
        <?php else: ?>
          <div class="item-img-placeholder"><?= productImageTag('', $product['title']) === '<div class="product-card-image-placeholder">👕</div>' ? '👕' : '🏷️' ?></div>
        <?php endif; ?>
      </div>

      <!-- Info -->
      <div class="item-info">
        <div>
          <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.75rem;flex-wrap:wrap;">
            <span class="badge badge-purple"><?= sanitize($product['category_name']) ?></span>
            <span class="badge <?= conditionBadgeClass($product['condition_label']) ?>"><?= sanitize($product['condition_label']) ?></span>
            <span class="badge <?= statusBadgeClass($product['status']) ?>"><?= sanitize($product['status']) ?></span>
          </div>
          <h1 class="item-title"><?= sanitize($product['title']) ?></h1>
          <div class="item-price"><?= formatPrice($product['price']) ?></div>
        </div>

        <div class="item-meta-grid">
          <div class="item-meta-box">
            <div class="item-meta-label">Seller</div>
            <div class="item-meta-value">@<?= sanitize($product['username']) ?></div>
          </div>
          <div class="item-meta-box">
            <div class="item-meta-label">Listed</div>
            <div class="item-meta-value"><?= timeAgo($product['date_listed']) ?></div>
          </div>
          <div class="item-meta-box">
            <div class="item-meta-label">Condition</div>
            <div class="item-meta-value"><?= sanitize($product['condition_label']) ?></div>
          </div>
          <div class="item-meta-box">
            <div class="item-meta-label">Category</div>
            <div class="item-meta-value"><?= sanitize($product['category_name']) ?></div>
          </div>
        </div>

        <div class="item-description-box">
          <h3>Description</h3>
          <p><?= nl2br(sanitize($product['description'])) ?></p>
        </div>

        <!-- Actions -->
        <?php if ($product['status'] === 'Available'): ?>
          <div class="item-actions">
            <?php if (!$isLoggedIn): ?>
              <a href="<?= BASE_URL ?>/login.php" class="btn btn-gold btn-lg" style="text-align:center;">🔐 Log In to Buy or Trade</a>
            <?php elseif ($isOwner): ?>
              <div class="card no-hover" style="padding:1rem;text-align:center;color:var(--text-muted);font-size:0.875rem;">
                This is your listing. <a href="<?= BASE_URL ?>/dashboard.php">Manage it in your Dashboard.</a>
              </div>
            <?php else: ?>
              <?php if (!$inCart): ?>
                <form action="<?= BASE_URL ?>/process/cart_process.php" method="POST">
                  <input type="hidden" name="action" value="add">
                  <input type="hidden" name="product_id" value="<?= $productId ?>">
                  <button type="submit" class="btn btn-gold btn-lg" style="width:100%;">🛒 Add to Cart</button>
                </form>
              <?php else: ?>
                <a href="<?= BASE_URL ?>/cart.php" class="btn btn-outline btn-lg" style="width:100%;">✅ In Cart – View Cart</a>
              <?php endif; ?>

              <?php if (!empty($myItems)): ?>
                <button type="button" id="propose-trade-btn" class="btn btn-primary btn-lg" style="width:100%;">🔄 Propose Trade</button>
              <?php elseif (empty($myItems)): ?>
                <div style="font-size:0.82rem;color:var(--text-muted);text-align:center;padding:0.5rem;">
                  <a href="<?= BASE_URL ?>/add_listing.php">List an item</a> to propose trades.
                </div>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <div class="card no-hover" style="padding:1.25rem;text-align:center;">
            <span class="badge <?= statusBadgeClass($product['status']) ?>" style="font-size:0.875rem;padding:0.5rem 1rem;">
              This item has been <?= strtolower(sanitize($product['status'])) ?>
            </span>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php if ($isLoggedIn && !$isOwner && !empty($myItems) && $product['status'] === 'Available'): ?>
<!-- TRADE MODAL -->
<div class="modal-overlay" id="trade-modal-overlay">
  <div class="modal">
    <div class="modal-header">
      <h2 class="modal-title">🔄 Propose a Trade</h2>
      <button class="modal-close" id="trade-modal-close" type="button">✕</button>
    </div>
    <p style="color:var(--text-secondary);font-size:0.875rem;margin-bottom:0.5rem;">
      You want: <strong style="color:var(--text-primary)"><?= sanitize($product['title']) ?></strong>
    </p>
    <p style="color:var(--text-secondary);font-size:0.85rem;margin-bottom:0.5rem;">Select one of your items to offer in exchange:</p>

    <form id="trade-form" action="<?= BASE_URL ?>/process/trade_process.php" method="POST">
      <input type="hidden" name="action" value="propose">
      <input type="hidden" name="requested_product_id" value="<?= $productId ?>">
      <input type="hidden" name="receiver_user_id" value="<?= $product['seller_id'] ?>">

      <div class="trade-items-grid" id="trade-items-grid">
        <?php foreach ($myItems as $mi): ?>
          <div class="trade-item-opt">
            <input type="radio" name="offered_product_id" id="ti_<?= $mi['product_id'] ?>" value="<?= $mi['product_id'] ?>">
            <label class="trade-item-label" for="ti_<?= $mi['product_id'] ?>">
              <?php if ($mi['image_path'] && file_exists($_SERVER['DOCUMENT_ROOT'] . $mi['image_path'])): ?>
                <img src="<?= sanitize($mi['image_path']) ?>" alt="<?= sanitize($mi['title']) ?>">
              <?php else: ?>
                <div class="t-img-placeholder">👕</div>
              <?php endif; ?>
              <div class="trade-item-info">
                <p><?= sanitize($mi['title']) ?></p>
                <strong><?= formatPrice($mi['price']) ?></strong>
              </div>
            </label>
          </div>
        <?php endforeach; ?>
      </div>

      <button type="submit" id="trade-submit-btn" class="btn btn-primary" style="width:100%;" disabled>
        Send Trade Offer →
      </button>
    </form>
  </div>
</div>
<script src="<?= BASE_URL ?>/js/trade-modal.js"></script>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
