<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/dashboard.php?tab=profile');
    exit;
}

$db   = getDB();
$user = currentUser();
$uid  = $user['user_id'];

$username    = sanitize(trim($_POST['username']        ?? ''));
$email       = trim($_POST['email']                   ?? '');
$newPassword = $_POST['new_password']                 ?? '';
$confirmPass = $_POST['confirm_password']             ?? '';

$errors = [];

if (strlen($username) < 3) $errors[] = 'Username must be at least 3 characters.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';

// Check unique username/email (excluding current user)
$stmt = $db->prepare("SELECT user_id FROM Users WHERE (username = ? OR email = ?) AND user_id != ?");
$stmt->execute([$username, $email, $uid]);
if ($stmt->fetch()) $errors[] = 'Username or email is already in use.';

if ($newPassword !== '') {
    if (strlen($newPassword) < 8) {
        $errors[] = 'New password must be at least 8 characters.';
    } elseif ($newPassword !== $confirmPass) {
        $errors[] = 'Passwords do not match.';
    }
}

if (!empty($errors)) {
    setFlash('error', implode(' ', $errors));
    header('Location: ' . BASE_URL . '/dashboard.php?tab=profile');
    exit;
}

if ($newPassword !== '') {
    $hash = password_hash($newPassword, PASSWORD_BCRYPT);
    $db->prepare("UPDATE Users SET username = ?, email = ?, password_hash = ? WHERE user_id = ?")
       ->execute([$username, $email, $hash, $uid]);
} else {
    $db->prepare("UPDATE Users SET username = ?, email = ? WHERE user_id = ?")
       ->execute([$username, $email, $uid]);
}

// Update session
$_SESSION['username'] = $username;
$_SESSION['email']    = $email;

setFlash('success', 'Profile updated successfully!');
header('Location: ' . BASE_URL . '/dashboard.php?tab=profile');
exit;
