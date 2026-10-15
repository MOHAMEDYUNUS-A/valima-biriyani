<style>
    .menu-page {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1.2rem;
    }
    .menu-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1.5rem;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid var(--border);
    }
    .menu-title-block h1 {
        font-family: var(--font-display);
        font-size: 34px;
        color: var(--text);
        margin-bottom: 4px;
    }
    .menu-title-block p {
        font-size: 14px;
        color: var(--text-2);
    }
    .menu-filter-bar {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .search-input {
        padding: 10px 14px;
        border: 1.5px solid var(--border);
        border-radius: 8px;
        font-size: 13.5px;
        outline: none;
        background: #fff;
        width: 220px;
        transition: border 0.15s;
    }
    .search-input:focus {
        border-color: var(--primary);
    }
    .veg-toggle-btn {
        padding: 8px 14px;
        border-radius: 8px;
        border: 1.5px solid #bbf7d0;
        background: #f0fdf4;
        color: #166534;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .veg-toggle-btn.active {
        background: #166534;
        color: #fff;
    }
    .categories-strip {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        padding-bottom: 12px;
        margin-bottom: 2rem;
    }
    .cat-pill {
        padding: 9px 18px;
        border-radius: 20px;
        background: #fff;
        border: 1px solid var(--border);
        color: var(--text-2);
        font-size: 13.5px;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .cat-pill:hover {
        border-color: var(--primary);
        color: var(--primary);
    }
    .cat-pill.active {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
        box-shadow: 0 4px 12px rgba(255,107,44,0.25);
    }
    .items-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 24px;
    }
    .item-card {
        background: #fff;
        border-radius: var(--radius-md);
        border: 1px solid var(--border);
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        display: flex;
        flex-direction: column;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .item-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
    }
    .item-image-wrap {
        height: 180px;
        background: #ffe8d5;
        position: relative;
        overflow: hidden;
    }
    .item-image-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .item-veg-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        width: 18px;
        height: 18px;
        background: #fff;
        border-radius: 4px;
        border: 1.5px solid #22c55e;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .item-veg-badge::after {
        content: '';
        width: 8px;
        height: 8px;
        background: #22c55e;
        border-radius: 50%;
    }
    .item-nonveg-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        width: 18px;
        height: 18px;
        background: #fff;
        border-radius: 4px;
        border: 1.5px solid #ef4444;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .item-nonveg-badge::after {
        content: '';
        width: 8px;
        height: 8px;
        background: #ef4444;
        border-radius: 50%;
    }
    .item-body {
        padding: 16px 18px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .item-category-tag {
        font-size: 11px;
        color: var(--primary-dark);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 4px;
    }
    .item-name {
        font-family: var(--font-heading);
        font-size: 17px;
        font-weight: 600;
        color: var(--text);
        margin-bottom: 6px;
    }
    .item-desc {
        font-size: 12.5px;
        color: var(--text-2);
        line-height: 1.5;
        margin-bottom: 14px;
        flex: 1;
    }
    .item-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 12px;
        border-top: 1px dashed var(--border);
    }
    .item-price {
        font-family: var(--font-heading);
        font-size: 19px;
        font-weight: 700;
        color: var(--text);
    }
    .item-price span {
        font-size: 14px;
        color: var(--primary);
    }
    .btn-add {
        background: var(--primary-light);
        color: var(--primary-dark);
        border: 1.5px solid var(--primary-mid);
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.15s;
    }
    .btn-add:hover {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
    }
    .table-indicator {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
        padding: 10px 16px;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 13.5px;
    }
</style>

