<?php
require 'db.php';

echo "Recalculating Cryptographic Signatures for Existing Sales...\n";

// Fetch all sales ordered by sale_id ASC to build the hash chain sequentially
$stmt = $pdo->query("SELECT * FROM sales ORDER BY sale_id ASC");
$sales = $stmt->fetchAll();

$previous_hash = 'GENESIS_BLOCK';

foreach ($sales as $row) {
    $sale_id = $row['sale_id'];
    $pid = $row['product_id'];
    $emp_id = $row['employee_id'];
    $qty = $row['quantity_sold'];
    $total = $row['total_amount'];

    // 1. Generate the SHA-256 hash string
    $data_string = $sale_id . $pid . $emp_id . $qty . $total . $previous_hash;
    $current_hash = hash('sha256', $data_string);

    // 2. Update the row with its previous_hash and hash_signature
    $update_stmt = $pdo->prepare("UPDATE sales SET previous_hash = ?, hash_signature = ? WHERE sale_id = ?");
    $update_stmt->execute([$previous_hash, $current_hash, $sale_id]);

    echo "Signed TXN-" . str_pad($sale_id, 4, '0', STR_PAD_LEFT) . " -> Hash: " . substr($current_hash, 0, 15) . "...\n";

    // 3. Pass current hash to the next block
    $previous_hash = $current_hash;
}

echo "Cryptographic Ledger Repair Complete!\n";
?>
