<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Database;

class MenuItem
{
    public static function allActive(): array
    {
        return Database::query(
            "SELECT m.*, c.name as category_name, c.slug as category_slug
             FROM menu_items m
             JOIN menu_categories c ON m.category_id = c.id
             WHERE m.is_available = 1 AND c.is_active = 1
             ORDER BY c.sort_order ASC, m.sort_order ASC, m.name ASC"
        );
    }

    public static function allForAdmin(): array
    {
        return Database::query(
            "SELECT m.*, c.name as category_name, c.slug as category_slug
             FROM menu_items m
             JOIN menu_categories c ON m.category_id = c.id
             ORDER BY c.sort_order ASC, m.sort_order ASC, m.name ASC"
        );
    }

    public static function findById(int $id): ?array
    {
        return Database::queryOne(
            "SELECT m.*, c.name as category_name, c.slug as category_slug
             FROM menu_items m
             JOIN menu_categories c ON m.category_id = c.id
             WHERE m.id = :id LIMIT 1",
            [':id' => $id]
        );
    }

    public static function getByCategory(int $categoryId): array
    {
        return Database::query(
            "SELECT * FROM menu_items WHERE category_id = :cid AND is_available = 1 ORDER BY sort_order ASC, name ASC",
            [':cid' => $categoryId]
        );
    }

    public static function search(string $query): array
    {
        $term = '%' . trim($query) . '%';
        return Database::query(
            "SELECT m.*, c.name as category_name, c.slug as category_slug
             FROM menu_items m
             JOIN menu_categories c ON m.category_id = c.id
             WHERE m.is_available = 1 AND (m.name LIKE :q OR m.description LIKE :q2)
             ORDER BY m.name ASC",
            [':q' => $term, ':q2' => $term]
        );
    }

    public static function toggleAvailability(int $id): bool
    {
        $item = self::findById($id);
        if (!$item) {
            return false;
        }

        $newStatus = $item['is_available'] ? 0 : 1;
        Database::execute(
            "UPDATE menu_items SET is_available = :avail WHERE id = :id",
            [':avail' => $newStatus, ':id' => $id]
        );
        return (bool)$newStatus;
    }

    public static function create(array $data): int
    {
        Database::execute(
            "INSERT INTO menu_items (
                category_id, name, slug, description, base_price, promo_price,
                image_url, is_veg, is_available, prep_time_minutes, tax_percent, sort_order
            ) VALUES (
                :category_id, :name, :slug, :description, :base_price, :promo_price,
                :image_url, :is_veg, :is_available, :prep_time_minutes, :tax_percent, :sort_order
            )",
            [
                ':category_id' => (int)$data['category_id'],
                ':name' => $data['name'],
                ':slug' => $data['slug'],
                ':description' => $data['description'] ?? null,
                ':base_price' => (float)$data['base_price'],
                ':promo_price' => !empty($data['promo_price']) ? (float)$data['promo_price'] : null,
                ':image_url' => $data['image_url'] ?? null,
                ':is_veg' => (int)($data['is_veg'] ?? 0),
                ':is_available' => (int)($data['is_available'] ?? 1),
                ':prep_time_minutes' => (int)($data['prep_time_minutes'] ?? 15),
                ':tax_percent' => (float)($data['tax_percent'] ?? 5.00),
                ':sort_order' => (int)($data['sort_order'] ?? 0),
            ]
        );
        return (int)Database::lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        Database::execute(
            "UPDATE menu_items SET
                category_id = :category_id, name = :name, slug = :slug, description = :description,
                base_price = :base_price, promo_price = :promo_price, image_url = :image_url,
                is_veg = :is_veg, is_available = :is_available, prep_time_minutes = :prep_time_minutes,
                tax_percent = :tax_percent, sort_order = :sort_order
             WHERE id = :id",
            [
                ':id' => $id,
                ':category_id' => (int)$data['category_id'],
                ':name' => $data['name'],
                ':slug' => $data['slug'],
                ':description' => $data['description'] ?? null,
                ':base_price' => (float)$data['base_price'],
                ':promo_price' => !empty($data['promo_price']) ? (float)$data['promo_price'] : null,
                ':image_url' => $data['image_url'] ?? null,
                ':is_veg' => (int)($data['is_veg'] ?? 0),
                ':is_available' => (int)($data['is_available'] ?? 1),
                ':prep_time_minutes' => (int)($data['prep_time_minutes'] ?? 15),
                ':tax_percent' => (float)($data['tax_percent'] ?? 5.00),
                ':sort_order' => (int)($data['sort_order'] ?? 0),
            ]
        );
    }

    public static function delete(int $id): void
    {
        Database::execute("DELETE FROM menu_items WHERE id = :id", [':id' => $id]);
    }
}
