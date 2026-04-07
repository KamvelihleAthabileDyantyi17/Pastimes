<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$db   = getDB();
$user = currentUser();
$uid  = $user['user_id'];

$activeTab = $_GET['tab'] ?? 'listings';

// My listings
$myListings = $db->prepare("
    SELECT p.*, c.category_name FROM Products p
    JOIN Categories c ON p.category_id = c.category_id
    WHERE p.user_id = ? ORDER BY p.date_listed DESC
");
$myListings->execute([$uid]);
$myListings = $myListings->fetchAll();

// Incoming trade requests (I'm the receiver)
$incomingTrades = $db->prepare("
    SELECT t.*,
           op.title AS offered_title, op.price AS offered_price, op.image_path AS offered_img,
           rp.title AS requested_title,
           iu.username AS initiator_name, iu.user_id AS initiator_id
    FROM Trades t
    JOIN Products op ON t.offered_product_id   = op.product_id
    JOIN Products rp ON t.requested_product_id = rp.product_id
    JOIN Users iu    ON t.initiator_user_id    = iu.user_id
    WHERE t.receiver_user_id = ? AND t.trade_status = 'Pending'
    ORDER BY t.date_proposed DESC
");
$incomingTrades->execute([$uid]);
$incomingTrades = $incomingTrades->fetchAll();

// Outgoing trade requests (I'm the initiator)
$outgoingTrades = $db->prepare("
    SELECT t.*,
           op.title AS offered_title,
           rp.title AS requested_title,
           ru.username AS receiver_name
    FROM Trades t
    JOIN Products op ON t.offered_product_id   = op.product_id
    JOIN Products rp ON t.requested_product_id = rp.product_id
    JOIN Users ru    ON t.receiver_user_id     = ru.user_id
    WHERE t.initiator_user_id = ?
    ORDER BY t.date_proposed DESC
");
$outgoingTrades->execute([$uid]);
$outgoingTrades = $outgoingTrades->fetchAll();

// Notifications
$notifStmt = $db->prepare("SELECT * FROM Notifications WHERE user_id = ? ORDER BY date_created DESC LIMIT 20");
$notifStmt->execute([$uid]);
$notifications = $notifStmt->fetchAll();

// Mark all as read when tab is notifications
if ($activeTab === 'notifications') {
    $db->prepare("UPDATE Notifications SET is_read = 1 WHERE user_id = ?")->execute([$uid]);
}

// Cart count
$cartCount = $db->prepare("SELECT COUNT(*) FROM Cart WHERE user_id = ?");
$cartCount->execute([$uid]);
$cartCount = (int)$cartCount->fetchColumn();

$pageTitle = 'Dashboard';
include __DIR__ . '/includes/header.php';
?>

<div class="page-content">
  <div class="container">
    <div class="dashboard-layout">

      <!-- SIDEBAR -->
      <aside class="dashboard-sidebar">
        <div class="card no-hover" style="padding:1.5rem;text-align:center;margin-bottom:1rem;">
          <div class="user-avatar" style="margin:0 auto 0.75rem;">
            <?= strtoupper(substr($user['username'], 0, 1)) ?>
          </div>
          <div style="font-weight:700;font-size:1rem;">@<?= sanitize($user['username']) ?></div>
          <div style="font-size:0.8rem;color:var(--text-muted);margin-top:0.25rem;"><?= sanitize($user['email']) ?></div>
          <?php if ($user['role'] === 'admin'): ?>
            <span class="badge badge-gold" style="margin-top:0.5rem;">Admin</span>
          <?php endif; ?>
        </div>

        <div class="card no-hover" style="padding:1rem;">
          <nav class="dashboard-nav">
            <button class="dash-nav-btn <?= $activeTab === 'listings'       ? 'active' : '' ?>" onclick="switchTab('listings')">
              📦 My Listings <span style="margin-left:auto;background:rgba(255,255,255,0.08);border-radius:99px;padding:0 6px;font-size:0.75rem;"><?= count($myListings) ?></span>
            </button>
            <button class="dash-nav-btn <?= $activeTab === 'trades'         ? 'active' : '' ?>" onclick="switchTab('trades')">
              🔄 Trade Requests
              <?php if (count($incomingTrades) > 0): ?>
                <span style="margin-left:auto;background:var(--accent);border-radius:99px;padding:0 6px;font-size:0.75rem;color:#fff;"><?= count($incomingTrades) ?></span>
              <?php endif; ?>
            </button>
            <button class="dash-nav-btn <?= $activeTab === 'cart'           ? 'active' : '' ?>" onclick="switchTab('cart')">
              🛒 Cart Summary
              <?php if ($cartCount > 0): ?>
                <span style="margin-left:auto;background:var(--gold);color:#0a0e1a;border-radius:99px;padding:0 6px;font-size:0.75rem;"><?= $cartCount ?></span>
              <?php endif; ?>
            </button>
            <button class="dash-nav-btn <?= $activeTab === 'notifications'  ? 'active' : '' ?>" onclick="switchTab('notifications')">
              🔔 Notifications
            </button>
            <button class="dash-nav-btn <?= $activeTab === 'profile'        ? 'active' : '' ?>" onclick="switchTab('profile')">
              👤 Profile
            </button>
          </nav>
        </div>
      </aside>

      <!-- MAIN CONTENT -->
      <div>

        <!-- ── LISTINGS TAB ── -->
        <div id="tab-listings" class="tab-panel <?= $activeTab === 'listings' ? 'active' : '' ?>">
          <div class="flex-between mb-lg">
            <h2 style="font-size:1.4rem;font-weight:700;">My Listings</h2>
            <a href="<?= BASE_URL ?>/add_listing.php" class="btn btn-primary btn-sm">+ Add Item</a>
          </div>
          <?php if (empty($myListings)): ?>
            <div class="empty-state">
              <div class="empty-state-icon">📦</div>
              <h3 class="empty-state-title">No listings yet</h3>
              <p>Start selling or trading by adding your first item.</p>
              <a href="<?= BASE_URL ?>/add_listing.php" class="btn btn-primary">+ Add Item</a>
            </div>
          <?php else: ?>
            <div class="table-wrap">
              <table>
                <thead><tr>
                  <th>Item</th><th>Category</th><th>Price</th><th>Condition</th><th>Status</th><th>Listed</th><th>Actions</th>
                </tr></thead>
                <tbody>
                <?php foreach ($myListings as $item): ?>
                  <tr>
                    <td>
                      <a href="<?= BASE_URL ?>/item.php?id=<?= $item['product_id'] ?>" style="color:var(--text-primary);font-weight:500;">
                        <?= sanitize($item['title']) ?>
                      </a>
                    </td>
                    <td><?= sanitize($item['category_name']) ?></td>
                    <td style="color:var(--gold);font-weight:600;"><?= formatPrice($item['price']) ?></td>
                    <td><span class="badge <?= conditionBadgeClass($item['condition_label']) ?>"><?= sanitize($item['condition_label']) ?></span></td>
                    <td><span class="badge <?= statusBadgeClass($item['status']) ?>"><?= sanitize($item['status']) ?></span></td>
                    <td><?= timeAgo($item['date_listed']) ?></td>
                    <td>
                      <?php if ($item['status'] === 'Available'): ?>
                        <form action="<?= BASE_URL ?>/process/add_listing_process.php" method="POST" style="display:inline;" onsubmit="return confirm('Delete this listing?')">
                          <input type="hidden" name="action" value="delete">
                          <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                          <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>

        <!-- ── TRADES TAB ── -->
        <div id="tab-trades" class="tab-panel <?= $activeTab === 'trades' ? 'active' : '' ?>">
          <h2 style="font-size:1.4rem;font-weight:700;margin-bottom:1.5rem;">Trade Requests</h2>

          <h3 style="font-size:1rem;font-weight:600;margin-bottom:1rem;color:var(--text-secondary);">📥 Incoming Offers</h3>
          <?php if (empty($incomingTrades)): ?>
            <div class="card no-hover" style="padding:1.5rem;text-align:center;color:var(--text-muted);font-size:0.9rem;margin-bottom:1.5rem;">No pending trade offers.</div>
          <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:0.875rem;margin-bottom:2rem;">
              <?php foreach ($incomingTrades as $t): ?>
                <div class="card no-hover" style="padding:1.25rem;">
                  <div class="flex-between" style="flex-wrap:wrap;gap:1rem;">
                    <div>
                      <p style="font-size:0.85rem;color:var(--text-muted);">
                        <strong style="color:var(--accent-light)">@<?= sanitize($t['initiator_name']) ?></strong>
                        wants to trade for your <strong style="color:var(--text-primary)"><?= sanitize($t['requested_title']) ?></strong>
                      </p>
                      <p style="font-size:0.82rem;color:var(--text-muted);margin-top:0.25rem;">
                        Offering: <strong style="color:var(--gold)"><?= sanitize($t['offered_title']) ?> (<?= formatPrice($t['offered_price']) ?>)</strong>
                        · <?= timeAgo($t['date_proposed']) ?>
                      </p>
                    </div>
                    <div style="display:flex;gap:0.5rem;">
                      <form action="<?= BASE_URL ?>/process/trade_process.php" method="POST">
                        <input type="hidden" name="action" value="accept">
                        <input type="hidden" name="trade_id" value="<?= $t['trade_id'] ?>">
                        <button type="submit" class="btn btn-success btn-sm">Accept</button>
                      </form>
                      <form action="<?= BASE_URL ?>/process/trade_process.php" method="POST" onsubmit="return confirm('Decline this offer?')">
                        <input type="hidden" name="action" value="decline">
                        <input type="hidden" name="trade_id" value="<?= $t['trade_id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Decline</button>
                      </form>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <h3 style="font-size:1rem;font-weight:600;margin-bottom:1rem;color:var(--text-secondary);">📤 My Outgoing Offers</h3>
          <?php if (empty($outgoingTrades)): ?>
            <div class="card no-hover" style="padding:1.5rem;text-align:center;color:var(--text-muted);font-size:0.9rem;">No outgoing trade offers.</div>
          <?php else: ?>
            <div class="table-wrap">
              <table>
                <thead><tr><th>I Offered</th><th>For</th><th>To</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                <?php foreach ($outgoingTrades as $t): ?>
                  <tr>
                    <td><?= sanitize($t['offered_title']) ?></td>
                    <td><?= sanitize($t['requested_title']) ?></td>
                    <td>@<?= sanitize($t['receiver_name']) ?></td>
                    <td>
                      <span class="badge <?= $t['trade_status'] === 'Pending' ? 'badge-gold' : ($t['trade_status'] === 'Accepted' ? 'badge-green' : 'badge-red') ?>">
                        <?= sanitize($t['trade_status']) ?>
                      </span>
                    </td>
                    <td><?= timeAgo($t['date_proposed']) ?></td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>

        <!-- ── CART SUMMARY TAB ── -->
        <div id="tab-cart" class="tab-panel <?= $activeTab === 'cart' ? 'active' : '' ?>">
          <div class="flex-between mb-lg">
            <h2 style="font-size:1.4rem;font-weight:700;">Cart Summary</h2>
            <?php if ($cartCount > 0): ?>
              <a href="<?= BASE_URL ?>/cart.php" class="btn btn-gold btn-sm">Go to Cart →</a>
            <?php endif; ?>
          </div>
          <?php
          $cartItems = $db->prepare("
              SELECT c.cart_id, p.title, p.price, p.image_path, p.product_id, p.status
              FROM Cart c JOIN Products p ON c.product_id = p.product_id
              WHERE c.user_id = ? ORDER BY c.date_added DESC
          ");
          $cartItems->execute([$uid]);
          $cartItems = $cartItems->fetchAll();
          ?>
          <?php if (empty($cartItems)): ?>
            <div class="empty-state">
              <div class="empty-state-icon">🛒</div>
              <h3 class="empty-state-title">Your cart is empty</h3>
              <p>Browse the catalog to find items to buy.</p>
              <a href="<?= BASE_URL ?>/catalog.php" class="btn btn-primary">Browse Catalog</a>
            </div>
          <?php else: ?>
            <?php foreach ($cartItems as $ci): ?>
              <div class="cart-item">
                <div class="cart-item-img"><?= $ci['image_path'] && file_exists($_SERVER['DOCUMENT_ROOT'] . $ci['image_path']) ? '<img src="'.sanitize($ci['image_path']).'" alt="">' : '👕' ?></div>
                <div class="cart-item-info">
                  <div class="cart-item-title"><?= sanitize($ci['title']) ?></div>
                  <div class="cart-item-price"><?= formatPrice($ci['price']) ?></div>
                  <?php if ($ci['status'] !== 'Available'): ?>
                    <span class="badge badge-red">No longer available</span>
                  <?php endif; ?>
                </div>
                <form action="<?= BASE_URL ?>/process/cart_process.php" method="POST">
                  <input type="hidden" name="action" value="remove">
                  <input type="hidden" name="cart_id" value="<?= $ci['cart_id'] ?>">
                  <button type="submit" class="btn btn-outline btn-sm" title="Remove">✕</button>
                </form>
              </div>
            <?php endforeach; ?>
            <div class="mt-lg">
              <a href="<?= BASE_URL ?>/cart.php" class="btn btn-gold btn-lg" style="width:100%;">Proceed to Checkout →</a>
            </div>
          <?php endif; ?>
        </div>

        <!-- ── NOTIFICATIONS TAB ── -->
        <div id="tab-notifications" class="tab-panel <?= $activeTab === 'notifications' ? 'active' : '' ?>">
          <h2 style="font-size:1.4rem;font-weight:700;margin-bottom:1.5rem;">Notifications</h2>
          <?php if (empty($notifications)): ?>
            <div class="empty-state">
              <div class="empty-state-icon">🔔</div>
              <h3 class="empty-state-title">No notifications</h3>
              <p>You're all caught up!</p>
            </div>
          <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:0.5rem;">
              <?php foreach ($notifications as $n): ?>
                <div class="card no-hover" style="padding:1rem;opacity:<?= $n['is_read'] ? '0.6' : '1' ?>;">
                  <div class="flex-between">
                    <p style="font-size:0.875rem;">
                      <?= $n['is_read'] ? '' : '🔵 ' ?>
                      <?= sanitize($n['message']) ?>
                    </p>
                    <span style="font-size:0.75rem;color:var(--text-muted);white-space:nowrap;margin-left:1rem;"><?= timeAgo($n['date_created']) ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- ── PROFILE TAB ── -->
        <div id="tab-profile" class="tab-panel <?= $activeTab === 'profile' ? 'active' : '' ?>">
          <h2 style="font-size:1.4rem;font-weight:700;margin-bottom:1.5rem;">Profile Settings</h2>
          <div class="card no-hover">
            <div class="card-body">
              <form action="<?= BASE_URL ?>/process/profile_process.php" method="POST" class="form-stack">
                <div class="form-group">
                  <label class="form-label" for="p_username">Username</label>
                  <input type="text" id="p_username" name="username" class="form-control"
                         value="<?= sanitize($user['username']) ?>" maxlength="50" required>
                </div>
                <div class="form-group">
                  <label class="form-label" for="p_email">Email Address</label>
                  <input type="email" id="p_email" name="email" class="form-control"
                         value="<?= sanitize($user['email']) ?>" required>
                </div>
                <hr style="border-color:var(--card-border);margin:0.5rem 0;">
                <p style="font-size:0.82rem;color:var(--text-muted);">Leave password fields blank to keep your current password.</p>
                <div class="form-group">
                  <label class="form-label" for="p_password">New Password</label>
                  <input type="password" id="p_password" name="new_password" class="form-control" placeholder="At least 8 characters">
                </div>
                <div class="form-group">
                  <label class="form-label" for="p_confirm">Confirm New Password</label>
                  <input type="password" id="p_confirm" name="confirm_password" class="form-control" placeholder="Repeat new password">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">Save Changes</button>
              </form>
            </div>
          </div>
        </div>

      </div><!-- /main content -->
    </div><!-- /dashboard-layout -->
  </div>
</div>

<script>
function switchTab(name) {
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.dash-nav-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-' + name).classList.add('active');
  event.currentTarget.classList.add('active');
  history.replaceState(null, '', '?tab=' + name);
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
