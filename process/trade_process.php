<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$db     = getDB();
$user   = currentUser();
$action = $_POST['action'] ?? '';

// ── Propose a trade ───────────────────────────────────────────────────────
if ($action === 'propose') {
    $offeredId   = (int)($_POST['offered_product_id']   ?? 0);
    $requestedId = (int)($_POST['requested_product_id'] ?? 0);
    $receiverId  = (int)($_POST['receiver_user_id']     ?? 0);

    if (!$offeredId || !$requestedId || !$receiverId) {
        setFlash('error', 'Invalid trade request. Please try again.');
        header('Location: ' . BASE_URL . '/catalog.php');
        exit;
    }

    // Verify offered item belongs to current user and is Available
    $stmt = $db->prepare("SELECT * FROM Products WHERE product_id = ? AND user_id = ? AND status = 'Available'");
    $stmt->execute([$offeredId, $user['user_id']]);
    if (!$stmt->fetch()) {
        setFlash('error', 'The item you offered is not valid or is no longer available.');
        header('Location: ' . BASE_URL . '/item.php?id=' . $requestedId);
        exit;
    }

    // Verify requested item is Available and not owned by initiator
    $stmt = $db->prepare("SELECT * FROM Products WHERE product_id = ? AND status = 'Available' AND user_id != ?");
    $stmt->execute([$requestedId, $user['user_id']]);
    $requestedProduct = $stmt->fetch();
    if (!$requestedProduct) {
        setFlash('error', 'The item you want to trade for is not available.');
        header('Location: ' . BASE_URL . '/item.php?id=' . $requestedId);
        exit;
    }

    // Check for existing pending trade between these items
    $check = $db->prepare("
        SELECT trade_id FROM Trades
        WHERE initiator_user_id = ? AND offered_product_id = ? AND requested_product_id = ? AND trade_status = 'Pending'
    ");
    $check->execute([$user['user_id'], $offeredId, $requestedId]);
    if ($check->fetch()) {
        setFlash('error', 'You already have a pending trade offer for this item.');
        header('Location: ' . BASE_URL . '/item.php?id=' . $requestedId);
        exit;
    }

    // Insert trade
    $stmt = $db->prepare("
        INSERT INTO Trades (initiator_user_id, receiver_user_id, offered_product_id, requested_product_id, trade_status)
        VALUES (?, ?, ?, ?, 'Pending')
    ");
    $stmt->execute([$user['user_id'], $receiverId, $offeredId, $requestedId]);

    // Notify receiver
    $msg = '@' . $user['username'] . ' wants to trade "'
        . $requestedProduct['title'] . '" for something. Check your trade requests!';
    $db->prepare("INSERT INTO Notifications (user_id, message) VALUES (?, ?)")
       ->execute([$receiverId, $msg]);

    setFlash('success', 'Trade offer sent! The seller has been notified.');
    header('Location: ' . BASE_URL . '/item.php?id=' . $requestedId);
    exit;
}

// ── Accept a trade ────────────────────────────────────────────────────────
if ($action === 'accept') {
    $tradeId = (int)($_POST['trade_id'] ?? 0);
    $stmt    = $db->prepare("SELECT * FROM Trades WHERE trade_id = ? AND receiver_user_id = ? AND trade_status = 'Pending'");
    $stmt->execute([$tradeId, $user['user_id']]);
    $trade = $stmt->fetch();

    if (!$trade) {
        setFlash('error', 'Trade not found or already resolved.');
        header('Location: ' . BASE_URL . '/dashboard.php?tab=trades');
        exit;
    }

    // Mark both products as Traded
    $db->prepare("UPDATE Products SET status = 'Traded' WHERE product_id IN (?, ?)")
       ->execute([$trade['offered_product_id'], $trade['requested_product_id']]);

    // Update trade status
    $db->prepare("UPDATE Trades SET trade_status = 'Accepted' WHERE trade_id = ?")
       ->execute([$tradeId]);

    // Decline all other pending trades involving these products
    $db->prepare("
        UPDATE Trades SET trade_status = 'Declined'
        WHERE trade_id != ? AND trade_status = 'Pending'
          AND (offered_product_id IN (?,?) OR requested_product_id IN (?,?))
    ")->execute([$tradeId, $trade['offered_product_id'], $trade['requested_product_id'],
                 $trade['offered_product_id'], $trade['requested_product_id']]);

    // Notify initiator
    $db->prepare("INSERT INTO Notifications (user_id, message) VALUES (?, ?)")
       ->execute([$trade['initiator_user_id'], '@' . $user['username'] . ' accepted your trade offer! 🎉']);

    setFlash('success', 'Trade accepted! Both items have been marked as Traded.');
    header('Location: ' . BASE_URL . '/dashboard.php?tab=trades');
    exit;
}

// ── Decline a trade ───────────────────────────────────────────────────────
if ($action === 'decline') {
    $tradeId = (int)($_POST['trade_id'] ?? 0);
    $stmt    = $db->prepare("SELECT * FROM Trades WHERE trade_id = ? AND receiver_user_id = ? AND trade_status = 'Pending'");
    $stmt->execute([$tradeId, $user['user_id']]);
    $trade = $stmt->fetch();

    if (!$trade) {
        setFlash('error', 'Trade not found or already resolved.');
        header('Location: ' . BASE_URL . '/dashboard.php?tab=trades');
        exit;
    }

    $db->prepare("UPDATE Trades SET trade_status = 'Declined' WHERE trade_id = ?")
       ->execute([$tradeId]);

    // Notify initiator
    $db->prepare("INSERT INTO Notifications (user_id, message) VALUES (?, ?)")
       ->execute([$trade['initiator_user_id'], '@' . $user['username'] . ' declined your trade offer.']);

    setFlash('info', 'Trade declined. Both items remain available.');
    header('Location: ' . BASE_URL . '/dashboard.php?tab=trades');
    exit;
}

setFlash('error', 'Invalid action.');
header('Location: ' . BASE_URL . '/dashboard.php');
exit;
