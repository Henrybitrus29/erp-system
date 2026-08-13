<?php
require 'db.php';

// 1. Catch the raw JSON payload from Make.com
$json_payload = file_get_contents('php://input');
$data = json_decode($json_payload, true);

// 2. Validate that the necessary data exists
if (isset($data['product_id']) && isset($data['quantity'])) {
    $product_id = (int)$data['product_id'];
    $quantity = (int)$data['quantity'];
    $supplier = $data['supplier'] ?? 'Telegram Automated Restock';

    try {
        $pdo->beginTransaction();

        // Fetch the current cost price to keep financial records accurate
        $stmt_get = $pdo->prepare("SELECT cost_price FROM products WHERE product_id = ?");
        $stmt_get->execute([$product_id]);
        $product = $stmt_get->fetch();
        
        $unit_price = $product ? $product['cost_price'] : 0.00;
        $total_cost = $unit_price * $quantity;

        // Log the automated purchase in the procurement table
        $stmt_proc = $pdo->prepare("INSERT INTO procurement (supplier, product_id, quantity, unit_price, total, status) VALUES (?, ?, ?, ?, ?, 'Completed')");
        $stmt_proc->execute([$supplier, $product_id, $quantity, $unit_price, $total_cost]);

        // Add the new stock to the inventory
        $stmt_update = $pdo->prepare("UPDATE products SET quantity = quantity + ? WHERE product_id = ?");
        $stmt_update->execute([$quantity, $product_id]);

        $pdo->commit();
        
        // Return a success signal to Make.com
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "Stock updated successfully"]);

    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid or missing JSON payload"]);
}
?>