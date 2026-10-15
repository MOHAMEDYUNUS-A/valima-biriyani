<?php
/** @var string $text */
/** @var string|null $variant */
/** @var string|null $icon */

$variant = $variant ?? 'gold';
$badgeClass = match ($variant) {
    'green', 'success', 'ready', 'served', 'completed', 'delivered' => 'badge-green',
    'blue', 'info', 'confirmed' => 'badge-blue',
    'red', 'destructive', 'cancelled', 'failed' => 'badge-red',
    'dark' => 'badge-dark',
    default => 'badge-gold',
};
?>
<span class="badge <?= $badgeClass ?> <?= $class ?? '' ?>">
    <?php if (!empty($icon)): ?>
        <?= \App\Helpers\Icon::render($icon, 'w-3 h-3') ?>
    <?php endif; ?>
    <?= htmlspecialchars($text) ?>
</span>
