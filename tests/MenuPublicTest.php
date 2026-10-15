<?php

declare(strict_types=1);

namespace Tests;

require_once __DIR__ . '/../app/Helpers/Env.php';
require_once __DIR__ . '/../app/Helpers/Database.php';
require_once __DIR__ . '/../app/Helpers/Security.php';
require_once __DIR__ . '/../app/Helpers/Session.php';
require_once __DIR__ . '/../app/Helpers/Logger.php';
require_once __DIR__ . '/../app/Helpers/Request.php';
require_once __DIR__ . '/../app/Helpers/Response.php';
require_once __DIR__ . '/../app/Helpers/View.php';
require_once __DIR__ . '/../app/Models/MenuCategory.php';
require_once __DIR__ . '/../app/Models/MenuItem.php';

use App\Helpers\Env;
use App\Helpers\Database;
use App\Helpers\View;
use App\Models\MenuCategory;
use App\Models\MenuItem;

class MenuPublicTest
{
    private int $passed = 0;
    private int $failed = 0;

    public function runAll(): void
    {
        echo "=====================================================\n";
        echo " RUNNING VALIMA BIRIYANI MENU & STOREFRONT TESTS\n";
        echo "=====================================================\n\n";

        Env::load(__DIR__ . '/../.env');
        Database::init(require __DIR__ . '/../config/database.php');

        $this->testCategoryQueries();
        $this->testMenuItemQueries();
        $this->testSearchAndFilter();
        $this->testAvailabilityToggle();
        $this->testPublicViewsRendering();

        echo "\n-----------------------------------------------------\n";
        echo "RESULTS: {$this->passed} Passed, {$this->failed} Failed\n";
        echo "-----------------------------------------------------\n";

        if ($this->failed > 0) {
            exit(1);
        }
    }

    private function assert(string $name, bool $condition, string $msg = ''): void
    {
        if ($condition) {
            echo " [PASS] {$name}\n";
            $this->passed++;
        } else {
            echo " [FAIL] {$name} - {$msg}\n";
            $this->failed++;
        }
    }

    private function testCategoryQueries(): void
    {
        $cats = MenuCategory::allActive();
        $this->assert('Active Categories Retrieved', count($cats) >= 3);
        $slugs = array_column($cats, 'slug');
        $this->assert('Biriyani Category Present', in_array('biryani', $slugs, true));
        $this->assert('Fried Rice Category Present', in_array('fried-rice', $slugs, true));
    }

    private function testMenuItemQueries(): void
    {
        $items = MenuItem::allActive();
        $this->assert('Active Items Retrieved', count($items) >= 6);

        $chickenBiriyani = null;
        foreach ($items as $item) {
            if ($item['slug'] === 'chicken-biriyani') {
                $chickenBiriyani = $item;
                break;
            }
        }

        $this->assert('Chicken Biriyani Exists in DB', $chickenBiriyani !== null);
        $this->assert('Chicken Biriyani Has Price 190.00', (float)$chickenBiriyani['base_price'] === 190.00);
        $this->assert('Chicken Biriyani Marked Non-Veg', (int)$chickenBiriyani['is_veg'] === 0);
    }

    private function testSearchAndFilter(): void
    {
        $results = MenuItem::search('noodles');
        $this->assert('Search "noodles" Returns Items', count($results) >= 2);

        $vegResults = MenuItem::search('veg');
        $this->assert('Search "veg" Returns Items', count($vegResults) >= 2);
    }

    private function testAvailabilityToggle(): void
    {
        $item = MenuItem::allActive()[0];
        $itemId = (int)$item['id'];
        $initialState = (int)$item['is_available'];

        // Toggle to opposite
        $newStatus = MenuItem::toggleAvailability($itemId);
        $this->assert('Availability Toggled', (int)$newStatus !== $initialState);

        // Toggle back to initial
        $restored = MenuItem::toggleAvailability($itemId);
        $this->assert('Availability Restored', (int)$restored === $initialState);
    }

    private function testPublicViewsRendering(): void
    {
        $categories = MenuCategory::allActive();
        $items = MenuItem::allActive();

        // 1. Home
        $homeHtml = View::render('customer.home', [
            'title' => 'Home',
            'categories' => $categories,
            'featuredItems' => $items,
        ], 'main');
        $this->assert('Home View Renders with Layout', str_contains($homeHtml, 'A Symphony of Spices'));

        // 2. Menu
        $menuHtml = View::render('customer.menu', [
            'title' => 'Menu',
            'categories' => $categories,
            'items' => $items,
            'selectedCategory' => null,
            'searchQuery' => '',
            'vegOnly' => false,
            'tableId' => null,
        ], 'main');
        $this->assert('Menu View Renders DB Items', str_contains($menuHtml, 'Chicken Biriyani'));
        $this->assert('Menu View Injects Real DB Prices', str_contains($menuHtml, '190.00'));

        // 3. About
        $aboutHtml = View::render('customer.about', ['title' => 'About'], 'main');
        $this->assert('About View Renders', str_contains($aboutHtml, 'The Valima Biriyani Legacy'));

        // 4. Contact
        $contactHtml = View::render('customer.contact', ['title' => 'Contact'], 'main');
        $this->assert('Contact View Renders WhatsApp Link', str_contains($contactHtml, 'https://wa.me/'));

        // 5. Policies
        $refundHtml = View::render('customer.policies.refund', ['title' => 'Refund'], 'main');
        $this->assert('Refund Policy View Renders', str_contains($refundHtml, 'Refund & Cancellation Policy'));
    }
}

// Run test
$test = new MenuPublicTest();
$test->runAll();
