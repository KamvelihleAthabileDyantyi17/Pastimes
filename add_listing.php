<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$db = getDB();
$categories = $db->query("SELECT * FROM Categories ORDER BY category_name")->fetchAll();
$conditions = ['New','Like New','Good','Fair','Worn'];

$pageTitle = 'Add New Listing';
include __DIR__ . '/includes/header.php';
?>

<div class="page-content">
  <div class="container" style="max-width:720px;">
    <div class="page-header">
      <div class="page-eyebrow">Sell or Trade</div>
      <h1 class="page-title">Add a New Listing</h1>
      <p class="page-subtitle">Fill in the details below to list your item on the marketplace.</p>
    </div>

    <div class="card no-hover">
      <div class="card-body">
        <form id="listing-form" action="<?= BASE_URL ?>/process/add_listing_process.php"
              method="POST" enctype="multipart/form-data" class="form-stack" novalidate>

          <div class="form-group">
            <label class="form-label" for="title">Item Title <span style="color:var(--danger)">*</span></label>
            <input type="text" id="title" name="title" class="form-control"
                   placeholder="e.g. Vintage Levi's 501 Jeans" maxlength="100" required>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="form-group">
              <label class="form-label" for="category_id">Category <span style="color:var(--danger)">*</span></label>
              <select id="category_id" name="category_id" class="form-control" required>
                <option value="">— Select Category —</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= $cat['category_id'] ?>"><?= sanitize($cat['category_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label" for="condition_label">Condition <span style="color:var(--danger)">*</span></label>
              <select id="condition_label" name="condition_label" class="form-control" required>
                <option value="">— Select Condition —</option>
                <?php foreach ($conditions as $c): ?>
                  <option value="<?= $c ?>"><?= $c ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="price">Price (ZAR) <span style="color:var(--danger)">*</span></label>
            <input type="number" id="price" name="price" class="form-control"
                   placeholder="0.00" min="1" max="999999" step="0.01" required>
          </div>

          <div class="form-group">
            <label class="form-label" for="description">Description <span style="color:var(--danger)">*</span></label>
            <textarea id="description" name="description" class="form-control"
                      rows="5" placeholder="Describe the item — size, colour, brand, any wear or defects..." required></textarea>
          </div>

          <div class="form-group">
            <label class="form-label">Item Photo</label>
            <div class="upload-zone" id="upload-zone">
              <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
              <div class="upload-icon">📷</div>
              <div class="upload-text">
                <strong>Click to upload</strong> or drag &amp; drop<br>
                <span style="font-size:0.8rem;color:var(--text-muted);">JPG, PNG, GIF, WEBP – max 5MB</span>
              </div>
              <img id="image-preview" src="" alt="Preview" class="upload-preview">
            </div>
          </div>

          <div style="display:flex;gap:1rem;margin-top:0.5rem;">
            <button type="submit" class="btn btn-primary btn-lg" style="flex:1;">📢 Publish Listing</button>
            <a href="<?= BASE_URL ?>/dashboard.php" class="btn btn-outline btn-lg">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="<?= BASE_URL ?>/js/validation.js"></script>
<?php include __DIR__ . '/includes/footer.php'; ?>
