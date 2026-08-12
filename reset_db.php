<?php
require 'db.php';

try {
    // 1. Forcefully drop the old, broken Python tables
    $pdo->exec("DROP TABLE IF EXISTS sales");
    $pdo->exec("DROP TABLE IF EXISTS products");
    $pdo->exec("DROP TABLE IF EXISTS employees");
    $pdo->exec("DROP TABLE IF EXISTS procurement");
    $pdo->exec("DROP TABLE IF EXISTS tamper_logs");

    // 2. Rebuild the tables PERFECTLY matching the PHP application requirements
    $pdo->exec("CREATE TABLE employees (
        employee_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        gender VARCHAR(50),
        department VARCHAR(100),
        phone_number VARCHAR(50)
    )");

    $pdo->exec("CREATE TABLE products (
        product_id INT AUTO_INCREMENT PRIMARY KEY,
        product_name VARCHAR(255) NOT NULL,
        quantity INT NOT NULL DEFAULT 0,
        cost_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        selling_price DECIMAL(12,2) NOT NULL DEFAULT 0.00
    )");

    $pdo->exec("CREATE TABLE procurement (
        id INT AUTO_INCREMENT PRIMARY KEY,
        supplier VARCHAR(255) NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        unit_price DECIMAL(12,2) NOT NULL,
        total DECIMAL(12,2) NOT NULL,
        status VARCHAR(50) DEFAULT 'Completed',
        date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE sales (
        sale_id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        employee_id INT NULL,
        quantity_sold INT NOT NULL,
        total_amount DECIMAL(12,2) NOT NULL,
        profit DECIMAL(12,2) NOT NULL,
        previous_hash VARCHAR(64) NULL,
        hash_signature VARCHAR(64) NULL,
        sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE tamper_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        transaction_id VARCHAR(50),
        invalid_hash VARCHAR(64),
        issue_detected VARCHAR(255),
        logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(transaction_id, invalid_hash)
    )");

    echo "<div style='font-family:sans-serif; text-align:center; padding:50px;'>";
    echo "<h1 style='color:green;'>✅ Factory Reset Successful!</h1>";
    echo "<p>All database tables have been wiped and rebuilt with the exact columns PHP requires.</p>";
    echo "<a href='dashboard.php' style='display:inline-block; margin-top:20px; padding:10px 20px; background:#4f46e5; color:white; text-decoration:none; border-radius:5px;'>Return to Dashboard</a>";
    echo "</div>";

} catch (PDOException $e) {
    die("<h1 style='color:red;'>❌ Reset Failed:</h1> " . $e->getMessage());
}
?>