<?php
$host = getenv('DB_HOST') ?: 'mysql-16bf1a03-meshachsunday86-41c1.h.aivencloud.com';
$port = getenv('DB_PORT') ?: 17867;
$db   = getenv('DB_NAME') ?: 'defaultdb';
$user = getenv('DB_USER') ?: 'avnadmin';
$pass = getenv('DB_PASSWORD');

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // 1. Auto-create missing tables on Aiven Cloud
    $pdo->exec("CREATE TABLE IF NOT EXISTS employees (
        employee_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        gender VARCHAR(50),
        department VARCHAR(100),
        phone_number VARCHAR(50)
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        product_id INT AUTO_INCREMENT PRIMARY KEY,
        product_name VARCHAR(255) NOT NULL,
        quantity INT NOT NULL DEFAULT 0,
        cost_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        selling_price DECIMAL(12,2) NOT NULL DEFAULT 0.00
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS sales (
        sale_id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        employee_id INT NULL,
        quantity_sold INT NOT NULL,
        total_amount DECIMAL(12,2) NOT NULL,
        profit DECIMAL(12,2) NOT NULL,
        previous_hash VARCHAR(64) NULL,
        hash_signature VARCHAR(64) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. PATCH SCHEMA MISMATCH: Rename older Python columns to match the new PHP code
    try { $pdo->exec("ALTER TABLE sales CHANGE total_price total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE sales CHANGE quantity quantity_sold INT NOT NULL DEFAULT 0"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE sales ADD COLUMN employee_id INT NULL"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE sales ADD COLUMN profit DECIMAL(12,2) NOT NULL DEFAULT 0.00"); } catch (PDOException $e) {}

    // 3. Drop old Python triggers temporarily so they don't crash due to the column renames
    try { $pdo->exec("DROP TRIGGER IF EXISTS detect_sales_update"); } catch (PDOException $e) {}
    try { $pdo->exec("DROP TRIGGER IF EXISTS detect_sales_delete"); } catch (PDOException $e) {}

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>