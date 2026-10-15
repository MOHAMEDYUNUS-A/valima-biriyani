<?php
/** @var array $channels associative array of channel_name => [count, percentage, color] */

$channels = $channels ?? [
    'Website' => ['count' => 62, 'pct' => 62, 'color' => '#2563EB'],
    'Phone' => ['count' => 15, 'pct' => 15, 'color' => '#16A34A'],
    'WhatsApp' => ['count' => 10, 'pct' => 10, 'color' => '#F5B800'],
    'Swiggy' => ['count' => 8, 'pct' => 8, 'color' => '#EA580C'],
    'Zomato' => ['count' => 5, 'pct' => 5, 'color' => '#DC2626'],
];
?>
<div class="card p-6" style="background: #FFFFFF;">
    <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1.25rem;">Order Channels</h3>

    <div style="display: flex; flex-direction: column; gap: 14px;">
        <?php foreach ($channels as $channel => $meta): ?>
            <div>
                <div class="flex items-center justify-between mb-1" style="font-size: 0.85rem;">
                    <span style="font-weight: 600; color: var(--foreground);"><?= htmlspecialchars($channel) ?></span>
                    <span style="color: var(--muted-foreground); font-weight: 600; font-size: 0.8rem;"><?= (int)$meta['pct'] ?>%</span>
                </div>
                <div style="height: 8px; width: 100%; background: #F1EADB; border-radius: 999px; overflow: hidden;">
                    <div style="height: 100%; width: <?= (int)$meta['pct'] ?>%; background: <?= $meta['color'] ?>; border-radius: 999px; transition: width 0.4s;"></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
