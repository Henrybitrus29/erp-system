<?php ob_start(); session_start();
require 'db.php'; 
if (!isset($_SESSION['loggedin'])) { header("Location: index.php"); exit; }
$pageTitle = 'Dashboard';

// Fetch Dynamic Real-time Metrics from Database
$total_employees = $pdo->query("SELECT COUNT(*) FROM employees")->fetchColumn();
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

$today = date('Y-m-d');
if ($_SESSION['role'] === 'Admin') {
    $total_orders = $pdo->query("SELECT COUNT(*) FROM sales")->fetchColumn();
    $stmt_sales = $pdo->prepare("SELECT SUM(total_amount) FROM sales WHERE DATE(sale_date) = ?");
    $stmt_sales->execute([$today]);
    $total_sales_today = $stmt_sales->fetchColumn() ?: 0;
} else {
    $stmt_orders = $pdo->prepare("SELECT COUNT(*) FROM sales WHERE employee_id = ?");
    $stmt_orders->execute([$_SESSION['employee_id']]);
    $total_orders = $stmt_orders->fetchColumn();
    
    $stmt_sales = $pdo->prepare("SELECT SUM(total_amount) FROM sales WHERE DATE(sale_date) = ? AND employee_id = ?");
    $stmt_sales->execute([$today, $_SESSION['employee_id']]);
    $total_sales_today = $stmt_sales->fetchColumn() ?: 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ERP SYSTEM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7fe; color: #1e293b; overflow-x: hidden; }
        .main-content { margin-left: 260px; padding: 30px; min-height: 100vh; }
        
        .stat-card { background: white; border-radius: 12px; padding: 20px; display: flex; flex-direction: column; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); height: 100%; border: 1px solid #f1f5f9; }
        .stat-card-top { display: flex; align-items: center; gap: 15px; margin-bottom: 10px; }
        .stat-icon { width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        .icon-blue { background: #e0e7ff; color: #4f46e5; }
        .icon-cyan { background: #cffafe; color: #06b6d4; }
        .icon-green { background: #dcfce7; color: #10b981; }
        .icon-purple { background: #f3e8ff; color: #a855f7; }
        .stat-info h6 { color: #64748b; font-size: 0.85rem; margin: 0; font-weight: 500; }
        .stat-info h3 { color: #0f172a; font-size: 1.5rem; margin: 0; font-weight: 700; }
        .stat-link { font-size: 0.8rem; color: #4f46e5; text-decoration: none; font-weight: 600; margin-top: auto; }
        
        .ui-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); border: 1px solid #f1f5f9; height: 100%; }
        .ui-card-title { font-size: 1rem; font-weight: 700; color: #1e293b; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        
        .low-stock-table td { padding: 12px 0; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; }
        .low-stock-table tr:last-child td { border-bottom: none; }
        
        .activity-item { display: flex; gap: 15px; margin-bottom: 15px; }
        .activity-dot { width: 10px; height: 10px; border-radius: 50%; margin-top: 5px; }
        .dot-blue { background: #3b82f6; } .dot-green { background: #10b981; } .dot-purple { background: #a855f7; }
        .activity-text { flex: 1; font-size: 0.9rem; font-weight: 500; }
        .activity-time { font-size: 0.75rem; color: #94a3b8; }
        
        .btn-quick { width: 100%; border: none; border-radius: 8px; padding: 10px; color: white !important; font-weight: 600; margin-bottom: 10px; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; transition: opacity 0.2s; }
        .btn-quick:hover { opacity: 0.85; }
        .btn-q-blue { background: #0ea5e9; } .btn-q-green { background: #22c55e; } .btn-q-purple { background: #a855f7; } .btn-q-orange { background: #f59e0b; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <?php include 'header.php'; ?>
        
        <!-- XAI Widget securely wrapped to only display for Administrators -->
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
            <?php include 'ai_widget.php'; ?>
        <?php endif; ?>
        
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-card-top">
                        <div class="stat-icon icon-blue"><i class="fa-solid fa-user-group"></i></div>
                        <div class="stat-info"><h6>Total Employees</h6><h3><?= $total_employees ?></h3></div>
                    </div>
                    <?php if ($_SESSION['role'] === 'Admin'): ?><a href="employees.php" class="stat-link">View all</a><?php endif; ?>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-card-top">
                        <div class="stat-icon icon-cyan"><i class="fa-solid fa-box"></i></div>
                        <div class="stat-info"><h6>Total Products</h6><h3><?= $total_products ?></h3></div>
                    </div>
                    <a href="inventory.php" class="stat-link">View all</a>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-card-top">
                        <div class="stat-icon icon-green"><i class="fa-solid fa-chart-line"></i></div>
                        <div class="stat-info"><h6>Total Sales (Today)</h6><h3>₦<?= number_format($total_sales_today, 2) ?></h3></div>
                    </div>
                    <?php if ($_SESSION['role'] === 'Employee'): ?><a href="sales.php" class="stat-link">View all</a><?php endif; ?>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-card-top">
                        <div class="stat-icon icon-purple"><i class="fa-solid fa-receipt"></i></div>
                        <div class="stat-info"><h6>Total Orders</h6><h3><?= $total_orders ?></h3></div>
                    </div>
                    <a href="reports.php" class="stat-link">View all</a>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-8">
                <div class="ui-card">
                    <div class="ui-card-title">
                        Sales Overview <small class="text-muted fw-normal">(This Month)</small>
                    </div>
                    <canvas id="salesChart" height="100"></canvas>
                </div>
            </div>
            <div class="col-md-4">
                <div class="ui-card">
                    <div class="ui-card-title">Top Products</div>
                    <div class="d-flex align-items-center justify-content-center" style="height: 200px;">
                        <canvas id="productsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="ui-card">
                    <div class="ui-card-title">Low Stock Alerts</div>
                    <table class="table table-borderless low-stock-table mb-0 w-100">
                        <thead><tr class="text-muted small border-bottom"><th>Product</th><th class="text-end">Quantity</th></tr></thead>
                        <tbody>
                            <?php
                            $low_stock = $pdo->query("SELECT product_name, quantity FROM products WHERE quantity > 0 AND quantity <= 5 LIMIT 4")->fetchAll();
                            if (count($low_stock) > 0) {
                                foreach($low_stock as $item) {
                                    echo "<tr><td class='fw-semibold'><i class='fa-solid fa-triangle-exclamation text-danger me-2'></i> {$item['product_name']}</td><td class='text-end text-danger fw-bold'>{$item['quantity']}</td></tr>";
                                }
                            } else {
                                echo "<tr><td colspan='2' class='text-muted small py-3'>No low stock alerts.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-5">
                <div class="ui-card">
                    <div class="ui-card-title">Recent Activities</div>
                    <div class="activity-item"><div class="activity-dot dot-blue"></div><div class="activity-text">New Sale Recorded</div><div class="activity-time">Just now</div></div>
                    <div class="activity-item"><div class="activity-dot dot-green"></div><div class="activity-text">New Employee Added</div><div class="activity-time">Today</div></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="ui-card">
                    <div class="ui-card-title">Quick Actions</div>
                    <?php if ($_SESSION['role'] === 'Admin'): ?>
                        <a href="inventory.php" class="btn-quick btn-q-blue"><i class="fa-solid fa-circle-plus"></i> Add Product</a>
                        <a href="employees.php" class="btn-quick btn-q-green"><i class="fa-solid fa-user-plus"></i> Add Employee</a>
                        <a href="admin_tamper.php" class="btn-quick" style="background-color: #dc2626;"><i class="fa-solid fa-user-shield"></i> Tamper Panel</a>
                    <?php endif; ?>
                    
                    <?php if ($_SESSION['role'] === 'Employee'): ?>
                        <a href="sales.php" class="btn-quick btn-q-purple"><i class="fa-solid fa-cart-plus"></i> New Sale</a>
                    <?php endif; ?>
                    
                    <a href="reports.php" class="btn-quick btn-q-orange"><i class="fa-solid fa-file-export"></i> Generate Report</a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctxSales = document.getElementById('salesChart').getContext('2d');
        new Chart(ctxSales, { type: 'line', data: { labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'], datasets: [{ data: [200000, 550000, 350000, 800000, 600000, 950000, 850000], borderColor: '#4f46e5', backgroundColor: 'rgba(79, 70, 229, 0.1)', borderWidth: 3, tension: 0.4, fill: true }] }, options: { responsive: true, plugins: { legend: { display: false } } } });
        const ctxProducts = document.getElementById('productsChart').getContext('2d');
        new Chart(ctxProducts, { type: 'doughnut', data: { labels: ['Rice', 'Sugar', 'Oil'], datasets: [{ data: [35, 20, 15], backgroundColor: ['#3b82f6', '#f59e0b', '#22c55e'], borderWidth: 0 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } } });
    </script>
</body>
</html>