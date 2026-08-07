<?php 
require 'db.php'; 
session_start();
if (!isset($_SESSION['loggedin'])) { header("Location: index.php"); exit; }
$pageTitle = 'Settings';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - ERP SYSTEM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7fe; color: #1e293b; overflow-x: hidden; }
        .main-content { margin-left: 260px; padding: 30px; min-height: 100vh; }
        
        .ui-card { background: white; border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); border: 1px solid #f1f5f9; max-width: 600px; margin: 0 auto; }
        .card-title-custom { font-size: 1.2rem; font-weight: 700; margin-bottom: 5px; color: #1e293b; }
        
        .form-label { font-size: 0.9rem; font-weight: 600; color: #64748b; margin-bottom: 8px; }
        .form-control { background: #f8fafc; border: 1px solid #e2e8f0; font-size: 0.95rem; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; }
        .form-control:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
        .btn-purple-full { background: #6366f1; color: white; width: 100%; padding: 12px; border-radius: 8px; font-weight: 600; border: none; margin-top: 10px; transition: 0.2s; }
        .btn-purple-full:hover { background: #4f46e5; }
        
        @media (max-width: 991px) {
            .main-content { margin-left: 0; padding: 15px; }
            .sidebar { display: none; } /* On mobile, sidebar hides */
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <?php include 'header.php'; ?>

        <div class="ui-card">
            <div class="text-center mb-4">
                <div style="width: 80px; height: 80px; border-radius: 50%; background: #e0e7ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 15px;">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h4 class="card-title-custom">Security Settings</h4>
                <p class="text-muted small">Update your account password</p>
            </div>
            
            <?php if (isset($_GET['msg'])): ?><div class="alert alert-success rounded-3 mb-4"><?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>
            <?php if (isset($_GET['error'])): ?><div class="alert alert-danger rounded-3 mb-4"><?= htmlspecialchars($_GET['error']) ?></div><?php endif; ?>

            <form action="actions.php" method="POST">
                <input type="hidden" name="action" value="change_password">
                
                <label class="form-label">Current Password</label>
                <input type="password" name="current_password" class="form-control" placeholder="Enter current password" required>
                
                <label class="form-label">New Password</label>
                <input type="password" name="new_password" class="form-control" placeholder="Enter new password" required>
                
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password" required>
                
                <button type="submit" class="btn-purple-full"><i class="fa-solid fa-floppy-disk me-2"></i> Update Password</button>
            </form>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
