<?php

declare(strict_types=1);

namespace Database\Seeds;

use PDO;

class MenuSeeder
{
    public static function run(PDO $pdo): void
    {
        $isMysql = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql';

        $categories = [
            ['name' => 'Biriyani', 'slug' => 'biryani', 'icon' => '🍲', 'sort_order' => 1, 'desc' => 'Traditional firewood dum biriyani prepared with aged basmati rice and secret royal spices.'],
            ['name' => 'Fried Rice', 'slug' => 'fried-rice', 'icon' => '🍚', 'sort_order' => 2, 'desc' => 'Wok-tossed fragrant jasmine rice with crisp garden vegetables and farm eggs.'],
            ['name' => 'Noodles', 'slug' => 'noodles', 'icon' => '🍜', 'sort_order' => 3, 'desc' => 'Classic wok-charred Hakka style noodles with authentic sauces.'],
            ['name' => 'Starters', 'slug' => 'starters', 'icon' => '🍗', 'sort_order' => 4, 'desc' => 'Crispy fried appetizers and juicy spiced tikkas.'],
            ['name' => 'Beverages', 'slug' => 'beverages', 'icon' => '🥤', 'sort_order' => 5, 'desc' => 'Refreshing coolers, sulaimani chai, and traditional lassi.'],
        ];

        $catStmt = $isMysql
            ? $pdo->prepare("INSERT INTO menu_categories (name, slug, description, icon, sort_order) VALUES (:name, :slug, :desc, :icon, :sort_order) ON DUPLICATE KEY UPDATE name=VALUES(name)")
            : $pdo->prepare("INSERT OR IGNORE INTO menu_categories (name, slug, description, icon, sort_order) VALUES (:name, :slug, :desc, :icon, :sort_order)");

        foreach ($categories as $cat) {
            $catStmt->execute([
                ':name' => $cat['name'],
                ':slug' => $cat['slug'],
                ':desc' => $cat['desc'],
                ':icon' => $cat['icon'],
                ':sort_order' => $cat['sort_order'],
            ]);
        }

        // Fetch category IDs
        $cats = $pdo->query("SELECT id, slug FROM menu_categories")->fetchAll(PDO::FETCH_KEY_PAIR);

        $items = [
            // Biriyani
            [
                'category_id' => $cats['biryani'] ?? 1,
                'name' => 'Chicken Biriyani',
                'slug' => 'chicken-biriyani',
                'description' => 'Aromatic aged basmati rice with tender spiced chicken pieces slow-cooked in dum with saffron and ghee.',
                'base_price' => 190.00,
                'image_url' => 'https://images.unsplash.com/photo-1563379091339-03246963d51a?w=400&q=80',
                'is_veg' => 0,
                'prep_time' => 15,
            ],
            [
                'category_id' => $cats['biryani'] ?? 1,
                'name' => 'Mutton Biriyani',
                'slug' => 'mutton-biriyani',
                'description' => 'Slow-cooked succulent mutton chunks layered with fragrant spiced long-grain rice in authentic royal dum style.',
                'base_price' => 280.00,
                'image_url' => 'https://images.unsplash.com/photo-1599043513900-ed6fe01d3833?w=400&q=80',
                'is_veg' => 0,
                'prep_time' => 20,
            ],
            // Fried Rice
            [
                'category_id' => $cats['fried-rice'] ?? 2,
                'name' => 'Veg Fried Rice',
                'slug' => 'veg-fried-rice',
                'description' => 'Wok-tossed vegetables and spring onions with fragrant rice seasoned with white pepper and oriental sauce.',
                'base_price' => 160.00,
                'image_url' => 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?w=400&q=80',
                'is_veg' => 1,
                'prep_time' => 12,
            ],
            [
                'category_id' => $cats['fried-rice'] ?? 2,
                'name' => 'Chicken Fried Rice',
                'slug' => 'chicken-fried-rice',
                'description' => 'Juicy shredded chicken and scrambled eggs stir-fried with fragrant rice, peppers, and scallions.',
                'base_price' => 200.00,
                'image_url' => 'https://images.unsplash.com/photo-1512058564366-18510be2db19?w=400&q=80',
                'is_veg' => 0,
                'prep_time' => 15,
            ],
            // Noodles
            [
                'category_id' => $cats['noodles'] ?? 3,
                'name' => 'Veg Noodles',
                'slug' => 'veg-noodles',
                'description' => 'Crisp garden vegetables tossed with delicate noodles in savory garlic and soy sauce.',
                'base_price' => 160.00,
                'image_url' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=400&q=80',
                'is_veg' => 1,
                'prep_time' => 12,
            ],
            [
                'category_id' => $cats['noodles'] ?? 3,
                'name' => 'Chicken Noodles',
                'slug' => 'chicken-noodles',
                'description' => 'Smoky wok-charred chicken strips, scrambled eggs, and Hakka noodles seasoned with five-spice blend.',
                'base_price' => 200.00,
                'image_url' => 'https://images.unsplash.com/photo-1585032226651-759b368d7246?w=400&q=80',
                'is_veg' => 0,
                'prep_time' => 15,
            ],
        ];

        $itemStmt = $isMysql
            ? $pdo->prepare("
                INSERT INTO menu_items (category_id, name, slug, description, base_price, image_url, is_veg, prep_time_minutes)
                VALUES (:category_id, :name, :slug, :description, :base_price, :image_url, :is_veg, :prep_time)
                ON DUPLICATE KEY UPDATE base_price=VALUES(base_price)
            ")
            : $pdo->prepare("
                INSERT OR IGNORE INTO menu_items (category_id, name, slug, description, base_price, image_url, is_veg, prep_time_minutes)
                VALUES (:category_id, :name, :slug, :description, :base_price, :image_url, :is_veg, :prep_time)
            ");

        foreach ($items as $item) {
            $itemStmt->execute([
                ':category_id' => $item['category_id'],
                ':name' => $item['name'],
                ':slug' => $item['slug'],
                ':description' => $item['description'],
                ':base_price' => $item['base_price'],
                ':image_url' => $item['image_url'],
                ':is_veg' => $item['is_veg'],
                ':prep_time' => $item['prep_time'],
            ]);
        }

        // Add-ons
        $addons = [
            ['name' => 'Extra Raita', 'price' => 20.00, 'is_veg' => 1],
            ['name' => 'Extra Salna / Gravy', 'price' => 30.00, 'is_veg' => 1],
            ['name' => 'Boiled Egg', 'price' => 15.00, 'is_veg' => 0],
            ['name' => 'Chicken 65 Piece (2 pcs)', 'price' => 50.00, 'is_veg' => 0],
        ];

        $addonStmt = $isMysql
            ? $pdo->prepare("INSERT INTO menu_addons (name, price, is_veg) VALUES (:name, :price, :is_veg) ON DUPLICATE KEY UPDATE price=VALUES(price)")
            : $pdo->prepare("INSERT OR IGNORE INTO menu_addons (name, price, is_veg) VALUES (:name, :price, :is_veg)");

        foreach ($addons as $addon) {
            $addonStmt->execute([
                ':name' => $addon['name'],
                ':price' => $addon['price'],
                ':is_veg' => $addon['is_veg'],
            ]);
        }
    }
}
