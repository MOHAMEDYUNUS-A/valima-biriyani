<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Request;
use App\Helpers\Session;
use App\Models\MenuCategory;
use App\Models\MenuItem;

class MenuController extends BaseController
{
    public function index(Request $request): void
    {
        $categories = MenuCategory::allActive();
        $selectedCatSlug = $request->input('category');
        $searchQuery = trim((string)$request->input('q', ''));
        $vegOnly = (bool)$request->input('veg', false);

        if (!empty($searchQuery)) {
            $items = MenuItem::search($searchQuery);
        } else {
            $items = MenuItem::allActive();
        }

        if ($vegOnly) {
            $items = array_filter($items, fn($i) => (int)$i['is_veg'] === 1);
        }

        if ($selectedCatSlug) {
            $items = array_filter($items, fn($i) => $i['category_slug'] === $selectedCatSlug);
        }

        // Table session context (if customer came from table QR / dine-in)
        $tableId = Session::get('table_id');

        $this->render('customer.menu', [
            'title' => 'Menu - Valima Biriyani & Delicacies',
            'categories' => $categories,
            'items' => array_values($items),
            'selectedCategory' => $selectedCatSlug,
            'searchQuery' => $searchQuery,
            'vegOnly' => $vegOnly,
            'tableId' => $tableId,
        ], 'main');
    }
}
