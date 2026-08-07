<?php
require 'db.php';

$pdo->exec("TRUNCATE TABLE ai_insights");

$stmt = $pdo->query("
    SELECT 
        p.product_id, 
        p.product_name, 
        p.quantity as current_stock,
        COALESCE(SUM(s.quantity_sold), 0) as total_sold_30_days
    FROM products p
    LEFT JOIN sales s ON p.product_id = s.product_id
    GROUP BY p.product_id
");
$products = $stmt->fetchAll();

foreach ($products as $row) {
    $daily_sales_avg = $row['total_sold_30_days'] / 30;
    
    $payload = [
        'product_name' => $row['product_name'],
        'current_stock' => $row['current_stock'],
        'daily_sales' => $daily_sales_avg
    ];

    $ch = curl_init('http://127.0.0.1:5000/analyze');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);

    $ai_result = json_decode($response, true);

    // SMARTER FILTER: Only save to database if the AI says it is an active alert!
    if ($ai_result && isset($ai_result['status']) && $ai_result['status'] === 'alert') {
        $insert = $pdo->prepare("INSERT INTO ai_insights (product_id, product_name, suggestion, explanation) VALUES (?, ?, ?, ?)");
        $insert->execute([
            $row['product_id'], 
            $row['product_name'], 
            $ai_result['suggestion'], 
            $ai_result['explanation']
        ]);
    }
}
header("Location: dashboard.php");
exit;
?>
