<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= \App\Helpers\Security::e($title ?? 'Valima Biriyani — Installer Locked') ?></title>
    <link rel="stylesheet" href="<?= ($basePath ?? '') ?>/assets/css/app.css">
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
        .locked-box {
            max-width: 520px;
            width: 100%;
            background: #FFFFFF;
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            box-shadow: 0 16px 48px rgba(20, 17, 12, 0.12);
            overflow: hidden;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="locked-box">
        <div class="bg-[#14110C] p-8 border-b border-[#2A241C]">
            <div class="w-16 h-16 rounded-full bg-amber-500/20 text-amber-400 mx-auto flex items-center justify-center mb-4 border border-amber-500/40">
                <?= \App\Helpers\Icon::render('shield-check', 'w-8 h-8 text-primary') ?>
            </div>
            <h1 class="text-xl font-serif font-bold text-white tracking-wide">Application Already Installed</h1>
            <p class="text-xs text-[#A09486] mt-1">Installed on <?= \App\Helpers\Security::e($lockDate) ?></p>
        </div>

        <div class="p-8 space-y-5 text-left">
            <p class="text-xs text-muted-foreground leading-relaxed">
                For security reasons, the installation wizard has been locked via <code class="bg-muted px-1 py-0.5 rounded">storage/installed.lock</code>. If you need to re-run the installer, remove this file from your Hostinger File Manager or run migrations via the CLI.
            </p>

            <div class="space-y-3 pt-2">
                <a href="<?= ($basePath ?? '') ?>/login" class="btn btn-primary w-full py-2.5 text-center text-sm font-bold shadow-soft flex items-center justify-center gap-2">
                    <?= \App\Helpers\Icon::render('user', 'w-4 h-4') ?>
                    <span>Go to Staff / Admin Login</span>
                </a>
                <a href="<?= ($basePath ?? '') ?>/" class="btn btn-secondary w-full py-2.5 text-center text-sm font-semibold flex items-center justify-center gap-2">
                    <?= \App\Helpers\Icon::render('shopping-bag', 'w-4 h-4') ?>
                    <span>Return to Customer Storefront</span>
                </a>
            </div>
        </div>
    </div>

</body>
</html>
