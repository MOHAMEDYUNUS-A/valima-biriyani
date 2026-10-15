<!-- ── ORDER CONFIRMED CELEBRATION BANNER (Matches Mockup Screen 7) ── -->
<div class="bg-dark text-white py-10 px-4 mb-8 border-b border-dark text-center relative overflow-hidden" style="background: #14110C;">
    <div class="max-w-md mx-auto relative z-10">
        <!-- Animated Green Checkmark Icon -->
        <div style="width: 64px; height: 64px; border-radius: 50%; background: #16A34A; color: #FFF; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem auto; box-shadow: 0 0 25px rgba(22, 163, 74, 0.4);">
            <?= \App\Helpers\Icon::render('check', 'w-8 h-8') ?>
        </div>

        <h1 class="font-serif font-extrabold text-2xl md:text-3xl text-white mb-2">
            Order Confirmed!
        </h1>
        <p style="color: #c5bba8; font-size: 0.95rem; margin-bottom: 0.5rem;">
            Your order has been placed successfully and sent to our kitchen.
        </p>

        <div style="display: inline-block; background: rgba(245, 184, 0, 0.15); border: 1px solid rgba(245, 184, 0, 0.35); padding: 4px 16px; border-radius: 999px; font-size: 0.85rem; font-weight: 700; color: var(--primary); margin-bottom: 1.5rem;">
            Order Number: #<?= htmlspecialchars($order['order_number']) ?>
        </div>

        <div class="flex items-center justify-center gap-3">
            <a href="#tracking-timeline" class="btn btn-primary btn-sm">
                Track Order &darr;
            </a>
            <a href="/pos/receipt/<?= (int)$order['id'] ?>" target="_blank" class="btn btn-outline btn-sm text-white" style="border-color: #444;">
                View Invoice
            </a>
        </div>
    </div>
</div>

