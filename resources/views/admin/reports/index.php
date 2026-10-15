<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-serif font-bold text-foreground">Sales & Operational Reports</h1>
            <p class="text-sm text-muted-foreground">Audited financial data, best-selling dishes, and channel metrics from the live database.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="/admin/reports/export.csv" class="btn btn-primary shadow-soft flex items-center gap-2">
                <?= \App\Helpers\Icon::render('download', 'w-4 h-4') ?>
                <span>Export Sales CSV</span>
            </a>
        </div>
    </div>

    <!-- Financial KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <?= \App\Helpers\Component::render('stat_card', [
            'title' => 'Gross Sales',
            'value' => '₹' . number_format($metrics['gross_sales'], 2),
            'icon' => 'currency-inr',
            'change' => 'Excludes cancelled orders',
            'changeType' => 'neutral'
        ]) ?>

        <?= \App\Helpers\Component::render('stat_card', [
            'title' => 'Collected Revenue',
            'value' => '₹' . number_format($metrics['collected_revenue'], 2),
            'icon' => 'check-circle',
            'change' => 'Confirmed & Paid',
            'changeType' => 'positive'
        ]) ?>

        <?= \App\Helpers\Component::render('stat_card', [
            'title' => 'Pending Payments',
            'value' => '₹' . number_format($metrics['pending_revenue'], 2),
            'icon' => 'clock',
            'change' => 'Cash/COD on fulfillment',
            'changeType' => 'negative'
        ]) ?>

        <?= \App\Helpers\Component::render('stat_card', [
            'title' => 'Total GST Collected',
            'value' => '₹' . number_format($metrics['total_tax'], 2),
            'icon' => 'file-text',
            'change' => '5% GST (CGST + SGST)',
            'changeType' => 'neutral'
        ]) ?>
    </div>

    <!-- Secondary Metrics Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Best-Selling Items Table -->
        <div class="card p-6">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-border">
                <div class="flex items-center gap-2">
                    <?= \App\Helpers\Icon::render('flame', 'w-5 h-5 text-copper') ?>
                    <h3 class="font-bold text-foreground">Top 10 Best-Selling Items</h3>
                </div>
                <span class="text-xs text-muted-foreground font-semibold">By Volume</span>
            </div>

            <?php if (empty($bestSellers)): ?>
                <div class="text-center py-8 text-muted-foreground text-sm">
                    No sales data available yet.
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-border text-xs text-muted-foreground uppercase">
                                <th class="pb-2">Dish</th>
                                <th class="pb-2 text-center">Qty Sold</th>
                                <th class="pb-2 text-right">Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/60">
                            <?php foreach ($bestSellers as $i => $item): ?>
                                <tr>
                                    <td class="py-2.5 font-medium text-foreground flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full bg-muted text-muted-foreground text-xs flex items-center justify-center font-bold">
                                            <?= $i + 1 ?>
                                        </span>
                                        <span><?= \App\Helpers\Security::e($item['item_name']) ?></span>
                                    </td>
                                    <td class="py-2.5 text-center font-semibold text-foreground">
                                        <?= (int)$item['total_qty'] ?>
                                    </td>
                                    <td class="py-2.5 text-right font-bold text-foreground">
                                        ₹<?= number_format((float)$item['total_revenue'], 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Channel Revenue Breakdown -->
        <div class="card p-6">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-border">
                <div class="flex items-center gap-2">
                    <?= \App\Helpers\Icon::render('chart-pie', 'w-5 h-5 text-primary') ?>
                    <h3 class="font-bold text-foreground">Revenue by Order Channel</h3>
                </div>
                <span class="text-xs text-muted-foreground font-semibold">All-Time</span>
            </div>

            <?php if (empty($channels)): ?>
                <div class="text-center py-8 text-muted-foreground text-sm">
                    No channel data recorded yet.
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php 
                    $totalChannelRev = array_sum(array_column($channels, 'total')) ?: 1.0;
                    foreach ($channels as $chan): 
                        $pct = round(($chan['total'] / $totalChannelRev) * 100);
                    ?>
                        <div>
                            <div class="flex justify-between items-center text-sm mb-1">
                                <span class="font-semibold text-foreground capitalize flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-primary inline-block"></span>
                                    <?= \App\Helpers\Security::e($chan['channel']) ?>
                                    <span class="text-xs text-muted-foreground">(<?= (int)$chan['count'] ?> orders)</span>
                                </span>
                                <span class="font-bold text-foreground">
                                    ₹<?= number_format((float)$chan['total'], 2) ?>
                                    <span class="text-xs text-muted-foreground font-normal">(<?= $pct ?>%)</span>
                                </span>
                            </div>
                            <div class="w-full bg-muted rounded-full h-2 overflow-hidden">
                                <div class="bg-primary h-2 rounded-full transition-all duration-300" style="width: <?= $pct ?>%;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-6 pt-4 border-t border-border flex items-center justify-between text-xs text-muted-foreground">
                    <span>Total Delivery Fees Collected:</span>
                    <span class="font-bold text-foreground">₹<?= number_format($metrics['total_delivery_fees'], 2) ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
