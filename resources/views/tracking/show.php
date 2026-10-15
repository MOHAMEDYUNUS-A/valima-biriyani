<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order #<?= htmlspecialchars($order['order_number']) ?> — Valima Biriyani</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Cinzel:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0d0f12;
            --surface: #161920;
            --surface-elevated: #20242f;
            --border: #293040;
            --text-main: #f3f4f6;
            --text-muted: #94a3b8;
            --gold: #d4a359;
            --gold-light: #fbeedb;
            --orange: #f97316;
            --green: #22c55e;
            --blue: #38bdf8;
            --radius: 14px;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text-main);
            min-height: 100vh;
            padding-bottom: 4rem;
        }

        /* ── NAVBAR ── */
        .track-nav {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .track-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: inherit;
        }

        .brand-icon {
            font-size: 1.4rem;
        }

        .brand-name {
            font-family: 'Cinzel', serif;
            font-size: 1.15rem;
            font-weight: 700;
            color: #fff;
        }

        .live-chip {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: var(--green);
            padding: 4px 12px;
            border-radius: 20px;
        }

        .live-dot {
            width: 7px;
            height: 7px;
            background: var(--green);
            border-radius: 50%;
            animation: pulse 1.8s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.85); }
        }

        /* ── TRACKING CONTAINER ── */
        .container {
            max-width: 680px;
            margin: 2rem auto;
            padding: 0 1.25rem;
        }

        .order-hero-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.75rem;
            box-shadow: 0 8px 24px rgba(0,0,0,0.3);
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .order-hero-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, var(--gold), #ea580c);
        }

        .hero-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 1.25rem;
        }

        .order-num-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
        }

        .order-num-val {
            font-size: 1.6rem;
            font-weight: 800;
            color: #fff;
            margin-top: 2px;
        }

        .order-type-badge {
            font-size: 0.8rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 6px;
            background: var(--surface-elevated);
            color: var(--gold);
        }

        /* ── PROGRESS STEPPER ── */
        .progress-stepper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            margin: 2rem 0 1.5rem 0;
        }

        .progress-stepper::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            height: 3px;
            background: var(--border);
            z-index: 1;
        }

        .progress-bar-fill {
            position: absolute;
            top: 20px;
            left: 20px;
            height: 3px;
            background: linear-gradient(90deg, var(--gold), var(--green));
            z-index: 2;
            transition: width 0.5s ease;
        }

        .step-node {
            position: relative;
            z-index: 3;
            text-align: center;
            width: 80px;
        }

        .node-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--surface-elevated);
            border: 2px solid var(--border);
            margin: 0 auto 8px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            transition: all 0.3s;
        }

        .step-node.active .node-circle {
            background: var(--gold);
            border-color: var(--gold);
            color: #000;
            box-shadow: 0 0 12px rgba(212, 163, 89, 0.4);
        }

        .step-node.completed .node-circle {
            background: var(--green);
            border-color: var(--green);
            color: #fff;
        }

        .node-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
        }

        .step-node.active .node-label,
        .step-node.completed .node-label {
            color: #fff;
            font-weight: 700;
        }

        /* ── DELIVERY / TABLE INFO ── */
        .info-box {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .info-header {
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            padding: 4px 0;
            color: var(--text-muted);
        }

        .info-val {
            font-weight: 600;
            color: var(--text-main);
        }

        .btn-track-external {
            display: inline-block;
            background: var(--blue);
            color: #000;
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 700;
            margin-top: 8px;
        }

        /* ── ITEMS SUMMARY ── */
        .items-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .item-line {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            font-size: 0.9rem;
        }

        .item-line:last-child {
            border-bottom: none;
        }

        .total-box {
            border-top: 2px dashed var(--border);
            margin-top: 10px;
            padding-top: 10px;
            display: flex;
            justify-content: space-between;
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--gold);
        }

        /* ── ACTIONS ── */
        .track-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .btn-action {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s;
            text-align: center;
        }

        .btn-add-more {
            background: var(--gold);
            color: #000;
        }

        .btn-add-more:hover {
            background: #c59247;
        }

        .btn-support {
            background: rgba(34, 197, 94, 0.15);
            border: 1px solid rgba(34, 197, 94, 0.4);
            color: var(--green);
        }

        .btn-support:hover {
            background: rgba(34, 197, 94, 0.25);
        }
    </style>