<div class="container max-w-xl pb-12" id="tracking-timeline">
    <!-- ── ORDER SUMMARY CARD ── -->
    <div class="card p-5 mb-6 shadow-soft" style="background: #FFFFFF;">
        <div class="flex items-center justify-between pb-3 border-b border-border mb-3">
            <div>
                <span class="text-xs text-muted-foreground uppercase font-bold tracking-wider">Fulfilment</span>
                <div class="font-serif font-bold text-lg text-foreground">
                    <?php if ($order['order_type'] === 'dine_in'): ?>
                        🪑 Dine-in Table <?= (int)$order['table_id'] ?>
                    <?php elseif ($order['order_type'] === 'delivery'): ?>
                        🛵 Home Delivery
                    <?php else: ?>
                        🛍️ Takeaway / Pickup
                    <?php endif; ?>
                </div>
            </div>

            <div class="text-right">
                <span class="text-xs text-muted-foreground uppercase font-bold tracking-wider">Total Paid</span>
                <div class="font-extrabold text-lg text-primary">₹<?= number_format((float)$order['total_amount'], 2) ?></div>
            </div>
        </div>

        <!-- Items Mini List -->
        <div class="space-y-2 mb-3">
            <?php foreach ($items as $item): ?>
                <div class="flex justify-between text-sm">
                    <span class="font-medium text-foreground"><?= (int)$item['quantity'] ?>x <?= htmlspecialchars($item['item_name']) ?></span>
                    <span class="font-bold text-foreground">₹<?= number_format((float)$item['subtotal'], 2) ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="pt-3 border-t border-border flex items-center justify-between text-xs text-muted-foreground">
            <span>Payment Method: <?= strtoupper($order['payment_method'] ?? 'CASH') ?></span>
            <span>Placed: <?= date('h:i A, d M', strtotime($order['created_at'])) ?></span>
        </div>
    </div>

    <!-- ── ESTIMATED TIME CARD (Matches Mockup Screen 8) ── -->
    <div class="card p-4 mb-6 flex items-center gap-4 bg-muted border-border" style="background: #FFFDF7; border-left: 4px solid var(--primary);">
        <div style="font-size: 2rem;">🛵</div>
        <div>
            <div style="font-size: 0.725rem; font-weight: 700; text-transform: uppercase; color: var(--muted-foreground);">Estimated Delivery Window</div>
            <div class="font-serif font-bold text-lg text-foreground">
                <?= date('h:i A', strtotime($order['created_at'] . ' + 30 minutes')) ?> – <?= date('h:i A', strtotime($order['created_at'] . ' + 45 minutes')) ?>
            </div>
            <div class="text-xs text-muted-foreground mt-0.5">Fresh dum batch currently finishing on charcoal</div>
        </div>
    </div>

    <!-- ── VERTICAL ORDER TIMELINE (Matches Mockup Screen 8) ── -->
    <div class="card p-6 mb-6 shadow-soft" style="background: #FFFFFF;">
        <h3 class="font-serif font-bold text-lg mb-6 pb-2 border-b border-border">Order Progress</h3>

        <?php
            $currentStatus = $order['status'];
            $steps = [
                'placed' => ['title' => 'Order Placed', 'desc' => 'Order received by restaurant server', 'icon' => 'check'],
                'confirmed' => ['title' => 'Payment Confirmed', 'desc' => 'Payment authorized & verified', 'icon' => 'check'],
                'accepted' => ['title' => 'Order Accepted', 'desc' => 'Kitchen acknowledged your order', 'icon' => 'check'],
                'preparing' => ['title' => 'Preparing in Kitchen', 'desc' => 'Chef layering basmati & tender meat', 'icon' => 'flame'],
                'ready' => ['title' => $order['order_type'] === 'delivery' ? 'Packed & Ready' : 'Ready to Serve', 'desc' => 'Sealed in insulated tamper-proof bag', 'icon' => 'sparkles'],
                'out_for_delivery' => ['title' => 'Out for Delivery', 'desc' => 'Rider is on the way to your location', 'icon' => 'truck'],
                'delivered' => ['title' => $order['order_type'] === 'dine_in' ? 'Served at Table' : 'Delivered', 'desc' => 'Enjoy your authentic Valima Biriyani feast!', 'icon' => 'shield-check'],
            ];

            // Determine active index
            $statusRank = [
                'new' => 1, 'pending' => 1, 'placed' => 1,
                'confirmed' => 2,
                'accepted' => 3,
                'preparing' => 4,
                'ready' => 5,
                'out_for_delivery' => 6,
                'served' => 7, 'delivered' => 7, 'completed' => 7
            ];
            $currentRank = $statusRank[$currentStatus] ?? 3;
        ?>

        <div class="relative pl-6 space-y-6" style="border-left: 2px solid var(--border); margin-left: 12px;">
            <?php $i = 1; foreach ($steps as $key => $meta): ?>
                <?php
                    $isCompleted = ($i < $currentRank);
                    $isActive = ($i === $currentRank);
                    $dotColor = $isCompleted ? '#16A34A' : ($isActive ? '#F5B800' : '#E8DFCB');
                    $dotText = $isCompleted ? '✓' : $i;
                ?>
                <div class="relative">
                    <!-- Timeline Node -->
                    <div style="position: absolute; left: -33px; top: 0; width: 22px; height: 22px; border-radius: 50%; background: <?= $dotColor ?>; color: <?= $isCompleted || $isActive ? '#000' : '#888' ?>; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; border: 2px solid #FFF; box-shadow: 0 0 0 2px <?= $dotColor ?>;">
                        <?= $dotText ?>
                    </div>

                    <div style="<?= !$isCompleted && !$isActive ? 'opacity: 0.45;' : '' ?>">
                        <div class="font-bold text-sm text-foreground flex items-center gap-2">
                            <span><?= htmlspecialchars($meta['title']) ?></span>
                            <?php if ($isActive): ?>
                                <span class="badge badge-gold" style="font-size: 9px;">In Progress</span>
                            <?php endif; ?>
                        </div>
                        <div class="text-xs text-muted-foreground mt-0.5">
                            <?= htmlspecialchars($meta['desc']) ?>
                        </div>
                    </div>
                </div>
            <?php $i++; endforeach; ?>
        </div>
    </div>

    <!-- ── RAPIDO DELIVERY INFO (If Applicable) ── -->
    <?php if (!empty($delivery)): ?>
        <div class="card p-5 mb-6 bg-muted" style="background: #F8F4EB;">
            <div class="flex items-center justify-between mb-2">
                <span class="font-bold text-sm text-foreground">🛵 Delivery Partner (<?= htmlspecialchars(ucfirst($delivery['provider_name'])) ?>)</span>
                <span class="badge badge-gold" style="text-transform: capitalize;"><?= htmlspecialchars(str_replace('_', ' ', $delivery['status'])) ?></span>
            </div>
            <div class="text-xs text-muted-foreground">Booking Ref: <strong><?= htmlspecialchars($delivery['booking_reference'] ?: 'Pending dispatch') ?></strong></div>
            <?php if (!empty($delivery['tracking_url'])): ?>
                <a href="<?= htmlspecialchars($delivery['tracking_url']) ?>" target="_blank" class="btn btn-outline btn-sm mt-3 w-full text-center">
                    🔗 Open External Live Tracking &rarr;
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- ── ACTIONS (Mockup Screen 8) ── -->
    <div class="grid grid-cols-2 gap-3" style="display: grid; grid-template-columns: 1fr 1fr;">
        <!-- Add More Items -->
        <a href="/track/<?= htmlspecialchars($order['tracking_token']) ?>/add-more" class="btn btn-primary text-center">
            ➕ Add More Items
        </a>

        <!-- Contact Support via WhatsApp -->
        <?php
            $msg = urlencode("Hi Valima Biriyani, I am checking on my Order #{$order['order_number']}.");
            $waUrl = "https://wa.me/919876543210?text={$msg}";
        ?>
        <a href="<?= $waUrl ?>" target="_blank" class="btn btn-outline text-center">
            💬 Contact Support
        </a>
    </div>
</div>

<!-- ── POLLING AUTO-REFRESH SCRIPT ── -->
<script>
const trackingToken = <?= json_encode($order['tracking_token']) ?>;
let lastStatus = <?= json_encode($order['status']) ?>;

async function pollOrderStatus() {
    try {
        const base = window.APP_BASE_URL || '';
        const res = await fetch(`${base}/api/track/${trackingToken}`);
        if (!res.ok) return;
        const data = await res.json();
        if (data.success && data.status !== lastStatus) {
            // Status changed in kitchen or delivery -> smooth reload to show new step!
            window.location.reload();
        }
    } catch (e) {}
}

setInterval(pollOrderStatus, 4000);
</script>
