<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$db     = getDB();
$action = $_POST['action'] ?? '';

if ($action === 'ban_user') {
    $userId = (int)($_POST['user_id'] ?? 0);
    // Prevent banning admins
    $stmt = $db->prepare("SELECT role FROM Users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $u = $stmt->fetch();
    if ($u && $u['role'] !== 'admin') {
        $db->prepare("UPDATE Users SET status = 'Banned' WHERE user_id = ?")->execute([$userId]);
        setFlash('success', 'User has been banned.');
    } else {
        setFlash('error', 'Cannot ban this account.');
    }
    header('Location: ' . BASE_URL . '/admin.php?tab=users');
    exit;
}

if ($action === 'suspend_user') {
    $userId = (int)($_POST['user_id'] ?? 0);
    $stmt = $db->prepare("SELECT role FROM Users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $u = $stmt->fetch();
    if ($u && $u['role'] !== 'admin') {
        $db->prepare("UPDATE Users SET status = 'Suspended' WHERE user_id = ?")->execute([$userId]);
        setFlash('success', 'User has been suspended.');
    } else {
        setFlash('error', 'Cannot suspend this account.');
    }
    header('Location: ' . BASE_URL . '/admin.php?tab=users');
    exit;
}

if ($action === 'activate_user') {
    $userId = (int)($_POST['user_id'] ?? 0);
    $db->prepare("UPDATE Users SET status = 'Active' WHERE user_id = ?")->execute([$userId]);
    setFlash('success', 'User account has been activated.');
    header('Location: ' . BASE_URL . '/admin.php?tab=users');
    exit;
}

if ($action === 'delete_listing') {
    $productId = (int)($_POST['product_id'] ?? 0);
    if (!$productId) {
        setFlash('error', 'Invalid product ID.');
        header('Location: ' . BASE_URL . '/admin.php?tab=listings');
        exit;
    }

    // Delete associated images from filesystem
    $stmt = $db->prepare("SELECT image_path FROM Products WHERE product_id = ?");
    $stmt->execute([$productId]);
    $prod = $stmt->fetch();
    if ($prod && $prod['image_path']) {
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . $prod['image_path'];
        if (file_exists($fullPath)) @unlink($fullPath);
    }

    $db->prepare("DELETE FROM Products WHERE product_id = ?")->execute([$productId]);
    setFlash('success', 'Listing #' . $productId . ' has been removed.');
    header('Location: ' . BASE_URL . '/admin.php?tab=listings');
    exit;
}

setFlash('error', 'Unknown admin action.');
header('Location: ' . BASE_URL . '/admin.php');
exit;
