<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION = [];
session_destroy();

header('Location: ' . BASE_URL . '/index.php');
exit;