</head>
<body>

    <nav class="track-nav">
        <a href="/" class="track-brand">
            <span class="brand-icon">🍛</span>
            <span class="brand-name">VALIMA BIRIYANI</span>
        </a>
        <div class="live-chip">
            <span class="live-dot"></span>
            <span id="liveStatusText">Live Tracking</span>
        </div>
    </nav>

    <div class="container">
        <!-- ORDER HERO -->
        <div class="order-hero-card">
            <div class="hero-top">
                <div>
                    <div class="order-num-label">Order Reference</div>
                    <div class="order-num-val">#<?= htmlspecialchars($order['order_number']) ?></div>
                </div>
                <div class="order-type-badge">
                    <?php if ($order['order_type'] === 'dine_in'): ?>
                        Table <?= (int)$order['table_id'] ?>
                    <?php elseif ($order['order_type'] === 'delivery'): ?>
                        🛵 Delivery
                    <?php else: ?>
                        🛍️ Takeaway
                    <?php endif; ?>
                </div>
            </div>

            <!-- STEPPER -->
            <?php
                $step = (int)$stepIndex;
                $pct = match ($step) {
                    1 => 10,
                    2 => 40,
                    3 => 75,
                    4 => 100,
                    default => 0,
                };
            ?>
            <div class="progress-stepper">
                <div class="progress-bar-fill" id="progressFill" style="width: <?= $pct ?>%;"></div>

                <div class="step-node <?= $step > 1 ? 'completed' : ($step === 1 ? 'active' : '') ?>" id="step1">
                    <div class="node-circle">📝</div>
                    <div class="node-label">Confirmed</div>
                </div>

                <div class="step-node <?= $step > 2 ? 'completed' : ($step === 2 ? 'active' : '') ?>" id="step2">
                    <div class="node-circle">🍳</div>
                    <div class="node-label">Preparing</div>
                </div>

                <div class="step-node <?= $step > 3 ? 'completed' : ($step === 3 ? 'active' : '') ?>" id="step3">
                    <div class="node-circle"><?= $order['order_type'] === 'delivery' ? '🛵' : '🍱' ?></div>
                    <div class="node-label"><?= $order['order_type'] === 'delivery' ? 'On The Way' : 'Ready' ?></div>
                </div>

                <div class="step-node <?= $step >= 4 ? 'completed' : '' ?>" id="step4">
                    <div class="node-circle">✨</div>
                    <div class="node-label"><?= $order['order_type'] === 'dine_in' ? 'Served' : 'Completed' ?></div>
                </div>
            </div>

            <div style="text-align: center; font-size: 0.9rem; color: var(--gold); font-weight: 600;" id="statusSummary">
                Status: <span id="currentStatusBadge" style="text-transform: capitalize;"><?= htmlspecialchars(str_replace('_', ' ', $order['status'])) ?></span>
            </div>
        </div>

        <!-- DELIVERY / FULFILLMENT INFO -->
        <?php if ($order['order_type'] === 'delivery' && !empty($delivery)): ?>
            <div class="info-box">
                <div class="info-header">🛵 Delivery Status (<?= htmlspecialchars(ucfirst($delivery['provider_name'])) ?>)</div>
                <div class="info-row">
                    <span>Booking Ref:</span>
                    <span class="info-val"><?= htmlspecialchars($delivery['booking_reference'] ?: 'Pending booking') ?></span>
                </div>
                <div class="info-row">
                    <span>Status:</span>
                    <span class="info-val" id="deliveryStatusBadge" style="color: var(--gold); text-transform: capitalize;">
                        <?= htmlspecialchars(str_replace('_', ' ', $delivery['status'])) ?>
                    </span>
                </div>
                <?php if (!empty($delivery['tracking_url'])): ?>
                    <a href="<?= htmlspecialchars($delivery['tracking_url']) ?>" target="_blank" class="btn-track-external">
                        🔗 Open Live Delivery Tracking &rarr;
                    </a>
                <?php endif; ?>
            </div>
        <?php elseif ($order['order_type'] === 'dine_in'): ?>
            <div class="info-box">
                <div class="info-header">🪑 Dine-in Service</div>
                <div class="info-row">
                    <span>Dining Table:</span>
                    <span class="info-val">Table <?= (int)$order['table_id'] ?></span>
                </div>
                <div class="info-row">
                    <span>Service:</span>
                    <span class="info-val">Our staff will serve fresh food directly to your table!</span>
                </div>
            </div>
        <?php endif; ?>

        <!-- ORDER ITEMS -->
        <div class="items-card">
            <div class="info-header">🍛 Order Items</div>
            <?php foreach ($items as $item): ?>
                <div class="item-line">
                    <div>
                        <strong><?= (int)$item['quantity'] ?>x</strong> <?= htmlspecialchars($item['item_name']) ?>
                        <?php if (!empty($item['variant_name'])): ?>
                            <span style="color: var(--text-muted); font-size: 0.8rem;">(<?= htmlspecialchars($item['variant_name']) ?>)</span>
                        <?php endif; ?>
                    </div>
                    <div>₹<?= number_format((float)$item['subtotal'], 2) ?></div>
                </div>
            <?php endforeach; ?>

            <div class="total-box">
                <span>Total Amount:</span>
                <span>₹<?= number_format((float)$order['total_amount'], 2) ?></span>
            </div>
        </div>

        <!-- ACTIONS -->
        <div class="track-actions">
            <!-- Add More Items with Session Preservation -->
            <a href="/track/<?= htmlspecialchars($order['tracking_token']) ?>/add-more" class="btn-action btn-add-more">
                ➕ Add More Items
            </a>

            <!-- WhatsApp Support -->
            <?php
                $supportText = urlencode("Hello Valima Biriyani, I am inquiring about my Order #{$order['order_number']}.");
                $waUrl = "https://wa.me/919876543210?text={$supportText}";
            ?>
            <a href="<?= $waUrl ?>" target="_blank" class="btn-action btn-support">
                💬 WhatsApp Help
            </a>
        </div>
    </div>

    <!-- POLLING SCRIPT -->
    <script>
        const token = <?= json_encode($order['tracking_token']) ?>;

        async function pollOrderStatus() {
            try {
                const res = await fetch(`/api/track/${token}`);
                if (!res.ok) return;
                const data = await res.json();

                if (data.success) {
                    const step = data.step_index;
                    const fill = document.getElementById('progressFill');
                    const badge = document.getElementById('currentStatusBadge');

                    badge.innerText = data.status.replace(/_/g, ' ');

                    const pcts = { 1: 10, 2: 40, 3: 75, 4: 100 };
                    fill.style.width = (pcts[step] || 10) + '%';

                    for (let i = 1; i <= 4; i++) {
                        const node = document.getElementById(`step${i}`);
                        if (!node) continue;
                        node.className = 'step-node';
                        if (i < step || (i === 4 && step === 4)) {
                            node.classList.add('completed');
                        } else if (i === step) {
                            node.classList.add('active');
                        }
                    }

                    if (data.delivery && document.getElementById('deliveryStatusBadge')) {
                        document.getElementById('deliveryStatusBadge').innerText = data.delivery.status.replace(/_/g, ' ');
                    }
                }
            } catch (err) {
                console.warn('Tracking poll error:', err);
            }
        }

        // Poll every 4 seconds
        setInterval(pollOrderStatus, 4000);
    </script>
</body>
</html>
