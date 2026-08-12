<?php 
require 'db.php'; 
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'Employee') { 
    header("Location: dashboard.php"); 
    exit; 
}
$pageTitle = 'Sales';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales - ERP SYSTEM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7fe; color: #1e293b; overflow-x: hidden; }
        .main-content { margin-left: 260px; padding: 30px; min-height: 100vh; }
        .ui-card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); border: 1px solid #f1f5f9; height: 100%; margin-bottom: 25px;}
        .card-title-custom { font-size: 1.1rem; font-weight: 700; margin-bottom: 25px; color: #1e293b; }
        .form-label { font-size: 0.85rem; font-weight: 600; color: #64748b; margin-bottom: 5px; }
        .form-control, .form-select { background: #f8fafc; border: 1px solid #e2e8f0; font-size: 0.9rem; padding: 10px 15px; border-radius: 8px; margin-bottom: 15px; }
        .form-control:focus, .form-select:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
        .btn-purple-full { background: #6366f1; color: white; width: 100%; padding: 12px; border-radius: 8px; font-weight: 600; border: none; margin-top: 10px; transition: 0.2s; }
        .btn-purple-full:hover { background: #4f46e5; }
        .table-custom { width: 100%; font-size: 0.9rem; border-collapse: collapse; }
        .table-custom th { color: #64748b; font-weight: 600; padding: 15px 10px; border-bottom: 1px solid #e2e8f0; }
        .table-custom td { padding: 15px 10px; border-bottom: 1px solid #f1f5f9; font-weight: 500; vertical-align: middle; }
        .cart-summary { margin-top: 30px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 20px; }
        .cart-summary h5 { font-weight: 700; margin: 0; }
        .btn-cart-action { padding: 10px 25px; border-radius: 8px; font-weight: 600; border: none; transition: 0.2s; font-size: 0.9rem; }
        .btn-clear { background: #fee2e2; color: #ef4444; } .btn-clear:hover { background: #fca5a5; }
        .btn-save { background: #dcfce7; color: #10b981; } .btn-save:hover { background: #bbf7d0; }
        .btn-print { background: #e0e7ff; color: #4f46e5; border: none; padding: 6px 12px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; transition: 0.2s; }
        .btn-print:hover { background: #c7d2fe; }
        .page-actions-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .date-picker-custom { background: white; border: 1px solid #e2e8f0; padding: 8px 15px; border-radius: 8px; font-size: 0.9rem; color: #64748b; }
        .btn-new-sale { background: #6366f1; color: white; border: none; padding: 8px 20px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; transition: 0.2s; }
        .btn-new-sale:hover { background: #4f46e5; }
        #printable-receipt { display: none; }
        @media print {
            body * { visibility: hidden; }
            #printable-receipt, #printable-receipt * { visibility: visible; }
            #printable-receipt { display: block; position: absolute; left: 0; top: 0; width: 100%; max-width: 300px; margin: 0 auto; font-family: 'Courier New', Courier, monospace; color: #000; background: #fff; padding: 15px; }
            .r-header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 10px; margin-bottom: 10px; }
            .r-header h2 { margin: 0; font-size: 1.2rem; font-weight: bold; }
            .r-header p { margin: 2px 0; font-size: 0.8rem; }
            .r-body { border-bottom: 1px dashed #000; padding-bottom: 10px; margin-bottom: 10px; font-size: 0.85rem; }
            .r-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
            .r-total { font-weight: bold; font-size: 1rem; margin-top: 10px; }
            .r-footer { text-align: center; font-size: 0.8rem; margin-top: 10px; }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <?php include 'header.php'; ?>

        <?php if (isset($_GET['msg'])): ?><div class="alert alert-success rounded-3 mb-3"><?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>
        <?php if (isset($_GET['error'])): ?><div class="alert alert-danger rounded-3 mb-3"><?= htmlspecialchars($_GET['error']) ?></div><?php endif; ?>

        <div class="page-actions-top">
            <div class="date-picker-custom"><i class="fa-regular fa-calendar me-2"></i> <?= date('d/m/Y') ?></div>
            <button class="btn-new-sale" onclick="document.getElementById('productSelect').focus();"><i class="fa-solid fa-plus me-1"></i> New Sale</button>
        </div>

        <div class="row g-4">
            <!-- Left POS Entry Form -->
            <div class="col-md-4">
                <div class="ui-card">
                    <h4 class="card-title-custom">New Sale</h4>
                    <div>
                        <label class="form-label">Customer</label>
                        <select id="customerSelect" class="form-select"><option>Walk-in Customer</option></select>
                        
                        <label class="form-label">Product</label>
                        <select id="productSelect" class="form-select" onchange="updateFormCalc()">
                            <option value="" data-price="0" data-stock="0" data-name="">Select Product</option>
                            <?php
                            $catalog = $pdo->query("SELECT * FROM products ORDER BY product_name ASC")->fetchAll();
                            foreach ($catalog as $p) {
                                echo "<option value='{$p['product_id']}' data-name='".htmlspecialchars($p['product_name'], ENT_QUOTES)."' data-price='{$p['selling_price']}' data-stock='{$p['quantity']}'>{$p['product_name']} (Stock: {$p['quantity']} | ₦{$p['selling_price']})</option>";
                            }
                            ?>
                        </select>
                        
                        <label class="form-label">Quantity</label>
                        <input type="number" id="qtyInput" class="form-control" value="1" min="1" oninput="updateFormCalc()">
                        
                        <label class="form-label">Price (Per Unit)</label>
                        <input type="text" id="priceDisplay" class="form-control" placeholder="₦0.00" disabled>
                        
                        <label class="form-label">Total Amount</label>
                        <input type="text" id="totalDisplay" class="form-control" placeholder="₦0.00" disabled>
                        
                        <button type="button" class="btn-purple-full" onclick="addToCart()">Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Right Sales Cart & Recent Transactions -->
            <div class="col-md-8">
                <!-- Shopping Cart Display -->
                <div class="ui-card mb-4">
                    <h4 class="card-title-custom">Sales Cart</h4>
                    <div class="table-responsive">
                        <table class="table-custom">
                            <thead><tr><th>Product</th><th>Qty</th><th>Unit Price</th><th>Total</th><th class="text-center">Action</th></tr></thead>
                            <tbody id="cartBody">
                                <tr><td colspan="5" class="text-center text-muted py-3">Cart is empty</td></tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="cart-summary">
                        <span class="fw-bold text-muted" id="totalItemsDisplay">Total Items: 0</span>
                        <div class="d-flex align-items-center gap-3">
                            <span class="fw-bold">Grand Total:</span>
                            <h5 class="m-0" id="grandTotalDisplay">₦0.00</h5>
                        </div>
                    </div>
                    
                    <form action="actions.php" method="POST" id="checkoutForm" onsubmit="return handleCheckout(event)">
                        <input type="hidden" name="action" value="process_cart">
                        <input type="hidden" name="cart_data" id="cartDataPayload">
                        <div class="d-flex justify-content-end gap-3 mt-4">
                            <button type="button" class="btn-cart-action btn-clear" onclick="clearCart()"><i class="fa-solid fa-trash-can me-1"></i> Clear Cart</button>
                            <button type="submit" class="btn-cart-action btn-save"><i class="fa-regular fa-square-check me-1"></i> Checkout All</button>
                        </div>
                    </form>
                </div>

                <!-- Recent Transactions Table -->
                <div class="ui-card">
                    <h4 class="card-title-custom">Recent Transactions</h4>
                    <div class="table-responsive">
                        <table class="table-custom">
                            <thead><tr><th>TXN ID</th><th>Product</th><th>Qty</th><th>Total</th><th>Date</th><th class="text-center">Action</th></tr></thead>
                            <tbody>
                                <?php
                                $stmt_sales = $pdo->prepare("SELECT s.*, p.product_name, p.selling_price FROM sales s JOIN products p ON s.product_id = p.product_id WHERE s.employee_id = ? ORDER BY s.sale_id DESC LIMIT 5");
                                $stmt_sales->execute([$_SESSION['employee_id']]);
                                $sales_logs = $stmt_sales->fetchAll();
                                if (count($sales_logs) > 0) {
                                    foreach ($sales_logs as $row) {
                                        $txn_id = 'TXN-' . str_pad($row['sale_id'], 4, '0', STR_PAD_LEFT);
                                        $price = $row['selling_price'];
                                        $total = $row['total_amount'];
                                        $date = date('d/m/Y H:i', strtotime($row['sale_date']));
                                        echo "<tr>
                                                <td class='text-muted fw-bold'>{$txn_id}</td>
                                                <td>{$row['product_name']}</td>
                                                <td>{$row['quantity_sold']}</td>
                                                <td class='fw-bold text-dark'>₦".number_format($total, 2)."</td>
                                                <td class='text-muted small'>{$date}</td>
                                                <td class='text-center'><button class='btn-print' onclick=\"printReceipt('{$txn_id}', '{$row['product_name']}', '{$row['quantity_sold']}', '{$price}', '{$total}', '{$date}')\"><i class='fa-solid fa-print me-1'></i> Print</button></td>
                                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center text-muted py-4'>No recent transactions found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Printable Receipt Template -->
    <div id="printable-receipt">
        <div class="r-header">
            <h2>ERP SYSTEM</h2>
            <p>Enterprise Resource Planning</p>
            <p>Tel: +234 123 456 7890</p>
        </div>
        <div class="r-body">
            <div class="r-row"><span>Receipt No:</span> <span id="r-id"></span></div>
            <div class="r-row"><span>Date:</span> <span id="r-date"></span></div>
            <div class="r-row"><span>Cashier:</span> <span><?= htmlspecialchars($_SESSION['username']) ?></span></div>
            <br>
            <div class="r-row" style="border-bottom: 1px solid #000; padding-bottom: 5px; font-weight: bold;"><span>Item</span><span>Qty x Price</span></div>
            <div class="r-row" style="margin-top: 5px;"><span id="r-product"></span><span><span id="r-qty"></span> x ₦<span id="r-price"></span></span></div>
            <div class="r-row r-total"><span>TOTAL:</span><span>₦<span id="r-total"></span></span></div>
        </div>
        <div class="r-footer">
            <p>Thank you for your business!</p>
            <p>Goods sold in good condition are not returnable.</p>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        let cart = [];

        function updateFormCalc() {
            const sel = document.getElementById('productSelect');
            if(sel.selectedIndex <= 0) {
                document.getElementById('priceDisplay').value = '';
                document.getElementById('totalDisplay').value = '';
                return;
            }
            const opt = sel.options[sel.selectedIndex];
            const price = parseFloat(opt.getAttribute('data-price'));
            const qty = parseInt(document.getElementById('qtyInput').value) || 1;
            
            document.getElementById('priceDisplay').value = '₦' + price.toLocaleString('en-US', {minimumFractionDigits: 2});
            document.getElementById('totalDisplay').value = '₦' + (price * qty).toLocaleString('en-US', {minimumFractionDigits: 2});
        }

        function addToCart() {
            const sel = document.getElementById('productSelect');
            const qtyInput = document.getElementById('qtyInput');
            
            if(sel.selectedIndex <= 0) { alert('Please select a product first.'); return; }
            
            const opt = sel.options[sel.selectedIndex];
            const id = sel.value;
            const name = opt.getAttribute('data-name');
            const price = parseFloat(opt.getAttribute('data-price'));
            const maxStock = parseInt(opt.getAttribute('data-stock'));
            const qty = parseInt(qtyInput.value) || 1;

            if (qty > maxStock) { alert('Not enough stock available! Current stock: ' + maxStock); return; }

            const existingIndex = cart.findIndex(item => item.id === id);
            
            if (existingIndex > -1) {
                if (cart[existingIndex].qty + qty > maxStock) {
                    alert('Cannot exceed available stock in cart! Current stock: ' + maxStock); return;
                }
                cart[existingIndex].qty += qty;
                cart[existingIndex].total = cart[existingIndex].qty * price;
            } else {
                cart.push({ id, name, qty, price, total: price * qty });
            }

            sel.selectedIndex = 0;
            qtyInput.value = 1;
            updateFormCalc();
            renderCart();
        }

        function renderCart() {
            const tbody = document.getElementById('cartBody');
            let grandTotal = 0;
            let totalItems = 0;

            if (cart.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Cart is empty</td></tr>';
            } else {
                tbody.innerHTML = '';
                cart.forEach((item, index) => {
                    grandTotal += item.total;
                    totalItems += item.qty;
                    tbody.innerHTML += `
                        <tr>
                            <td>${item.name}</td>
                            <td>${item.qty}</td>
                            <td>₦${item.price.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                            <td class="fw-bold">₦${item.total.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                            <td class="text-center"><i class="fa-solid fa-trash text-danger" style="cursor:pointer;" onclick="removeFromCart(${index})"></i></td>
                        </tr>
                    `;
                });
            }

            document.getElementById('totalItemsDisplay').innerText = 'Total Items: ' + totalItems;
            document.getElementById('grandTotalDisplay').innerText = '₦' + grandTotal.toLocaleString('en-US', {minimumFractionDigits: 2});
            
            document.getElementById('cartDataPayload').value = JSON.stringify(cart);
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            renderCart();
        }

        function clearCart() {
            cart = [];
            renderCart();
        }

        function handleCheckout(e) {
            if (cart.length === 0) {
                e.preventDefault();
                alert('Your cart is empty. Please add products before checking out.');
                return false;
            }
            return true;
        }

        function printReceipt(id, product, qty, price, total, date) {
            document.getElementById('r-id').innerText = id;
            document.getElementById('r-date').innerText = date;
            document.getElementById('r-product').innerText = product;
            document.getElementById('r-qty').innerText = qty;
            document.getElementById('r-price').innerText = parseFloat(price).toLocaleString('en-US', {minimumFractionDigits: 2});
            document.getElementById('r-total').innerText = parseFloat(total).toLocaleString('en-US', {minimumFractionDigits: 2});
            window.print();
        }
    </script>
</body>
</html>
