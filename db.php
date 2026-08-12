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

    // MAKE STRICT COLUMNS FORGIVING SO RUN_AI.PHP CAN'T CRASH THEM
    try { $pdo->exec("ALTER TABLE ai_insights MODIFY insight_type VARCHAR(50) NULL DEFAULT 'General'"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE ai_insights MODIFY product_name VARCHAR(255) NULL DEFAULT 'Unknown'"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE ai_insights MODIFY recommendation TEXT NULL"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE ai_insights MODIFY suggestion TEXT NULL"); } catch (PDOException $e) {}
    try { $pdo->exec("ALTER TABLE ai_insights MODIFY explanation TEXT NULL"); } catch (PDOException $e) {}

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>