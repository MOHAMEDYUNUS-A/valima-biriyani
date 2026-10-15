<?php
/** @var string $label */
/** @var string|int|float $value */
/** @var string|null $trend */
/** @var string|null $icon */
/** @var string|null $color */

$label = $title ?? $label ?? '';
$trend = $change ?? $trend ?? null;
$color = $color ?? 'gold';
$borderColor = match ($color) {
    'green' => '#16A34A',
    'blue' => '#2563EB',
    'red' => '#DC2626',
    default => '#F5B800',
};
?>
<div class="card p-5" style="border-top: 3px solid <?= $borderColor ?>; background: #FFFFFF;">
    <div class="flex items-center justify-between mb-2">
        <span style="font-size: 0.775rem; text-transform: uppercase; font-weight: 700; color: var(--muted-foreground); letter-spacing: 0.5px;">
            <?= htmlspecialchars($label) ?>
        </span>
        <?php if (!empty($trend)): ?>
            <span style="font-size: 0.75rem; font-weight: 700; color: #16A34A; background: #F0FDF4; padding: 2px 6px; border-radius: 4px;">
                <?= htmlspecialchars($trend) ?>
            </span>
        <?php endif; ?>
    </div>
    <div style="font-size: 1.85rem; font-weight: 800; color: var(--foreground); line-height: 1.1;">
        <?= htmlspecialchars((string)$value) ?>
    </div>
</div>
