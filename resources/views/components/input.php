<?php
/** @var string $name */
/** @var string|null $label */
/** @var string|null $type */
/** @var string|null $value */
/** @var string|null $placeholder */
/** @var string|null $helper */
/** @var bool|null $required */

$type = $type ?? 'text';
$required = !empty($required);
?>
<div class="mb-4">
    <?php if (!empty($label)): ?>
        <label for="<?= htmlspecialchars($name) ?>" class="form-label">
            <?= htmlspecialchars($label) ?>
            <?php if ($required): ?>
                <span style="color: var(--destructive);">*</span>
            <?php endif; ?>
        </label>
    <?php endif; ?>

    <input type="<?= htmlspecialchars($type) ?>"
           id="<?= htmlspecialchars($name) ?>"
           name="<?= htmlspecialchars($name) ?>"
           value="<?= htmlspecialchars((string)($value ?? '')) ?>"
           placeholder="<?= htmlspecialchars($placeholder ?? '') ?>"
           <?= $required ? 'required' : '' ?>
           class="input <?= $class ?? '' ?>"
           <?= $attrs ?? '' ?>>

    <?php if (!empty($helper)): ?>
        <div class="form-helper"><?= htmlspecialchars($helper) ?></div>
    <?php endif; ?>
</div>
