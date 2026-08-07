<?php
// api_webhook.php - Secure endpoint for n8n to trigger Restock Orders
require 'db.php';

// Force JSON response header
header('Content-Type: application/json');

// 1. Security Check: Validate API Key from n8n
$headers = apache_request_headers();
$auth_header = $headers['Authorization'] ?? '';

// Using a custom secure key for the agency infrastructure
if ($auth_header !== 'Bearer CHIDAMA-TECH-KEY-2026') {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized Access']);
    exit;
}

// 2. Read incoming JSON payload from n8n
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['product_id']) || !isset($data['quantity'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing product_id or quantity']);
    exit;
}

$product_id = (int)$data['product_id'];
$restock_qty = (int)$data['quantity'];
$supplier = $data['supplier'] ?? 'Automated WhatsApp Order';

try {
    $pdo->beginTransaction();

    // 3. Get the product's cost price to calculate the total procurement cost
    $stmt = $pdo->prepare("SELECT product_name, cost_price FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        throw new Exception("Product not found");
    }

    $total_cost = $product['cost_price'] * $restock_qty;

    // 4. Log the purchase into the Procurement table
    $stmt_proc = $pdo->prepare("INSERT INTO procurement (supplier, product_id, quantity, unit_price, total, status) VALUES (?, ?, ?, ?, ?, 'Completed')");
    $stmt_proc->execute([$supplier, $product_id, $restock_qty, $product['cost_price'], $total_cost]);

    // 5. Instantly update the Inventory
    $stmt_inv = $pdo->prepare("UPDATE products SET quantity = quantity + ? WHERE product_id = ?");
    $stmt_inv->execute([$restock_qty, $product_id]);

    $pdo->commit();

    // 6. Send success confirmation back to n8n
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => "Successfully restocked {$restock_qty} units of {$product['product_name']}",
        'new_total_cost' => $total_cost
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
