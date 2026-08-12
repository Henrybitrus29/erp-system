<?php 
require 'db.php'; 
session_start();
if (!isset($_SESSION['loggedin'])) { header("Location: index.php"); exit; }
$pageTitle = 'Reports';

$employee_id = $_SESSION['employee_id'];
$role = $_SESSION['role'];

// Filter metrics based on Role
if ($role === 'Admin') {
    $total_sales = $pdo->query("SELECT SUM(total_amount) FROM sales")->fetchColumn();
    $total_profit = $pdo->query("SELECT SUM(profit) FROM sales")->fetchColumn();
} else {
    $stmt1 = $pdo->prepare("SELECT SUM(total_amount) FROM sales WHERE employee_id = ?");
    $stmt1->execute([$employee_id]);
    $total_sales = $stmt1->fetchColumn();

    $stmt2 = $pdo->prepare("SELECT SUM(profit) FROM sales WHERE employee_id = ?");
    $stmt2->execute([$employee_id]);
    $total_profit = $stmt2->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - ERP SYSTEM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7fe; color: #1e293b; overflow-x: hidden; }
        .main-content { margin-left: 260px; padding: 30px; min-height: 100vh; }
        .ui-card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); border: 1px solid #f1f5f9; height: 100%; margin-bottom: 25px; }
        .card-title-custom { font-size: 1.1rem; font-weight: 700; margin-bottom: 25px; color: #1e293b; }
        .table-custom { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        .table-custom th { color: #64748b; font-weight: 600; padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        .table-custom td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-weight: 500; }
        
        .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-print { background: #4f46e5; color: white; border: none; padding: 8px 20px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; transition: 0.2s; }
        .btn-print:hover { background: #4338ca; }

        /* Print-specific styling to make the report look like a clean document */
        @media print {
            body { background-color: white !important; color: black !important; }
            .sidebar, .top-header, .btn-print, form, .alert { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 0 !important; width: 100% !important; }
            .ui-card { box-shadow: none !important; border: 1px solid #cbd5e1 !important; margin-bottom: 20px !important; page-break-inside: avoid; }
            .table-custom th { color: black !important; border-bottom: 2px solid #000 !important; }
            .table-custom td { border-bottom: 1px solid #cbd5e1 !important; }
            .text-success { color: #000 !important; }
            .text-primary { color: #000 !important; }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <?php include 'header.php'; ?>
        
        <div class="header-actions">
            <div>
                <h3 class="fw-bold m-0 text-dark"><?= $role === 'Admin' ? 'Master Sales Report' : 'My Sales Report' ?></h3>
                <p class="text-muted small m-0 mt-1">Generated on <?= date('d M, Y h:i A') ?></p>
            </div>
            <button class="btn-print" onclick="window.print()"><i class="fa-solid fa-print me-2"></i> Print Document</button>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6"><div class="ui-card"><h4 class="card-title-custom">Total Revenue</h4><h2 class="text-success fw-bold">₦<?= number_format($total_sales ?? 0, 2) ?></h2></div></div>
            <?php if ($role === 'Admin'): ?>
                <div class="col-md-6"><div class="ui-card"><h4 class="card-title-custom">Total Profit</h4><h2 class="text-primary fw-bold">₦<?= number_format($total_profit ?? 0, 2) ?></h2></div></div>
            <?php endif; ?>
        </div>

        <div class="ui-card">
            <h4 class="card-title-custom">Sales Audit Log</h4>
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>TXN ID</th>
                            <th>Product</th>
                            <?php if ($role === 'Admin'): ?>
                                <th>Sold By</th>
                            <?php endif; ?>
                            <th>Qty</th>
                            <th>Revenue</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Filter table data based on Role
                        if ($role === 'Admin') {
                            $query = "SELECT s.*, p.product_name, e.name AS employee_name FROM sales s JOIN products p ON s.product_id = p.product_id JOIN employees e ON s.employee_id = e.employee_id ORDER BY s.sale_id DESC";
                            $all_sales = $pdo->query($query)->fetchAll();
                        } else {
                            $stmt3 = $pdo->prepare("SELECT s.*, p.product_name FROM sales s JOIN products p ON s.product_id = p.product_id WHERE s.employee_id = ? ORDER BY s.sale_id DESC");
                            $stmt3->execute([$employee_id]);
                            $all_sales = $stmt3->fetchAll();
                        }
                        
                        if (count($all_sales) > 0) {
                            foreach ($all_sales as $row) {
                                $txn = 'TXN-' . str_pad($row['sale_id'], 4, '0', STR_PAD_LEFT);
                                $date = date('d/m/Y H:i', strtotime($row['sale_date']));
                                
                                echo "<tr>
                                        <td class='text-muted fw-bold'>{$txn}</td>
                                        <td>{$row['product_name']}</td>";
                                
                                if ($role === 'Admin') {
                                    echo "<td><span class='badge bg-light text-dark border'>".htmlspecialchars($row['employee_name'])."</span></td>";
                                }
                                
                                echo "  <td>{$row['quantity_sold']}</td>
                                        <td class='text-success fw-bold'>₦".number_format($row['total_amount'], 2)."</td>
                                        <td class='text-muted small'>{$date}</td>
                                      </tr>";
                            }
                        } else {
                            $colspan = $role === 'Admin' ? 6 : 5;
                            echo "<tr><td colspan='{$colspan}' class='text-center text-muted'>No sales recorded yet.</td></tr>";
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
