<?php
$host = 'sql208.infinityfree.com';
$db   = 'if0_42627768_erp';
$user = 'if0_42627768';
$pass = 'PASTE_YOUR_COPIED_PASSWORD_HERE'; // Replace this with your actual InfinityFree password!

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>