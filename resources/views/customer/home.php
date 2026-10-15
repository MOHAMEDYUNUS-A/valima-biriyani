<style>
    .hero {
        background: linear-gradient(145deg, #fff8f0 0%, #fff3e6 100%);
        padding: 4rem 1.5rem;
        border-bottom: 1px solid var(--border);
        text-align: center;
    }
    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--primary-light);
        color: var(--primary-dark);
        border: 1px solid var(--primary-mid);
        border-radius: 30px;
        padding: 6px 16px;
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        margin-bottom: 1.2rem;
    }
    .hero-title {
        font-family: var(--font-display);
        font-size: 46px;
        font-weight: 700;
        line-height: 1.15;
        color: var(--text);
        max-width: 780px;
        margin: 0 auto 1.2rem;
    }
    .hero-desc {
        font-size: 16px;
        color: var(--text-2);
        max-width: 580px;
        margin: 0 auto 2rem;
        line-height: 1.6;
    }
    .hero-actions {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 14px;
        flex-wrap: wrap;
    }
    .btn-hero-primary {
        background: var(--primary);
        color: #fff;
        padding: 14px 28px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 15px;
        text-decoration: none;
        box-shadow: 0 4px 16px rgba(255,107,44,0.3);
        transition: transform 0.15s, background 0.15s;
    }
    .btn-hero-primary:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
    }
    .btn-hero-secondary {
        background: #fff;
        color: var(--text);
        border: 1.5px solid var(--border);
        padding: 13px 26px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 15px;
        text-decoration: none;
        transition: background 0.15s;
    }
    .btn-hero-secondary:hover {
        background: #fdfaf7;
    }
    .quick-features {
        max-width: 1100px;
        margin: -30px auto 4rem;
        padding: 0 1.5rem;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        position: relative;
        z-index: 10;
    }
    .feature-card {
        background: #fff;
        border-radius: var(--radius-md);
        padding: 24px;
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border);
        text-align: center;
    }
    .feature-icon {
        font-size: 32px;
        margin-bottom: 12px;
    }
    .feature-card h3 {
        font-family: var(--font-heading);
        font-size: 17px;
        font-weight: 600;
        margin-bottom: 8px;
    }
    .feature-card p {
        font-size: 13.5px;
        color: var(--text-2);
        line-height: 1.5;
    }
    @media (max-width: 768px) {
        .hero-title { font-size: 32px; }
        .quick-features { grid-template-columns: 1fr; margin-top: 2rem; }
    }
</style>

<div class="hero">
    <div class="hero-badge">
        <span>✨</span> Traditional Dum Cooking
    </div>
    <h1 class="hero-title">A Symphony of Spices & Royal Heritage Biriyani</h1>
    <p class="hero-desc">
        Welcome to <?= \App\Helpers\Security::e($app['restaurant']['name']) ?>. Prepared with long-grain aged Basmati rice, tender farm-fresh meats, and our heirloom spice blend slow-cooked over firewood dum.
    </p>
    <div class="hero-actions">
        <a href="/dine-in" class="btn-hero-primary">🍽️ Dine-in Table Order</a>
        <a href="/menu" class="btn-hero-secondary">📋 Explore Full Menu</a>
    </div>
</div>

<div class="quick-features">
    <div class="feature-card">
        <div class="feature-icon">🔥</div>
        <h3>Firewood Dum Cooked</h3>
        <p>Sealed in authentic copper handis to lock in every note of saffron, cardamom, and spice.</p>
    </div>
    <div class="feature-card">
        <div class="feature-icon">⚡</div>
        <h3>Live Kitchen Tracking</h3>
        <p>Watch your order progress from prep station to your table with live status updates.</p>
    </div>
    <div class="feature-card">
        <div class="feature-icon">🎁</div>
        <h3>Instant Loyalty Rewards</h3>
        <p>Earn loyalty points automatically with every order and redeem on your favorite biriyani.</p>
    </div>
</div>
