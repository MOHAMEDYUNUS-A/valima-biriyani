<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 1.8rem; font-weight: 800; color: #1c0a00;">Customer CRM &amp; Loyalty</h1>
        <p style="color: #666; font-size: 0.9rem;">Manage registered restaurant customers, lifetime value, and loyalty balances</p>
    </div>
</div>

<!-- STATS CARDS -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
    <div style="background: #fff; padding: 1.25rem 1.5rem; border-radius: 12px; border: 1px solid #f0e6dc; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: #888; font-weight: 700; letter-spacing: 0.5px;">Total Customers</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: #1c0a00; margin-top: 4px;"><?= number_format((int)$stats['total_customers']) ?></div>
    </div>
    <div style="background: #fff; padding: 1.25rem 1.5rem; border-radius: 12px; border: 1px solid #f0e6dc; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: #888; font-weight: 700; letter-spacing: 0.5px;">Loyalty Points Issued</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: #d4a359; margin-top: 4px;"><?= number_format((int)$stats['loyalty_issued']) ?> pts</div>
    </div>
    <div style="background: #fff; padding: 1.25rem 1.5rem; border-radius: 12px; border: 1px solid #f0e6dc; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: #888; font-weight: 700; letter-spacing: 0.5px;">Customer Lifetime Spend</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: #16a34a; margin-top: 4px;">₹<?= number_format((float)$stats['total_revenue'], 2) ?></div>
    </div>
</div>

<!-- SEARCH BAR -->
<div style="background: #fff; padding: 1.25rem; border-radius: 12px; border: 1px solid #f0e6dc; margin-bottom: 1.5rem;">
    <form method="GET" action="/admin/crm" style="display: flex; gap: 12px;">
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name, phone (+91...), or customer code..."
               style="flex: 1; padding: 10px 14px; border: 1px solid #ddd; border-radius: 8px; font-size: 0.95rem;">
        <button type="submit" style="background: #d4a359; color: #000; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer;">
            Search
        </button>
        <?php if (!empty($search)): ?>
            <a href="/admin/crm" style="background: #f1f5f9; color: #333; text-decoration: none; padding: 10px 16px; border-radius: 8px; font-weight: 600; display: flex; align-items: center;">
                Clear
            </a>
        <?php endif; ?>
    </form>
</div>

<!-- CUSTOMER TABLE -->
<div style="background: #fff; border-radius: 12px; border: 1px solid #f0e6dc; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
        <thead style="background: #faf6f2; border-bottom: 2px solid #f0e6dc; color: #666; font-size: 0.8rem; text-transform: uppercase;">
            <tr>
                <th style="padding: 12px 16px;">Code</th>
                <th style="padding: 12px 16px;">Customer Name</th>
                <th style="padding: 12px 16px;">Mobile</th>
                <th style="padding: 12px 16px;">Orders</th>
                <th style="padding: 12px 16px;">Total Spend</th>
                <th style="padding: 12px 16px;">Points</th>
                <th style="padding: 12px 16px;">Last Order</th>
                <th style="padding: 12px 16px; text-align: right;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($customers)): ?>
                <tr>
                    <td colspan="8" style="padding: 3rem; text-align: center; color: #888;">
                        No customers found matching your criteria.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($customers as $c): ?>
                    <tr style="border-bottom: 1px solid #f5ede6;">
                        <td style="padding: 12px 16px; font-weight: 700; color: #d4a359;">
                            <?= htmlspecialchars($c['customer_code']) ?>
                        </td>
                        <td style="padding: 12px 16px; font-weight: 600; color: #1c0a00;">
                            <?= htmlspecialchars($c['name']) ?>
                        </td>
                        <td style="padding: 12px 16px; color: #555;">
                            <?= htmlspecialchars($c['phone']) ?>
                        </td>
                        <td style="padding: 12px 16px; font-weight: 600;">
                            <?= (int)$c['total_orders'] ?>
                        </td>
                        <td style="padding: 12px 16px; font-weight: 700; color: #16a34a;">
                            ₹<?= number_format((float)$c['lifetime_spend'], 2) ?>
                        </td>
                        <td style="padding: 12px 16px;">
                            <span style="background: #fdf6ec; color: #b45309; padding: 4px 10px; border-radius: 12px; font-weight: 700; font-size: 0.85rem; border: 1px solid #fde68a;">
                                <?= (int)$c['loyalty_points'] ?> pts
                            </span>
                        </td>
                        <td style="padding: 12px 16px; color: #777; font-size: 0.85rem;">
                            <?= !empty($c['last_order_at']) ? date('d M Y, h:i A', strtotime($c['last_order_at'])) : '—' ?>
                        </td>
                        <td style="padding: 12px 16px; text-align: right;">
                            <a href="/admin/crm/<?= (int)$c['id'] ?>" style="background: #1c0a00; color: #fff; text-decoration: none; padding: 6px 12px; border-radius: 6px; font-size: 0.8rem; font-weight: 600;">
                                View Profile &rarr;
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
