<?php
use App\Helpers\Database;

// Fetch live metrics
$todayDate = date('Y-m-d');
$ordersToday = (int)Database::queryOne("SELECT COUNT(*) as c FROM orders WHERE date(created_at) = date('now')")['c'];
$grossCollected = (float)Database::queryOne("SELECT COALESCE(SUM(total_amount), 0) as s FROM orders WHERE date(created_at) = date('now') AND payment_status = 'paid'")['s'];
$pendingPrep = (int)Database::queryOne("SELECT COUNT(*) as c FROM orders WHERE status IN ('new', 'accepted', 'preparing')")['c'];
$servedToday = (int)Database::queryOne("SELECT COUNT(*) as c FROM orders WHERE date(created_at) = date('now') AND status IN ('served', 'completed')")['c'];

$recentOrders = Database::query("
    SELECT o.*, t.table_number
    FROM orders o
    LEFT JOIN restaurant_tables t ON o.table_id = t.id
    ORDER BY o.id DESC LIMIT 8
");
?>

<div class="page-header">
    <div>
        <h1>Restaurant Operations Dashboard</h1>
        <div class="sub">Welcome back, <?= \App\Helpers\Security::e($currentUser['name'] ?? 'Administrator') ?> · Real-time status for <?= date('l, d F Y') ?></div>
    </div>
    <div style="display:flex; gap:10px;">
        <a href="/pos" class="btn btn-primary">🖥️ Open POS Terminal</a>
        <a href="/kitchen" class="btn btn-secondary" target="_blank">👨‍🍳 Kitchen Screen</a>
    </div>
</div>

<!-- Stats Row -->
<div class="stats-row" style="display:grid; grid-template-columns: repeat(4, 1fr); gap:16px; margin-bottom:24px;">
    <div class="stat-card">
        <div class="s-icon" style="background:#fff0ee; color:#e8533a;">🧾</div>
        <div class="s-info">
            <div class="s-label">Today's Orders</div>
            <div class="s-value"><?= $ordersToday ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="s-icon" style="background:#f0fdf4; color:#166534;">💰</div>
        <div class="s-info">
            <div class="s-label">Collected Sales</div>
            <div class="s-value">₹<?= number_format($grossCollected, 2) ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="s-icon" style="background:#fff8e7; color:#b45309;">👨‍🍳</div>
        <div class="s-info">
            <div class="s-label">In Kitchen</div>
            <div class="s-value"><?= $pendingPrep ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="s-icon" style="background:#eff6ff; color:#1d4ed8;">🍽️</div>
        <div class="s-info">
            <div class="s-label">Served Today</div>
            <div class="s-value"><?= $servedToday ?></div>
        </div>
    </div>
</div>

<!-- Recent Orders Table -->
<div class="card">
    <div class="card-header">
        <h3>Live Orders Stream</h3>
        <a href="/admin/orders" class="btn btn-sm btn-secondary">View All Orders →</a>
    </div>

    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Type / Channel</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentOrders)): ?>
                    <tr><td colspan="7" style="text-align:center; padding:2rem; color:var(--text2);">No orders placed today yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($recentOrders as $ro): ?>
                        <tr>
                            <td style="font-weight:700;">
                                <a href="/admin/orders/<?= $ro['id'] ?>" style="color:var(--primary); text-decoration:none;">
                                    <?= \App\Helpers\Security::e($ro['order_number']) ?>
                                </a>
                            </td>
                            <td>
                                <div><?= \App\Helpers\Security::e($ro['customer_name']) ?></div>
                                <div style="font-size:12px; color:var(--text2);"><?= \App\Helpers\Security::e($ro['customer_phone']) ?></div>
                            </td>
                            <td>
                                <?php if ($ro['order_type'] === 'dine_in'): ?>
                                    <span class="badge badge-info">Table #<?= $ro['table_number'] ?? $ro['table_id'] ?></span>
                                <?php elseif ($ro['order_type'] === 'delivery'): ?>
                                    <span class="badge badge-warn">Delivery</span>
                                <?php else: ?>
                                    <span class="badge badge-neutral">Takeaway</span>
                                <?php endif; ?>
                                <span style="font-size:11px; color:var(--text2); text-transform:uppercase;"> · <?= $ro['channel'] ?></span>
                            </td>
                            <td style="font-weight:700;">₹<?= number_format((float)$ro['total_amount'], 2) ?></td>
                            <td>
                                <span class="badge <?= $ro['payment_status'] === 'paid' ? 'badge-success' : 'badge-warn' ?>">
                                    <?= ucfirst($ro['payment_status']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-neutral"><?= ucfirst($ro['status']) ?></span>
                            </td>
                            <td style="text-align:right;">
                                <a href="/admin/orders/<?= $ro['id'] ?>" class="btn btn-sm btn-secondary">Manage</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
