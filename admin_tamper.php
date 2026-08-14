<?php
// 1. Connect to the database
require 'db.php'; 

$success_message = "";

// 2. Listen for the Admin clicking 'Update & Alert'
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tamper_sale'])) {
    $sale_id_val = $_POST['sale_id'];
    $new_quantity = $_POST['quantity'];
    $new_price = $_POST['total_price'];

    // Fetch the old record first to log the exact change
    $stmt = $pdo->prepare("SELECT * FROM sales WHERE sale_id = ?");
    $stmt->execute([$sale_id_val]);
    $old_sale = $stmt->fetch(PDO::FETCH_ASSOC);

    // 3. Update the database with the tampered numbers
    $update_stmt = $pdo->prepare("UPDATE sales SET quantity = ?, total_price = ? WHERE sale_id = ?");
    $update_stmt->execute([$new_quantity, $new_price, $sale_id_val]);

    // 4. Construct the Telegram Tamper Alert Message
    $telegram_message = "🚨 *ADMIN TAMPER ALERT!* 🚨\n\n";
    $telegram_message .= "A record in the Sales database was manually modified.\n\n";
    $telegram_message .= "Sale ID: " . $sale_id_val . "\n";
    $telegram_message .= "Product ID: " . ($old_sale['product_id'] ?? 'N/A') . "\n";
    $telegram_message .= "Quantity Changed: " . $old_sale['quantity'] . " ➡️ " . $new_quantity . "\n";
    $telegram_message .= "Price Changed: " . $old_sale['total_price'] . " ➡️ " . $new_price . "\n\n";
    $telegram_message .= "Timestamp: " . date('Y-m-d H:i:s');

    // 5. Fire the Webhook to Make.com
    $webhook_url = "https://hook.eu1.make.com/d858n8olj9732sfpbg213vua6qoqscml"; 
    
    $data = json_encode([
        "telegram_message" => $telegram_message,
        "event" => "tamper_alert"
    ]);

    // cURL configuration to send the HTTP POST request silently
    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_exec($ch);
    curl_close($ch);

    $success_message = "Database updated successfully! Telegram alert sent.";
}

// 6. Fetch the latest sales to display in the table
$sales_query = $pdo->query("SELECT * FROM sales ORDER BY sale_id DESC LIMIT 50");
$sales = $sales_query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Tamper Panel</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; padding: 20px; }
        h2 { color: #d9534f; }
        .success { color: #155724; font-weight: bold; padding: 12px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #333; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        input[type="number"] { width: 90px; padding: 6px; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 8px 14px; background-color: #d9534f; color: white; border: none; cursor: pointer; border-radius: 4px; font-weight: bold; }
        button:hover { background-color: #c9302c; }
    </style>
</head>
<body>

    <h2>🚨 Admin Override: Sales Database</h2>
    
    <?php if (!empty($success_message)): ?>
        <div class="success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Sale ID</th>
                <th>Product ID</th>
                <th>Quantity</th>
                <th>Total Price</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($sales)): ?>
                <?php foreach ($sales as $sale): ?>
                    <tr>
                        <form method="POST" action="admin_tamper.php">
                            <td>
                                <?= htmlspecialchars($sale['sale_id']) ?>
                                <input type="hidden" name="sale_id" value="<?= htmlspecialchars($sale['sale_id']) ?>">
                            </td>
                            <td><?= htmlspecialchars($sale['product_id']) ?></td>
                            <td>
                                <input type="number" name="quantity" value="<?= htmlspecialchars($sale['quantity_sold'] ?? $sale['quantity']) ?>" required>
                            </td>
                            <td>
                                <input type="number" name="total_price" step="0.01" value="<?= htmlspecialchars($sale['total_amount'] ?? $sale['total_price']) ?>" required>
                            </td>
                            <td>
                                <button type="submit" name="tamper_sale">Update & Alert</button>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center;">No sales records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>