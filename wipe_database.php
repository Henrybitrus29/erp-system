<?php
require 'db.php';

try {
    // 1. Disable constraints to force-drop everything without errors
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // 2. NUKE THE OLD DATABASE TABLES
    $pdo->exec("DROP TABLE IF EXISTS sales");
    $pdo->exec("DROP TABLE IF EXISTS products");
    $pdo->exec("DROP TABLE IF EXISTS employees");
    $pdo->exec("DROP TABLE IF EXISTS procurement");
    $pdo->exec("DROP TABLE IF EXISTS tamper_logs");
    $pdo->exec("DROP TABLE IF EXISTS users");

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // 3. REBUILD TABLES WITH FLAWLESS SCHEMA
    $pdo->exec("CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(50) NOT NULL DEFAULT 'Admin',
        employee_id INT NULL
    )");

    $pdo->exec("CREATE TABLE employees (
        employee_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        gender VARCHAR(50),
        department VARCHAR(100),
        phone_number VARCHAR(50)
    )");

    // This creates the selling_price column that was missing
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

    // 4. INJECT DEFAULT ADMIN ACCOUNT
    $pdo->exec("INSERT INTO users (username, password, role) VALUES ('admin', 'admin123', 'Admin')");

    echo "<div style='font-family:sans-serif; text-align:center; padding:50px; background:#f8fafc; height:100vh;'>";
    echo "<h1 style='color:#16a34a; font-size: 2rem; margin-bottom: 10px;'>✅ Complete Database Wipe & Rebuild Successful!</h1>";
    echo "<p style='color:#475569; margin-bottom: 30px;'>All broken tables were destroyed. Fresh tables with the correct schema have been generated.</p>";
    echo "<a href='index.php' style='display:inline-block; padding:12px 24px; background:#4f46e5; color:white; text-decoration:none; border-radius:8px; font-weight:bold;'>Click Here to Login</a>";
    echo "</div>";

} catch (PDOException $e) {
    die("<div style='font-family:sans-serif; text-align:center; padding:50px;'><h1 style='color:red;'>❌ Critical Error:</h1><p>" . $e->getMessage() . "</p></div>");
}
?>