<?php
// ======================================================
// PASTIMES — One-Time Database Setup Script
// Visit: http://localhost/Pastimes/setup.php ONCE
// Then DELETE this file for security!
// ======================================================

$host = 'localhost';
$user = 'root';
$pass = '';

$errors = [];
$successes = [];

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `pastimes_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `pastimes_db`");
    $successes[] = "Database 'pastimes_db' created/selected.";

    // Users
    $pdo->exec("CREATE TABLE IF NOT EXISTS Users (
        user_id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('user','admin') DEFAULT 'user',
        status ENUM('Active','Banned','Suspended') DEFAULT 'Active',
        date_joined DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    $successes[] = "Table 'Users' ready.";

    // Categories
    $pdo->exec("CREATE TABLE IF NOT EXISTS Categories (
        category_id INT PRIMARY KEY AUTO_INCREMENT,
        category_name VARCHAR(50) NOT NULL
    )");
    $successes[] = "Table 'Categories' ready.";

    // Products
    $pdo->exec("CREATE TABLE IF NOT EXISTS Products (
        product_id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        category_id INT NOT NULL,
        title VARCHAR(100) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        image_path VARCHAR(255),
        condition_label ENUM('New','Like New','Good','Fair','Worn') NOT NULL,
        status ENUM('Available','Sold','Traded') DEFAULT 'Available',
        date_listed DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (category_id) REFERENCES Categories(category_id)
    )");
    $successes[] = "Table 'Products' ready.";

    // Trades
    $pdo->exec("CREATE TABLE IF NOT EXISTS Trades (
        trade_id INT PRIMARY KEY AUTO_INCREMENT,
        initiator_user_id INT NOT NULL,
        receiver_user_id INT NOT NULL,
        offered_product_id INT NOT NULL,
        requested_product_id INT NOT NULL,
        trade_status ENUM('Pending','Accepted','Declined') DEFAULT 'Pending',
        date_proposed DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (initiator_user_id) REFERENCES Users(user_id),
        FOREIGN KEY (receiver_user_id) REFERENCES Users(user_id),
        FOREIGN KEY (offered_product_id) REFERENCES Products(product_id),
        FOREIGN KEY (requested_product_id) REFERENCES Products(product_id)
    )");
    $successes[] = "Table 'Trades' ready.";

    // Cart
    $pdo->exec("CREATE TABLE IF NOT EXISTS Cart (
        cart_id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        date_added DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES Products(product_id) ON DELETE CASCADE
    )");
    $successes[] = "Table 'Cart' ready.";

    // Orders
    $pdo->exec("CREATE TABLE IF NOT EXISTS Orders (
        order_id INT PRIMARY KEY AUTO_INCREMENT,
        buyer_user_id INT NOT NULL,
        product_id INT NOT NULL,
        order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        status ENUM('Pending','Completed') DEFAULT 'Completed',
        FOREIGN KEY (buyer_user_id) REFERENCES Users(user_id),
        FOREIGN KEY (product_id) REFERENCES Products(product_id)
    )");
    $successes[] = "Table 'Orders' ready.";

    // Notifications
    $pdo->exec("CREATE TABLE IF NOT EXISTS Notifications (
        notification_id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        message VARCHAR(255) NOT NULL,
        is_read BOOLEAN DEFAULT 0,
        date_created DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
    )");
    $successes[] = "Table 'Notifications' ready.";

    // Seed Categories
    $catCount = $pdo->query("SELECT COUNT(*) FROM Categories")->fetchColumn();
    if ($catCount == 0) {
        $pdo->exec("INSERT INTO Categories (category_name) VALUES
            ('Vintage'),('Shoes'),('Clothing'),('Electronics'),('Accessories'),('Other')");
        $successes[] = "Categories seeded.";
    }

    // Seed Users
    $userCount = $pdo->query("SELECT COUNT(*) FROM Users")->fetchColumn();
    if ($userCount == 0) {
        $adminHash = password_hash('Admin@123', PASSWORD_BCRYPT);
        $userHash  = password_hash('Demo@123', PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("INSERT INTO Users (username, email, password_hash, role, status) VALUES (?, ?, ?, ?, 'Active')");
        $stmt->execute(['admin',       'admin@pastimes.co.za',   $adminHash, 'admin']);
        $stmt->execute(['thriftqueen', 'thrift@pastimes.co.za',  $userHash,  'user']);
        $stmt->execute(['vintagevibe', 'vintage@pastimes.co.za', $userHash,  'user']);
        $successes[] = "Users seeded. Admin: admin@pastimes.co.za / Admin@123 | Demo users password: Demo@123";
    }

    // Seed Products
    $prodCount = $pdo->query("SELECT COUNT(*) FROM Products")->fetchColumn();
    if ($prodCount == 0) {
        $products = [
            [2, 1, 'Vintage Leather Jacket', 'Classic 90s brown leather jacket in excellent condition. Size M.', 450.00, 'Like New'],
            [2, 3, 'Y2K Cargo Pants', 'Olive green cargo pants with multiple pockets. Size 32.', 180.00, 'Good'],
            [3, 2, 'Nike Air Force 1 Low', 'White leather AF1s, barely worn. Size UK 9.', 950.00, 'Like New'],
            [3, 3, 'Floral Summer Dress', 'Beautiful floral maxi dress, perfect for summer. Size S.', 220.00, 'Good'],
            [2, 5, 'Vintage Watch Collection', 'Seiko 5 automatic from the 80s, running well.', 750.00, 'Good'],
            [3, 1, 'Levi\'s 501 Vintage Jeans', 'Original 501s from the 90s, stonewashed. Size 30x32.', 380.00, 'Fair'],
        ];
        $stmt = $pdo->prepare("INSERT INTO Products (user_id, category_id, title, description, price, condition_label) VALUES (?,?,?,?,?,?)");
        foreach ($products as $p) {
            $stmt->execute($p);
        }
        $successes[] = "Sample products seeded.";
    }

} catch (PDOException $e) {
    $errors[] = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Pastimes Setup</title>
<style>
  body { font-family: monospace; background: #0a0e1a; color: #f1f5f9; padding: 2rem; }
  h1 { color: #9d65ff; margin-bottom: 1.5rem; }
  .ok  { color: #34d399; margin: 0.3rem 0; }
  .err { color: #f87171; margin: 0.3rem 0; }
  .box { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 1.5rem; margin-bottom: 1rem; }
  .warn { background: rgba(245,158,11,0.15); border: 1px solid rgba(245,158,11,0.3); color: #fbbf24; padding: 1rem; border-radius: 8px; margin-top: 1rem; }
  a { color: #7c3aed; }
</style>
</head>
<body>
<h1>⚙️ Pastimes — Database Setup</h1>
<div class="box">
<?php foreach ($successes as $s): ?>
  <p class="ok">✅ <?= htmlspecialchars($s) ?></p>
<?php endforeach; ?>
<?php foreach ($errors as $e): ?>
  <p class="err">❌ <?= htmlspecialchars($e) ?></p>
<?php endforeach; ?>
</div>

<?php if (empty($errors)): ?>
<div class="warn">
  ⚠️ <strong>Setup complete!</strong> Delete this file (<code>setup.php</code>) and visit <a href="index.php">index.php</a> to launch Pastimes.
</div>
<?php endif; ?>
</body>
</html>
