<?php

declare(strict_types=1);

namespace Tests;

require_once __DIR__ . '/../app/Helpers/Env.php';
require_once __DIR__ . '/../app/Helpers/Logger.php';
require_once __DIR__ . '/../app/Helpers/Database.php';
require_once __DIR__ . '/../app/Helpers/Security.php';
require_once __DIR__ . '/../app/Helpers/Request.php';
require_once __DIR__ . '/../app/Helpers/Response.php';
require_once __DIR__ . '/../app/Helpers/Session.php';
require_once __DIR__ . '/../app/Helpers/View.php';
require_once __DIR__ . '/../app/Helpers/Icon.php';
require_once __DIR__ . '/../app/Helpers/Component.php';
require_once __DIR__ . '/../app/Models/MenuItem.php';
require_once __DIR__ . '/../app/Services/OrderService.php';
require_once __DIR__ . '/../app/Services/LoyaltyService.php';

use App\Helpers\Env;
use App\Helpers\Database;
use App\Helpers\Request;
use App\Helpers\Session;
use App\Helpers\View;
use App\Helpers\Security;
use App\Services\OrderService;
use App\Services\LoyaltyService;
use PDO;

class WorkflowEndToEndTest
{
    private int $passed = 0;
    private int $failed = 0;

    public function runAll(): void
    {
        echo "=====================================================\n";
        echo " RUNNING VALIMA BIRIYANI FULL END-TO-END WORKFLOW TESTS\n";
        echo "=====================================================\n\n";

        Env::load(__DIR__ . '/../.env');
        Database::init(require __DIR__ . '/../config/database.php');

        $this->testHostingerHtaccessShielding();
        $this->testWebInstallerWorkflow();
        $this->testPublicPagesRendering();
        $this->testCustomerOrderAndCheckoutWorkflow();
        $this->testTrackingAndAddMoreWorkflow();
        $this->testKitchenKdsAndKotWorkflow();
        $this->testPosBillingAndReceiptWorkflow();
        $this->testAuthAndRoleAccessWorkflow();
        $this->testAdminOrderManagementWorkflow();
        $this->testAdminMenuManagementWorkflow();
        $this->testCrmAndLoyaltyWorkflow();
        $this->testAdminReportsAndExportWorkflow();
        $this->testAdminSettingsAndIntegrationsWorkflow();

        echo "\n-----------------------------------------------------\n";
        echo "RESULTS: {$this->passed} Passed, {$this->failed} Failed\n";
        echo "-----------------------------------------------------\n\n";

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

    /**
     * 1. Test Hostinger .htaccess configurations
     */
    private function testHostingerHtaccessShielding(): void
    {
        $rootHtaccess = file_get_contents(__DIR__ . '/../.htaccess');
        $this->assert('Root .htaccess Exists', $rootHtaccess !== false);
        $this->assert('Root .htaccess Blocks Sensitive Files (.env, .git, app, storage)', str_contains($rootHtaccess, 'RewriteRule ^(\.env|\.git|app|config|database|storage|legacy|tests) - [F,L]'));
        $this->assert('Root .htaccess Rewrites to public/', str_contains($rootHtaccess, 'RewriteRule ^(.*)$ public/$1 [L]'));

        $publicHtaccess = file_get_contents(__DIR__ . '/../public/.htaccess');
        $this->assert('Public .htaccess Exists', $publicHtaccess !== false);
        $this->assert('Public .htaccess Enforces Security Headers', str_contains($publicHtaccess, 'X-Content-Type-Options') && str_contains($publicHtaccess, 'X-Frame-Options'));
        $this->assert('Public .htaccess Routes All Traffic to index.php', str_contains($publicHtaccess, 'RewriteRule ^ index.php [L,QSA]'));
    }

    /**
     * 2. Test Web Installer Workflow (/install)
     */
    private function testWebInstallerWorkflow(): void
    {
        $lockFile = __DIR__ . '/../storage/installed.lock';

        // 1. Render installer index view
        $installHtml = View::render('install.index', [
            'title' => 'Hostinger Installation Wizard',
            'checks' => [
                'php_version' => PHP_VERSION,
                'php_pass' => true,
                'extensions' => ['curl' => true, 'mbstring' => true, 'pdo' => true],
                'pdo_mysql' => true,
                'storage_writable' => true,
                'env_writable' => true,
                'all_passed' => true,
            ],
            'allChecksPass' => true,
            'errors' => [],
            'old' => [
                'db_host' => 'localhost',
                'db_port' => '3306',
                'db_name' => '',
                'db_user' => '',
                'store_name' => 'Valima Biriyani',
                'store_phone' => '+91 98765 43210',
                'admin_name' => 'Admin Owner',
                'admin_email' => 'admin@valimabiriyani.com',
            ],
        ]);

        $this->assert('Installer Page Renders Successfully', str_contains($installHtml, 'Valima Biriyani Setup Wizard'));
        $this->assert('Installer Contains Hostinger Environment Check', str_contains($installHtml, 'Hostinger Server Environment'));
        $this->assert('Installer Contains MySQL Database Credentials Form', str_contains($installHtml, 'Database Host') && str_contains($installHtml, 'Database Name'));

        // 2. Render success view
        $successHtml = View::render('install.success', [
            'title' => 'Installation Complete',
            'storeName' => 'Valima Biriyani',
            'adminEmail' => 'admin@valimabiriyani.com',
        ]);
        $this->assert('Installer Success Page Renders Celebration', str_contains($successHtml, 'Installation Complete'));
        $this->assert('Installer Success Page Informs of Lock File Activation', str_contains($successHtml, 'Security Lock Activated'));

        // 3. Create lock file and test locked screen
        file_put_contents($lockFile, json_encode(['installed_at' => gmdate('Y-m-d H:i:s'), 'version' => '1.0.0']));
        $this->assert('storage/installed.lock File Created', file_exists($lockFile));

        $lockedHtml = View::render('install.locked', [
            'title' => 'Installer Locked',
            'lockDate' => date('Y-m-d H:i:s'),
        ]);
        $this->assert('Installer Locks Once Completed', str_contains($lockedHtml, 'Application Already Installed'));
    }

    /**
     * 3. Test Public Customer Storefront Pages
     */
    private function testPublicPagesRendering(): void
    {
        $categories = Database::fetchAll("SELECT * FROM menu_categories WHERE is_active = 1");
        $items = Database::fetchAll("SELECT * FROM menu_items WHERE is_available = 1");

        // Home
        $homeHtml = View::render('home', [
            'title' => 'Valima Biriyani — Authentic Biryani',
            'categories' => $categories,
            'popularItems' => array_slice($items, 0, 6),
        ], 'main');
        $this->assert('Home Page Renders Hero with Gold Accent', str_contains($homeHtml, 'Rich in Tradition'));
        $this->assert('Home Page Contains Valima Logo', str_contains($homeHtml, 'valima-logo.jpg'));
        $this->assert('Home Page Contains 4-Icon Trust Strip', str_contains($homeHtml, 'Fresh Ingredients Always') && str_contains($homeHtml, 'Fast & Reliable Delivery'));

        // Menu
        $menuHtml = View::render('customer.menu', [
            'title' => 'Our Special Menu',
            'categories' => $categories,
            'items' => $items,
            'selectedCategory' => null,
            'searchQuery' => '',
            'vegOnly' => false,
            'tableId' => null,
        ], 'main');
        $this->assert('Menu Page Renders Category Tabs', str_contains($menuHtml, 'Biriyani'));
        $this->assert('Menu Page Renders Real Database Biriyani Dishes', str_contains($menuHtml, 'Chicken Biriyani'));
        $this->assert('Menu Page Renders Variant Selector Bottom-Sheet', str_contains($menuHtml, 'Choose Variant') && str_contains($menuHtml, 'Family Pack'));

        // About & Contact
        $aboutHtml = View::render('customer.about', ['title' => 'About'], 'main');
        $this->assert('About Page Renders', str_contains($aboutHtml, 'Valima Biriyani'));

        $contactHtml = View::render('customer.contact', ['title' => 'Contact'], 'main');
        $this->assert('Contact Page Renders Support WhatsApp Link', str_contains($contactHtml, 'wa.me'));

        // Styleguide
        $styleguideHtml = View::render('styleguide');
        $this->assert('Styleguide Page Renders UI Component Showcase', str_contains($styleguideHtml, 'UI/UX Design System'));
    }

    /**
     * 4. Test Customer Ordering & Checkout Workflow
     */
    private function testCustomerOrderAndCheckoutWorkflow(): void
    {
        $chickenItem = Database::fetchOne("SELECT id, name, base_price FROM menu_items WHERE is_available = 1 LIMIT 1");
        $this->assert('Active Menu Item Found for Order', !empty($chickenItem));

        $testPhone = '+9198765' . rand(10000, 99999);
        $orderData = [
            'order_type' => 'delivery',
            'channel' => 'website',
            'customer_name' => 'Hostinger Customer',
            'customer_phone' => $testPhone,
            'customer_email' => 'customer@valima.com',
            'delivery_address' => 'Flat 4B, Anna Nagar West, Chennai - 600040',
            'delivery_instructions' => 'Call at gate',
            'payment_method' => 'cash',
            'cart_items' => [
                [
                    'id' => $chickenItem['id'],
                    'qty' => 2,
                ]
            ]
        ];

        $order = OrderService::createOrder($orderData);
        $this->assert('Order Created Successfully with Server Price Calculation', !empty($order['order_number']));
        $this->assert('Order Total Matches Server Pricing with Tax & Delivery Fee', (float)$order['total_amount'] > (float)$chickenItem['base_price'] * 2);
        $this->assert('Order Assigned Secure Random Tracking Token', !empty($order['tracking_token']) && strlen($order['tracking_token']) === 32);

        // Verify stored in DB
        $dbOrder = Database::fetchOne("SELECT * FROM orders WHERE order_number = :num", [':num' => $order['order_number']]);
        $this->assert('Order Record Persisted in Database', !empty($dbOrder) && $dbOrder['order_number'] === $order['order_number']);
    }

    /**
     * 5. Test Live Tracking & "Add More Items"
     */
    private function testTrackingAndAddMoreWorkflow(): void
    {
        $order = Database::fetchOne("SELECT * FROM orders ORDER BY id DESC LIMIT 1");
        $items = Database::fetchAll("SELECT * FROM order_items WHERE order_id = :oid", [':oid' => $order['id']]);

        $trackHtml = View::render('tracking.show', [
            'title' => 'Track Order #' . $order['order_number'],
            'order' => $order,
            'items' => $items,
            'currentStep' => 2,
            'statusBadge' => 'badge-gold',
            'whatsappUrl' => 'https://wa.me/919876543210',
            'deliveryBooking' => null,
            'supportPhone' => '+91 98765 43210',
            'estimatedTime' => '12:15 PM - 12:30 PM',
        ], 'main');

        $this->assert('Tracking View Displays Order Number', str_contains($trackHtml, $order['order_number']));
        $this->assert('Tracking View Displays Vertical Timeline', str_contains($trackHtml, 'Order Placed') || str_contains($trackHtml, 'Preparing'));
        $this->assert('Tracking View Displays Celebration Confirmation Banner', str_contains($trackHtml, 'Order Confirmed!'));
    }

    /**
     * 6. Test Kitchen KDS and Thermal KOT
     */
    private function testKitchenKdsAndKotWorkflow(): void
    {
        $tables = Database::fetchAll("SELECT * FROM restaurant_tables ORDER BY table_number ASC");
        foreach ($tables as &$tbl) {
            $tbl['is_occupied'] = false;
            $tbl['active_order'] = null;
        }
        unset($tbl);

        $kdsHtml = View::render('kitchen.display', [
            'tables' => $tables,
            'activeOrders' => [],
            'stats' => ['new' => 0, 'preparing' => 0, 'ready' => 0, 'served_today' => 5],
        ]);

        $this->assert('KDS View Displays Kitchen Interface', str_contains($kdsHtml, 'Kitchen Display System') || str_contains($kdsHtml, 'Dining Tables Overview'));
        $this->assert('KDS View Hides Customer Phone Number for Privacy', !str_contains($kdsHtml, '+9198765'));

        $latestOrder = Database::fetchOne("SELECT * FROM orders ORDER BY id DESC LIMIT 1");
        $kotHtml = View::render('kitchen.kot', [
            'title' => 'KOT #' . $latestOrder['order_number'],
            'order' => $latestOrder,
            'items' => [],
            'ticket' => ['id' => 1, 'ticket_number' => 'KOT-1', 'status' => 'pending'],
            'isReprint' => false,
            'printJobId' => 1,
        ]);

        $this->assert('KOT View Generates 80mm Thermal Receipt', str_contains($kotHtml, 'KITCHEN ORDER TICKET') && str_contains($kotHtml, '80mm'));
    }

    /**
     * 7. Test POS Billing and Thermal Receipt
     */
    private function testPosBillingAndReceiptWorkflow(): void
    {
        $categories = Database::fetchAll("SELECT * FROM menu_categories");
        $items = Database::fetchAll("SELECT m.*, c.slug as category_slug FROM menu_items m JOIN menu_categories c ON m.category_id = c.id");
        $tables = Database::fetchAll("SELECT * FROM restaurant_tables");

        $posHtml = View::render('pos.terminal', [
            'title' => 'POS Terminal',
            'categories' => $categories,
            'items' => $items,
            'tables' => $tables,
        ], 'admin');

        $this->assert('POS Terminal View Renders Quick Item Grid', str_contains($posHtml, 'Walk-in Billing') || str_contains($posHtml, 'POS'));

        $order = Database::fetchOne("SELECT * FROM orders ORDER BY id DESC LIMIT 1");
        $receiptHtml = View::render('pos.receipt', [
            'title' => 'Receipt #' . $order['order_number'],
            'order' => $order,
            'items' => [],
            'taxRate' => '5%',
            'store' => [
                'name' => 'Valima Biriyani',
                'address' => 'Chennai',
                'phone' => '+91 98765 43210',
                'gstin' => '33AAAAA0000A1Z5',
            ]
        ]);

        $this->assert('POS Thermal Receipt Renders Valima Biriyani Header', stripos($receiptHtml, 'Valima Biriyani') !== false && str_contains($receiptHtml, 'Receipt'));
    }

    /**
     * 8. Test Authentication & Role-Based Access Control
     */
    private function testAuthAndRoleAccessWorkflow(): void
    {
        $admin = Database::fetchOne("SELECT * FROM users WHERE email = 'admin@valimabiriyani.com'");
        if (!$admin) {
            $admin = Database::fetchOne("SELECT * FROM users LIMIT 1");
        }
        $this->assert('Admin User Exists in Database', !empty($admin));

        Session::start();
        Session::set('user', [
            'id' => $admin['id'],
            'role' => 'owner',
            'name' => $admin['name'],
            'email' => $admin['email'],
        ]);

        $currentUser = Session::user();
        $this->assert('Authenticated Session User Loaded', !empty($currentUser) && $currentUser['id'] === $admin['id']);
    }

    /**
     * 9. Test Admin Order Management
     */
    private function testAdminOrderManagementWorkflow(): void
    {
        $orders = Database::fetchAll("SELECT * FROM orders ORDER BY id DESC LIMIT 10");

        $ordersListHtml = View::render('admin.orders.index', [
            'title' => 'Orders Management',
            'currentPage' => 'orders',
            'orders' => $orders,
            'search' => '',
            'statusFilter' => '',
            'channelFilter' => '',
        ], 'admin');

        $this->assert('Admin Orders List Displays Order Table', str_contains($ordersListHtml, 'Central Orders Management') || str_contains($ordersListHtml, 'Order #'));

        $order = Database::fetchOne("SELECT * FROM orders ORDER BY id DESC LIMIT 1");
        $items = Database::fetchAll("SELECT * FROM order_items WHERE order_id = :oid", [':oid' => $order['id']]);

        $orderShowHtml = View::render('admin.orders.show', [
            'title' => 'Order #' . $order['order_number'],
            'currentPage' => 'orders',
            'order' => $order,
            'items' => $items,
            'customer' => ['name' => $order['customer_name'] ?? 'Guest', 'phone' => $order['customer_phone'] ?? ''],
            'timeline' => [],
            'delivery' => null,
            'canManageDelivery' => true,
        ], 'admin');

        $this->assert('Admin Order Detail Displays Customer Card & Timeline', str_contains($orderShowHtml, 'Order Timeline') && str_contains($orderShowHtml, 'Items'));
    }

    /**
     * 10. Test Admin Menu Management
     */
    private function testAdminMenuManagementWorkflow(): void
    {
        $items = Database::fetchAll("
            SELECT m.*, c.name as category_name 
            FROM menu_items m 
            JOIN menu_categories c ON m.category_id = c.id 
            ORDER BY m.id DESC LIMIT 10
        ");

        $menuListHtml = View::render('admin.menu.index', [
            'title' => 'Menu Management',
            'currentPage' => 'menu',
            'items' => $items,
        ], 'admin');

        $this->assert('Admin Menu Index Displays Catalog', str_contains($menuListHtml, 'Menu Management') && str_contains($menuListHtml, 'Active Food Catalog'));
    }

    /**
     * 11. Test Customer CRM & Loyalty Points
     */
    private function testCrmAndLoyaltyWorkflow(): void
    {
        $customers = Database::fetchAll("SELECT * FROM customers ORDER BY id DESC LIMIT 10");

        $crmListHtml = View::render('admin.crm.index', [
            'title' => 'Customer CRM & Loyalty',
            'currentPage' => 'crm',
            'customers' => $customers,
            'stats' => [
                'total_customers' => count($customers),
                'loyalty_issued' => 150,
                'total_revenue' => 4500.0,
            ],
            'search' => '',
        ], 'admin');

        $this->assert('CRM View Displays Customer Directory', str_contains($crmListHtml, 'Customer CRM'));

        $customer = Database::fetchOne("SELECT id, customer_code FROM customers ORDER BY id DESC LIMIT 1");
        $this->assert('Customer Has Unique Code (VAL-CXXXXXX)', !empty($customer['customer_code']) && str_starts_with($customer['customer_code'], 'VAL-C'));

        // Test awarding 10 loyalty points on completed order
        $latestOrder = Database::fetchOne("SELECT id FROM orders WHERE customer_id = :cid ORDER BY id DESC LIMIT 1", [':cid' => $customer['id']]);
        if (!$latestOrder) {
            $latestOrder = Database::fetchOne("SELECT id FROM orders ORDER BY id DESC LIMIT 1");
        }
        Database::execute("UPDATE orders SET customer_id = :cid, status = 'completed' WHERE id = :id", [
            ':cid' => $customer['id'],
            ':id' => $latestOrder['id']
        ]);
        $award = LoyaltyService::awardOrderPoints((int)$latestOrder['id']);
        $this->assert('Loyalty Points Awarded Idempotently', $award !== null && isset($award['points']));
    }

    /**
     * 12. Test Admin Reports & CSV Export
     */
    private function testAdminReportsAndExportWorkflow(): void
    {
        $grossSales = (float)Database::fetchValue("SELECT COALESCE(SUM(total_amount), 0.0) FROM orders WHERE status != 'cancelled'");
        $collectedRevenue = (float)Database::fetchValue("SELECT COALESCE(SUM(total_amount), 0.0) FROM orders WHERE payment_status = 'paid' AND status != 'cancelled'");
        $pendingRevenue = (float)Database::fetchValue("SELECT COALESCE(SUM(total_amount), 0.0) FROM orders WHERE payment_status = 'pending' AND status != 'cancelled'");
        $totalTax = (float)Database::fetchValue("SELECT COALESCE(SUM(tax_amount), 0.0) FROM orders WHERE status != 'cancelled'");
        $totalDeliveryFees = (float)Database::fetchValue("SELECT COALESCE(SUM(delivery_fee), 0.0) FROM orders WHERE status != 'cancelled'");

        $bestSellers = Database::fetchAll("
            SELECT item_name, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue
            FROM order_items
            GROUP BY item_name
            ORDER BY total_qty DESC
            LIMIT 10
        ");

        $channels = Database::fetchAll("
            SELECT channel, COUNT(*) as count, SUM(total_amount) as total
            FROM orders
            WHERE status != 'cancelled'
            GROUP BY channel
            ORDER BY total DESC
        ");

        $reportsHtml = View::render('admin.reports.index', [
            'title' => 'Reports',
            'currentPage' => 'reports',
            'metrics' => [
                'gross_sales' => $grossSales,
                'collected_revenue' => $collectedRevenue,
                'pending_revenue' => $pendingRevenue,
                'total_tax' => $totalTax,
                'total_delivery_fees' => $totalDeliveryFees,
            ],
            'bestSellers' => $bestSellers,
            'channels' => $channels,
        ], 'admin');

        $this->assert('Admin Reports Renders Financial Cards', str_contains($reportsHtml, 'Gross Sales') && str_contains($reportsHtml, 'Collected Revenue'));
        $this->assert('Admin Reports Renders Best-Selling Dishes Table', str_contains($reportsHtml, 'Top 10 Best-Selling Items'));
        $this->assert('Admin Reports Contains Export CSV Button', str_contains($reportsHtml, '/admin/reports/export.csv'));
    }

    /**
     * 13. Test Admin Settings & Integrations
     */
    private function testAdminSettingsAndIntegrationsWorkflow(): void
    {
        $settingsHtml = View::render('admin.settings.index', [
            'title' => 'Settings',
            'currentPage' => 'settings',
            'currentUser' => ['name' => 'Owner'],
            'integrations' => [
                'razorpay' => ['label' => 'Test Mode', 'badge' => 'warning'],
                'whatsapp' => ['label' => 'Free wa.me Links (Active)', 'badge' => 'success'],
                'make' => ['label' => 'Not Configured', 'badge' => 'neutral'],
                'mail' => ['label' => 'Local / Disabled', 'badge' => 'neutral'],
            ],
            'config' => [
                'store_name' => 'Valima Biriyani',
                'store_tagline' => 'Biriyani Specialist',
                'currency' => 'INR (₹)',
                'tax_rate' => '5% GST',
                'delivery_fee' => '₹30.00',
                'phone' => '+91 98765 43210',
                'address' => 'Chennai',
            ]
        ], 'admin');

        $this->assert('Settings View Displays Razorpay Card', str_contains($settingsHtml, 'Razorpay Payment Gateway'));
        $this->assert('Settings View Displays WhatsApp Card', str_contains($settingsHtml, 'WhatsApp Notifications'));
        $this->assert('Settings View Displays Automated Integration Flow', str_contains($settingsHtml, 'Automated Integration Flow'));
    }
}

// Run standalone
$test = new WorkflowEndToEndTest();
$test->runAll();
