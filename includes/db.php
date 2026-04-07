<?php
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=localhost;dbname=pastimes_db;charset=utf8mb4',
                'root',
                '',
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            die('<div style="font-family:monospace;color:#f87171;padding:2rem;">
                ❌ Database connection failed: ' . htmlspecialchars($e->getMessage()) . '<br>
                Make sure XAMPP MySQL is running and you have run <a href="/Pastimes/setup.php" style="color:#9d65ff">setup.php</a>.
            </div>');
        }
    }
    return $pdo;
}

// Base URL (works at any depth within the project)
if (!defined('BASE_URL')) {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME']));
    $docRoot   = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'], '/\\'));
    $relPath   = ltrim(str_replace($docRoot, '', $scriptDir), '/');
    $parts     = explode('/', $relPath);
    $baseFolder = ($parts[0] !== '') ? '/' . $parts[0] : '';
    $protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    define('BASE_URL', $protocol . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $baseFolder);
}
