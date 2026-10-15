<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Request;
use App\Helpers\Session;
use App\Helpers\Logger;
use App\Models\MenuCategory;
use App\Models\MenuItem;

class AdminMenuController extends BaseController
{
    public function index(Request $request): void
    {
        $categories = MenuCategory::all();
        $items = MenuItem::allForAdmin();

        $this->render('admin.menu.index', [
            'title' => 'Menu Management',
            'currentPage' => 'menu',
            'categories' => $categories,
            'items' => $items,
        ], 'admin');
    }

    public function createItem(Request $request): void
    {
        $categories = MenuCategory::allActive();

        $this->render('admin.menu.form', [
            'title' => 'Add Menu Item',
            'currentPage' => 'menu',
            'categories' => $categories,
            'item' => null,
            'isEdit' => false,
        ], 'admin');
    }

    public function storeItem(Request $request): void
    {
        $name = trim((string)$request->input('name', ''));
        $categoryId = (int)$request->input('category_id', 0);
        $basePrice = (float)$request->input('base_price', 0);

        if (empty($name) || $categoryId <= 0 || $basePrice <= 0) {
            Session::flash('error', 'Please fill in all required fields (Name, Category, and Valid Base Price).');
            $this->redirect('/admin/menu/create');
        }

        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name)) . '-' . bin2hex(random_bytes(2));
        $imageUrl = $this->handleImageUpload();

        $itemId = MenuItem::create([
            'category_id' => $categoryId,
            'name' => $name,
            'slug' => $slug,
            'description' => trim((string)$request->input('description', '')),
            'base_price' => $basePrice,
            'promo_price' => $request->input('promo_price'),
            'image_url' => $imageUrl ?: 'https://images.unsplash.com/photo-1563379091339-03246963d51a?w=400&q=80',
            'is_veg' => (int)$request->input('is_veg', 0),
            'is_available' => (int)$request->input('is_available', 1),
            'prep_time_minutes' => (int)$request->input('prep_time_minutes', 15),
            'tax_percent' => (float)$request->input('tax_percent', 5.00),
            'sort_order' => (int)$request->input('sort_order', 0),
        ]);

        Logger::audit('MENU_ITEM_CREATED', Session::user()['name'] ?? 'Admin', [
            'item_id' => $itemId,
            'name' => $name,
            'price' => $basePrice,
        ]);

        Session::flash('success', "Menu item '{$name}' created successfully!");
        $this->redirect('/admin/menu');
    }

    public function editItem(Request $request, string $id): void
    {
        $item = MenuItem::findById((int)$id);
        if (!$item) {
            Session::flash('error', 'Menu item not found.');
            $this->redirect('/admin/menu');
        }

        $categories = MenuCategory::allActive();

        $this->render('admin.menu.form', [
            'title' => "Edit Menu Item - {$item['name']}",
            'currentPage' => 'menu',
            'categories' => $categories,
            'item' => $item,
            'isEdit' => true,
        ], 'admin');
    }

    public function updateItem(Request $request, string $id): void
    {
        $itemId = (int)$id;
        $item = MenuItem::findById($itemId);
        if (!$item) {
            Session::flash('error', 'Menu item not found.');
            $this->redirect('/admin/menu');
        }

        $name = trim((string)$request->input('name', ''));
        $categoryId = (int)$request->input('category_id', 0);
        $basePrice = (float)$request->input('base_price', 0);

        if (empty($name) || $categoryId <= 0 || $basePrice <= 0) {
            Session::flash('error', 'Please fill in all required fields.');
            $this->redirect("/admin/menu/{$itemId}/edit");
        }

        $newImage = $this->handleImageUpload();
        $imageUrl = $newImage ?: $item['image_url'];

        MenuItem::update($itemId, [
            'category_id' => $categoryId,
            'name' => $name,
            'slug' => $item['slug'],
            'description' => trim((string)$request->input('description', '')),
            'base_price' => $basePrice,
            'promo_price' => $request->input('promo_price'),
            'image_url' => $imageUrl,
            'is_veg' => (int)$request->input('is_veg', 0),
            'is_available' => (int)$request->input('is_available', 1),
            'prep_time_minutes' => (int)$request->input('prep_time_minutes', 15),
            'tax_percent' => (float)$request->input('tax_percent', 5.00),
            'sort_order' => (int)$request->input('sort_order', 0),
        ]);

        Logger::audit('MENU_ITEM_UPDATED', Session::user()['name'] ?? 'Admin', [
            'item_id' => $itemId,
            'name' => $name,
            'new_price' => $basePrice,
            'old_price' => $item['base_price'],
        ]);

        Session::flash('success', "Item '{$name}' updated successfully!");
        $this->redirect('/admin/menu');
    }

    public function toggleAvailability(Request $request, string $id): void
    {
        $itemId = (int)$id;
        $newStatus = MenuItem::toggleAvailability($itemId);

        Logger::audit('MENU_ITEM_AVAILABILITY_TOGGLED', Session::user()['name'] ?? 'Admin', [
            'item_id' => $itemId,
            'is_available' => $newStatus,
        ]);

        if ($request->isJson()) {
            $this->success(['is_available' => $newStatus], 'Availability updated.');
        }

        Session::flash('success', 'Item availability updated.');
        $this->redirect('/admin/menu');
    }

    public function deleteItem(Request $request, string $id): void
    {
        $itemId = (int)$id;
        $item = MenuItem::findById($itemId);
        if ($item) {
            MenuItem::delete($itemId);
            Logger::audit('MENU_ITEM_DELETED', Session::user()['name'] ?? 'Admin', [
                'item_id' => $itemId,
                'name' => $item['name'],
            ]);
            Session::flash('success', "Item '{$item['name']}' deleted.");
        }

        $this->redirect('/admin/menu');
    }

    private function handleImageUpload(): ?string
    {
        if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $file = $_FILES['image'];
        $maxBytes = 3 * 1024 * 1024; // 3MB limit
        if ($file['size'] > $maxBytes) {
            Session::flash('error', 'Uploaded image exceeds the 3MB limit.');
            return null;
        }

        // Validate MIME type
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowedMimes, true)) {
            Session::flash('error', 'Invalid image format. Only JPG, PNG, and WebP are allowed.');
            return null;
        }

        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };

        $filename = 'menu_' . bin2hex(random_bytes(10)) . '.' . $ext;
        $uploadDir = __DIR__ . '/../../public/uploads/menu';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $targetPath = $uploadDir . '/' . $filename;
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return '/uploads/menu/' . $filename;
        }

        return null;
    }
}
