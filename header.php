<style>
    .top-header { background: #ffffff; height: 70px; display: flex; align-items: center; justify-content: space-between; padding: 0 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); margin-bottom: 30px; border-radius: 12px; }
    .page-title { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 1.25rem; color: #1e293b; margin: 0; }
    .page-title i { color: #4f46e5; }
    .header-actions { display: flex; align-items: center; gap: 20px; }
    .search-bar { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 20px; padding: 8px 15px; display: flex; align-items: center; gap: 10px; width: 300px; }
    .search-bar input { border: none; background: transparent; outline: none; width: 100%; font-size: 0.85rem; }
    .search-bar i { color: #94a3b8; }
    .notification-btn { position: relative; color: #64748b; font-size: 1.2rem; cursor: pointer; }
    .notification-badge { position: absolute; top: -5px; right: -5px; background: #ef4444; color: white; font-size: 0.6rem; font-weight: bold; width: 15px; height: 15px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
</style>
<div class="top-header">
    <h2 class="page-title"><i class="fa-solid fa-bars"></i> <?= $pageTitle ?? 'Dashboard' ?></h2>
    <div class="header-actions">
        <div class="search-bar">
            <i class="fa-solid fa-search"></i>
            <input type="text" placeholder="Search...">
        </div>
        <div class="notification-btn">
            <i class="fa-regular fa-bell"></i>
            <span class="notification-badge">3</span>
        </div>
        <div class="profile-mini">
            <img src="https://ui-avatars.com/api/?name=Admin&background=4f46e5&color=fff" alt="Admin" style="width:35px; height:35px; border-radius:50%;">
            <div class="profile-info">
                <strong style="color: #1e293b; font-size: 0.85rem; display: block; line-height: 1.2;"><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></strong>
                <small style="color: #64748b; font-size: 0.75rem;">Administrator</small>
            </div>
        </div>
    </div>
</div>
