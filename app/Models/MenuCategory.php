<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\Database;

class MenuCategory
{
    public static function allActive(): array
    {
        return Database::query(
            "SELECT * FROM menu_categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC"
        );
    }

    public static function all(): array
    {
        return Database::query(
            "SELECT c.*, (SELECT COUNT(*) FROM menu_items WHERE category_id = c.id) as item_count
             FROM menu_categories c
             ORDER BY sort_order ASC, name ASC"
        );
    }

    public static function findById(int $id): ?array
    {
        return Database::queryOne("SELECT * FROM menu_categories WHERE id = :id", [':id' => $id]);
    }

    public static function findBySlug(string $slug): ?array
    {
        return Database::queryOne("SELECT * FROM menu_categories WHERE slug = :s", [':s' => $slug]);
    }

    public static function create(array $data): int
    {
        Database::execute(
            "INSERT INTO menu_categories (name, slug, description, icon, sort_order, is_active)
             VALUES (:name, :slug, :desc, :icon, :sort_order, :is_active)",
            [
                ':name' => $data['name'],
                ':slug' => $data['slug'],
                ':desc' => $data['description'] ?? null,
                ':icon' => $data['icon'] ?? '🍲',
                ':sort_order' => (int)($data['sort_order'] ?? 0),
                ':is_active' => (int)($data['is_active'] ?? 1),
            ]
        );
        return (int)Database::lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        Database::execute(
            "UPDATE menu_categories
             SET name = :name, slug = :slug, description = :desc, icon = :icon,
                 sort_order = :sort_order, is_active = :is_active
             WHERE id = :id",
            [
                ':id' => $id,
                ':name' => $data['name'],
                ':slug' => $data['slug'],
                ':desc' => $data['description'] ?? null,
                ':icon' => $data['icon'] ?? '🍲',
                ':sort_order' => (int)($data['sort_order'] ?? 0),
                ':is_active' => (int)($data['is_active'] ?? 1),
            ]
        );
    }

    public static function delete(int $id): void
    {
        Database::execute("DELETE FROM menu_categories WHERE id = :id", [':id' => $id]);
    }
}
