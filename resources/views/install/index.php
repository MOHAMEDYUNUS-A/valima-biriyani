<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= \App\Helpers\Security::e($title ?? 'Valima Biriyani — Setup Wizard') ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;0,900;1,600&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: var(--background);
            color: var(--foreground);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem 1rem;
        }
        .install-box {
            max-width: 680px;
            width: 100%;
            background: #FFFFFF;
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            box-shadow: 0 16px 48px rgba(20, 17, 12, 0.12);
            overflow: hidden;
        }
    </style>
</head>
<body>

    <div class="install-box">
        <!-- Brand Header -->
        <div class="bg-[#14110C] p-6 text-center border-b border-[#2A241C]">
            <img src="/assets/images/valima-logo.jpg" alt="Valima Biriyani" style="height: 52px; margin: 0 auto 12px; border-radius: 8px;">
            <h1 class="text-2xl font-serif font-bold text-white tracking-wide">Valima Biriyani Setup Wizard</h1>
            <p class="text-xs text-[#A09486] mt-1">Hostinger Shared Hosting & Database Installation</p>
        </div>

        <div class="p-6 sm:p-8 space-y-6">
            <?php if (!empty($errors)): ?>
                <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm space-y-1">
                    <div class="font-bold flex items-center gap-1.5">
                        <?= \App\Helpers\Icon::render('x', 'w-4 h-4 text-red-600') ?>
                        <span>Installation Error</span>
                    </div>
                    <?php foreach ($errors as $err): ?>
                        <div class="text-xs"><?= \App\Helpers\Security::e($err) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- System Prerequisites Check -->
            <div class="card p-5 bg-[#FBF6EC] border border-[#E8DFCB]">
                <h3 class="font-bold text-foreground text-sm flex items-center justify-between mb-3">
                    <span>Hostinger Server Environment</span>
                    <?php if ($allChecksPass): ?>
                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">
                            Ready for Install
                        </span>
                    <?php else: ?>
                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-red-100 text-red-800 border border-red-300">
                            Prerequisites Missing
                        </span>
                    <?php endif; ?>
                </h3>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
                    <div class="flex items-center gap-1.5">
                        <span class="<?= $checks['php_pass'] ? 'text-emerald-600 font-bold' : 'text-red-500 font-bold' ?>">
                            <?= $checks['php_pass'] ? '✓' : '✗' ?>
                        </span>
                        <span>PHP <?= PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION ?> (>= 8.2)</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="<?= $checks['pdo_mysql'] ? 'text-emerald-600 font-bold' : 'text-red-500 font-bold' ?>">
                            <?= $checks['pdo_mysql'] ? '✓' : '✗' ?>
                        </span>
                        <span>PDO MySQL Driver</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="<?= $checks['extensions']['curl'] ? 'text-emerald-600 font-bold' : 'text-red-500 font-bold' ?>">
                            <?= $checks['extensions']['curl'] ? '✓' : '✗' ?>
                        </span>
                        <span>cURL Extension</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="<?= $checks['extensions']['mbstring'] ? 'text-emerald-600 font-bold' : 'text-red-500 font-bold' ?>">
                            <?= $checks['extensions']['mbstring'] ? '✓' : '✗' ?>
                        </span>
                        <span>mbstring</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="<?= $checks['storage_writable'] ? 'text-emerald-600 font-bold' : 'text-red-500 font-bold' ?>">
                            <?= $checks['storage_writable'] ? '✓' : '✗' ?>
                        </span>
                        <span>storage/ Writable</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="<?= $checks['env_writable'] ? 'text-emerald-600 font-bold' : 'text-red-500 font-bold' ?>">
                            <?= $checks['env_writable'] ? '✓' : '✗' ?>
                        </span>
                        <span>.env Writable</span>
                    </div>
                </div>
            </div>

            <!-- Installation Form -->
            <form action="/install" method="POST" class="space-y-6">
                <!-- Section 1: Database Setup -->
                <div>
                    <h3 class="text-sm font-bold text-foreground uppercase tracking-wider mb-3 pb-1 border-b border-border flex items-center gap-2">
                        <?= \App\Helpers\Icon::render('credit-card', 'w-4 h-4 text-primary') ?>
                        <span>1. Hostinger MySQL Database Credentials</span>
                    </h3>
                    <p class="text-xs text-muted-foreground mb-4">
                        Obtain these from your Hostinger hPanel &rarr; <strong>Databases &rarr; MySQL Databases</strong>.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-foreground mb-1">Database Host</label>
                            <input type="text" name="db_host" value="<?= \App\Helpers\Security::e($old['db_host'] ?? 'localhost') ?>" class="input w-full" required>
                            <span class="text-[11px] text-muted-foreground">Usually <code>localhost</code> in Hostinger</span>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-foreground mb-1">Database Port</label>
                            <input type="text" name="db_port" value="<?= \App\Helpers\Security::e($old['db_port'] ?? '3306') ?>" class="input w-full" required>
                            <span class="text-[11px] text-muted-foreground">Default is <code>3306</code></span>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-foreground mb-1">Database Name <span class="text-red-500">*</span></label>
                            <input type="text" name="db_name" value="<?= \App\Helpers\Security::e($old['db_name'] ?? '') ?>" placeholder="u123456789_valima" class="input w-full" required>
                            <span class="text-[11px] text-muted-foreground">e.g. <code>u123456789_valima</code></span>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-foreground mb-1">Database Username <span class="text-red-500">*</span></label>
                            <input type="text" name="db_user" value="<?= \App\Helpers\Security::e($old['db_user'] ?? '') ?>" placeholder="u123456789_admin" class="input w-full" required>
                            <span class="text-[11px] text-muted-foreground">e.g. <code>u123456789_admin</code></span>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-foreground mb-1">Database Password</label>
                            <input type="password" name="db_pass" placeholder="Enter Hostinger MySQL User Password" class="input w-full">
                        </div>
                    </div>
                </div>

                <!-- Section 2: Store Information -->
                <div>
                    <h3 class="text-sm font-bold text-foreground uppercase tracking-wider mb-3 pb-1 border-b border-border flex items-center gap-2">
                        <?= \App\Helpers\Icon::render('award', 'w-4 h-4 text-primary') ?>
                        <span>2. Restaurant Profile</span>
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-foreground mb-1">Restaurant Name</label>
                            <input type="text" name="store_name" value="<?= \App\Helpers\Security::e($old['store_name'] ?? 'Valima Biriyani') ?>" class="input w-full" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-foreground mb-1">Support Phone (+91)</label>
                            <input type="text" name="store_phone" value="<?= \App\Helpers\Security::e($old['store_phone'] ?? '+91 98765 43210') ?>" class="input w-full" required>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Super Admin Account -->
                <div>
                    <h3 class="text-sm font-bold text-foreground uppercase tracking-wider mb-3 pb-1 border-b border-border flex items-center gap-2">
                        <?= \App\Helpers\Icon::render('user', 'w-4 h-4 text-primary') ?>
                        <span>3. Master Owner / Admin Account</span>
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-foreground mb-1">Admin Name</label>
                            <input type="text" name="admin_name" value="<?= \App\Helpers\Security::e($old['admin_name'] ?? 'Admin Owner') ?>" class="input w-full" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-foreground mb-1">Admin Email <span class="text-red-500">*</span></label>
                            <input type="email" name="admin_email" value="<?= \App\Helpers\Security::e($old['admin_email'] ?? 'admin@valimabiriyani.com') ?>" class="input w-full" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-foreground mb-1">Admin Password (min 8 chars) <span class="text-red-500">*</span></label>
                            <input type="password" name="admin_pass" class="input w-full" placeholder="••••••••" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-foreground mb-1">Confirm Password <span class="text-red-500">*</span></label>
                            <input type="password" name="admin_confirm" class="input w-full" placeholder="••••••••" required>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Seed Sample Data Checkbox -->
                <div class="p-4 bg-muted/60 rounded-xl border border-border flex items-start gap-3">
                    <input type="checkbox" id="seed_data" name="seed_data" value="1" checked class="mt-1 w-4 h-4 text-primary rounded border-border focus:ring-primary">
                    <label for="seed_data" class="text-xs cursor-pointer">
                        <strong class="text-foreground block">Seed Authentic Valima Biriyani Menu & Dining Tables (Recommended)</strong>
                        <span class="text-muted-foreground">
                            Automatically populates Biriyani dishes, variants (Regular/Family/Jumbo), extras (Boiled Egg, Chicken), addons, and 8 dining tables.
                        </span>
                    </label>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn btn-primary w-full py-3 text-base font-bold shadow-floating flex items-center justify-center gap-2">
                        <?= \App\Helpers\Icon::render('sparkles', 'w-5 h-5') ?>
                        <span>Test Database, Run Migrations & Complete Installation</span>
                    </button>
                    <p class="text-center text-[11px] text-muted-foreground mt-2">
                        This process creates all MySQL tables, seeds roles & business rules, and locks the installer for security.
                    </p>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
