<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$db      = getDB();
$user    = currentUser();
$action  = $_POST['action'] ?? '';

// ── Delete own listing ────────────────────────────────────────────────────
if ($action === 'delete') {
    $productId = (int)($_POST['product_id'] ?? 0);
    if (!$productId) {
        setFlash('error', 'Invalid product.');
        header('Location: ' . BASE_URL . '/dashboard.php');
        exit;
    }
    // Verify ownership
    $stmt = $db->prepare("SELECT user_id, status FROM Products WHERE product_id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product || $product['user_id'] != $user['user_id']) {
        setFlash('error', 'You do not have permission to delete this listing.');
        header('Location: ' . BASE_URL . '/dashboard.php');
        exit;
    }
    if ($product['status'] !== 'Available') {
        setFlash('error', 'Only available listings can be deleted.');
        header('Location: ' . BASE_URL . '/dashboard.php');
        exit;
    }

    $db->prepare("DELETE FROM Products WHERE product_id = ?")->execute([$productId]);
    setFlash('success', 'Listing deleted successfully.');
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

// ── Add new listing ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/add_listing.php');
    exit;
}

$title       = sanitize(trim($_POST['title']           ?? ''));
$description = sanitize(trim($_POST['description']     ?? ''));
$price       = (float)($_POST['price']                 ?? 0);
$categoryId  = (int)($_POST['category_id']             ?? 0);
$condition   = trim($_POST['condition_label']          ?? '');

$allowedConditions = ['New','Like New','Good','Fair','Worn'];
$errors = [];

if (strlen($title) < 3) $errors[] = 'Title must be at least 3 characters.';
if (strlen($description) < 10) $errors[] = 'Description must be at least 10 characters.';
if ($price <= 0) $errors[] = 'Price must be greater than 0.';
if (!$categoryId) $errors[] = 'Please select a category.';
if (!in_array($condition, $allowedConditions)) $errors[] = 'Please select a valid condition.';

// Validate and save image
$imagePath = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $file     = $_FILES['image'];
    $allowedMimes = ['image/jpeg','image/png','image/gif','image/webp'];
    $allowedExts  = ['jpg','jpeg','png','gif','webp'];
    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $mimeType = mime_content_type($file['tmp_name']);

    if (!in_array($mimeType, $allowedMimes) || !in_array($ext, $allowedExts)) {
        $errors[] = 'Only JPG, PNG, GIF, or WEBP images are allowed.';
    } elseif ($file['size'] > 5 * 1024 * 1024) {
        $errors[] = 'Image must be under 5MB.';
    } else {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Pastimes/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $filename  = uniqid('img_', true) . '.' . $ext;
        $destPath  = $uploadDir . $filename;
        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            $imagePath = '/Pastimes/uploads/' . $filename;
        } else {
            $errors[] = 'Image upload failed. Please try again.';
        }
    }
}

if (!empty($errors)) {
    setFlash('error', implode(' ', $errors));
    header('Location: ' . BASE_URL . '/add_listing.php');
    exit;
}

$stmt = $db->prepare("
    INSERT INTO Products (user_id, category_id, title, description, price, image_path, condition_label, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'Available')
");
$stmt->execute([$user['user_id'], $categoryId, $title, $description, $price, $imagePath, $condition]);
$newId = $db->lastInsertId();

setFlash('success', 'Your listing has been published! 🎉');
header('Location: ' . BASE_URL . '/item.php?id=' . $newId);
exit;
