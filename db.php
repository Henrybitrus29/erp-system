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

    // Self-healing: Auto-create missing tables on Aiven Cloud
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

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>