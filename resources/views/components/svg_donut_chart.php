<?php
/** @var array $data associative array of label => [count, color] */
/** @var int|string $total */

$data = $data ?? [
    'Confirmed' => [18, '#F5B800'],
    'Preparing' => [10, '#D97706'],
    'Ready' => [8, '#16A34A'],
    'Out for Delivery' => [7, '#EA580C'],
    'Delivered' => [5, '#2563EB'],
];

$sum = 0;
foreach ($data as $item) {
    $sum += (int)$item[0];
}
$sum = max(1, $sum);

$circumference = 2 * M_PI * 38; // r = 38 => circumference ~ 238.76
$offset = 0;
?>
<div class="card p-6" style="background: #FFFFFF;">
    <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 1.25rem;">Order Status</h3>
    
    <div class="flex flex-col md:flex-row items-center justify-center gap-6">
        <!-- SVG Donut -->
        <div style="position: relative; width: 160px; height: 160px; flex-shrink: 0;">
            <svg viewBox="0 0 100 100" style="transform: rotate(-90deg); width: 100%; height: 100%;">
                <circle cx="50" cy="50" r="38" fill="transparent" stroke="#F1EADB" stroke-width="12" />
                <?php foreach ($data as $label => $info): ?>
                    <?php
                        $val = (int)$info[0];
                        $color = $info[1];
                        $segLength = ($val / $sum) * $circumference;
                    ?>
                    <?php if ($val > 0): ?>
                        <circle cx="50" cy="50" r="38" fill="transparent" stroke="<?= $color ?>" stroke-width="12"
                                stroke-dasharray="<?= $segLength ?> <?= $circumference - $segLength ?>"
                                stroke-dashoffset="-<?= $offset ?>" />
                        <?php $offset += $segLength; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </svg>
            <div style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
                <span style="font-size: 1.75rem; font-weight: 800; line-height: 1;"><?= $total ?? $sum ?></span>
                <span style="font-size: 0.7rem; font-weight: 600; text-transform: uppercase; color: var(--muted-foreground); margin-top: 2px;">Orders</span>
            </div>
        </div>

        <!-- Legend -->
        <div class="flex-1 w-full" style="display: flex; flex-direction: column; gap: 8px;">
            <?php foreach ($data as $label => $info): ?>
                <div class="flex items-center justify-between" style="font-size: 0.85rem;">
                    <div class="flex items-center gap-2">
                        <span style="width: 10px; height: 10px; border-radius: 50%; background-color: <?= $info[1] ?>; display: inline-block;"></span>
                        <span style="color: var(--foreground); font-weight: 500;"><?= htmlspecialchars($label) ?></span>
                    </div>
                    <span style="font-weight: 700; color: var(--foreground);"><?= (int)$info[0] ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
