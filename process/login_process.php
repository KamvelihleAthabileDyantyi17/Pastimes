<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$email    = trim($_POST['email']    ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    setFlash('error', 'Please enter your email and password.');
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$db   = getDB();
$stmt = $db->prepare("SELECT * FROM Users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    setFlash('error', 'Invalid email or password. Please try again.');
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

if ($user['status'] === 'Banned') {
    setFlash('error', 'Your account has been banned. Please contact support.');
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}
if ($user['status'] === 'Suspended') {
    setFlash('error', 'Your account is currently suspended. Please contact support.');
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// Start session
if (session_status() === PHP_SESSION_NONE) session_start();
session_regenerate_id(true);

$_SESSION['user_id']  = $user['user_id'];
$_SESSION['username'] = $user['username'];
$_SESSION['email']    = $user['email'];
$_SESSION['role']     = $user['role'];
$_SESSION['status']   = $user['status'];

setFlash('success', 'Welcome back, @' . $user['username'] . '!');

if ($user['role'] === 'admin') {
    header('Location: ' . BASE_URL . '/admin.php');
} else {
    header('Location: ' . BASE_URL . '/dashboard.php');
}
exit;
