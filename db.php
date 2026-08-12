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

    // SILENTLY ADD THE FINAL MISSING AI COLUMN
    try { $pdo->exec("ALTER TABLE ai_insights ADD COLUMN explanation TEXT"); } catch (PDOException $e) {}

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>