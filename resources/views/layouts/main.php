<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= \App\Helpers\Security::e($title ?? 'Authentic Dum Biriyani & More') ?> | <?= \App\Helpers\Security::e($app['name']) ?></title>
    <meta name="description" content="Experience authentic Dum Biriyani, flavorful Fried Rice, Hakka Noodles and culinary delights at Valima Biriyani. Dine-in, Takeaway and Online Ordering.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,700;1,500&family=Poppins:wght@400;500;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Design System CSS -->
    <style>
        :root {
            --primary: #FF6B2C;
            --primary-light: #FFF0E9;
            --primary-mid: #FFD6BF;
            --primary-dark: #E05520;
            --accent: #FFB800;
            --accent-light: #FFF8E1;
            --surface: #FFFFFF;
            --bg: #FFF8F5;
            --text: #1C0A00;
            --text-2: #7A5C4E;
            --text-3: #B89080;
            --border: #F0DDD5;
            --success: #1EA87A;
            --success-light: #E6F7F1;
            --danger: #E53935;
            --radius-sm: 8px;
            --radius-md: 14px;
            --radius-lg: 20px;
            --shadow-sm: 0 1px 4px rgba(255,107,44,0.08);
            --shadow-md: 0 4px 20px rgba(255,107,44,0.12);
            --shadow-lg: 0 8px 32px rgba(255,107,44,0.16);
            --font-display: 'Playfair Display', serif;
            --font-heading: 'Poppins', sans-serif;
            --font-body: 'DM Sans', sans-serif;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: var(--font-body);
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Top Announcement / Nav ── */
        .site-nav {
            background: #FFFFFF;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow-sm);
        }

        .nav-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0.8rem 1.2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand-link {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: inherit;
        }

        .brand-logo-icon {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, var(--primary) 0%, #FF9A5C 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 20px;
            box-shadow: 0 3px 10px rgba(255,107,44,0.3);
        }

        .brand-text-block {
            display: flex;
            flex-direction: column;
        }

        .brand-title {
            font-family: var(--font-heading);
            font-size: 19px;
            font-weight: 700;
            color: var(--text);
            line-height: 1.1;
            letter-spacing: -0.3px;
        }

        .brand-tag {
            font-size: 10px;
            letter-spacing: 1.8px;
            text-transform: uppercase;
            color: var(--primary-dark);
            font-weight: 600;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            list-style: none;
        }

        .nav-menu a {
            text-decoration: none;
            color: var(--text-2);
            font-size: 14px;
            font-weight: 500;
            transition: color 0.15s ease;
        }

        .nav-menu a:hover {
            color: var(--primary);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-order-nav {
            background: var(--primary);
            color: #fff !important;
            padding: 8px 18px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13.5px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.15s, transform 0.1s;
        }

        .btn-order-nav:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        /* ── Flash Messages ── */
        .flash-wrap {
            max-width: 1200px;
            margin: 1rem auto 0;
            padding: 0 1.2rem;
            width: 100%;
        }

        .flash {
            padding: 12px 18px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .flash-success {
            background: var(--success-light);
            color: var(--success);
            border: 1px solid #bbf7d0;
        }

        .flash-error {
            background: #fff1f0;
            color: var(--danger);
            border: 1px solid #ffd5d2;
        }

        /* ── Page Content ── */
        main.main-content {
            flex: 1;
            width: 100%;
        }

        /* ── Footer ── */
        .site-footer {
            background: #1C0A00;
            color: #E8DDD8;
            padding: 3rem 1.2rem 1.5rem;
            margin-top: 4rem;
        }

        .footer-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1.5fr;
            gap: 2.5rem;
            padding-bottom: 2.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .footer-col h4 {
            font-family: var(--font-heading);
            font-size: 16px;
            color: #fff;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .footer-col p {
            font-size: 13.5px;
            line-height: 1.6;
            color: #B89080;
        }

        .footer-col ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .footer-col ul a {
            color: #B89080;
            text-decoration: none;
            font-size: 13.5px;
            transition: color 0.15s;
        }

        .footer-col ul a:hover {
            color: var(--primary);
        }

        .footer-bottom {
            max-width: 1200px;
            margin: 1.5rem auto 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12.5px;
            color: #7A5C4E;
        }

        @media (max-width: 768px) {
            .footer-inner {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            .nav-menu {
                display: none;
            }
            .footer-bottom {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
        }
    </style>
</head>
<body>

    <!-- Navigation Header -->
    <nav class="site-nav">
        <div class="nav-inner">
            <a href="/" class="brand-link">
                <div class="brand-logo-icon">🍲</div>
                <div class="brand-text-block">
                    <span class="brand-title"><?= \App\Helpers\Security::e($app['restaurant']['name']) ?></span>
                    <span class="brand-tag">Royal Culinary Experience</span>
                </div>
            </a>

            <ul class="nav-menu">
                <li><a href="/">Home</a></li>
                <li><a href="/menu">Menu</a></li>
                <li><a href="/about">About Us</a></li>
                <li><a href="/contact">Contact</a></li>
                <li><a href="/faq">FAQ</a></li>
            </ul>

            <div class="nav-actions">
                <a href="/dine-in" class="btn-order-nav">
                    <span>Order Online</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </nav>

    <!-- Flash Alerts -->
    <?php if (!empty($flashSuccess)): ?>
        <div class="flash-wrap">
            <div class="flash flash-success">
                <span>✅</span>
                <div><?= \App\Helpers\Security::e($flashSuccess) ?></div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($flashError)): ?>
        <div class="flash-wrap">
            <div class="flash flash-error">
                <span>⚠️</span>
                <div><?= \App\Helpers\Security::e($flashError) ?></div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Page Content -->
    <main class="main-content">
        <?= $content ?? '' ?>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-col">
                <h4><?= \App\Helpers\Security::e($app['restaurant']['name']) ?></h4>
                <p><?= \App\Helpers\Security::e($app['restaurant']['tagline']) ?></p>
                <p style="margin-top: 10px;"><?= \App\Helpers\Security::e($app['restaurant']['address']) ?></p>
                <p style="margin-top: 6px;">Phone: <?= \App\Helpers\Security::e($app['restaurant']['phone']) ?></p>
            </div>
            <div class="footer-col">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="/">Home</a></li>
                    <li><a href="/menu">Browse Menu</a></li>
                    <li><a href="/dine-in">Table Ordering</a></li>
                    <li><a href="/about">Our Story</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Policies</h4>
                <ul>
                    <li><a href="/privacy-policy">Privacy Policy</a></li>
                    <li><a href="/terms-of-service">Terms of Service</a></li>
                    <li><a href="/refund-policy">Refund & Cancellation</a></li>
                    <li><a href="/delivery-policy">Delivery Policy</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Operating Hours</h4>
                <p>Monday – Sunday</p>
                <p style="color: #fff; font-weight: 600; margin-top: 4px;">11:00 AM – 11:30 PM</p>
                <p style="margin-top: 10px; font-size: 12px; color: #7A5C4E;">Counter Cash & UPI Accepted</p>
            </div>
        </div>
        <div class="footer-bottom">
            <div>&copy; <?= date('Y') ?> <?= \App\Helpers\Security::e($app['restaurant']['name']) ?>. All rights reserved.</div>
            <div>Built on Free-Stack Core PHP & MySQL.</div>
        </div>
    </footer>

</body>
</html>