<div class="menu-page">
    <?php if (!empty($tableId)): ?>
        <div class="table-indicator">
            <div>🍽️ <strong>Dine-In Session:</strong> Table #<?= \App\Helpers\Security::e((string)$tableId) ?></div>
            <a href="/dine-in" style="color:#1d4ed8; font-weight:600; text-decoration:none;">Change Table</a>
        </div>
    <?php endif; ?>

    <div class="menu-header">
        <div class="menu-title-block">
            <h1>Our Culinary Specialties</h1>
            <p>Every dish is crafted using traditional firewood dum cooking and premium spices.</p>
        </div>

        <div class="menu-filter-bar">
            <form action="/menu" method="GET" style="display:flex; gap:8px;">
                <?php if ($selectedCategory): ?>
                    <input type="hidden" name="category" value="<?= \App\Helpers\Security::e($selectedCategory) ?>">
                <?php endif; ?>
                <input type="text" name="q" class="search-input" placeholder="Search biriyani, rice..." value="<?= \App\Helpers\Security::e($searchQuery) ?>">
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
            </form>

            <a href="/menu?<?= http_build_query(array_merge($_GET, ['veg' => $vegOnly ? null : '1'])) ?>" class="veg-toggle-btn <?= $vegOnly ? 'active' : '' ?>">
                <span>🌱</span> <?= $vegOnly ? 'Show All' : 'Veg Only' ?>
            </a>
        </div>
    </div>

    <!-- Category Pills -->
    <div class="categories-strip">
        <a href="/menu" class="cat-pill <?= empty($selectedCategory) ? 'active' : '' ?>">
            <span>🍽️</span> All Dishes
        </a>
        <?php foreach ($categories as $cat): ?>
            <a href="/menu?category=<?= urlencode($cat['slug']) ?>" class="cat-pill <?= ($selectedCategory === $cat['slug']) ? 'active' : '' ?>">
                <span><?= \App\Helpers\Security::e($cat['icon'] ?: '🍲') ?></span>
                <?= \App\Helpers\Security::e($cat['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Menu Items Grid -->
    <?php if (empty($items)): ?>
        <div style="text-align:center; padding:4rem 1rem; background:#fff; border-radius:14px; border:1px solid var(--border);">
            <div style="font-size:42px; margin-bottom:10px;">🔍</div>
            <h3>No menu items found</h3>
            <p style="color:var(--text-2); margin-top:6px;">Try adjusting your search query or category filter.</p>
            <p style="margin-top:15px;"><a href="/menu" class="btn btn-secondary btn-sm">Clear Filters</a></p>
        </div>
    <?php else: ?>
        <div class="items-grid">
            <?php foreach ($items as $item): ?>
                <div class="item-card">
                    <div class="item-image-wrap">
                        <img src="<?= \App\Helpers\Security::e($item['image_url'] ?: 'https://images.unsplash.com/photo-1563379091339-03246963d51a?w=400&q=80') ?>" alt="<?= \App\Helpers\Security::e($item['name']) ?>" loading="lazy">
                        <?php if ($item['is_veg']): ?>
                            <div class="item-veg-badge" title="Vegetarian"></div>
                        <?php else: ?>
                            <div class="item-nonveg-badge" title="Non-Vegetarian"></div>
                        <?php endif; ?>
                    </div>
                    <div class="item-body">
                        <div class="item-category-tag"><?= \App\Helpers\Security::e($item['category_name']) ?></div>
                        <h3 class="item-name"><?= \App\Helpers\Security::e($item['name']) ?></h3>
                        <div class="item-desc"><?= \App\Helpers\Security::e($item['description'] ?: 'Authentic signature recipe.') ?></div>
                        <div class="item-footer">
                            <div class="item-price"><span>₹</span><?= number_format((float)$item['base_price'], 2) ?></div>
                            <button class="btn-add" onclick="addToCart(<?= (int)$item['id'] ?>, '<?= addslashes($item['name']) ?>', <?= (float)$item['base_price'] ?>)">+ Add</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
// Customer cart handler (client state synced with server in Phase 6 checkout)
function addToCart(id, name, price) {
    let cart = JSON.parse(localStorage.getItem('valima_cart') || '{}');
    if (!cart[id]) {
        cart[id] = { id: id, name: name, price: price, qty: 1 };
    } else {
        cart[id].qty++;
    }
    localStorage.setItem('valima_cart', JSON.stringify(cart));
    alert('Added ' + name + ' to your cart!');
}
</script>
