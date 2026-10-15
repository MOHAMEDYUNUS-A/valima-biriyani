<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= \App\Helpers\Security::e($title ?? 'Installation Successful — Valima Biriyani') ?></title>
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
        .success-box {
            max-width: 580px;
            width: 100%;
            background: #FFFFFF;
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            box-shadow: 0 16px 48px rgba(20, 17, 12, 0.14);
            overflow: hidden;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="success-box">
        <div class="bg-[#14110C] p-8 border-b border-[#2A241C]">
            <div class="w-16 h-16 rounded-full bg-emerald-500/20 text-emerald-400 mx-auto flex items-center justify-center mb-4 border border-emerald-500/40">
                <?= \App\Helpers\Icon::render('check', 'w-8 h-8 text-emerald-400') ?>
            </div>
            <h1 class="text-2xl font-serif font-bold text-white tracking-wide">Installation Complete!</h1>
            <p class="text-sm text-[#A09486] mt-1">Valima Biriyani is successfully deployed on your Hostinger server.</p>
        </div>

        <div class="p-8 space-y-6 text-left">
            <div class="p-4 rounded-xl bg-amber-50/70 border border-amber-200 text-xs text-amber-900 space-y-2">
                <div class="font-bold flex items-center gap-1.5 text-amber-950">
                    <?= \App\Helpers\Icon::render('shield-check', 'w-4 h-4 text-copper') ?>
                    <span>Security Lock Activated</span>
                </div>
                <p>
                    A lock file (<code>storage/installed.lock</code>) has been generated. The <code>/install</code> setup wizard is now permanently disabled to prevent unauthorized access.
                </p>
            </div>

            <div class="bg-[#FBF6EC] p-4 rounded-xl border border-[#E8DFCB] space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Store Name:</span>
                    <span class="font-bold text-foreground"><?= \App\Helpers\Security::e($storeName) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Admin Login:</span>
                    <span class="font-bold text-foreground"><?= \App\Helpers\Security::e($adminEmail) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Database Schema:</span>
                    <span class="font-bold text-emerald-700">Migrated & Seeded (5/5)</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Environment File:</span>
                    <span class="font-mono text-foreground font-semibold">.env updated</span>
                </div>
            </div>

            <div class="space-y-3 pt-2">
                <a href="/login" class="btn btn-primary w-full py-3 text-center text-sm font-bold shadow-soft flex items-center justify-center gap-2">
                    <?= \App\Helpers\Icon::render('user', 'w-4 h-4') ?>
                    <span>Log In to Admin Dashboard</span>
                </a>
                <a href="/" class="btn btn-secondary w-full py-2.5 text-center text-sm font-semibold flex items-center justify-center gap-2">
                    <?= \App\Helpers\Icon::render('shopping-bag', 'w-4 h-4') ?>
                    <span>View Customer Storefront</span>
                </a>
            </div>
        </div>
    </div>

</body>
</html>
