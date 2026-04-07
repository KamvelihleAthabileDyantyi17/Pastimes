<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$db  = getDB();
$uid = currentUser()['user_id'];

$stmt = $db->prepare("
    SELECT c.cart_id, c.date_added,
           p.product_id, p.title, p.price, p.image_path, p.status, p.user_id AS seller_id
    FROM Cart c
    JOIN Products p ON c.product_id = p.product_id
    WHERE c.user_id = ?
    ORDER BY c.date_added DESC
");
$stmt->execute([$uid]);
$cartItems = $stmt->fetchAll();

$availableItems = array_filter($cartItems, fn($i) => $i['status'] === 'Available');
$total = array_sum(array_column($availableItems, 'price'));

$pageTitle = 'Cart';
include __DIR__ . '/includes/header.php';
?>

<div class="page-content">
  <div class="container">
    <div class="page-header">
      <div class="page-eyebrow">Shopping</div>
      <h1 class="page-title">Your Cart</h1>
      <p class="page-subtitle"><?= count($cartItems) ?> item<?= count($cartItems) !== 1 ? 's' : '' ?> in your cart</p>
    </div>

    <?php if (empty($cartItems)): ?>
      <div class="empty-state">
        <div class="empty-state-icon">🛒</div>
        <h3 class="empty-state-title">Your cart is empty</h3>
        <p>Browse the catalog to find something you love.</p>
        <a href="<?= BASE_URL ?>/catalog.php" class="btn btn-primary">Browse Catalog</a>
      </div>
    <?php else: ?>
      <div class="cart-layout">
        <!-- Cart Items -->
        <div>
          <?php foreach ($cartItems as $item): ?>
            <div class="cart-item">
              <div class="cart-item-img">
                <?php if ($item['image_path'] && file_exists($_SERVER['DOCUMENT_ROOT'] . $item['image_path'])): ?>
                  <img src="<?= sanitize($item['image_path']) ?>" alt="<?= sanitize($item['title']) ?>">
                <?php else: ?>
                  👕
                <?php endif; ?>
              </div>
              <div class="cart-item-info" style="flex:1;">
                <div class="cart-item-title">
                  <a href="<?= BASE_URL ?>/item.php?id=<?= $item['product_id'] ?>" style="color:var(--text-primary);">
                    <?= sanitize($item['title']) ?>
                  </a>
                </div>
                <div class="cart-item-price"><?= formatPrice($item['price']) ?></div>
                <?php if ($item['status'] !== 'Available'): ?>
                  <span class="badge badge-red" style="margin-top:0.25rem;">⚠️ No longer available</span>
                <?php endif; ?>
                <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.25rem;">
                  Added <?= timeAgo($item['date_added']) ?>
                </div>
              </div>
              <form action="<?= BASE_URL ?>/process/cart_process.php" method="POST">
                <input type="hidden" name="action" value="remove">
                <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                <button type="submit" class="btn btn-outline btn-sm" title="Remove from cart">✕ Remove</button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Order Summary -->
        <div class="order-summary">
          <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:1.25rem;">Order Summary</h3>

          <?php if (count($cartItems) !== count($availableItems)): ?>
            <div class="flash flash-warning" style="font-size:0.82rem;">
              ⚠️ <?= count($cartItems) - count($availableItems) ?> item(s) are no longer available and will be excluded from checkout.
            </div>
          <?php endif; ?>

          <div class="order-row">
            <span>Available Items</span>
            <span><?= count($availableItems) ?></span>
          </div>
          <div class="order-row">
            <span>Subtotal</span>
            <span><?= formatPrice($total) ?></span>
          </div>
          <div class="order-row">
            <span>Delivery</span>
            <span style="color:var(--success);">Arrange with seller</span>
          </div>
          <div class="order-row" style="margin-top:0.75rem;padding-top:0.75rem;border-top:1px solid var(--card-border);">
            <span class="order-total">Total</span>
            <span class="order-total" style="color:var(--gold);"><?= formatPrice($total) ?></span>
          </div>

          <?php if (!empty($availableItems)): ?>
            <form action="<?= BASE_URL ?>/process/checkout_process.php" method="POST" style="margin-top:1.25rem;"
                  onsubmit="return confirm('Confirm mock checkout for <?= formatPrice($total) ?>?')">
              <button type="submit" class="btn btn-gold" style="width:100%;font-size:1rem;padding:0.875rem;">
                ✅ Checkout — <?= formatPrice($total) ?>
              </button>
            </form>
            <p style="font-size:0.75rem;color:var(--text-muted);text-align:center;margin-top:0.75rem;">
              This is a mock checkout. No real payment is processed.
            </p>
          <?php else: ?>
            <div class="flash flash-error" style="margin-top:1rem;font-size:0.85rem;">
              No available items to checkout.
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
