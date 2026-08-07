<style>
    .sidebar { width: 260px; background-color: #0b0f19; color: #94a3b8; height: 100vh; position: fixed; left: 0; top: 0; display: flex; flex-direction: column; font-family: 'Inter', sans-serif; z-index: 1000; }
    .sidebar-brand { display: flex; align-items: center; gap: 10px; padding: 25px 20px; color: white; font-weight: 700; font-size: 1.25rem; letter-spacing: 1px; border-bottom: 1px solid rgba(255,255,255,0.05); }
    .brand-icon { width: 35px; height: 35px; background: linear-gradient(135deg, #6366f1, #a855f7); border-radius: 8px; display: flex; align-items: center; justify-content: center; }
    .nav-menu { padding: 20px 15px; flex-grow: 1; overflow-y: auto; }
    .nav-item { display: flex; align-items: center; gap: 12px; padding: 12px 15px; color: #94a3b8; text-decoration: none; border-radius: 8px; font-weight: 500; font-size: 0.9rem; margin-bottom: 5px; transition: all 0.3s; }
    .nav-item:hover { background-color: rgba(255,255,255,0.05); color: white; }
    .nav-item.active { background-color: #4f46e5; color: white; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3); }
    .nav-item i { font-size: 1.1rem; width: 20px; text-align: center; }
    .sidebar-footer { padding: 20px; border-top: 1px solid rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: space-between; }
    .profile-mini { display: flex; align-items: center; gap: 10px; }
    .profile-mini img { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; }
    .profile-info { line-height: 1.2; }
    .profile-info strong { color: white; font-size: 0.85rem; display: block; }
    .profile-info small { font-size: 0.75rem; }
    .logout-btn { color: #94a3b8; text-decoration: none; transition: 0.2s; }
    .logout-btn:hover { color: #ef4444; }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fa-solid fa-cube"></i></div>
        ERP SYSTEM
    </div>
    <div class="nav-menu">
        <a href="dashboard.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i> Dashboard</a>
        
        <?php if ($_SESSION['role'] === 'Admin'): ?>
        <a href="employees.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'employees.php' ? 'active' : '' ?>"><i class="fa-solid fa-users"></i> Employees</a>
        <?php endif; ?>
        
        <a href="inventory.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'inventory.php' ? 'active' : '' ?>"><i class="fa-solid fa-boxes-stacked"></i> Inventory</a>
        
        <?php if ($_SESSION['role'] === 'Admin'): ?>
        <a href="procurement.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'procurement.php' ? 'active' : '' ?>"><i class="fa-solid fa-cart-shopping"></i> Procurement</a>
        <?php endif; ?>
        
        <?php if ($_SESSION['role'] === 'Employee'): ?>
        <a href="sales.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'sales.php' ? 'active' : '' ?>"><i class="fa-solid fa-chart-line"></i> Sales</a>
        <?php endif; ?>
        
        <a href="reports.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>"><i class="fa-solid fa-file-invoice-dollar"></i> Reports</a>
        
        <?php if ($_SESSION['role'] === 'Admin'): ?>
        <a href="audit.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'audit.php' ? 'active' : '' ?>"><i class="fa-solid fa-shield-halved"></i> Security Audit</a>
        <?php endif; ?>
        
        <a href="settings.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>"><i class="fa-solid fa-gear"></i> Settings</a>
    </div>
    <div class="sidebar-footer">
        <div class="profile-mini">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['username']) ?>&background=4f46e5&color=fff" alt="User">
            <div class="profile-info">
                <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
                <small><?= htmlspecialchars($_SESSION['role']) ?></small>
            </div>
        </div>
        <a href="actions.php?logout=true" class="logout-btn" title="Logout"><i class="fa-solid fa-power-off"></i></a>
    </div>
</div>
