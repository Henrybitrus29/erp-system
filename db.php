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

    // 1. PRODUCTS TABLE PATCH
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (product_id INT AUTO_INCREMENT PRIMARY KEY, product_name VARCHAR(255) NOT NULL, quantity INT NOT NULL DEFAULT 0)");
    try { $pdo->exec("ALTER TABLE products ADD COLUMN cost_price DECIMAL(12,2) NOT NULL DEFAULT 0.00"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE products ADD COLUMN selling_price DECIMAL(12,2) NOT NULL DEFAULT 0.00"); } catch (PDOException $e) {}

    // 2. EMPLOYEES TABLE PATCH
    $pdo->exec("CREATE TABLE IF NOT EXISTS employees (employee_id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL)");
    try { $pdo->exec("ALTER TABLE employees ADD COLUMN gender VARCHAR(50)"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE employees ADD COLUMN department VARCHAR(100)"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE employees ADD COLUMN phone_number VARCHAR(50)"); } catch (PDOException $e) {}

    // 3. SALES TABLE PATCH
    $pdo->exec("CREATE TABLE IF NOT EXISTS sales (sale_id INT AUTO_INCREMENT PRIMARY KEY, product_id INT NOT NULL, quantity_sold INT NOT NULL DEFAULT 0)");
    try { $pdo->exec("ALTER TABLE sales CHANGE total_price total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE sales CHANGE quantity quantity_sold INT NOT NULL DEFAULT 0"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE sales CHANGE created_at sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE sales ADD COLUMN total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE sales ADD COLUMN profit DECIMAL(12,2) NOT NULL DEFAULT 0.00"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE sales ADD COLUMN employee_id INT NULL"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE sales ADD COLUMN previous_hash VARCHAR(64) NULL"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE sales ADD COLUMN hash_signature VARCHAR(64) NULL"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE sales ADD COLUMN sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP"); } catch (PDOException $e) {}

    // 4. PROCUREMENT TABLE SMART-NUKE
    try {
        // Test if the perfect 'id' column exists
        $pdo->query("SELECT id FROM procurement LIMIT 1");
    } catch (PDOException $e) {
        // If it fails, the table is old/broken. Destroy and rebuild it flawlessly.
        $pdo->exec("DROP TABLE IF EXISTS procurement");
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
    }

    // 5. DROP CONFLICTING PYTHON TRIGGERS
    try { $pdo->exec("DROP TRIGGER IF EXISTS detect_sales_update"); } catch (PDOException $e) {}
    try { $pdo->exec("DROP TRIGGER IF EXISTS detect_sales_delete"); } catch (PDOException $e) {}

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>