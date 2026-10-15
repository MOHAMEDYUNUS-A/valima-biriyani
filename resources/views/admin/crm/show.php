<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <a href="/admin/crm" style="color: #888; font-size: 0.85rem; text-decoration: none; font-weight: 600;">&larr; Back to Customers</a>
        <h1 style="font-size: 1.8rem; font-weight: 800; color: #1c0a00; margin-top: 4px;">
            <?= htmlspecialchars($customer['name']) ?>
            <span style="font-size: 1rem; color: #d4a359; font-weight: 700; margin-left: 8px;">(<?= htmlspecialchars($customer['customer_code']) ?>)</span>
        </h1>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; align-items: start;">
    <!-- CUSTOMER PROFILE CARD -->
    <div>
        <div style="background: #fff; border-radius: 12px; border: 1px solid #f0e6dc; padding: 1.5rem; box-shadow: 0 2px 6px rgba(0,0,0,0.03); margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: #1c0a00; margin-bottom: 1rem; border-bottom: 1px solid #f0e6dc; padding-bottom: 8px;">
                Customer Summary
            </h3>

            <div style="margin-bottom: 12px;">
                <div style="font-size: 0.75rem; text-transform: uppercase; color: #888; font-weight: 700;">Mobile Number</div>
                <div style="font-size: 1.1rem; font-weight: 700; color: #1c0a00; margin-top: 2px;"><?= htmlspecialchars($customer['phone']) ?></div>
            </div>

            <div style="margin-bottom: 12px;">
                <div style="font-size: 0.75rem; text-transform: uppercase; color: #888; font-weight: 700;">Loyalty Points Balance</div>
                <div style="font-size: 1.6rem; font-weight: 800; color: #d4a359; margin-top: 2px;">
                    <?= (int)$customer['loyalty_points'] ?> <span style="font-size: 0.9rem; font-weight: 600; color: #888;">points (₹<?= number_format((float)$customer['loyalty_points'], 2) ?>)</span>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                <div>
                    <div style="font-size: 0.75rem; text-transform: uppercase; color: #888; font-weight: 700;">Total Orders</div>
                    <div style="font-size: 1.2rem; font-weight: 700; color: #1c0a00;"><?= (int)$customer['total_orders'] ?></div>
                </div>
                <div>
                    <div style="font-size: 0.75rem; text-transform: uppercase; color: #888; font-weight: 700;">Lifetime Spend</div>
                    <div style="font-size: 1.2rem; font-weight: 700; color: #16a34a;">₹<?= number_format((float)$customer['lifetime_spend'], 2) ?></div>
                </div>
            </div>

            <div style="font-size: 0.8rem; color: #777; margin-top: 1rem; padding-top: 12px; border-top: 1px solid #f0e6dc;">
                Registered: <?= date('d M Y', strtotime($customer['created_at'])) ?>
            </div>
        </div>

        <!-- POINTS ADJUSTMENT CARD -->
        <div style="background: #fff; border-radius: 12px; border: 1px solid #f0e6dc; padding: 1.5rem; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: #1c0a00; margin-bottom: 1rem; border-bottom: 1px solid #f0e6dc; padding-bottom: 8px;">
                Adjust Loyalty Points
            </h3>

            <form method="POST" action="/admin/crm/<?= (int)$customer['id'] ?>/adjust">
                <?= $csrf_field ?>
                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #555; margin-bottom: 4px;">
                        Points to Add or Deduct (Use - for negative)
                    </label>
                    <input type="number" name="points" placeholder="e.g. 20 or -10" required
                           style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.95rem;">
                </div>

                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #555; margin-bottom: 4px;">
                        Reason for Adjustment
                    </label>
                    <input type="text" name="reason" placeholder="e.g. Birthday gift, Courtesy bonus, Correction" required
                           style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.95rem;">
                </div>

                <button type="submit" style="width: 100%; background: #d4a359; color: #000; border: none; padding: 10px; border-radius: 6px; font-weight: 700; cursor: pointer;">
                    Apply Points Adjustment
                </button>
            </form>
        </div>
    </div>

    <!-- RIGHT: ORDER HISTORY & LOYALTY LEDGER -->
    <div>
        <!-- ORDER HISTORY -->
        <div style="background: #fff; border-radius: 12px; border: 1px solid #f0e6dc; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: #1c0a00; margin-bottom: 1rem;">
                Order History (<?= count($orders) ?>)
            </h3>

            <?php if (empty($orders)): ?>
                <div style="padding: 1.5rem; text-align: center; color: #888; background: #faf6f2; border-radius: 8px;">
                    No past orders recorded for this customer profile.
                </div>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid #f0e6dc; color: #777;">
                            <th style="padding: 8px 10px;">Order #</th>
                            <th style="padding: 8px 10px;">Type</th>
                            <th style="padding: 8px 10px;">Channel</th>
                            <th style="padding: 8px 10px;">Status</th>
                            <th style="padding: 8px 10px;">Total</th>
                            <th style="padding: 8px 10px; text-align: right;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $ord): ?>
                            <tr style="border-bottom: 1px solid #f5ede6;">
                                <td style="padding: 10px 10px; font-weight: 700;">
                                    <a href="/admin/orders/<?= (int)$ord['id'] ?>" style="color: #d4a359; text-decoration: none;">
                                        #<?= htmlspecialchars($ord['order_number']) ?>
                                    </a>
                                </td>
                                <td style="padding: 10px 10px; text-transform: capitalize; color: #555;">
                                    <?= htmlspecialchars(str_replace('_', ' ', $ord['order_type'])) ?>
                                </td>
                                <td style="padding: 10px 10px; text-transform: uppercase; font-size: 0.75rem; color: #777;">
                                    <?= htmlspecialchars($ord['channel']) ?>
                                </td>
                                <td style="padding: 10px 10px;">
                                    <span style="font-weight: 700; text-transform: capitalize; color: <?= $ord['status'] === 'completed' || $ord['status'] === 'served' ? '#16a34a' : '#ea580c' ?>;">
                                        <?= htmlspecialchars(str_replace('_', ' ', $ord['status'])) ?>
                                    </span>
                                </td>
                                <td style="padding: 10px 10px; font-weight: 700; color: #1c0a00;">
                                    ₹<?= number_format((float)$ord['total_amount'], 2) ?>
                                </td>
                                <td style="padding: 10px 10px; text-align: right; color: #888;">
                                    <?= date('d M Y, h:i A', strtotime($ord['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- LOYALTY LEDGER -->
        <div style="background: #fff; border-radius: 12px; border: 1px solid #f0e6dc; padding: 1.5rem; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: #1c0a00; margin-bottom: 1rem;">
                Loyalty Points Ledger (Auditable History)
            </h3>

            <?php if (empty($ledger)): ?>
                <div style="padding: 1.5rem; text-align: center; color: #888; background: #faf6f2; border-radius: 8px;">
                    No loyalty transactions recorded yet.
                </div>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid #f0e6dc; color: #777;">
                            <th style="padding: 8px 10px;">Type</th>
                            <th style="padding: 8px 10px;">Points</th>
                            <th style="padding: 8px 10px;">Balance After</th>
                            <th style="padding: 8px 10px;">Description</th>
                            <th style="padding: 8px 10px; text-align: right;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ledger as $tx): ?>
                            <tr style="border-bottom: 1px solid #f5ede6;">
                                <td style="padding: 10px 10px; font-weight: 700; text-transform: uppercase; font-size: 0.75rem;">
                                    <span style="color: <?= $tx['type'] === 'earn' ? '#16a34a' : ($tx['type'] === 'redeem' ? '#b45309' : '#dc2626') ?>;">
                                        <?= htmlspecialchars($tx['type']) ?>
                                    </span>
                                </td>
                                <td style="padding: 10px 10px; font-weight: 800; color: <?= $tx['points'] > 0 ? '#16a34a' : '#dc2626' ?>;">
                                    <?= $tx['points'] > 0 ? "+{$tx['points']}" : $tx['points'] ?> pts
                                </td>
                                <td style="padding: 10px 10px; font-weight: 700; color: #1c0a00;">
                                    <?= (int)$tx['balance_after'] ?> pts
                                </td>
                                <td style="padding: 10px 10px; color: #555;">
                                    <?= htmlspecialchars($tx['description']) ?>
                                </td>
                                <td style="padding: 10px 10px; text-align: right; color: #888;">
                                    <?= date('d M Y, h:i A', strtotime($tx['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
