<div class="page-header">
    <div>
        <h1>Order #<?= \App\Helpers\Security::e($order['order_number']) ?></h1>
        <div class="sub">Placed on <?= date('d M Y, h:i:s A', strtotime($order['created_at'])) ?> via <?= strtoupper(\App\Helpers\Security::e($order['channel'])) ?></div>
    </div>
    <div style="display:flex; gap:10px;">
        <a href="/pos/receipt/<?= $order['id'] ?>" target="_blank" class="btn btn-secondary">🖨️ Print Receipt</a>
        <a href="/admin/orders" class="btn btn-secondary">← Back to Orders</a>
    </div>
</div>

<div style="display:grid; grid-template-columns: 2fr 1fr; gap:24px; align-items:flex-start;">
    <!-- Left: Order Items & Pricing Breakdown -->
    <div>
        <div class="card">
            <div class="card-header">
                <h3>Order Items (<?= count($items) ?> Items)</h3>
                <span class="badge badge-info"><?= strtoupper(\App\Helpers\Security::e($order['order_type'])) ?></span>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th style="text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $it): ?>
                        <tr>
                            <td>
                                <div style="font-weight:600;"><?= \App\Helpers\Security::e($it['item_name']) ?></div>
                                <?php if (!empty($it['notes'])): ?>
                                    <div style="font-size:12px; color:var(--text2);">Note: <?= \App\Helpers\Security::e($it['notes']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>₹<?= number_format((float)$it['unit_price'], 2) ?></td>
                            <td>× <?= (int)$it['quantity'] ?></td>
                            <td style="text-align: right; font-weight:600;">₹<?= number_format((float)$it['subtotal'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="margin-top:20px; padding-top:15px; border-top:1px solid var(--border); max-width:320px; margin-left:auto;">
                <div style="display:flex; justify-content:space-between; padding:4px 0; font-size:13.5px; color:var(--text2);">
                    <span>Subtotal:</span>
                    <span>₹<?= number_format((float)$order['subtotal'], 2) ?></span>
                </div>
                <div style="display:flex; justify-content:space-between; padding:4px 0; font-size:13.5px; color:var(--text2);">
                    <span>GST (5%):</span>
                    <span>₹<?= number_format((float)$order['tax_amount'], 2) ?></span>
                </div>
                <?php if ((float)$order['delivery_fee'] > 0): ?>
                    <div style="display:flex; justify-content:space-between; padding:4px 0; font-size:13.5px; color:var(--text2);">
                        <span>Delivery Fee:</span>
                        <span>₹<?= number_format((float)$order['delivery_fee'], 2) ?></span>
                    </div>
                <?php endif; ?>
                <div style="display:flex; justify-content:space-between; padding:10px 0 0; margin-top:8px; border-top:2px solid var(--border); font-size:18px; font-weight:700; color:var(--primary);">
                    <span>Final Total:</span>
                    <span>₹<?= number_format((float)$order['total_amount'], 2) ?></span>
                </div>
            </div>
        </div>

        <!-- Status Events Timeline -->
        <div class="card">
            <div class="card-header">
                <h3>Order Lifecycle Timeline</h3>
            </div>
            <div style="display:flex; flex-direction:column; gap:12px;">
                <?php foreach ($events as $ev): ?>
                    <div style="display:flex; gap:12px; font-size:13.5px; border-left:2px solid var(--primary); padding-left:14px;">
                        <div style="color:var(--text2); min-width:140px; font-size:12px;">
                            <?= date('d M, h:i A', strtotime($ev['created_at'])) ?>
                        </div>
                        <div>
                            <strong><?= ucfirst(str_replace('_', ' ', $ev['status'])) ?></strong>
                            <div style="color:var(--text2); font-size:12.5px;"><?= \App\Helpers\Security::e($ev['notes']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Right: Customer Info & Status Action Box -->
    <div>
        <div class="card">
            <div class="card-header">
                <h3>Update Order Status</h3>
            </div>

            <form action="/admin/orders/<?= $order['id'] ?>/status" method="POST">
                <?= $csrf_field ?>
                <div class="form-group" style="margin-bottom:15px;">
                    <label>Current Status</label>
                    <div style="margin-top:4px;">
                        <span class="badge badge-warn" style="font-size:13px;"><?= strtoupper(\App\Helpers\Security::e($order['status'])) ?></span>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:15px;">
                    <label>Change Status To *</label>
                    <select name="status" style="padding:10px; border:1px solid #dde1ea; border-radius:8px; width:100%;" required>
                        <?php foreach (['new', 'accepted', 'preparing', 'ready', 'out_for_delivery', 'served', 'completed', 'cancelled'] as $st): ?>
                            <option value="<?= $st ?>" <?= ($order['status'] === $st) ? 'selected' : '' ?>>
                                <?= ucfirst(str_replace('_', ' ', $st)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom:18px;">
                    <label>Action Notes (Audit Log)</label>
                    <input type="text" name="notes" placeholder="e.g. Dispatched with rider...">
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">Update Status</button>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Customer & Delivery</h3>
            </div>
            <div style="line-height:1.7; font-size:13.5px;">
                <div><strong>Name:</strong> <?= \App\Helpers\Security::e($order['customer_name']) ?></div>
                <div><strong>Phone:</strong> <?= \App\Helpers\Security::e($order['customer_phone']) ?></div>
                <?php if ($order['table_id']): ?>
                    <div><strong>Dining Table:</strong> Table #<?= (int)($order['table_number'] ?? $order['table_id']) ?></div>
                <?php endif; ?>
                <?php if (!empty($order['delivery_address_json'])): ?>
                    <?php $addr = json_decode($order['delivery_address_json'], true); ?>
                    <div style="margin-top:10px; padding-top:10px; border-top:1px dashed var(--border);">
                        <strong>Delivery Address:</strong>
                        <div><?= \App\Helpers\Security::e($addr['line1'] ?? '') ?></div>
                        <div><?= \App\Helpers\Security::e($addr['landmark'] ?? '') ?></div>
                        <div>Pincode: <?= \App\Helpers\Security::e($addr['postal_code'] ?? '') ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
