<div class="page-header">
    <div>
        <h1>Menu Management</h1>
        <div class="sub">Create, edit, and toggle real-time availability for all food items.</div>
    </div>
    <div>
        <a href="/admin/menu/create" class="btn btn-primary">➕ Add Menu Item</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Active Food Catalog (<?= count($items) ?> Items)</h3>
    </div>

    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th style="width: 60px;">Image</th>
                    <th>Item Details</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Diet</th>
                    <th>Live Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2rem; color: var(--text2);">
                            No menu items found. Click "+ Add Menu Item" to get started.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <img src="<?= \App\Helpers\Security::e($item['image_url'] ?: '/assets/placeholder.jpg') ?>" alt="Thumb" style="width: 44px; height: 44px; border-radius: 8px; object-fit: cover; border: 1px solid var(--border);">
                            </td>
                            <td>
                                <div style="font-weight: 600; color: var(--text);"><?= \App\Helpers\Security::e($item['name']) ?></div>
                                <div style="font-size: 12px; color: var(--text2);"><?= \App\Helpers\Security::e(substr((string)$item['description'], 0, 45)) ?>...</div>
                            </td>
                            <td>
                                <span class="badge badge-neutral"><?= \App\Helpers\Security::e($item['category_name']) ?></span>
                            </td>
                            <td style="font-weight: 700; color: var(--text);">
                                ₹<?= number_format((float)$item['base_price'], 2) ?>
                            </td>
                            <td>
                                <?php if ($item['is_veg']): ?>
                                    <span class="badge badge-success">Veg</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Non-Veg</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form action="/admin/menu/<?= $item['id'] ?>/toggle" method="POST" style="margin: 0;">
                                    <?= $csrf_field ?>
                                    <?php if ($item['is_available']): ?>
                                        <button type="submit" class="btn btn-sm btn-success" title="Click to mark Out of Stock">In Stock</button>
                                    <?php else: ?>
                                        <button type="submit" class="btn btn-sm btn-danger" title="Click to mark In Stock">Out of Stock</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="/admin/menu/<?= $item['id'] ?>/edit" class="btn btn-sm btn-secondary">Edit</a>
                                    <form action="/admin/menu/<?= $item['id'] ?>/delete" method="POST" onsubmit="return confirm('Are you sure you want to delete this menu item?');" style="margin: 0;">
                                        <?= $csrf_field ?>
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
