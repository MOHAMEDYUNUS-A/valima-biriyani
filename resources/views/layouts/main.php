<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= \App\Helpers\Security::e($title ?? 'Authentic Biryani, Rich in Tradition') ?> | <?= \App\Helpers\Security::e($app['name']) ?></title>
    <meta name="description" content="Experience authentic Valima Biriyani, rich in tradition. Handcrafted dum biriyani, kebabs, and authentic sides. Order online for delivery, pickup, or dine-in.">

    <!-- Precompiled Design System CSS -->
    <link rel="stylesheet" href="<?= $basePath ?? '' ?>/assets/css/app.css">

    <!-- Standalone Alpine.js -->
    <script src="<?= $basePath ?? '' ?>/assets/js/alpine.min.js" defer></script>
    <script>window.APP_BASE_URL = '<?= $basePath ?? '' ?>';</script>
</head>
<body class="bg-background text-foreground flex flex-col min-h-screen" x-data="{ mobileCartCount: 0 }">

    <!-- ── DARK STICKY HEADER (Matches Mockup) ── -->
    <header class="bg-dark text-white border-b border-dark sticky top-0 z-40 shadow-soft">
        <div class="container py-3 flex items-center justify-between">
            <!-- Brand Logo & Name -->
            <a href="<?= $basePath ?? '' ?>/" class="flex items-center gap-3 text-white" style="text-decoration: none;">
                <img src="<?= $basePath ?? '' ?>/assets/images/valima-logo.jpg" alt="Valima Biriyani" style="height: 42px; border-radius: 6px; object-fit: contain;">
                <div class="hidden sm:block">
                    <div class="font-serif text-white font-bold text-lg leading-tight tracking-wide">VALIMA BIRIYANI</div>
                    <div style="font-size: 0.65rem; color: var(--primary); letter-spacing: 2px; text-transform: uppercase; font-weight: 700;">Biriyani Specialist</div>
                </div>
            </a>

            <!-- Navigation Links -->
            <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
                <a href="<?= $basePath ?? '' ?>/" class="text-white hover:text-primary transition" style="text-decoration: none;">Home</a>
                <a href="<?= $basePath ?? '' ?>/menu" class="text-white hover:text-primary transition" style="text-decoration: none;">Menu</a>
                <a href="<?= $basePath ?? '' ?>/about" class="text-white hover:text-primary transition" style="text-decoration: none;">About</a>
                <a href="<?= $basePath ?? '' ?>/contact" class="text-white hover:text-primary transition" style="text-decoration: none;">Contact</a>
            </nav>

            <!-- Actions (Search, Cart & Order Online Button) -->
            <div class="flex items-center gap-3">
                <a href="<?= $basePath ?? '' ?>/menu" class="text-white hover:text-primary p-2" title="Search Menu">
                    <?= \App\Helpers\Icon::render('search', 'w-5 h-5') ?>
                </a>

                <a href="<?= $basePath ?? '' ?>/menu" class="hidden sm:inline-flex btn btn-primary btn-sm">
                    Order Online &rarr;
                </a>
            </div>
        </div>
    </header>

    <!-- ── FLASH NOTIFICATIONS ── -->
    <?php if (!empty($flashSuccess)): ?>
        <div class="container mt-4">
            <div class="p-4 rounded-md bg-green-50 border border-green-200 text-green-800 flex items-center gap-3">
                <?= \App\Helpers\Icon::render('check', 'w-5 h-5 text-green-600') ?>
                <span><?= htmlspecialchars($flashSuccess) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($flashError)): ?>
        <div class="container mt-4">
            <div class="p-4 rounded-md bg-red-50 border border-red-200 text-red-800 flex items-center gap-3">
                <?= \App\Helpers\Icon::render('x', 'w-5 h-5 text-red-600') ?>
                <span><?= htmlspecialchars($flashError) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <!-- ── MAIN PAGE CONTENT ── -->
    <main class="flex-grow pb-16 md:pb-0">
        <?= $content ?>
    </main>

    <!-- ── MOBILE BOTTOM NAVIGATION (Matches Mockup Screen 9) ── -->
    <div class="mobile-bottom-nav md:hidden">
        <a href="<?= $basePath ?? '' ?>/" class="mobile-nav-item <?= empty($_SERVER['REQUEST_URI']) || $_SERVER['REQUEST_URI'] === '/' || str_ends_with($_SERVER['REQUEST_URI'], '/hotel/hotel/') ? 'active' : '' ?>">
            <?= \App\Helpers\Icon::render('shopping-bag', 'w-5 h-5') ?>
            <span>Home</span>
        </a>
        <a href="<?= $basePath ?? '' ?>/menu" class="mobile-nav-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/menu') ? 'active' : '' ?>">
            <?= \App\Helpers\Icon::render('utensils', 'w-5 h-5') ?>
            <span>Menu</span>
        </a>
        <a href="<?= $basePath ?? '' ?>/checkout" class="mobile-nav-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/checkout') ? 'active' : '' ?>">
            <div style="position: relative;">
                <?= \App\Helpers\Icon::render('shopping-cart', 'w-5 h-5') ?>
                <span id="navCartBadge" style="display: none; position: absolute; -top: 4px; -right: 6px; background: var(--primary); color: #000; font-size: 10px; font-weight: 800; border-radius: 999px; width: 15px; height: 15px; text-align: center; line-height: 15px;">0</span>
            </div>
            <span>Cart</span>
        </a>
        <a href="<?= $basePath ?? '' ?>/dine-in" class="mobile-nav-item">
            <?= \App\Helpers\Icon::render('sparkles', 'w-5 h-5') ?>
            <span>Dine-In</span>
        </a>
        <a href="<?= $basePath ?? '' ?>/admin" class="mobile-nav-item">
            <?= \App\Helpers\Icon::render('user', 'w-5 h-5') ?>
            <span>Staff</span>
        </a>
    </div>

    <!-- ── FOOTER (Dark & Elegant) ── -->
    <footer class="bg-dark text-white border-t border-dark py-12 mt-12 hidden md:block">
        <div class="container grid" style="grid-template-columns: 2fr 1fr 1fr 1fr; gap: 2rem;">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <img src="<?= $basePath ?? '' ?>/assets/images/valima-logo.jpg" alt="Valima Biriyani" style="height: 48px; border-radius: 6px;">
                    <div>
                        <div class="font-serif font-bold text-xl text-white">VALIMA BIRIYANI</div>
                        <div style="font-size: 0.7rem; color: var(--primary); letter-spacing: 2px;">Biriyani Specialist</div>
                    </div>
                </div>
                <p style="color: #999; font-size: 0.9rem; line-height: 1.6; max-width: 320px;">
                    Experience the royal taste of authentic dum biriyani crafted with pure spices, succulent meats, and traditional recipes.
                </p>
            </div>

            <div>
                <h4 class="font-serif font-bold text-base mb-3 text-primary">Quick Links</h4>
                <ul style="list-style: none; font-size: 0.875rem; display: flex; flex-direction: column; gap: 8px;">
                    <li><a href="<?= $basePath ?? '' ?>/" style="color: #bbb; text-decoration: none;">Home</a></li>
                    <li><a href="<?= $basePath ?? '' ?>/menu" style="color: #bbb; text-decoration: none;">Our Special Menu</a></li>
                    <li><a href="<?= $basePath ?? '' ?>/dine-in" style="color: #bbb; text-decoration: none;">Table Dine-In</a></li>
                    <li><a href="<?= $basePath ?? '' ?>/about" style="color: #bbb; text-decoration: none;">About Valima</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-serif font-bold text-base mb-3 text-primary">Customer Care</h4>
                <ul style="list-style: none; font-size: 0.875rem; display: flex; flex-direction: column; gap: 8px;">
                    <li><a href="<?= $basePath ?? '' ?>/contact" style="color: #bbb; text-decoration: none;">Contact & WhatsApp</a></li>
                    <li><a href="<?= $basePath ?? '' ?>/faq" style="color: #bbb; text-decoration: none;">FAQs</a></li>
                    <li><a href="<?= $basePath ?? '' ?>/refund-policy" style="color: #bbb; text-decoration: none;">Refund Policy</a></li>
                    <li><a href="<?= $basePath ?? '' ?>/delivery-policy" style="color: #bbb; text-decoration: none;">Delivery Policy</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-serif font-bold text-base mb-3 text-primary">Opening Hours</h4>
                <div style="font-size: 0.85rem; color: #bbb; line-height: 1.6;">
                    Mon – Sun: 11:30 AM – 11:00 PM<br>
                    <span style="color: var(--primary);">Fresh Dum Biriyani Batches Daily</span>
                </div>
                <div style="margin-top: 1rem;">
                    <a href="https://wa.me/919876543210" target="_blank" class="btn btn-outline-gold btn-sm" style="display: inline-flex;">
                        <?= \App\Helpers\Icon::render('phone', 'w-4 h-4') ?> WhatsApp Direct
                    </a>
                </div>
            </div>
        </div>

        <div class="container" style="border-top: 1px solid #222; margin-top: 2rem; padding-top: 1.5rem; text-align: center; color: #777; font-size: 0.8rem;">
            &copy; <?= date('Y') ?> Valima Biriyani. All rights reserved. Built with Core PHP & Free-Stack Architecture.
        </div>
    </footer>

    <!-- Global Cart Count Synchronizer -->
    <script>
        function updateGlobalCartBadge() {
            try {
                const raw = localStorage.getItem('valima_cart');
                const badge = document.getElementById('navCartBadge');
                if (!badge) return;
                if (raw) {
                    const cart = JSON.parse(raw);
                    const count = Object.values(cart).reduce((sum, item) => sum + (item.qty || 1), 0);
                    if (count > 0) {
                        badge.innerText = count;
                        badge.style.display = 'block';
                    } else {
                        badge.style.display = 'none';
                    }
                } else {
                    badge.style.display = 'none';
                }
            } catch (e) {}
        }
        window.addEventListener('load', updateGlobalCartBadge);
        window.addEventListener('storage', updateGlobalCartBadge);
    </script>
</body>
</html>
