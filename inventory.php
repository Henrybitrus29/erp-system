<?php 
require 'db.php'; 
session_start();
if (!isset($_SESSION['loggedin'])) { header("Location: index.php"); exit; }
$pageTitle = 'Inventory';

$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_value = $pdo->query("SELECT SUM(quantity * cost_price) FROM products")->fetchColumn();
$low_stock = $pdo->query("SELECT COUNT(*) FROM products WHERE quantity > 0 AND quantity <= 5")->fetchColumn();
$out_stock = $pdo->query("SELECT COUNT(*) FROM products WHERE quantity = 0")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory - ERP SYSTEM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7fe; color: #1e293b; overflow-x: hidden; }
        .main-content { margin-left: 260px; padding: 30px; min-height: 100vh; }
        .metric-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); border: 1px solid #f1f5f9; display: flex; align-items: center; gap: 15px; height: 100%; }
        .metric-icon { width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        .icon-blue { background: #e0e7ff; color: #4f46e5; }
        .icon-green { background: #dcfce7; color: #10b981; }
        .icon-yellow { background: #fef3c7; color: #f59e0b; }
        .icon-red { background: #fee2e2; color: #ef4444; }
        .metric-info h6 { color: #64748b; font-size: 0.8rem; margin: 0; font-weight: 500; }
        .metric-info h3 { color: #0f172a; font-size: 1.25rem; margin: 0; font-weight: 700; }
        .ui-card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); border: 1px solid #f1f5f9; margin-top: 25px; }
        .card-header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-add-primary { background: #6366f1; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; transition: 0.2s; }
        .btn-add-primary:hover { background: #4f46e5; color: white; }
        .table-custom { width: 100%; border-collapse: collapse; }
        .table-custom th { color: #64748b; font-size: 0.85rem; font-weight: 600; padding: 15px 10px; border-bottom: 1px solid #e2e8f0; }
        .table-custom td { padding: 15px 10px; font-size: 0.9rem; color: #1e293b; border-bottom: 1px solid #f1f5f9; vertical-align: middle; font-weight: 500; }
        .badge-status { padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; display: inline-block; }
        .status-instock { background: #dcfce7; color: #10b981; }
        .status-low { background: #fef3c7; color: #f59e0b; }
        .status-out { background: #fee2e2; color: #ef4444; }
        .action-btns { display: flex; gap: 8px; justify-content: center; }
        .btn-act { width: 32px; height: 32px; border-radius: 6px; border: none; display: flex; align-items: center; justify-content: center; color: white; transition: 0.2s; }
        .btn-edit { background: #3b82f6; } .btn-edit:hover { background: #2563eb; }
        .btn-del { background: #ef4444; } .btn-del:hover { background: #dc2626; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <?php include 'header.php'; ?>

        <?php if (isset($_GET['msg'])): ?><div class="alert alert-success rounded-3 mb-3"><?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>
        <?php if (isset($_GET['error'])): ?><div class="alert alert-danger rounded-3 mb-3"><?= htmlspecialchars($_GET['error']) ?></div><?php endif; ?>

        <!-- Metric Cards Row -->
        <div class="row g-4">
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-icon icon-blue"><i class="fa-solid fa-boxes-stacked"></i></div>
                    <div class="metric-info"><h6>Total Products</h6><h3><?= $total_products ?></h3></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-icon icon-green"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                    <div class="metric-info"><h6>Total Stock Value</h6><h3>₦<?= number_format($total_value ?? 0) ?></h3></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-icon icon-yellow"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <div class="metric-info"><h6>Low Stock Items</h6><h3><?= $low_stock ?></h3></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-icon icon-red"><i class="fa-solid fa-box-open"></i></div>
                    <div class="metric-info"><h6>Out of Stock</h6><h3><?= $out_stock ?></h3></div>
                </div>
            </div>
        </div>

        <div class="ui-card">
            <div class="card-header-flex">
                <h5 class="m-0 fw-bold text-dark" style="visibility: hidden;">Items</h5>
                <?php if ($_SESSION['role'] === 'Admin'): ?>
                <button class="btn-add-primary" data-bs-toggle="modal" data-bs-target="#addProductModal"><i class="fa-solid fa-plus me-1"></i> Add Product</button>
                <?php endif; ?>
            </div>

            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Product ID</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Status</th>
                            <?php if ($_SESSION['role'] === 'Admin'): ?><th class="text-center">Action</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $products = $pdo->query("SELECT * FROM products ORDER BY product_id ASC")->fetchAll();
                        if (count($products) > 0) {
                            foreach ($products as $prod) {
                                $formatted_id = 'PRD' . str_pad($prod['product_id'], 3, '0', STR_PAD_LEFT);
                                
                                if ($prod['quantity'] > 5) {
                                    $statusClass = 'status-instock'; $statusText = 'In Stock';
                                } elseif ($prod['quantity'] > 0 && $prod['quantity'] <= 5) {
                                    $statusClass = 'status-low'; $statusText = 'Low Stock';
                                } else {
                                    $statusClass = 'status-out'; $statusText = 'Out of Stock';
                                }

                                echo "<tr>
                                        <td class='fw-semibold text-muted'>{$formatted_id}</td>
                                        <td class='fw-bold'>".htmlspecialchars($prod['product_name'])."</td>
                                        <td>General</td>
                                        <td>{$prod['quantity']}</td>
                                        <td class='fw-bold text-dark'>₦".number_format($prod['selling_price'])."</td>
                                        <td><span class='badge-status {$statusClass}'>{$statusText}</span></td>";
                                        
                                if ($_SESSION['role'] === 'Admin') {
                                    echo "<td class='text-center'>
                                            <div class='action-btns'>
                                                <button class='btn-act btn-edit' data-bs-toggle='modal' data-bs-target='#editProdModal{$prod['product_id']}'><i class='fa-solid fa-pen'></i></button>
                                                <form action='actions.php' method='POST' class='d-inline' onsubmit=\"return confirm('Are you sure you want to delete this product?');\">
                                                    <input type='hidden' name='action' value='delete_product'>
                                                    <input type='hidden' name='product_id' value='{$prod['product_id']}'>
                                                    <button type='submit' class='btn-act btn-del'><i class='fa-solid fa-trash'></i></button>
                                                </form>
                                            </div>
                                          </td>";
                                }
                                echo "</tr>";

                                // Edit Product Modal specific to this product
                                if ($_SESSION['role'] === 'Admin') {
                                    echo '<div class="modal fade" id="editProdModal'.$prod['product_id'].'" tabindex="-1" aria-hidden="true">
                                      <div class="modal-dialog">
                                        <div class="modal-content border-0 shadow text-start">
                                          <form action="actions.php" method="POST">
                                              <div class="modal-header border-0 pb-0">
                                                <h5 class="modal-title fw-bold">Edit Product</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                              </div>
                                              <div class="modal-body">
                                                  <input type="hidden" name="action" value="edit_product">
                                                  <input type="hidden" name="product_id" value="'.$prod['product_id'].'">
                                                  <div class="mb-3">
                                                      <label class="form-label small fw-semibold text-muted">Product Name</label>
                                                      <input type="text" name="product_name" class="form-control" value="'.htmlspecialchars($prod['product_name'], ENT_QUOTES).'" required>
                                                  </div>
                                                  <div class="mb-3">
                                                      <label class="form-label small fw-semibold text-muted">Quantity</label>
                                                      <input type="number" name="quantity" class="form-control" value="'.$prod['quantity'].'" required>
                                                  </div>
                                                  <div class="row">
                                                      <div class="col-6 mb-3">
                                                          <label class="form-label small fw-semibold text-muted">Cost Price (₦)</label>
                                                          <input type="number" step="0.01" name="cost_price" class="form-control" value="'.$prod['cost_price'].'" required>
                                                      </div>
                                                      <div class="col-6 mb-3">
                                                          <label class="form-label small fw-semibold text-muted">Selling Price (₦)</label>
                                                          <input type="number" step="0.01" name="selling_price" class="form-control" value="'.$prod['selling_price'].'" required>
                                                      </div>
                                                  </div>
                                              </div>
                                              <div class="modal-footer border-0 pt-0">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary" style="background:#6366f1; border:none;">Save Changes</button>
                                              </div>
                                          </form>
                                        </div>
                                      </div>
                                    </div>';
                                }
                            }
                        } else {
                            $colspan = ($_SESSION['role'] === 'Admin') ? 7 : 6;
                            echo "<tr><td colspan='{$colspan}' class='text-center text-muted py-4'>No products found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <form action="actions.php" method="POST">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">Add New Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_product">
                        <div class="mb-3"><label class="form-label small fw-semibold text-muted">Product Name</label><input type="text" name="product_name" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label small fw-semibold text-muted">Quantity</label><input type="number" name="quantity" class="form-control" required></div>
                        <div class="row">
                            <div class="col-6 mb-3"><label class="form-label small fw-semibold text-muted">Cost Price (₦)</label><input type="number" step="0.01" name="cost_price" class="form-control" required></div>
                            <div class="col-6 mb-3"><label class="form-label small fw-semibold text-muted">Selling Price (₦)</label><input type="number" step="0.01" name="selling_price" class="form-control" required></div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="background:#6366f1; border:none;">Save Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
