<div class="page-header">
    <div>
        <h1><?= $isEdit ? 'Edit Menu Item' : 'Add New Menu Item' ?></h1>
        <div class="sub">Configure pricing, category, descriptions, and upload item photo.</div>
    </div>
    <div>
        <a href="/admin/menu" class="btn btn-secondary">← Back to Menu</a>
    </div>
</div>

<div class="card" style="max-width: 800px;">
    <form action="<?= $isEdit ? '/admin/menu/' . $item['id'] . '/update' : '/admin/menu/store' ?>" method="POST" enctype="multipart/form-data">
        <?= $csrf_field ?>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
            <div class="form-group">
                <label>Item Name *</label>
                <input type="text" name="name" value="<?= \App\Helpers\Security::e($item['name'] ?? '') ?>" placeholder="e.g. Special Mutton Dum Biriyani" required>
            </div>

            <div class="form-group">
                <label>Category *</label>
                <select name="category_id" style="padding:9px 12px; border:1px solid #dde1ea; border-radius:8px; width:100%;" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= (($item['category_id'] ?? 0) == $cat['id']) ? 'selected' : '' ?>>
                            <?= \App\Helpers\Security::e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-top: 15px;">
            <div class="form-group">
                <label>Base Price (₹) *</label>
                <input type="number" step="0.50" name="base_price" value="<?= \App\Helpers\Security::e((string)($item['base_price'] ?? '')) ?>" placeholder="190.00" required>
            </div>

            <div class="form-group">
                <label>Promo Price (₹)</label>
                <input type="number" step="0.50" name="promo_price" value="<?= \App\Helpers\Security::e((string)($item['promo_price'] ?? '')) ?>" placeholder="Leave blank if none">
            </div>

            <div class="form-group">
                <label>Prep Time (Minutes)</label>
                <input type="number" name="prep_time_minutes" value="<?= \App\Helpers\Security::e((string)($item['prep_time_minutes'] ?? 15)) ?>">
            </div>
        </div>

        <div class="form-group" style="margin-top: 15px;">
            <label>Description</label>
            <textarea name="description" rows="3" style="width:100%; padding:9px 12px; border:1px solid #dde1ea; border-radius:8px; font-family:inherit; font-size:13.5px;" placeholder="Ingredients, special preparation notes, dum details..."><?= \App\Helpers\Security::e($item['description'] ?? '') ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px;">
            <div class="form-group">
                <label>Dietary Tag</label>
                <select name="is_veg" style="padding:9px 12px; border:1px solid #dde1ea; border-radius:8px; width:100%;">
                    <option value="0" <?= (($item['is_veg'] ?? 0) == 0) ? 'selected' : '' ?>>Non-Vegetarian 🍗</option>
                    <option value="1" <?= (($item['is_veg'] ?? 0) == 1) ? 'selected' : '' ?>>Vegetarian 🌱</option>
                </select>
            </div>

            <div class="form-group">
                <label>Availability</label>
                <select name="is_available" style="padding:9px 12px; border:1px solid #dde1ea; border-radius:8px; width:100%;">
                    <option value="1" <?= (($item['is_available'] ?? 1) == 1) ? 'selected' : '' ?>>Available (In Stock)</option>
                    <option value="0" <?= (($item['is_available'] ?? 1) == 0) ? 'selected' : '' ?>>Unavailable (Out of Stock)</option>
                </select>
            </div>
        </div>

        <div class="form-group" style="margin-top: 15px;">
            <label>Upload Item Image (Max 3MB, JPG/PNG/WebP)</label>
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
            <?php if (!empty($item['image_url'])): ?>
                <div style="margin-top: 8px;">
                    <span style="font-size: 12px; color: var(--text2);">Current Photo:</span><br>
                    <img src="<?= \App\Helpers\Security::e($item['image_url']) ?>" alt="Preview" style="height: 60px; border-radius: 6px; margin-top: 4px; border: 1px solid var(--border);">
                </div>
            <?php endif; ?>
        </div>

        <div style="margin-top: 25px; padding-top: 15px; border-top: 1px solid var(--border); display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save Changes' : 'Create Menu Item' ?></button>
            <a href="/admin/menu" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
