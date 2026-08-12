<?php 
require 'db.php'; 
session_start();
// Security Redirect if Employee tries to access
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: dashboard.php"); 
    exit; 
}
$pageTitle = 'Procurement';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procurement - ERP SYSTEM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7fe; color: #1e293b; overflow-x: hidden; }
        .main-content { margin-left: 260px; padding: 30px; min-height: 100vh; }
        .page-actions-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .search-bar-custom { background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 15px; display: flex; align-items: center; gap: 10px; width: 300px; }
        .search-bar-custom input { border: none; outline: none; width: 100%; font-size: 0.9rem; }
        .btn-new-purchase { background: #6366f1; color: white; border: none; padding: 8px 20px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; }
        .ui-card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); border: 1px solid #f1f5f9; height: 100%; }
        .card-title-custom { font-size: 1.1rem; font-weight: 700; margin-bottom: 25px; color: #1e293b; }
        .form-label { font-size: 0.85rem; font-weight: 600; color: #64748b; margin-bottom: 5px; }
        .form-control, .form-select { background: #f8fafc; border: 1px solid #e2e8f0; font-size: 0.9rem; padding: 10px 15px; border-radius: 8px; margin-bottom: 15px; }
        .form-control:focus, .form-select:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
        .btn-purple-full { background: #6366f1; color: white; width: 100%; padding: 12px; border-radius: 8px; font-weight: 600; border: none; margin-top: 10px; transition: 0.2s; }
        .btn-purple-full:hover { background: #4f46e5; }
        .total-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; display: flex; justify-content: space-between; align-items: center; font-weight: 700; margin-top: 10px; margin-bottom: 15px; }
        .table-procurement { width: 100%; font-size: 0.85rem; }
        .table-procurement th { color: #64748b; font-weight: 600; padding-bottom: 15px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        .table-procurement td { padding: 15px 0; border-bottom: 1px solid #f1f5f9; font-weight: 500; }
        .badge-status { padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; display: inline-block; }
        .status-completed { background: #dcfce7; color: #10b981; }
        .status-pending { background: #fef3c7; color: #f59e0b; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <?php include 'header.php'; ?>
        <?php if (isset($_GET['msg'])): ?><div class="alert alert-success rounded-3 mb-3"><?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>
        <?php if (isset($_GET['error'])): ?><div class="alert alert-danger rounded-3 mb-3"><?= htmlspecialchars($_GET['error']) ?></div><?php endif; ?>
        <div class="page-actions-top">
            <div class="search-bar-custom"><i class="fa-solid fa-search text-muted"></i><input type="text" placeholder="Search supplier..."></div>
            <button class="btn-new-purchase" onclick="document.getElementById('supplier').focus();"><i class="fa-solid fa-plus me-1"></i> New Purchase</button>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="ui-card">
                    <h4 class="card-title-custom">New Purchase Order</h4>
                    <form action="actions.php" method="POST">
                        <input type="hidden" name="action" value="add_purchase">
                        <label class="form-label">Supplier</label>
                        <input type="text" name="supplier" id="supplier" class="form-control" placeholder="E.g. ABC Supplies" required>
                        <label class="form-label">Product</label>
                        <select name="product_id" class="form-select" required>
                            <option value="">Select Product to Restock</option>
                            <?php
                            $catalog = $pdo->query("SELECT * FROM products ORDER BY product_name ASC")->fetchAll();
                            foreach ($catalog as $p) {
                                echo "<option value='{$p['product_id']}'>{$p['product_name']} (Current Stock: {$p['quantity']})</option>";
                            }
                            ?>
                        </select>
                        <div class="row">
                            <div class="col-6"><label class="form-label">Quantity</label><input type="number" name="quantity" id="qty" class="form-control" placeholder="0" required oninput="calcTotal()"></div>
                            <div class="col-6"><label class="form-label">Unit Price (₦)</label><input type="number" step="0.01" name="unit_price" id="price" class="form-control" placeholder="0.00" required oninput="calcTotal()"></div>
                        </div>
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="Completed">Completed (Adds to Stock)</option>
                            <option value="Pending">Pending (Awaiting Delivery)</option>
                        </select>
                        <div class="total-box"><span class="text-muted">Total Cost</span><span class="text-dark" id="displayTotal">₦0.00</span></div>
                        <button type="submit" class="btn-purple-full">Record Purchase</button>
                    </form>
                </div>
            </div>
            <div class="col-md-8">
                <div class="ui-card">
                    <h4 class="card-title-custom">Procurement Log</h4>
                    <div class="table-responsive">
                        <table class="table-procurement">
                            <thead><tr><th>Date</th><th>Supplier</th><th>Product</th><th>Qty</th><th>Total</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php
                                $procurements = $pdo->query("SELECT pr.*, p.product_name FROM procurement pr JOIN products p ON pr.product_id = p.product_id ORDER BY pr.id DESC")->fetchAll();
                                if (count($procurements) > 0) {
                                    foreach ($procurements as $row) {
                                        $date = date('d/m/Y', strtotime($row['date_added']));
                                        $statusClass = $row['status'] === 'Completed' ? 'status-completed' : 'status-pending';
                                        echo "<tr><td class='text-muted small'>{$date}</td><td class='fw-semibold'>".htmlspecialchars($row['supplier'])."</td><td>{$row['product_name']}</td><td>{$row['quantity']}</td><td class='fw-bold text-dark'>₦".number_format($row['total'], 2)."</td><td><span class='badge-status {$statusClass}'>{$row['status']}</span></td></tr>";
                                    }
                                } else { echo "<tr><td colspan='6' class='text-center text-muted py-4'>No procurement records found.</td></tr>"; }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        function calcTotal() {
            let qty = document.getElementById('qty').value || 0;
            let price = document.getElementById('price').value || 0;
            document.getElementById('displayTotal').innerText = '₦' + (qty * price).toLocaleString('en-US', {minimumFractionDigits: 2});
        }
    </script>
</body>
</html>
