<?php
require 'db.php';
session_start();

// 1. Ensure required database tables and columns exist
$pdo->exec("CREATE TABLE IF NOT EXISTS procurement (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier VARCHAR(255) NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    total DECIMAL(12,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'Completed',
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$pdo->exec("ALTER TABLE sales 
    ADD COLUMN IF NOT EXISTS previous_hash VARCHAR(64) NULL,
    ADD COLUMN IF NOT EXISTS hash_signature VARCHAR(64) NULL
");

$pdo->exec("CREATE TABLE IF NOT EXISTS tamper_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id VARCHAR(50),
    invalid_hash VARCHAR(64),
    issue_detected VARCHAR(255),
    logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(transaction_id, invalid_hash)
)");

// 2. Helper function to trigger n8n webhooks
function triggerN8nWebhook($payload, $webhook_url = 'http://localhost:5678/webhook/erp-alerts') {
    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1500);
    curl_exec($ch);
    curl_close($ch);
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// LOGIN ACTION
if ($action === 'login') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->execute([$_POST['username'], $_POST['password']]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role']; 
        $_SESSION['employee_id'] = $user['employee_id']; 
        header("Location: dashboard.php");
    } else {
        header("Location: index.php?error=Invalid username or password");
    }
    exit;
}

// EMPLOYEE ACTIONS (Admin Only)
if ($action === 'add_employee' && $_SESSION['role'] === 'Admin') {
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO employees (name, gender, department, phone_number) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['name'], $_POST['gender'], $_POST['department'], $_POST['phone']]);
        $emp_id = $pdo->lastInsertId();

        $username = strtolower(str_replace(' ', '', $_POST['name'])); 
        $password = 'password123'; 
        $stmt2 = $pdo->prepare("INSERT INTO users (username, password, role, employee_id) VALUES (?, ?, 'Employee', ?)");
        $stmt2->execute([$username, $password, $emp_id]);

        $pdo->commit();
        header("Location: employees.php?msg=Employee & Login Account Created");
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: employees.php?error=Failed to register employee");
    }
    exit;
}

if ($action === 'edit_employee' && $_SESSION['role'] === 'Admin') {
    $stmt = $pdo->prepare("UPDATE employees SET name = ?, gender = ?, department = ?, phone_number = ? WHERE employee_id = ?");
    $stmt->execute([$_POST['name'], $_POST['gender'], $_POST['department'], $_POST['phone'], $_POST['employee_id']]);
    header("Location: employees.php?msg=Employee Details Updated");
    exit;
}

if ($action === 'delete_employee' && $_SESSION['role'] === 'Admin') {
    $pdo->beginTransaction();
    try {
        $stmt1 = $pdo->prepare("DELETE FROM users WHERE employee_id = ?");
        $stmt1->execute([$_POST['employee_id']]);
        $stmt2 = $pdo->prepare("DELETE FROM employees WHERE employee_id = ?");
        $stmt2->execute([$_POST['employee_id']]);
        $pdo->commit();
        header("Location: employees.php?msg=Employee Removed");
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: employees.php?error=Deletion failed");
    }
    exit;
}

// PRODUCT ACTIONS (Admin Only)
if ($action === 'add_product' && $_SESSION['role'] === 'Admin') {
    $stmt = $pdo->prepare("INSERT INTO products (product_name, quantity, cost_price, selling_price) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$_POST['product_name'], $_POST['quantity'], $_POST['cost_price'], $_POST['selling_price']])) {
        header("Location: inventory.php?msg=Product Added Successfully");
    } else {
        header("Location: inventory.php?error=Failed to add product");
    }
    exit;
}

if ($action === 'edit_product' && $_SESSION['role'] === 'Admin') {
    $stmt = $pdo->prepare("UPDATE products SET product_name = ?, quantity = ?, cost_price = ?, selling_price = ? WHERE product_id = ?");
    if ($stmt->execute([$_POST['product_name'], $_POST['quantity'], $_POST['cost_price'], $_POST['selling_price'], $_POST['product_id']])) {
        header("Location: inventory.php?msg=Product Updated Successfully");
    } else {
        header("Location: inventory.php?error=Failed to update product");
    }
    exit;
}

if ($action === 'delete_product' && $_SESSION['role'] === 'Admin') {
    $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
    if ($stmt->execute([$_POST['product_id']])) {
        header("Location: inventory.php?msg=Product Deleted Successfully");
    } else {
        header("Location: inventory.php?error=Failed to delete product");
    }
    exit;
}

