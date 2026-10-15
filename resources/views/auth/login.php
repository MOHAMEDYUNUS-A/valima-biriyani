<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login | <?= \App\Helpers\Security::e($app['name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #FF6B2C;
            --primary-dark: #E05520;
            --primary-light: #FFF0E9;
            --bg: #FFF8F5;
            --text: #1C0A00;
            --text-2: #7A5C4E;
            --border: #F0DDD5;
            --radius: 14px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            background-image:
                radial-gradient(circle at 15% 15%, #FFF0E9 0%, transparent 40%),
                radial-gradient(circle at 85% 85%, #FDEFD8 0%, transparent 40%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            color: var(--text);
        }
        .login-wrap {
            width: 100%;
            max-width: 420px;
        }
        .login-card {
            background: #FFFFFF;
            border-radius: var(--radius);
            box-shadow: 0 10px 40px rgba(255,107,44,0.10);
            border: 1px solid var(--border);
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, var(--primary) 0%, #FF9A5C 100%);
            padding: 2.2rem 2rem 1.8rem;
            text-align: center;
            color: #fff;
        }
        .brand-badge {
            font-size: 38px;
            margin-bottom: 8px;
            display: inline-block;
        }
        .brand-title {
            font-family: 'Poppins', sans-serif;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }
        .brand-sub {
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            opacity: 0.9;
            margin-top: 2px;
            font-weight: 500;
        }
        .card-body {
            padding: 2rem 2rem 2.2rem;
        }
        .alert {
            padding: 11px 14px;
            border-radius: 8px;
            font-size: 13.5px;
            margin-bottom: 1.4rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .alert-error {
            background: #FFF1F0;
            color: #D63031;
            border: 1px solid #FFD5D2;
        }
        .alert-success {
            background: #F0FDF4;
            color: #166534;
            border: 1px solid #BBF7D0;
        }
        .form-group {
            margin-bottom: 1.3rem;
        }
        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-2);
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 6px;
        }
        .form-input {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            color: var(--text);
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
            background: #FAFBFC;
        }
        .form-input:focus {
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(255,107,44,0.12);
        }
        .btn-submit {
            width: 100%;
            padding: 13px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: background 0.15s, transform 0.1s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 1.6rem;
            box-shadow: 0 4px 14px rgba(255,107,44,0.25);
        }
        .btn-submit:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }
        .btn-submit:active {
            transform: scale(0.98);
        }
        .demo-box {
            margin-top: 1.8rem;
            padding: 12px 14px;
            background: #FDF9F5;
            border-radius: 8px;
            border: 1px dashed var(--border);
            font-size: 12px;
            color: var(--text-2);
            line-height: 1.6;
        }
        .demo-box strong {
            color: var(--text);
        }
        .footer-note {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 13px;
            color: var(--text-2);
        }
        .footer-note a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="login-wrap">
    <div class="login-card">
        <div class="card-header">
            <div class="brand-badge">🍲</div>
            <div class="brand-title">Valima Biriyani</div>
            <div class="brand-sub">Management Portal</div>
        </div>

        <div class="card-body">
            <?php if (!empty($flashError)): ?>
                <div class="alert alert-error">
                    <span>⚠️</span> <?= \App\Helpers\Security::e($flashError) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($flashSuccess)): ?>
                <div class="alert alert-success">
                    <span>✅</span> <?= \App\Helpers\Security::e($flashSuccess) ?>
                </div>
            <?php endif; ?>

            <form action="/login" method="POST">
                <?= $csrf_field ?>

                <div class="form-group">
                    <label class="form-label">Staff Email Address</label>
                    <input type="email" name="email" class="form-input" placeholder="e.g. admin@valimabiriyani.com" required autofocus autocomplete="username">
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-input" placeholder="••••••••" required autocomplete="current-password">
                </div>

                <button type="submit" class="btn-submit">
                    <span>Sign In to Terminal</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </button>
            </form>

            <div class="demo-box">
                <div><strong>Default Owner:</strong> admin@valimabiriyani.com / Admin@Valima2026</div>
                <div><strong>Demo Cashier:</strong> cashier@valimabiriyani.com / Cashier@2026</div>
                <div><strong>Demo Kitchen:</strong> kitchen@valimabiriyani.com / Kitchen@2026</div>
            </div>
        </div>
    </div>

    <div class="footer-note">
        <a href="/">← Return to Customer Storefront</a>
    </div>
</div>

</body>
</html>
