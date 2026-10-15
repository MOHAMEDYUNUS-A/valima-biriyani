<?php
use App\Helpers\Component;
use App\Helpers\Icon;
?>

<div class="flex items-center justify-between pb-4 mb-6 border-b border-border">
    <div class="flex items-center gap-3">
        <a href="/admin/orders" class="btn btn-outline btn-sm font-bold">
            &larr; Back to Orders
        </a>
        <h1 class="font-serif text-2xl font-bold text-foreground">
            Order #<?= htmlspecialchars($order['order_number']) ?>
        </h1>
        <?= Component::render('badge', ['text' => str_replace('_', ' ', $order['status']), 'variant' => $order['status']]) ?>
    </div>

    <div class="flex items-center gap-3">
        <a href="/pos/receipt/<?= (int)$order['id'] ?>" target="_blank" class="btn btn-outline btn-sm">
            <?= Icon::render('file-text', 'w-4 h-4') ?>
            <span>Print Receipt</span>
        </a>
        <a href="/kitchen/kot/<?= (int)$order['id'] ?>" target="_blank" class="btn btn-outline btn-sm">
            <?= Icon::render('flame', 'w-4 h-4') ?>
            <span>Print KOT</span>
        </a>
    </div>
</div>

<div class="grid md:grid-cols-3 gap-8" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));">
    <!-- ── LEFT 2 COLUMNS: CUSTOMER, ITEMS, TIMELINE ── -->
    <div style="grid-column: span 2;">
        <!-- Customer Card (Mockup Screen 11) -->
        <div class="card p-5 mb-6" style="background: #FFFFFF;">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <span class="text-xs text-muted-foreground uppercase font-bold tracking-wider">Customer Details</span>
                    <h3 class="font-serif font-bold text-lg text-foreground mt-1">
                        <?= htmlspecialchars($order['customer_name']) ?>
                    </h3>
                    <div class="text-sm font-medium text-foreground flex items-center gap-2 mt-1">
                        <span><?= htmlspecialchars($order['customer_phone']) ?></span>
                        <a href="tel:<?= htmlspecialchars($order['customer_phone']) ?>" class="btn btn-outline btn-sm" style="padding: 2px 8px; font-size: 11px;">
                            📞 Call
                        </a>
                    </div>
                </div>

                <div class="text-right">
                    <span class="badge badge-gold" style="text-transform: uppercase;">
                        <?= htmlspecialchars($order['channel']) ?>
                    </span>
                    <div class="text-xs text-muted-foreground mt-1">
                        <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?>
                    </div>
                </div>
            </div>

            <!-- Delivery Address / Dine-in Table -->
            <?php if ($order['order_type'] === 'dine_in'): ?>
                <div class="p-3 bg-muted rounded-md text-sm" style="background: #FFFDF7; border: 1px solid var(--border);">
                    <strong>🪑 Dining Table:</strong> Table <?= (int)$order['table_id'] ?>
                </div>
            <?php elseif ($order['order_type'] === 'delivery'): ?>
                <div class="p-3 bg-muted rounded-md text-sm flex items-start justify-between" style="background: #FFFDF7; border: 1px solid var(--border);">
                    <div>
                        <strong>🛵 Delivery Address:</strong>
                        <div class="text-muted-foreground text-xs mt-1">
                            <?= htmlspecialchars($order['delivery_address_json'] ?? 'Anna Nagar, Chennai - 600040') ?>
                        </div>
                    </div>
                    <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($order['delivery_address_json'] ?? 'Anna Nagar, Chennai') ?>" target="_blank" class="btn btn-outline btn-sm" style="padding: 2px 8px; font-size: 11px;">
                        🗺️ View on Map
                    </a>
                </div>
            <?php else: ?>
                <div class="p-3 bg-muted rounded-md text-sm" style="background: #FFFDF7; border: 1px solid var(--border);">
                    <strong>🛍️ Takeaway:</strong> Customer will pickup at restaurant counter.
                </div>
            <?php endif; ?>

            <?php if (!empty($order['special_instructions'])): ?>
                <div class="mt-3 p-3 rounded-md bg-amber-50 text-amber-900 text-xs border border-amber-200">
                    <strong>⚠️ Special Instructions:</strong> <?= htmlspecialchars($order['special_instructions']) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Items Table (Mockup Screen 11) -->
        <div class="card p-5 mb-6" style="background: #FFFFFF;">
            <h3 class="font-serif font-bold text-base mb-3 pb-2 border-b border-border">Ordered Items</h3>

            <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border); text-align: left; color: var(--muted-foreground); font-size: 0.75rem; text-transform: uppercase;">
                        <th style="padding: 8px 10px;">Item</th>
                        <th style="padding: 8px 10px;">Price</th>
                        <th style="padding: 8px 10px;">Qty</th>
                        <th style="padding: 8px 10px; text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $it): ?>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 10px;">
                                <div class="font-bold text-foreground"><?= htmlspecialchars($it['item_name']) ?></div>
                                <?php if (!empty($it['variant_name'])): ?>
                                    <div class="text-xs text-muted-foreground"><?= htmlspecialchars($it['variant_name']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($it['notes'])): ?>
                                    <div class="text-xs text-amber-700 font-medium">Note: <?= htmlspecialchars($it['notes']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 10px;">₹<?= number_format((float)$it['unit_price'], 2) ?></td>
                            <td style="padding: 10px; font-weight: 700;">x<?= (int)$it['quantity'] ?></td>
                            <td style="padding: 10px; text-align: right; font-weight: 800; color: var(--foreground);">
                                ₹<?= number_format((float)$it['subtotal'], 2) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Price Totals Breakdown -->
            <div class="max-w-xs ml-auto pt-4 space-y-1.5 text-sm">
                <div class="flex justify-between text-muted-foreground">
                    <span>Subtotal:</span>
                    <span class="font-bold text-foreground">₹<?= number_format((float)$order['subtotal'], 2) ?></span>
                </div>
                <div class="flex justify-between text-muted-foreground">
                    <span>GST (5%):</span>
                    <span class="font-bold text-foreground">₹<?= number_format((float)$order['tax_amount'], 2) ?></span>
                </div>
                <div class="flex justify-between text-muted-foreground">
                    <span>Delivery Fee:</span>
                    <span class="font-bold text-foreground">₹<?= number_format((float)$order['delivery_fee'], 2) ?></span>
                </div>
                <div class="flex justify-between pt-2 border-t-2 border-border text-base font-extrabold text-foreground">
                    <span>Total Amount:</span>
                    <span class="text-primary font-serif font-extrabold text-lg">₹<?= number_format((float)$order['total_amount'], 2) ?></span>
                </div>
            </div>
        </div>

        <!-- ── RAPIDO DELIVERY MANAGEMENT (If Delivery) ── -->
        <?php if ($order['order_type'] === 'delivery'): ?>
            <div class="card p-5 mb-6" style="background: #FFFFFF;">
                <h3 class="font-serif font-bold text-base mb-3 pb-2 border-b border-border flex items-center gap-2">
                    <?= Icon::render('truck', 'w-5 h-5 text-primary') ?>
                    <span>Rapido Parcel Delivery Booking</span>
                </h3>

                <form method="POST" action="/admin/orders/<?= (int)$order['id'] ?>/delivery">
                    <?= $csrf_field ?>
                    <div class="grid grid-cols-2 gap-3 mb-3" style="display: grid; grid-template-columns: 1fr 1fr;">
                        <div>
                            <label class="form-label">Booking Reference</label>
                            <input type="text" name="booking_reference" value="<?= htmlspecialchars($delivery['booking_reference'] ?? '') ?>" placeholder="e.g. RAPIDO-BLR-00123" class="input">
                        </div>
                        <div>
                            <label class="form-label">Delivery Fee (₹)</label>
                            <input type="number" step="0.01" name="delivery_fee" value="<?= htmlspecialchars((string)($delivery['delivery_fee'] ?? '40.00')) ?>" class="input">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Rapido Live Tracking URL (Optional)</label>
                        <input type="url" name="tracking_url" value="<?= htmlspecialchars($delivery['tracking_url'] ?? '') ?>" placeholder="https://rapido.bike/track/..." class="input">
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-4" style="display: grid; grid-template-columns: 1fr 1fr;">
                        <div>
                            <label class="form-label">Delivery Status</label>
                            <select name="status" class="select">
                                <?php
                                    $curDSt = $delivery['status'] ?? 'awaiting_booking';
                                    $dOptions = [
                                        'awaiting_booking' => 'Awaiting Booking',
                                        'booked' => 'Booked',
                                        'pickup_pending' => 'Pickup Pending',
                                        'picked_up' => 'Picked Up (Out for Delivery)',
                                        'in_transit' => 'In Transit',
                                        'delivered' => 'Delivered (Completed)',
                                        'failed' => 'Delivery Failed',
                                        'cancelled' => 'Cancelled',
                                    ];
                                ?>
                                <?php foreach ($dOptions as $k => $lbl): ?>
                                    <option value="<?= $k ?>" <?= $curDSt === $k ? 'selected' : '' ?>><?= $lbl ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Rider Notes</label>
                            <input type="text" name="delivery_notes" value="<?= htmlspecialchars($delivery['delivery_notes'] ?? '') ?>" placeholder="Rider name & phone" class="input">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm">
                        Save Delivery Details
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <!-- ── RIGHT COLUMN: TIMELINE & ACTION BUTTONS (Mockup Screen 11) ── -->
    <div>
        <!-- Order Timeline (Mockup Screen 11) -->
        <div class="card p-5 mb-6" style="background: #FFFFFF;">
            <h3 class="font-serif font-bold text-base mb-4 pb-2 border-b border-border">Order Timeline</h3>

            <div class="relative pl-6 space-y-4" style="border-left: 2px solid var(--border); margin-left: 10px;">
                <div class="relative">
                    <span style="position: absolute; left: -31px; top: 0; width: 16px; height: 16px; border-radius: 50%; background: #16A34A; border: 2px solid #FFF;"></span>
                    <div class="font-bold text-xs">Placed</div>
                    <div class="text-xs text-muted-foreground"><?= date('h:i A', strtotime($order['created_at'])) ?></div>
                </div>

                <div class="relative">
                    <span style="position: absolute; left: -31px; top: 0; width: 16px; height: 16px; border-radius: 50%; background: #16A34A; border: 2px solid #FFF;"></span>
                    <div class="font-bold text-xs">Payment Confirmed</div>
                    <div class="text-xs text-muted-foreground"><?= date('h:i A', strtotime($order['created_at'])) ?></div>
                </div>

                <div class="relative">
                    <span style="position: absolute; left: -31px; top: 0; width: 16px; height: 16px; border-radius: 50%; background: <?= in_array($order['status'], ['accepted', 'preparing', 'ready', 'out_for_delivery', 'served', 'completed']) ? '#16A34A' : '#E8DFCB' ?>; border: 2px solid #FFF;"></span>
                    <div class="font-bold text-xs">Accepted</div>
                    <div class="text-xs text-muted-foreground"><?= in_array($order['status'], ['accepted', 'preparing', 'ready', 'out_for_delivery', 'served', 'completed']) ? 'Kitchen accepted' : 'Pending' ?></div>
                </div>

                <div class="relative">
                    <span style="position: absolute; left: -31px; top: 0; width: 16px; height: 16px; border-radius: 50%; background: <?= in_array($order['status'], ['preparing', 'ready', 'out_for_delivery', 'served', 'completed']) ? '#F5B800' : '#E8DFCB' ?>; border: 2px solid #FFF;"></span>
                    <div class="font-bold text-xs">Preparing</div>
                    <div class="text-xs text-muted-foreground"><?= in_array($order['status'], ['preparing', 'ready', 'out_for_delivery', 'served', 'completed']) ? 'In kitchen' : 'Queued' ?></div>
                </div>

                <div class="relative">
                    <span style="position: absolute; left: -31px; top: 0; width: 16px; height: 16px; border-radius: 50%; background: <?= in_array($order['status'], ['ready', 'out_for_delivery', 'served', 'completed']) ? '#16A34A' : '#E8DFCB' ?>; border: 2px solid #FFF;"></span>
                    <div class="font-bold text-xs">Ready / Packed</div>
                </div>

                <div class="relative">
                    <span style="position: absolute; left: -31px; top: 0; width: 16px; height: 16px; border-radius: 50%; background: <?= in_array($order['status'], ['out_for_delivery', 'served', 'completed']) ? '#16A34A' : '#E8DFCB' ?>; border: 2px solid #FFF;"></span>
                    <div class="font-bold text-xs">Out for Delivery</div>
                </div>

                <div class="relative">
                    <span style="position: absolute; left: -31px; top: 0; width: 16px; height: 16px; border-radius: 50%; background: <?= in_array($order['status'], ['served', 'completed', 'delivered']) ? '#16A34A' : '#E8DFCB' ?>; border: 2px solid #FFF;"></span>
                    <div class="font-bold text-xs">Delivered / Served</div>
                </div>
            </div>
        </div>

        <!-- Action Buttons (Mockup Screen 11) -->
        <div class="card p-5" style="background: #FFFFFF;">
            <h3 class="font-serif font-bold text-base mb-3 pb-2 border-b border-border">Order Actions</h3>

            <div class="flex flex-col gap-2">
                <!-- Mark Ready (Gold) -->
                <form method="POST" action="/admin/orders/<?= (int)$order['id'] ?>/status">
                    <?= $csrf_field ?>
                    <input type="hidden" name="status" value="ready">
                    <button type="submit" class="btn btn-primary w-full text-center" <?= $order['status'] === 'ready' ? 'disabled' : '' ?>>
                        Mark Ready
                    </button>
                </form>

                <!-- Mark Out for Delivery (Gold) -->
                <?php if ($order['order_type'] === 'delivery'): ?>
                    <form method="POST" action="/admin/orders/<?= (int)$order['id'] ?>/status">
                        <?= $csrf_field ?>
                        <input type="hidden" name="status" value="out_for_delivery">
                        <button type="submit" class="btn btn-primary w-full text-center">
                            Mark Out for Delivery
                        </button>
                    </form>
                <?php else: ?>
                    <form method="POST" action="/admin/orders/<?= (int)$order['id'] ?>/status">
                        <?= $csrf_field ?>
                        <input type="hidden" name="status" value="served">
                        <button type="submit" class="btn btn-primary w-full text-center">
                            Mark Served
                        </button>
                    </form>
                <?php endif; ?>

                <!-- Complete Order -->
                <form method="POST" action="/admin/orders/<?= (int)$order['id'] ?>/status">
                    <?= $csrf_field ?>
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="btn btn-outline w-full text-center">
                        Complete Order
                    </button>
                </form>

                <!-- Cancel Order (Destructive) -->
                <form method="POST" action="/admin/orders/<?= (int)$order['id'] ?>/status" onsubmit="return confirm('Are you sure you want to cancel this order? This action cannot be undone.');">
                    <?= $csrf_field ?>
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="btn btn-destructive w-full text-center mt-2">
                        Cancel Order
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
