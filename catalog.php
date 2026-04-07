<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$db = getDB();

// Filters from GET
$search    = trim($_GET['q']        ?? '');
$catFilter = (int)($_GET['category']  ?? 0);
$condFilter= trim($_GET['condition']  ?? '');
$minPrice  = $_GET['min_price'] !== '' ? (float)($_GET['min_price'] ?? '') : null;
$maxPrice  = $_GET['max_price'] !== '' ? (float)($_GET['max_price'] ?? '') : null;

// Build query dynamically
$where  = ["p.status = 'Available'"];
$params = [];

if ($search !== '') {
    $where[]  = "(p.title LIKE ? OR p.description LIKE ? OR u.username LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($catFilter > 0) {
    $where[]  = "p.category_id = ?";
    $params[] = $catFilter;
}
if ($condFilter !== '') {
    $where[]  = "p.condition_label = ?";
    $params[] = $condFilter;
}
if ($minPrice !== null) {
    $where[]  = "p.price >= ?";
    $params[] = $minPrice;
}
if ($maxPrice !== null) {
    $where[]  = "p.price <= ?";
    $params[] = $maxPrice;
}

$whereSQL = implode(' AND ', $where);
$sql = "
    SELECT p.*, c.category_name, u.username
    FROM Products p
    JOIN Categories c ON p.category_id = c.category_id
    JOIN Users u ON p.user_id = u.user_id
    WHERE $whereSQL
    ORDER BY p.date_listed DESC
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// All categories for filter
$categories = $db->query("SELECT * FROM Categories ORDER BY category_name")->fetchAll();
$conditions = ['New','Like New','Good','Fair','Worn'];

$pageTitle = 'Catalog';
include __DIR__ . '/includes/header.php';
?>

<div class="page-content">
  <div class="container">
    <div class="page-header">
      <div class="page-eyebrow">Marketplace</div>
      <h1 class="page-title">Browse Catalog</h1>
      <p class="page-subtitle"><?= number_format(count($products)) ?> item<?= count($products) !== 1 ? 's' : '' ?> available</p>
    </div>

    <!-- Search -->
    <form method="GET" action="<?= BASE_URL ?>/catalog.php" id="catalog-form">
      <div class="search-wrap">
        <span class="search-icon">🔍</span>
        <input type="text" name="q" id="catalog-search" class="search-input"
               placeholder="Search by title, description, or seller..."
               value="<?= sanitize($search) ?>">
        <!-- Preserve other filters on search -->
        <?php if ($catFilter): ?><input type="hidden" name="category"  value="<?= $catFilter ?>"><?php endif; ?>
        <?php if ($condFilter): ?><input type="hidden" name="condition" value="<?= sanitize($condFilter) ?>"><?php endif; ?>
        <?php if ($minPrice !== null): ?><input type="hidden" name="min_price" value="<?= $minPrice ?>"><?php endif; ?>
        <?php if ($maxPrice !== null): ?><input type="hidden" name="max_price" value="<?= $maxPrice ?>"><?php endif; ?>
      </div>
    </form>

    <div class="catalog-layout">
      <!-- FILTER SIDEBAR -->
      <aside class="filter-sidebar">
        <form method="GET" action="<?= BASE_URL ?>/catalog.php">
          <?php if ($search): ?>
            <input type="hidden" name="q" value="<?= sanitize($search) ?>">
          <?php endif; ?>
          <div class="filter-card">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
              <span style="font-weight:700;font-size:0.95rem;">Filters</span>
              <a href="<?= BASE_URL ?>/catalog.php" style="font-size:0.75rem;color:var(--text-muted);">Clear all</a>
            </div>

            <!-- Category -->
            <div class="filter-group">
              <div class="filter-section-title">Category</div>
              <div class="filter-options" style="display:flex;flex-direction:column;gap:0.4rem;">
                <label class="filter-option">
                  <input type="radio" name="category" value="0" <?= $catFilter === 0 ? 'checked' : '' ?>>
                  All Categories
                </label>
                <?php foreach ($categories as $cat): ?>
                  <label class="filter-option">
                    <input type="radio" name="category" value="<?= $cat['category_id'] ?>"
                           <?= $catFilter === (int)$cat['category_id'] ? 'checked' : '' ?>>
                    <?= sanitize($cat['category_name']) ?>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Condition -->
            <div class="filter-group">
              <div class="filter-section-title">Condition</div>
              <div class="filter-options" style="display:flex;flex-direction:column;gap:0.4rem;">
                <label class="filter-option">
                  <input type="radio" name="condition" value="" <?= $condFilter === '' ? 'checked' : '' ?>>
                  Any Condition
                </label>
                <?php foreach ($conditions as $cond): ?>
                  <label class="filter-option">
                    <input type="radio" name="condition" value="<?= $cond ?>"
                           <?= $condFilter === $cond ? 'checked' : '' ?>>
                    <?= $cond ?>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Price Range -->
            <div class="filter-group">
              <div class="filter-section-title">Price Range (ZAR)</div>
              <div class="price-inputs">
                <input type="number" name="min_price" class="form-control" placeholder="Min"
                       min="0" step="10" value="<?= $minPrice !== null ? $minPrice : '' ?>">
                <input type="number" name="max_price" class="form-control" placeholder="Max"
                       min="0" step="10" value="<?= $maxPrice !== null ? $maxPrice : '' ?>">
              </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;">Apply Filters</button>
          </div>
        </form>
      </aside>

      <!-- PRODUCT GRID -->
      <div>
        <?php if (empty($products)): ?>
          <div class="empty-state">
            <div class="empty-state-icon">🔍</div>
            <h3 class="empty-state-title">No items found</h3>
            <p>Try adjusting your search or filters.</p>
            <a href="<?= BASE_URL ?>/catalog.php" class="btn btn-outline">Clear Filters</a>
          </div>
        <?php else: ?>
          <div class="products-grid">
            <?php foreach ($products as $p): ?>
              <a href="<?= BASE_URL ?>/item.php?id=<?= $p['product_id'] ?>" class="product-card">
                <?php if ($p['image_path'] && file_exists($_SERVER['DOCUMENT_ROOT'] . $p['image_path'])): ?>
                  <img src="<?= sanitize($p['image_path']) ?>" alt="<?= sanitize($p['title']) ?>" class="product-card-img">
                <?php else: ?>
                  <?= productImageTag('', $p['title']) ?>
                <?php endif; ?>
                <div class="product-card-body">
                  <div class="product-card-title"><?= sanitize($p['title']) ?></div>
                  <div class="product-card-seller">by @<?= sanitize($p['username']) ?> · <?= sanitize($p['category_name']) ?></div>
                  <div class="product-card-footer">
                    <span class="product-card-price"><?= formatPrice($p['price']) ?></span>
                    <span class="badge <?= conditionBadgeClass($p['condition_label']) ?>"><?= sanitize($p['condition_label']) ?></span>
                  </div>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
// Live search via form submit on enter (form already handles it)
// Auto-submit on radio change for filters
document.querySelectorAll('.filter-option input').forEach(input => {
  input.addEventListener('change', () => {
    input.closest('form').submit();
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
