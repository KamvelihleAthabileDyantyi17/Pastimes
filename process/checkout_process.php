<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$db   = getDB();
$user = currentUser();
$uid  = $user['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/cart.php');
    exit;
}

// Fetch all available items in cart
$stmt = $db->prepare("
    SELECT c.cart_id, p.product_id, p.title, p.price, p.status, p.user_id AS seller_id
    FROM Cart c
    JOIN Products p ON c.product_id = p.product_id
    WHERE c.user_id = ? AND p.status = 'Available'
");
$stmt->execute([$uid]);
$items = $stmt->fetchAll();

if (empty($items)) {
    setFlash('error', 'No available items to checkout. Your cart may contain sold items.');
    header('Location: ' . BASE_URL . '/cart.php');
    exit;
}

// Process each item
$orderStmt  = $db->prepare("INSERT INTO Orders (buyer_user_id, product_id, status) VALUES (?, ?, 'Completed')");
$updateStmt = $db->prepare("UPDATE Products SET status = 'Sold' WHERE product_id = ? AND status = 'Available'");
$cartStmt   = $db->prepare("DELETE FROM Cart WHERE user_id = ? AND product_id = ?");
$notifStmt  = $db->prepare("INSERT INTO Notifications (user_id, message) VALUES (?, ?)");

$db->beginTransaction();
try {
    foreach ($items as $item) {
        $orderStmt->execute([$uid, $item['product_id']]);
        $updateStmt->execute([$item['product_id']]);
        $cartStmt->execute([$uid, $item['product_id']]);

        // Notify seller
        $notifStmt->execute([
            $item['seller_id'],
            '@' . $user['username'] . ' purchased "' . $item['title'] . '" for ' . formatPrice($item['price']) . '.'
        ]);
    }

    // Clear any remaining cart entries (including unavailable ones)
    $db->prepare("DELETE FROM Cart WHERE user_id = ?")->execute([$uid]);

    $db->commit();
} catch (Exception $e) {
    $db->rollBack();
    setFlash('error', 'Checkout failed. Please try again.');
    header('Location: ' . BASE_URL . '/cart.php');
    exit;
}

$total = array_sum(array_column($items, 'price'));
setFlash('success', '🎉 Checkout successful! You purchased ' . count($items) . ' item(s) for ' . formatPrice($total) . '. Sellers have been notified.');
header('Location: ' . BASE_URL . '/dashboard.php');
exit;
