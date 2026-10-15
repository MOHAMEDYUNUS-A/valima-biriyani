<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= \App\Helpers\Security::e($title ?? 'Management Console') ?> | Valima Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #e8533a;
            --primary-dark: #c43d28;
            --primary-light: #fff0ee;
            --sidebar-bg: #0f1117;
            --sidebar-text: #a0a3ad;
            --bg: #f5f6fa;
            --white: #fff;
            --border: rgba(0,0,0,0.08);
            --text: #1a1d23;
            --text2: #6b7280;
            --radius: 10px;
            --radius-lg: 14px;
            --shadow: 0 1px 4px rgba(0,0,0,0.06);
            --shadow-md: 0 4px 16px rgba(0,0,0,0.08);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); display: flex; min-height: 100vh; font-size: 14px; color: var(--text); }

        /* ── Sidebar ── */
        .sidebar { width: 220px; background: var(--sidebar-bg); position: fixed; height: 100vh; display: flex; flex-direction: column; z-index: 100; transition: left 0.25s; }
        .sidebar .logo { padding: 22px 20px 18px; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .sidebar .logo .logo-icon { width: 32px; height: 32px; background: var(--primary); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .sidebar .logo span { color: #fff; font-weight: 600; font-size: 15px; letter-spacing: 0.2px; font-family: 'Poppins', sans-serif; }
        .sidebar nav { padding: 14px 10px; flex: 1; overflow-y: auto; }
        .nav-section { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.9px; color: rgba(160,163,173,0.45); padding: 10px 14px 5px; margin-top: 4px; }
        .sidebar nav a { display: flex; align-items: center; gap: 10px; color: var(--sidebar-text); padding: 10px 12px; border-radius: 8px; text-decoration: none; font-size: 13.5px; font-weight: 500; margin-bottom: 2px; transition: background 0.15s, color 0.15s; }
        .sidebar nav a:hover { background: rgba(255,255,255,0.06); color: #dde0e8; }
        .sidebar nav a.active { background: var(--primary); color: #fff; }
        .sidebar-footer { padding: 12px 10px; border-top: 1px solid rgba(255,255,255,0.06); }
        .sidebar-footer a { display: flex; align-items: center; gap: 9px; color: var(--sidebar-text); padding: 9px 12px; border-radius: 8px; text-decoration: none; font-size: 13px; transition: background 0.15s; }
        .sidebar-footer a:hover { background: rgba(255,255,255,0.06); color: #dde0e8; }

        /* ── Main Area ── */
        .main { flex: 1; margin-left: 220px; padding: 28px 30px; min-width: 0; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .page-header h1 { font-size: 22px; font-weight: 700; color: var(--text); font-family: 'Poppins', sans-serif; }
        .page-header .sub { font-size: 13px; color: var(--text2); margin-top: 2px; }

        /* ── Cards & Grid ── */
        .card { background: var(--white); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 22px; margin-bottom: 20px; box-shadow: var(--shadow); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid var(--border); }
        .card-header h3 { font-size: 15px; font-weight: 600; color: var(--text); }

        /* ── Buttons ── */
        .btn { padding: 9px 16px; border: none; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; transition: background 0.15s, transform 0.1s; font-family: inherit; }
        .btn:active { transform: scale(0.97); }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-secondary { background: #f0f2f7; color: var(--text); border: 1px solid var(--border); }
        .btn-danger { background: #fff1f0; color: #d63031; border: 1px solid #ffd5d2; }
        .btn-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .btn-sm { padding: 6px 11px; font-size: 12px; }

        /* ── Tables ── */
        table { width: 100%; border-collapse: collapse; }
        thead th { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: var(--text2); padding: 0 14px 12px; text-align: left; }
        tbody td { padding: 13px 14px; font-size: 13.5px; border-top: 1px solid var(--border); vertical-align: middle; }
        tbody tr:hover td { background: #fafbfc; }

        /* ── Badges ── */
        .badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; }
        .badge-warn   { background: #fff8e7; color: #b45309; }
        .badge-info   { background: #eff6ff; color: #1d4ed8; }
        .badge-success{ background: #f0fdf4; color: #166534; }
        .badge-neutral{ background: #f3f4f6; color: #4b5563; }
        .badge-danger { background: #fff1f0; color: #d63031; }

        /* ── Flash ── */
        .alert-box { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13.5px; font-weight: 500; display: flex; align-items: center; gap: 8px; }
        .alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background: #fff1f0; color: #d63031; border: 1px solid #ffd5d2; }

        @media (max-width: 900px) {
            .sidebar { left: -220px; }
            .sidebar.open { left: 0; }
            .main { margin-left: 0; padding: 20px 16px; }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar" id="adminSidebar">
        <div class="logo">
            <div class="logo-icon">🍲</div>
            <span>Valima Admin</span>
        </div>
        <nav>
            <div class="nav-section">Operations</div>
            <a href="/admin" class="<?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">📊 Dashboard</a>
            <a href="/pos" class="<?= ($currentPage ?? '') === 'pos' ? 'active' : '' ?>">🖥️ POS Billing</a>
            <a href="/kitchen" target="_blank">👨‍🍳 Kitchen Display</a>
            <a href="/admin/orders" class="<?= ($currentPage ?? '') === 'orders' ? 'active' : '' ?>">🧾 Live Orders</a>
            <a href="/admin/tables" class="<?= ($currentPage ?? '') === 'tables' ? 'active' : '' ?>">🪑 Table Layout</a>

            <div class="nav-section">Management</div>
            <a href="/admin/menu" class="<?= ($currentPage ?? '') === 'menu' ? 'active' : '' ?>">📋 Menu Items</a>
            <a href="/admin/crm" class="<?= ($currentPage ?? '') === 'crm' ? 'active' : '' ?>">👥 Customers & CRM</a>
            <a href="/admin/loyalty" class="<?= ($currentPage ?? '') === 'loyalty' ? 'active' : '' ?>">🎁 Loyalty Rewards</a>
            <a href="/admin/reports" class="<?= ($currentPage ?? '') === 'reports' ? 'active' : '' ?>">📈 Sales Reports</a>

            <div class="nav-section">System</div>
            <a href="/admin/settings" class="<?= ($currentPage ?? '') === 'settings' ? 'active' : '' ?>">⚙️ Settings</a>
            <a href="/admin/audit-logs" class="<?= ($currentPage ?? '') === 'audit' ? 'active' : '' ?>">🛡️ Audit Logs</a>
        </nav>
        <div class="sidebar-footer">
            <a href="/logout">🚪 Sign Out (<?= \App\Helpers\Security::e($currentUser['name'] ?? 'Staff') ?>)</a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main">
        <?php if (!empty($flashSuccess)): ?>
            <div class="alert-box alert-success">✅ <?= \App\Helpers\Security::e($flashSuccess) ?></div>
        <?php endif; ?>
        <?php if (!empty($flashError)): ?>
            <div class="alert-box alert-error">⚠️ <?= \App\Helpers\Security::e($flashError) ?></div>
        <?php endif; ?>

        <?= $content ?? '' ?>
    </div>

</body>
</html>
