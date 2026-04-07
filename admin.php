<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireAdmin();

$db = getDB();

// Stats
$stats = [
    'users'    => $db->query("SELECT COUNT(*) FROM Users WHERE role = 'user'")->fetchColumn(),
    'listings' => $db->query("SELECT COUNT(*) FROM Products")->fetchColumn(),
    'available'=> $db->query("SELECT COUNT(*) FROM Products WHERE status = 'Available'")->fetchColumn(),
    'trades'   => $db->query("SELECT COUNT(*) FROM Trades WHERE trade_status = 'Accepted'")->fetchColumn(),
    'orders'   => $db->query("SELECT COUNT(*) FROM Orders")->fetchColumn(),
    'banned'   => $db->query("SELECT COUNT(*) FROM Users WHERE status = 'Banned'")->fetchColumn(),
];

// Users
$users = $db->query("SELECT * FROM Users ORDER BY date_joined DESC")->fetchAll();

// All listings
$listings = $db->query("
    SELECT p.*, u.username, c.category_name
    FROM Products p
    JOIN Users u ON p.user_id = u.user_id
    JOIN Categories c ON p.category_id = c.category_id
    ORDER BY p.date_listed DESC
")->fetchAll();

$activeTab = $_GET['tab'] ?? 'stats';

$pageTitle = 'Admin Panel';
include __DIR__ . '/includes/header.php';
?>

<div class="page-content">
  <div class="container">
    <div class="page-header">
      <div class="page-eyebrow">Administration</div>
      <h1 class="page-title">Admin Panel</h1>
      <p class="page-subtitle">Manage users, listings, and monitor site activity.</p>
    </div>

    <!-- Quick Stats -->
    <div class="admin-stat-grid">
      <div class="admin-stat-card">
        <div class="admin-stat-num"><?= number_format($stats['users']) ?></div>
        <div class="admin-stat-label">👥 Registered Users</div>
      </div>
      <div class="admin-stat-card">
        <div class="admin-stat-num"><?= number_format($stats['available']) ?></div>
        <div class="admin-stat-label">📦 Active Listings</div>
      </div>
      <div class="admin-stat-card">
        <div class="admin-stat-num"><?= number_format($stats['trades']) ?></div>
        <div class="admin-stat-label">🔄 Completed Trades</div>
      </div>
      <div class="admin-stat-card">
        <div class="admin-stat-num"><?= number_format($stats['orders']) ?></div>
        <div class="admin-stat-label">🛒 Total Orders</div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="admin-tabs">
      <button class="admin-tab <?= $activeTab === 'stats'    ? 'active' : '' ?>" onclick="switchAdminTab('stats')">📊 Statistics</button>
      <button class="admin-tab <?= $activeTab === 'users'    ? 'active' : '' ?>" onclick="switchAdminTab('users')">👥 Users</button>
      <button class="admin-tab <?= $activeTab === 'listings' ? 'active' : '' ?>" onclick="switchAdminTab('listings')">📦 Listings</button>
    </div>

    <!-- STATS TAB -->
    <div id="atab-stats" class="admin-tab-panel <?= $activeTab === 'stats' ? 'active' : '' ?>">
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1.25rem;margin-top:1.5rem;">
        <?php
        $statCards = [
          ['📦 Total Listings',        $stats['listings'], 'All time'],
          ['✅ Available Now',          $stats['available'],'Currently active'],
          ['🛒 Total Orders',           $stats['orders'],   'Mock checkouts'],
          ['🔄 Trades Accepted',        $stats['trades'],   'Completed exchanges'],
          ['🚫 Banned Accounts',        $stats['banned'],   'Restricted users'],
          ['🏷️ Total Products',         (int)$db->query("SELECT COUNT(*) FROM Products WHERE status='Sold'")->fetchColumn(), 'Items sold'],
        ];
        foreach ($statCards as [$label, $value, $sub]): ?>
          <div class="card no-hover" style="padding:1.5rem;">
            <div style="font-family:'Outfit',sans-serif;font-size:1.8rem;font-weight:800;background:linear-gradient(135deg,var(--accent-light),var(--gold));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;"><?= number_format($value) ?></div>
            <div style="font-weight:600;margin-top:0.25rem;"><?= $label ?></div>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:0.2rem;"><?= $sub ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- USERS TAB -->
    <div id="atab-users" class="admin-tab-panel <?= $activeTab === 'users' ? 'active' : '' ?>">
      <div style="margin-top:1.5rem;" class="table-wrap">
        <table>
          <thead><tr>
            <th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th>
          </tr></thead>
          <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td style="color:var(--text-muted);">#<?= $u['user_id'] ?></td>
              <td><strong style="color:var(--text-primary);">@<?= sanitize($u['username']) ?></strong></td>
              <td><?= sanitize($u['email']) ?></td>
              <td>
                <span class="badge <?= $u['role'] === 'admin' ? 'badge-gold' : 'badge-gray' ?>">
                  <?= sanitize($u['role']) ?>
                </span>
              </td>
              <td>
                <span class="badge <?= $u['status'] === 'Active' ? 'badge-green' : ($u['status'] === 'Banned' ? 'badge-red' : 'badge-gold') ?>">
                  <?= sanitize($u['status']) ?>
                </span>
              </td>
              <td><?= date('d M Y', strtotime($u['date_joined'])) ?></td>
              <td>
                <?php if ($u['role'] !== 'admin'): ?>
                  <div style="display:flex;gap:0.4rem;flex-wrap:wrap;">
                    <?php if ($u['status'] !== 'Active'): ?>
                      <form action="<?= BASE_URL ?>/process/admin_process.php" method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="activate_user">
                        <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                        <button class="btn btn-success btn-sm">Activate</button>
                      </form>
                    <?php endif; ?>
                    <?php if ($u['status'] !== 'Banned'): ?>
                      <form action="<?= BASE_URL ?>/process/admin_process.php" method="POST" style="display:inline;" onsubmit="return confirm('Ban @<?= sanitize($u['username']) ?>?')">
                        <input type="hidden" name="action" value="ban_user">
                        <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                        <button class="btn btn-danger btn-sm">Ban</button>
                      </form>
                    <?php endif; ?>
                    <?php if ($u['status'] !== 'Suspended'): ?>
                      <form action="<?= BASE_URL ?>/process/admin_process.php" method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="suspend_user">
                        <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                        <button class="btn btn-warning btn-sm">Suspend</button>
                      </form>
                    <?php endif; ?>
                  </div>
                <?php else: ?>
                  <span style="font-size:0.78rem;color:var(--text-muted);">Protected</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- LISTINGS TAB -->
    <div id="atab-listings" class="admin-tab-panel <?= $activeTab === 'listings' ? 'active' : '' ?>">
      <div style="margin-top:1.5rem;" class="table-wrap">
        <table>
          <thead><tr>
            <th>ID</th><th>Title</th><th>Seller</th><th>Category</th><th>Price</th><th>Condition</th><th>Status</th><th>Listed</th><th>Actions</th>
          </tr></thead>
          <tbody>
          <?php foreach ($listings as $l): ?>
            <tr>
              <td style="color:var(--text-muted);">#<?= $l['product_id'] ?></td>
              <td>
                <a href="<?= BASE_URL ?>/item.php?id=<?= $l['product_id'] ?>" style="color:var(--text-primary);">
                  <?= sanitize($l['title']) ?>
                </a>
              </td>
              <td>@<?= sanitize($l['username']) ?></td>
              <td><?= sanitize($l['category_name']) ?></td>
              <td style="color:var(--gold);font-weight:600;"><?= formatPrice($l['price']) ?></td>
              <td><span class="badge <?= conditionBadgeClass($l['condition_label']) ?>"><?= sanitize($l['condition_label']) ?></span></td>
              <td><span class="badge <?= statusBadgeClass($l['status']) ?>"><?= sanitize($l['status']) ?></span></td>
              <td><?= timeAgo($l['date_listed']) ?></td>
              <td>
                <form action="<?= BASE_URL ?>/process/admin_process.php" method="POST" style="display:inline;"
                      onsubmit="return confirm('Delete listing #<?= $l['product_id'] ?>?')">
                  <input type="hidden" name="action" value="delete_listing">
                  <input type="hidden" name="product_id" value="<?= $l['product_id'] ?>">
                  <button class="btn btn-danger btn-sm">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
function switchAdminTab(name) {
  document.querySelectorAll('.admin-tab-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.admin-tab').forEach(b => b.classList.remove('active'));
  document.getElementById('atab-' + name).classList.add('active');
  event.currentTarget.classList.add('active');
  history.replaceState(null, '', '?tab=' + name);
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