// PROCESS CART WITH SHA-256 HASHING & WEBHOOKS
if ($action === 'process_cart' && $_SESSION['role'] === 'Employee') {
    $cart_data = json_decode($_POST['cart_data'], true);
    if (empty($cart_data)) { header("Location: sales.php?error=Cart is empty"); exit; }

    $pdo->beginTransaction();
    try {
        foreach ($cart_data as $item) {
            $pid = $item['id'];
            $qty = $item['qty'];

            $stmt = $pdo->prepare("SELECT product_name, cost_price, selling_price, quantity FROM products WHERE product_id = ? FOR UPDATE");
            $stmt->execute([$pid]);
            $product = $stmt->fetch();

            if ($product && $product['quantity'] >= $qty) {
                $total_amount = $product['selling_price'] * $qty;
                $total_profit = ($product['selling_price'] - $product['cost_price']) * $qty;

                $stmt_last = $pdo->query("SELECT hash_signature FROM sales ORDER BY sale_id DESC LIMIT 1");
                $last_sale = $stmt_last->fetch();
                $previous_hash = $last_sale ? $last_sale['hash_signature'] : 'GENESIS_BLOCK';

                $stmt2 = $pdo->prepare("INSERT INTO sales (product_id, employee_id, quantity_sold, total_amount, profit, previous_hash) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt2->execute([$pid, $_SESSION['employee_id'], $qty, $total_amount, $total_profit, $previous_hash]);
                $new_sale_id = $pdo->lastInsertId();

                $data_string = $new_sale_id . $pid . $_SESSION['employee_id'] . $qty . $total_amount . $previous_hash;
                $current_hash = hash('sha256', $data_string);

                $stmt_hash = $pdo->prepare("UPDATE sales SET hash_signature = ? WHERE sale_id = ?");
                $stmt_hash->execute([$current_hash, $new_sale_id]);

                $new_qty = $product['quantity'] - $qty;
                $stmt3 = $pdo->prepare("UPDATE products SET quantity = ? WHERE product_id = ?");
                $stmt3->execute([$new_qty, $pid]);

                if ($new_qty <= 5) {
                    $payload = [
                        'event' => 'low_stock_alert',
                        'product_id' => $pid,
                        'product_name' => $product['product_name'],
                        'remaining_qty' => $new_qty,
                        'timestamp' => date('c')
                    ];
                    triggerN8nWebhook($payload);
                }

            } else { throw new Exception("Insufficient stock for one or more items."); }
        }
        $pdo->commit();
        header("Location: sales.php?msg=Multiple Items Processed Successfully");
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: sales.php?error=" . urlencode($e->getMessage()));
    }
    exit;
}

// SAFE VOID SALE
if ($action === 'void_sale' && $_SESSION['role'] === 'Admin') {
    $void_target_id = $_POST['sale_id'];
    
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("SELECT * FROM sales WHERE sale_id = ?");
        $stmt->execute([$void_target_id]);
        $original_sale = $stmt->fetch();
        
        if (!$original_sale) throw new Exception("Sale not found.");
        
        $void_qty = -$original_sale['quantity_sold'];
        $void_total = -$original_sale['total_amount'];
        $void_profit = -$original_sale['profit'];
        
        $stmt_last = $pdo->query("SELECT hash_signature FROM sales ORDER BY sale_id DESC LIMIT 1");
        $last_sale = $stmt_last->fetch();
        $previous_hash = $last_sale ? $last_sale['hash_signature'] : 'GENESIS_BLOCK';

        $stmt_insert = $pdo->prepare("INSERT INTO sales (product_id, employee_id, quantity_sold, total_amount, profit, previous_hash) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_insert->execute([$original_sale['product_id'], $_SESSION['employee_id'], $void_qty, $void_total, $void_profit, $previous_hash]);
        $new_sale_id = $pdo->lastInsertId();

        $data_string = $new_sale_id . $original_sale['product_id'] . $_SESSION['employee_id'] . $void_qty . $void_total . $previous_hash;
        $current_hash = hash('sha256', $data_string);

        $stmt_hash = $pdo->prepare("UPDATE sales SET hash_signature = ? WHERE sale_id = ?");
        $stmt_hash->execute([$current_hash, $new_sale_id]);
        
        $stmt_inv = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE product_id = ?");
        $stmt_inv->execute([$void_qty, $original_sale['product_id']]);
        
        $pdo->commit();
        header("Location: reports.php?msg=Sale Successfully Voided (Ledger Updated)");
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: reports.php?error=Failed to void sale: " . urlencode($e->getMessage()));
    }
    exit;
}

// RUN AUDIT SWEEP
if ($action === 'run_audit_sweep') {
    if (empty($_SESSION['role']) && $_SERVER['REMOTE_ADDR'] !== '127.0.0.1' && $_SERVER['REMOTE_ADDR'] !== '::1') {
        header("Location: index.php");
        exit;
    }

    $stmt = $pdo->query("SELECT * FROM sales ORDER BY sale_id ASC");
    $sales = $stmt->fetchAll();
    $tampered_transactions = [];
    $new_tampers_found = false;

    foreach ($sales as $row) {
        $expected_data = $row['sale_id'] . $row['product_id'] . $row['employee_id'] . $row['quantity_sold'] . $row['total_amount'] . $row['previous_hash'];
        $calculated_hash = hash('sha256', $expected_data);
        
        if ($calculated_hash !== $row['hash_signature']) {
            $txn_name = "TXN-" . str_pad($row['sale_id'], 4, '0', STR_PAD_LEFT);
            $tampered_transactions[] = $txn_name;

            try {
                $log_stmt = $pdo->prepare("INSERT INTO tamper_logs (transaction_id, invalid_hash, issue_detected) VALUES (?, ?, ?)");
                $log_stmt->execute([$txn_name, $calculated_hash, "Cryptographic hash mismatch"]);
                $new_tampers_found = true; 
            } catch (PDOException $e) {}
        }
    }

    if ($new_tampers_found && count($tampered_transactions) > 0) {
        $payload = [
            'event' => 'critical_tamper_alert',
            'broadcast_all' => true,
            'tampered_txns' => implode(", ", $tampered_transactions),
            'timestamp' => date('c'),
            'trigger_ip' => $_SERVER['REMOTE_ADDR']
        ];
        triggerN8nWebhook($payload, 'http://localhost:5678/webhook/security-alerts');
    }

    if (!empty($_SESSION['role'])) {
        if (count($tampered_transactions) > 0) {
            header("Location: audit.php?error=CRITICAL: Tampering detected! Broadcast alert sent to all staff.");
        } else {
            header("Location: audit.php?msg=Audit Sweep Complete: Ledger is 100% secure.");
        }
    } else {
        echo "Watchdog sweep completed successfully.";
    }
    exit;
}

// PROCUREMENT ACTION
if ($action === 'add_purchase') {
    $supplier = $_POST['supplier'];
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $unit_price = $_POST['unit_price'];
    $status = $_POST['status'];
    $total = $quantity * $unit_price;

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO procurement (supplier, product_id, quantity, unit_price, total, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$supplier, $product_id, $quantity, $unit_price, $total, $status]);
        
        if ($status === 'Completed') {
            $stmt2 = $pdo->prepare("UPDATE products SET quantity = quantity + ? WHERE product_id = ?");
            $stmt2->execute([$quantity, $product_id]);
        }
        
        $pdo->commit();
        header("Location: procurement.php?msg=Purchase Order Recorded");
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: procurement.php?error=Failed to record purchase");
    }
    exit;
}

