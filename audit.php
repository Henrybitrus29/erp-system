<?php 
require 'db.php'; 
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: dashboard.php"); 
    exit; 
}
$pageTitle = 'Security Audit';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Audit - ERP SYSTEM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7fe; color: #1e293b; overflow-x: hidden; }
        .main-content { margin-left: 260px; padding: 30px; min-height: 100vh; }
        .ui-card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); border: 1px solid #f1f5f9; height: 100%; margin-bottom: 25px; }
        .card-title-custom { font-size: 1.1rem; font-weight: 700; margin-bottom: 25px; color: #1e293b; display: flex; justify-content: space-between; align-items: center; }
        .hash-font { font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; color: #64748b; word-break: break-all; }
        .table-custom { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        .table-custom th { color: #64748b; font-weight: 600; padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        .table-custom td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-weight: 500; vertical-align: middle; }
        .badge-verified { background: #dcfce7; color: #10b981; padding: 6px 12px; border-radius: 6px; font-weight: 700; font-size: 0.75rem; }
        .badge-tampered { background: #fee2e2; color: #ef4444; padding: 6px 12px; border-radius: 6px; font-weight: 700; font-size: 0.75rem; animation: pulse 2s infinite; }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); } 70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); } 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); } }
        .btn-sweep { background: #ef4444; color: white; border: none; padding: 8px 15px; border-radius: 8px; font-weight: 600; font-size: 0.85rem; transition: 0.2s; }
        .btn-sweep:hover { background: #dc2626; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <?php include 'header.php'; ?>

        <?php if (isset($_GET['msg'])): ?><div class="alert alert-success rounded-3 mb-3 fw-bold"><i class="fa-solid fa-shield-check me-2"></i> <?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>
        <?php if (isset($_GET['error'])): ?><div class="alert alert-danger rounded-3 mb-3 fw-bold"><i class="fa-solid fa-radiation me-2"></i> <?= htmlspecialchars($_GET['error']) ?></div><?php endif; ?>

        <div class="ui-card">
            <div class="card-title-custom">
                <div><i class="fa-solid fa-shield-halved text-primary me-2"></i> Cryptographic Ledger Verification</div>
                <form action="actions.php" method="POST" class="m-0">
                    <input type="hidden" name="action" value="run_audit_sweep">
                    <button type="submit" class="btn-sweep"><i class="fa-solid fa-radar me-1"></i> Run Security Sweep</button>
                </form>
            </div>
            
            <p class="text-muted small mb-4">Any unauthorized database modification will break the chain and flag the transaction. Running a Security Sweep will verify all hashes and alert all staff if tampering is found.</p>
            
            <div class="table-responsive">
                <table class="table-custom">
                    <thead><tr><th>Block (TXN)</th><th>Transaction Data</th><th>Stored Hash Signature</th><th>Integrity Status</th></tr></thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("SELECT s.*, p.product_name FROM sales s JOIN products p ON s.product_id = p.product_id ORDER BY s.sale_id ASC");
                        $sales = $stmt->fetchAll();
                        
                        if (count($sales) > 0) {
                            foreach ($sales as $row) {
                                $expected_data_string = $row['sale_id'] . $row['product_id'] . $row['employee_id'] . $row['quantity_sold'] . $row['total_amount'] . $row['previous_hash'];
                                $calculated_hash = hash('sha256', $expected_data_string);
                                $is_valid = ($calculated_hash === $row['hash_signature']);

                                $status_badge = $is_valid ? "<span class='badge-verified'><i class='fa-solid fa-check me-1'></i> Verified</span>" : "<span class='badge-tampered'><i class='fa-solid fa-triangle-exclamation me-1'></i> Tampered</span>";
                                $row_style = $is_valid ? "" : "background-color: #fef2f2;";

                                echo "<tr style='{$row_style}'>
                                        <td class='fw-bold'>TXN-" . str_pad($row['sale_id'], 4, '0', STR_PAD_LEFT) . "</td>
                                        <td><div class='small'>Qty: {$row['quantity_sold']} x {$row['product_name']}</div><div class='small fw-bold'>Total: ₦{$row['total_amount']}</div></td>
                                        <td class='hash-font'><div>PREV: " . substr($row['previous_hash'], 0, 20) . "...</div><div class='text-dark fw-bold'>CURR: " . substr($row['hash_signature'], 0, 20) . "...</div></td>
                                        <td>{$status_badge}</td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' class='text-center text-muted'>No transactions to audit.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
