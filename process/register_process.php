<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/register.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email']    ?? '');
$password        = $_POST['password']         ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Validation
$errors = [];

if (strlen($username) < 3 || strlen($username) > 50) {
    $errors[] = 'Username must be between 3 and 50 characters.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}
if (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters.';
}
if ($password !== $confirmPassword) {
    $errors[] = 'Passwords do not match.';
}

if (!empty($errors)) {
    setFlash('error', implode(' ', $errors));
    header('Location: ' . BASE_URL . '/register.php?username=' . urlencode($username) . '&email=' . urlencode($email));
    exit;
}

$db = getDB();

// Check for existing username/email
$stmt = $db->prepare("SELECT user_id FROM Users WHERE username = ? OR email = ?");
$stmt->execute([$username, $email]);
if ($stmt->fetch()) {
    setFlash('error', 'Username or email is already taken. Please choose another.');
    header('Location: ' . BASE_URL . '/register.php');
    exit;
}

// Insert user
$hash = password_hash($password, PASSWORD_BCRYPT);
$stmt = $db->prepare("INSERT INTO Users (username, email, password_hash, role, status) VALUES (?, ?, ?, 'user', 'Active')");
$stmt->execute([sanitize($username), $email, $hash]);

setFlash('success', 'Account created! You can now log in.');
header('Location: ' . BASE_URL . '/login.php');
exit;
