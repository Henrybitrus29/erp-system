<?php
session_start();
if (isset($_SESSION['loggedin'])) { header("Location: dashboard.php"); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP SYSTEM - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #0f0c29, #302b63, #24243e); height: 100vh; overflow: hidden; color: white; display: flex; flex-direction: column; }
        .login-nav { padding: 30px 50px; display: flex; align-items: center; gap: 15px; }
        .brand-icon { width: 45px; height: 45px; background: linear-gradient(135deg, #6366f1, #a855f7); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .brand-text h1 { margin: 0; font-size: 1.5rem; font-weight: 700; letter-spacing: 1px; }
        .brand-text p { margin: 0; font-size: 0.85rem; color: #cbd5e1; }
        
        .main-wrapper { flex: 1; display: flex; align-items: center; justify-content: center; padding: 0 50px; gap: 50px; }
        .login-card { background: #ffffff; border-radius: 1rem; padding: 3rem; width: 100%; max-width: 450px; color: #1e293b; box-shadow: 0 20px 40px rgba(0,0,0,0.3); }
        .login-card h3 { font-weight: 700; margin-bottom: 5px; text-align: center; }
        .login-card p { color: #64748b; font-size: 0.9rem; text-align: center; margin-bottom: 2rem; }
        
        .form-control { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 15px; font-size: 0.95rem; }
        .form-control:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
        .input-group-text { background: transparent; border-left: none; cursor: pointer; color: #94a3b8; }
        .form-control { border-right: none; }
        
        .btn-primary-custom { background: #4f46e5; border: none; padding: 12px; border-radius: 8px; font-weight: 600; width: 100%; transition: 0.2s; }
        .btn-primary-custom:hover { background: #4338ca; }
        
        .presentation-area { flex: 1; display: flex; justify-content: center; align-items: center; }
        .laptop-mockup { width: 100%; max-width: 700px; height: 400px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; border-bottom: 15px solid #a855f7; border-bottom-left-radius: 20px; border-bottom-right-radius: 20px; display: flex; align-items: center; justify-content: center; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); position: relative; overflow: hidden; }
        .laptop-mockup::before { content: 'Dashboard Interface Preview'; color: rgba(255,255,255,0.5); font-weight: 600; font-size: 1.2rem; }
    </style>
</head>
<body>
    <div class="login-nav">
        <div class="brand-icon"><i class="fa-solid fa-cube"></i></div>
        <div class="brand-text">
            <h1>ERP SYSTEM</h1>
            <p>Enterprise Resource Planning System</p>
        </div>
    </div>
    <div class="main-wrapper container-fluid">
        <div class="login-card">
            <h3>Welcome Back</h3>
            <p>Sign in to continue</p>
            
            <?php if(isset($_GET['error'])): ?><div class="alert alert-danger p-2 text-center small"><?= htmlspecialchars($_GET['error']) ?></div><?php endif; ?>
            
            <form action="actions.php" method="POST">
                <input type="hidden" name="action" value="login">
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Enter username" required>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-semibold text-muted">Password</label>
                    <div class="input-group">
                        <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                        <span class="input-group-text"><i class="fa-regular fa-eye-slash"></i></span>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember">
                        <label class="form-check-label small text-muted" for="remember">Remember me</label>
                    </div>
                    <a href="#" class="small text-decoration-none" style="color: #4f46e5; font-weight: 500;">Forgot Password?</a>
                </div>
                <button type="submit" class="btn btn-primary-custom text-white"><i class="fa-solid fa-lock me-2"></i> Login</button>
            </form>
        </div>
        <div class="presentation-area d-none d-lg-flex">
            <div class="laptop-mockup">
            </div>
        </div>
    </div>
</body>
</html>