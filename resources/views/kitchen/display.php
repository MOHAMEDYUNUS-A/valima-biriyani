<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Display System (KDS) — Valima Biriyani</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Cinzel:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0f1115;
            --surface: #181b22;
            --surface-elevated: #222733;
            --border: #2d3444;
            --text-main: #f3f4f6;
            --text-muted: #94a3b8;
            --gold: #d4a359;
            --gold-glow: rgba(212, 163, 89, 0.2);
            --orange: #f97316;
            --green: #22c55e;
            --blue: #38bdf8;
            --red: #ef4444;
            --radius: 12px;
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
            padding-bottom: 3rem;
        }

        /* ── HEADER ── */
        .kds-header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0.75rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .kds-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .kds-logo {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--gold), #b8860b);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .kds-title {
            font-family: 'Cinzel', serif;
            font-size: 1.15rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: 0.5px;
        }

        .kds-subtitle {
            font-size: 0.75rem;
            color: var(--gold);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 600;
        }

        .kds-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .live-status {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--green);
        }

        .live-dot {
            width: 8px;
            height: 8px;
            background: var(--green);
            border-radius: 50%;
            animation: pulse 1.8s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.85); }
        }

        .sound-btn {
            background: var(--surface-elevated);
            border: 1px solid var(--border);
            color: var(--text-main);
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .sound-btn.active {
            background: var(--gold);
            color: #000;
            border-color: var(--gold);
        }

        /* ── STATS ROW ── */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1.5rem 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-box {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.25rem 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .stat-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
        }

        .stat-box.c-new::before { background: var(--gold); }
        .stat-box.c-prep::before { background: var(--orange); }
        .stat-box.c-ready::before { background: var(--green); }
        .stat-box.c-served::before { background: var(--blue); }

        .stat-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .stat-num {
            font-size: 2.2rem;
            font-weight: 800;
            line-height: 1;
        }

        .stat-box.c-new .stat-num { color: var(--gold); }
        .stat-box.c-prep .stat-num { color: var(--orange); }
        .stat-box.c-ready .stat-num { color: var(--green); }
        .stat-box.c-served .stat-num { color: var(--blue); }

        /* ── 3D TABLE SECTION (Preserving KMS Look) ── */
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tables-grid {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 12px;
            margin-bottom: 2.5rem;
        }

        @media (max-width: 1024px) {
            .tables-grid { grid-template-columns: repeat(4, 1fr); }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 640px) {
            .tables-grid { grid-template-columns: repeat(2, 1fr); }
        }

        .table-scene {
            perspective: 500px;
        }

        .table-wrap {
            transform: rotateX(15deg) rotateY(-4deg);
            transform-style: preserve-3d;
            transition: transform 0.3s ease;
        }

        .table-scene:hover .table-wrap {
            transform: rotateX(5deg) translateY(-4px);
        }

        .table-box {
            background: #252b38;
            border: 2px solid var(--border);
            border-radius: 12px;
            padding: 12px 8px;
            text-align: center;
            position: relative;
            box-shadow: 0 8px 16px rgba(0,0,0,0.4);
            cursor: default;
        }

        .table-box.occupied {
            background: linear-gradient(145deg, #2b1f14, #1f1710);
            border-color: var(--orange);
            box-shadow: 0 0 15px rgba(249, 115, 22, 0.2);
        }

        .table-number {
            font-size: 1.1rem;
            font-weight: 800;
            color: #fff;
        }

        .table-status {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 4px;
            color: var(--text-muted);
        }

        .table-box.occupied .table-status {
            color: var(--orange);
        }

        .table-order-badge {
            font-size: 0.65rem;
            color: var(--gold);
            margin-top: 4px;
            display: inline-block;
            background: rgba(212, 163, 89, 0.15);
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
        }

        /* ── ACTIVE ORDERS GRID ── */
        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.25rem;
        }

        .order-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            box-shadow: 0 4px 12px rgba(0,0,0,0.25);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.4);
        }

        .order-card.delayed {
            border-color: var(--red);
            box-shadow: 0 0 16px rgba(239, 68, 68, 0.25);
        }

        .card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px dashed var(--border);
        }

        .order-id-box h4 {
            font-size: 1.15rem;
            font-weight: 800;
            color: #fff;
        }

        .order-type-chip {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 2px 8px;
            border-radius: 6px;
            background: var(--surface-elevated);
            color: var(--gold);
            margin-top: 4px;
        }

        .card-timer {
            text-align: right;
        }

        .timer-badge {
            font-size: 0.85rem;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-muted);
            display: inline-block;
        }

        .timer-badge.warning {
            background: rgba(239, 68, 68, 0.2);
            color: var(--red);
            animation: pulse 1.5s infinite;
        }

        /* ── ITEMS LIST ── */
        .items-list {
            margin-bottom: 1.25rem;
            flex-grow: 1;
        }

        .item-row {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        }

        .item-row:last-child {
            border-bottom: none;
        }

        .item-details {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .item-qty {
            font-size: 1rem;
            font-weight: 800;
            background: var(--surface-elevated);
            color: var(--gold);
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .item-name {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-main);
        }

        .item-variant {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .item-notes {
            font-size: 0.75rem;
            color: var(--orange);
            font-style: italic;
            margin-top: 2px;
        }

        .special-instr {
            margin-top: 8px;
            padding: 6px 10px;
            background: rgba(249, 115, 22, 0.1);
            border-left: 3px solid var(--orange);
            border-radius: 0 6px 6px 0;
            font-size: 0.8rem;
            color: #fdba74;
        }

        /* ── CARD ACTIONS ── */
        .card-actions {
            display: flex;
            gap: 8px;
            margin-top: 1rem;
        }

        .btn-status {
            flex: 1;
            padding: 10px 14px;
            border: none;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }

        .btn-status.c-prep {
            background: var(--orange);
            color: #fff;
        }

        .btn-status.c-prep:hover {
            background: #ea580c;
        }

        .btn-status.c-ready {
            background: var(--green);
            color: #fff;
        }

        .btn-status.c-ready:hover {
            background: #16a34a;
        }

        .btn-status.c-served {
            background: var(--blue);
            color: #fff;
        }

        .btn-status.c-served:hover {
            background: #0284c7;
        }

        .btn-kot {
            background: var(--surface-elevated);
            border: 1px solid var(--border);
            color: var(--text-main);
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-kot:hover {
            border-color: var(--gold);
            color: var(--gold);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--surface);
            border: 1px dashed var(--border);
            border-radius: var(--radius);
            grid-column: 1 / -1;
        }

        .empty-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.5rem;
        }

        .empty-desc {
            font-size: 0.9rem;
            color: var(--text-muted);
        }
    </style>
    <script>window.APP_BASE_URL = '<?= $basePath ?? '' ?>';</script>
</head>
<body>

    <audio id="kitchenAudio" src="<?= $basePath ?? '' ?>/assets/pop.mp3" preload="auto"></audio>

    <!-- HEADER -->
    <header class="kds-header">
        <div class="kds-brand">
            <div class="kds-logo">🍛</div>
            <div>
                <h1 class="kds-title">VALIMA BIRIYANI</h1>
                <p class="kds-subtitle">Kitchen Display System</p>
            </div>
        </div>

        <div class="kds-controls">
            <button id="soundToggle" class="sound-btn" onclick="toggleAudioChime()">
                🔔 <span id="soundLabel">Enable Chime</span>
            </button>
            <div id="connStatus" class="live-status">
                <span class="live-dot"></span>
                <span id="connLabel">Live Kitchen Stream</span>
            </div>
            <a href="<?= $basePath ?? '' ?>/admin" style="color: var(--text-muted); font-size: 0.85rem; text-decoration: none; font-weight: 600; margin-left: 8px;">
                Dashboard &rarr;
            </a>
        </div>
    </header>

    <div class="container">
        <!-- STATS ROW -->
        <div class="stats-grid">
            <div class="stat-box c-new">
                <div class="stat-label">New / Pending</div>
                <div class="stat-num" id="statNew"><?= (int)($stats['new'] ?? 0) ?></div>
            </div>
            <div class="stat-box c-prep">
                <div class="stat-label">Preparing</div>
                <div class="stat-num" id="statPrep"><?= (int)($stats['preparing'] ?? 0) ?></div>
            </div>
            <div class="stat-box c-ready">
                <div class="stat-label">Ready For Pickup</div>
                <div class="stat-num" id="statReady"><?= (int)($stats['ready'] ?? 0) ?></div>
            </div>
            <div class="stat-box c-served">
                <div class="stat-label">Served Today</div>
                <div class="stat-num" id="statServed"><?= (int)($stats['served_today'] ?? 0) ?></div>
            </div>
        </div>

        <!-- 3D TABLE OVERVIEW -->
        <div class="section-header">
            <h2 class="section-title">
                🪑 Dining Tables Overview (1–8)
            </h2>
            <span style="font-size: 0.8rem; color: var(--text-muted);">Real-time Dining Room Status</span>
        </div>

        <div class="tables-grid" id="tablesContainer">
            <?php foreach ($tables as $tbl): ?>
                <div class="table-scene">
                    <div class="table-wrap">
                        <div class="table-box <?= $tbl['is_occupied'] ? 'occupied' : '' ?>">
                            <div class="table-number">Table <?= (int)$tbl['table_number'] ?></div>
                            <div class="table-status"><?= $tbl['is_occupied'] ? 'Occupied' : 'Available' ?></div>
                            <?php if ($tbl['is_occupied'] && !empty($tbl['active_order'])): ?>
                                <div class="table-order-badge">#<?= htmlspecialchars($tbl['active_order']['order_number']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- ACTIVE ORDERS -->
        <div class="section-header">
            <h2 class="section-title">
                🔥 Active Kitchen Queue
            </h2>
            <span id="activeCountBadge" style="background: var(--surface-elevated); padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; color: var(--gold);">
                <?= count($activeOrders) ?> Orders
            </span>
        </div>

        <div class="orders-grid" id="ordersGrid">
            <?php if (empty($activeOrders)): ?>
                <div class="empty-state">
                    <div class="empty-icon">🍳</div>
                    <div class="empty-title">All Caught Up!</div>
                    <div class="empty-desc">No pending kitchen orders at the moment. New orders will appear here automatically.</div>
                </div>
            <?php else: ?>
                <?php foreach ($activeOrders as $order): ?>
                    <div class="order-card <?= $order['is_delayed'] ? 'delayed' : '' ?>" id="order-card-<?= (int)$order['id'] ?>">
                        <div>
                            <div class="card-top">
                                <div class="order-id-box">
                                    <h4>#<?= htmlspecialchars($order['order_number']) ?></h4>
                                    <div class="order-type-chip">
                                        <?php if (($order['order_type'] ?? '') === 'dine_in'): ?>
                                            Table <?= (int)$order['table_id'] ?>
                                        <?php elseif (($order['order_type'] ?? '') === 'delivery'): ?>
                                            🛵 Delivery
                                        <?php else: ?>
                                            🛍️ Takeaway
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="card-timer">
                                    <span class="timer-badge <?= $order['is_delayed'] ? 'warning' : '' ?>">
                                        ⏱️ <?= (int)$order['elapsed_minutes'] ?>m ago
                                    </span>
                                </div>
                            </div>

                            <div class="items-list">
                                <?php foreach ($order['items'] as $item): ?>
                                    <div class="item-row">
                                        <div class="item-details">
                                            <div class="item-qty"><?= (int)$item['quantity'] ?>x</div>
                                            <div>
                                                <div class="item-name"><?= htmlspecialchars($item['item_name']) ?></div>
                                                <?php if (!empty($item['variant_name'])): ?>
                                                    <div class="item-variant">(<?= htmlspecialchars($item['variant_name']) ?>)</div>
                                                <?php endif; ?>
                                                <?php if (!empty($item['notes'])): ?>
                                                    <div class="item-notes">Note: <?= htmlspecialchars($item['notes']) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <?php if (!empty($order['special_instructions'])): ?>
                                    <div class="special-instr">
                                        ⚠️ Note: <?= htmlspecialchars($order['special_instructions']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card-actions">
                            <a href="<?= $basePath ?? '' ?>/kitchen/kot/<?= (int)$order['id'] ?>" target="_blank" class="btn-kot" title="Print KOT Ticket">
                                🖨️ KOT
                            </a>

                            <?php if ($order['status'] === 'pending' || $order['status'] === 'confirmed'): ?>
                                <button class="btn-status c-prep" onclick="advanceOrderStatus(<?= (int)$order['id'] ?>, 'preparing')">
                                    Start Preparing
                                </button>
                            <?php elseif ($order['status'] === 'preparing'): ?>
                                <button class="btn-status c-ready" onclick="advanceOrderStatus(<?= (int)$order['id'] ?>, 'ready')">
                                    Mark Ready
                                </button>
                            <?php elseif ($order['status'] === 'ready'): ?>
                                <button class="btn-status c-served" onclick="advanceOrderStatus(<?= (int)$order['id'] ?>, 'served')">
                                    Mark Served
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- JAVASCRIPT FOR REAL-TIME POLLING & CHIME (No reload required) -->
    <script>
        let audioAllowed = false;
        let lastKnownOrderCount = <?= count($activeOrders) ?>;
        const audioEl = document.getElementById('kitchenAudio');

        function toggleAudioChime() {
            audioAllowed = !audioAllowed;
            const btn = document.getElementById('soundToggle');
            const label = document.getElementById('soundLabel');
            if (audioAllowed) {
                btn.classList.add('active');
                label.innerText = 'Chime Enabled';
                // Play a brief test chime so browser policy grants audio context
                audioEl.play().catch(e => console.warn('Audio play restricted:', e));
            } else {
                btn.classList.remove('active');
                label.innerText = 'Enable Chime';
            }
        }

        async function advanceOrderStatus(orderId, newStatus) {
            try {
                const base = window.APP_BASE_URL || '';
                const res = await fetch(`${base}/api/kitchen/orders/${orderId}/status`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ status: newStatus })
                });
                const data = await res.json();
                if (data.success) {
                    pollKitchenStream(); // Immediate refresh
                } else {
                    alert(data.error || 'Failed to update order status.');
                }
            } catch (err) {
                console.error('Status update failed:', err);
                alert('Network error while updating status.');
            }
        }

        async function pollKitchenStream() {
            try {
                const base = window.APP_BASE_URL || '';
                const res = await fetch(`${base}/api/kitchen/stream`);
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();

                if (data.success) {
                    // Update connection label
                    document.getElementById('connLabel').innerText = 'Live Kitchen Stream';
                    document.getElementById('connStatus').style.color = 'var(--green)';

                    // Update stats
                    document.getElementById('statNew').innerText = data.stats.new;
                    document.getElementById('statPrep').innerText = data.stats.preparing;
                    document.getElementById('statReady').innerText = data.stats.ready;
                    document.getElementById('statServed').innerText = data.stats.served_today;
                    document.getElementById('activeCountBadge').innerText = `${data.orders.length} Orders`;

                    // Check for new orders
                    if (data.orders.length > lastKnownOrderCount && audioAllowed) {
                        audioEl.currentTime = 0;
                        audioEl.play().catch(e => console.warn('Sound play prevented:', e));
                    }
                    lastKnownOrderCount = data.orders.length;

                    // Re-render Tables
                    renderTables(data.tables);

                    // Re-render Orders
                    renderOrders(data.orders);
                }
            } catch (err) {
                document.getElementById('connLabel').innerText = 'Reconnecting...';
                document.getElementById('connStatus').style.color = 'var(--red)';
            }
        }

        function renderTables(tables) {
            const container = document.getElementById('tablesContainer');
            if (!container) return;

            container.innerHTML = tables.map(tbl => `
                <div class="table-scene">
                    <div class="table-wrap">
                        <div class="table-box ${tbl.is_occupied ? 'occupied' : ''}">
                            <div class="table-number">Table ${tbl.table_number}</div>
                            <div class="table-status">${tbl.is_occupied ? 'Occupied' : 'Available'}</div>
                            ${tbl.is_occupied && tbl.active_order ? `<div class="table-order-badge">#${escapeHtml(tbl.active_order.order_number)}</div>` : ''}
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function renderOrders(orders) {
            const grid = document.getElementById('ordersGrid');
            if (!grid) return;

            if (orders.length === 0) {
                grid.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">🍳</div>
                        <div class="empty-title">All Caught Up!</div>
                        <div class="empty-desc">No pending kitchen orders at the moment. New orders will appear here automatically.</div>
                    </div>
                `;
                return;
            }

            grid.innerHTML = orders.map(order => {
                let actionBtn = '';
                if (order.status === 'pending' || order.status === 'confirmed') {
                    actionBtn = `<button class="btn-status c-prep" onclick="advanceOrderStatus(${order.id}, 'preparing')">Start Preparing</button>`;
                } else if (order.status === 'preparing') {
                    actionBtn = `<button class="btn-status c-ready" onclick="advanceOrderStatus(${order.id}, 'ready')">Mark Ready</button>`;
                } else if (order.status === 'ready') {
                    actionBtn = `<button class="btn-status c-served" onclick="advanceOrderStatus(${order.id}, 'served')">Mark Served</button>`;
                }

                let typeLabel = '🛍️ Takeaway';
                if (order.order_type === 'dine_in') {
                    typeLabel = `Table ${order.table_id}`;
                } else if (order.order_type === 'delivery') {
                    typeLabel = '🛵 Delivery';
                }

                return `
                    <div class="order-card ${order.is_delayed ? 'delayed' : ''}" id="order-card-${order.id}">
                        <div>
                            <div class="card-top">
                                <div class="order-id-box">
                                    <h4>#${escapeHtml(order.order_number)}</h4>
                                    <div class="order-type-chip">${typeLabel}</div>
                                </div>
                                <div class="card-timer">
                                    <span class="timer-badge ${order.is_delayed ? 'warning' : ''}">
                                        ⏱️ ${order.elapsed_minutes}m ago
                                    </span>
                                </div>
                            </div>

                            <div class="items-list">
                                ${order.items.map(it => `
                                    <div class="item-row">
                                        <div class="item-details">
                                            <div class="item-qty">${it.quantity}x</div>
                                            <div>
                                                <div class="item-name">${escapeHtml(it.item_name)}</div>
                                                ${it.variant_name ? `<div class="item-variant">(${escapeHtml(it.variant_name)})</div>` : ''}
                                                ${it.notes ? `<div class="item-notes">Note: ${escapeHtml(it.notes)}</div>` : ''}
                                            </div>
                                        </div>
                                    </div>
                                `).join('')}

                                ${order.special_instructions ? `
                                    <div class="special-instr">
                                        ⚠️ Note: ${escapeHtml(order.special_instructions)}
                                    </div>
                                ` : ''}
                            </div>
                        </div>

                        <div class="card-actions">
                            <a href="${window.APP_BASE_URL || ''}/kitchen/kot/${order.id}" target="_blank" class="btn-kot" title="Print KOT Ticket">
                                🖨️ KOT
                            </a>
                            ${actionBtn}
                        </div>
                    </div>
                `;
            }).join('');
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // Poll every 3 seconds
        setInterval(pollKitchenStream, 3000);
    </script>
</body>
</html>
