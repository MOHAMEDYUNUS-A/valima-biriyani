<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= \App\Helpers\Security::e($title ?? 'Admin Console') ?> | Valima Biriyani</title>
    
    <!-- Design System CSS -->
    <link rel="stylesheet" href="/assets/css/app.css">
    <script src="/assets/js/alpine.min.js" defer></script>

    <style>
        :root {
            --sidebar-bg: #14110C;
            --sidebar-border: #2C261E;
            --sidebar-active: #F5B800;
        }

        body {
            background-color: #F8F5EE;
            display: flex;
            min-height: 100vh;
        }

        .admin-sidebar {
            width: 240px;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--sidebar-border);
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        .admin-logo-box {
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--sidebar-border);
        }

        .admin-nav {
            padding: 1.25rem 0.85rem;
            flex: 1;
            overflow-y: auto;
        }

        .admin-nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: var(--radius-md);
            color: #A09486;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 4px;
            transition: all 0.15s;
        }

        .admin-nav-item:hover {
            color: #FFFFFF;
            background: rgba(255, 255, 255, 0.05);
        }

        .admin-nav-item.active {
            background: var(--sidebar-active);
            color: #14110C;
            font-weight: 700;
        }

        .admin-main {
            margin-left: 240px;
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        .admin-topbar {
            background: #FFFFFF;
            border-bottom: 1px solid var(--border);
            padding: 0.85rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .admin-content {
            padding: 2rem;
            flex: 1;
        }
    </style>
</head>
<body>

    <!-- ── DARK SIDEBAR (Matches Mockup Screen 10) ── -->
    <aside class="admin-sidebar">
        <div class="admin-logo-box">
            <img src="/assets/images/valima-logo.jpg" alt="Valima Biriyani" style="height: 38px; border-radius: 6px;">
            <div>
                <div class="font-serif font-bold text-white text-base leading-tight">VALIMA</div>
                <div style="font-size: 0.65rem; color: var(--primary); letter-spacing: 1.5px; text-transform: uppercase; font-weight: 700;">Management</div>
            </div>
        </div>

        <nav class="admin-nav">
            <a href="/admin" class="admin-nav-item <?= empty($currentPage) || $currentPage === 'dashboard' ? 'active' : '' ?>">
                <?= \App\Helpers\Icon::render('layout-dashboard', 'w-5 h-5') ?>
                <span>Dashboard</span>
            </a>

            <a href="/admin/orders" class="admin-nav-item <?= ($currentPage ?? '') === 'orders' ? 'active' : '' ?>">
                <?= \App\Helpers\Icon::render('shopping-bag', 'w-5 h-5') ?>
                <span>Orders</span>
            </a>

            <a href="/admin/menu" class="admin-nav-item <?= ($currentPage ?? '') === 'menu' ? 'active' : '' ?>">
                <?= \App\Helpers\Icon::render('menu', 'w-5 h-5') ?>
                <span>Menu</span>
            </a>

            <a href="/admin/crm" class="admin-nav-item <?= ($currentPage ?? '') === 'crm' ? 'active' : '' ?>">
                <?= \App\Helpers\Icon::render('user', 'w-5 h-5') ?>
                <span>Customers</span>
            </a>

            <a href="/admin/reports" class="admin-nav-item <?= ($currentPage ?? '') === 'reports' ? 'active' : '' ?>">
                <?= \App\Helpers\Icon::render('bar-chart', 'w-5 h-5') ?>
                <span>Reports</span>
            </a>

            <a href="/admin/settings" class="admin-nav-item <?= ($currentPage ?? '') === 'settings' ? 'active' : '' ?>">
                <?= \App\Helpers\Icon::render('settings', 'w-5 h-5') ?>
                <span>Settings</span>
            </a>

            <a href="/pos" class="admin-nav-item">
                <?= \App\Helpers\Icon::render('credit-card', 'w-5 h-5') ?>
                <span>POS Billing</span>
            </a>

            <a href="/kitchen" class="admin-nav-item">
                <?= \App\Helpers\Icon::render('flame', 'w-5 h-5') ?>
                <span>Kitchen KDS</span>
            </a>

            <a href="/styleguide" class="admin-nav-item">
                <?= \App\Helpers\Icon::render('sparkles', 'w-5 h-5') ?>
                <span>Styleguide</span>
            </a>
        </nav>

        <div style="padding: 1rem 0.85rem; border-top: 1px solid var(--sidebar-border);">
            <a href="/logout" class="admin-nav-item text-red-400 hover:text-red-300">
                <?= \App\Helpers\Icon::render('logout', 'w-5 h-5') ?>
                <span>Sign Out</span>
            </a>
        </div>
    </aside>

    <!-- ── ADMIN MAIN AREA ── -->
    <div class="admin-main">
        <!-- Top Bar (Mockup Screen 10) -->
        <header class="admin-topbar">
            <div class="flex items-center gap-3">
                <span class="text-xs font-bold text-muted-foreground flex items-center gap-1">
                    <?= \App\Helpers\Icon::render('calendar', 'w-4 h-4 text-primary') ?>
                    <span><?= date('l, d M Y') ?></span>
                </span>
            </div>

            <div class="flex items-center gap-4">
                <a href="/pos" class="btn btn-primary btn-sm shadow-soft">
                    <?= \App\Helpers\Icon::render('plus', 'w-4 h-4') ?>
                    <span>New Order (POS)</span>
                </a>

                <div class="flex items-center gap-2 pl-4 border-l border-border">
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--primary); color: #14110C; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem;">
                        <?= strtoupper(substr($currentUser['name'] ?? 'A', 0, 1)) ?>
                    </div>
                    <div class="hidden sm:block text-xs">
                        <div class="font-bold text-foreground"><?= htmlspecialchars($currentUser['name'] ?? 'Admin') ?></div>
                        <div class="text-muted-foreground capitalize"><?= htmlspecialchars($currentUser['role'] ?? 'Staff') ?></div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Dynamic Content -->
        <main class="admin-content">
            <?php if (!empty($flashSuccess)): ?>
                <div class="p-4 rounded-md bg-green-50 border border-green-200 text-green-800 flex items-center gap-2 mb-6">
                    <?= \App\Helpers\Icon::render('check', 'w-5 h-5 text-green-600') ?>
                    <span><?= htmlspecialchars($flashSuccess) ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($flashError)): ?>
                <div class="p-4 rounded-md bg-red-50 border border-red-200 text-red-800 flex items-center gap-2 mb-6">
                    <?= \App\Helpers\Icon::render('x', 'w-5 h-5 text-red-600') ?>
                    <span><?= htmlspecialchars($flashError) ?></span>
                </div>
            <?php endif; ?>

            <?= $content ?>
        </main>
    </div>
</body>
</html>
