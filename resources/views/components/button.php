<?php
/** @var string $text */
/** @var string|null $variant */
/** @var string|null $size */
/** @var string|null $icon */
/** @var string|null $href */
/** @var string|null $class */
/** @var string|null $type */

$variant = $variant ?? 'primary';
$size = $size ?? 'md';
$type = $type ?? 'button';
$class = $class ?? '';

$classes = "btn btn-{$variant}";
if ($size === 'sm') $classes .= " btn-sm";
if ($size === 'lg') $classes .= " btn-lg";
if (!empty($class)) $classes .= " {$class}";
?>

<?php if (!empty($href)): ?>
    <a href="<?= htmlspecialchars($href) ?>" class="<?= $classes ?>" <?= $attrs ?? '' ?>>
        <?php if (!empty($icon)): ?>
            <?= \App\Helpers\Icon::render($icon, 'w-4 h-4') ?>
        <?php endif; ?>
        <span><?= htmlspecialchars($text ?? '') ?></span>
    </a>
<?php else: ?>
    <button type="<?= htmlspecialchars($type) ?>" class="<?= $classes ?>" <?= $attrs ?? '' ?>>
        <?php if (!empty($icon)): ?>
            <?= \App\Helpers\Icon::render($icon, 'w-4 h-4') ?>
        <?php endif; ?>
        <span><?= htmlspecialchars($text ?? '') ?></span>
    </button>
<?php endif; ?>
