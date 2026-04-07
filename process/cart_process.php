<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$db     = getDB();
$user   = currentUser();
$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $productId = (int)($_POST['product_id'] ?? 0);
    if (!$productId) {
        setFlash('error', 'Invalid product.');
        header('Location: ' . BASE_URL . '/catalog.php');
        exit;
    }

    // Check product exists and is Available
    $stmt = $db->prepare("SELECT * FROM Products WHERE product_id = ? AND status = 'Available'");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        setFlash('error', 'This item is no longer available.');
        header('Location: ' . BASE_URL . '/catalog.php');
        exit;
    }

    // Prevent adding own item to cart
    if ($product['user_id'] == $user['user_id']) {
        setFlash('error', 'You cannot add your own listing to your cart.');
        header('Location: ' . BASE_URL . '/item.php?id=' . $productId);
        exit;
    }

    // Already in cart?
    $check = $db->prepare("SELECT cart_id FROM Cart WHERE user_id = ? AND product_id = ?");
    $check->execute([$user['user_id'], $productId]);
    if ($check->fetch()) {
        setFlash('info', 'This item is already in your cart.');
        header('Location: ' . BASE_URL . '/cart.php');
        exit;
    }

    $db->prepare("INSERT INTO Cart (user_id, product_id) VALUES (?, ?)")
       ->execute([$user['user_id'], $productId]);

    setFlash('success', '"' . sanitize($product['title']) . '" added to your cart!');
    header('Location: ' . BASE_URL . '/cart.php');
    exit;
}

if ($action === 'remove') {
    $cartId = (int)($_POST['cart_id'] ?? 0);
    if (!$cartId) {
        setFlash('error', 'Invalid cart item.');
        header('Location: ' . BASE_URL . '/cart.php');
        exit;
    }

    // Verify ownership of cart entry
    $stmt = $db->prepare("SELECT cart_id FROM Cart WHERE cart_id = ? AND user_id = ?");
    $stmt->execute([$cartId, $user['user_id']]);
    if (!$stmt->fetch()) {
        setFlash('error', 'Cart item not found.');
        header('Location: ' . BASE_URL . '/cart.php');
        exit;
    }

    $db->prepare("DELETE FROM Cart WHERE cart_id = ?")->execute([$cartId]);
    setFlash('success', 'Item removed from cart.');
    header('Location: ' . BASE_URL . '/cart.php');
    exit;
}

setFlash('error', 'Invalid action.');
header('Location: ' . BASE_URL . '/cart.php');
exit;
