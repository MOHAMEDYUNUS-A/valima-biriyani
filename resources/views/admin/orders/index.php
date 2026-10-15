<div class="page-header">
    <div>
        <h1>Central Orders Management</h1>
        <div class="sub">Filter, inspect, and update orders across Website, POS, Swiggy, and WhatsApp.</div>
    </div>
</div>

<div class="card" style="margin-bottom: 20px;">
    <form action="/admin/orders" method="GET" style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">
        <div class="form-group" style="flex:2; min-width:200px;">
            <label>Search Orders</label>
            <input type="text" name="q" placeholder="Order #, Customer Name, or Phone..." value="<?= \App\Helpers\Security::e($search) ?>">
        </div>

        <div class="form-group" style="flex:1; min-width:140px;">
            <label>Status</label>
            <select name="status" style="padding:9px 12px; border:1px solid #dde1ea; border-radius:8px; width:100%;">
                <option value="">All Statuses</option>
                <?php foreach (['new', 'accepted', 'preparing', 'ready', 'out_for_delivery', 'served', 'completed', 'cancelled'] as $st): ?>
                    <option value="<?= $st ?>" <?= ($statusFilter === $st) ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $st)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group" style="flex:1; min-width:140px;">
            <label>Channel</label>
            <select name="channel" style="padding:9px 12px; border:1px solid #dde1ea; border-radius:8px; width:100%;">
                <option value="">All Channels</option>
                <?php foreach (['website', 'pos', 'phone', 'whatsapp', 'swiggy', 'zomato'] as $ch): ?>
                    <option value="<?= $ch ?>" <?= ($channelFilter === $ch) ? 'selected' : '' ?>><?= ucfirst($ch) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="/admin/orders" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

<div class="card">
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Created At</th>
                    <th>Customer</th>
                    <th>Fulfillment</th>
                    <th>Channel</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="9" style="text-align:center; padding:2rem; color:var(--text2);">No orders match the selected filters.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td style="font-weight:700;">
                                <a href="/admin/orders/<?= $o['id'] ?>" style="color:var(--primary); text-decoration:none;">
                                    <?= \App\Helpers\Security::e($o['order_number']) ?>
                                </a>
                            </td>
                            <td style="font-size:12px; color:var(--text2);">
                                <?= date('d M, h:i A', strtotime($o['created_at'])) ?>
                            </td>
                            <td>
                                <div style="font-weight:600;"><?= \App\Helpers\Security::e($o['customer_name']) ?></div>
                                <div style="font-size:12px; color:var(--text2);"><?= \App\Helpers\Security::e($o['customer_phone']) ?></div>
                            </td>
                            <td>
                                <?php if ($o['order_type'] === 'dine_in'): ?>
                                    <span class="badge badge-info">Table #<?= $o['table_number'] ?? $o['table_id'] ?></span>
                                <?php elseif ($o['order_type'] === 'delivery'): ?>
                                    <span class="badge badge-warn">Delivery</span>
                                <?php else: ?>
                                    <span class="badge badge-neutral">Takeaway</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-neutral" style="text-transform:uppercase; font-size:10px;">
                                    <?= \App\Helpers\Security::e($o['channel']) ?>
                                </span>
                            </td>
                            <td style="font-weight:700;">₹<?= number_format((float)$o['total_amount'], 2) ?></td>
                            <td>
                                <span class="badge <?= $o['payment_status'] === 'paid' ? 'badge-success' : 'badge-warn' ?>">
                                    <?= ucfirst($o['payment_status']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-neutral"><?= ucfirst(str_replace('_', ' ', $o['status'])) ?></span>
                            </td>
                            <td style="text-align:right;">
                                <div style="display:inline-flex; gap:6px;">
                                    <a href="/admin/orders/<?= $o['id'] ?>" class="btn btn-sm btn-secondary">Details</a>
                                    <a href="/pos/receipt/<?= $o['id'] ?>" target="_blank" class="btn btn-sm btn-secondary" title="Print Bill">🖨️</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