// AI INSTANT RESTOCK ACTION
if ($action === 'ai_instant_restock' && $_SESSION['role'] === 'Admin') {
    $pdo->beginTransaction();
    try {
        $product_id = $_POST['product_id'];
        $quantity_to_add = $_POST['quantity'];

        $stmt = $pdo->prepare("UPDATE products SET quantity = quantity + ? WHERE product_id = ?");
        $stmt->execute([$quantity_to_add, $product_id]);

        $pdo->commit();
        header("Location: run_ai.php"); 
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: dashboard.php?error=Failed to restock product via AI");
    }
    exit;
}

// AI PROFIT INCREASE ACTION
if ($action === 'ai_profit_increase' && $_SESSION['role'] === 'Admin') {
    $pdo->beginTransaction();
    try {
        $product_id = $_POST['product_id'];
        
        // Mathematically increase the selling price by exactly 5%
        $stmt = $pdo->prepare("UPDATE products SET selling_price = selling_price * 1.05 WHERE product_id = ?");
        $stmt->execute([$product_id]);

        $pdo->commit();
        // Rerun the AI instantly so the Opportunity card disappears from the dashboard
        header("Location: run_ai.php"); 
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: dashboard.php?error=Failed to update product pricing");
    }
    exit;
}

// CHANGE PASSWORD ACTION
if ($action === 'change_password') {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    
    if ($new !== $confirm) {
        header("Location: settings.php?error=New passwords do not match.");
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if ($user && $user['password'] === $current) {
        $stmt2 = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt2->execute([$new, $_SESSION['user_id']]);
        header("Location: settings.php?msg=Password updated successfully.");
    } else {
        header("Location: settings.php?error=Incorrect current password.");
    }
    exit;
}

// LOGOUT
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

header("Location: index.php");
exit;
?>