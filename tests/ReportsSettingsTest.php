<?php

declare(strict_types=1);

namespace Tests;

require_once __DIR__ . '/../app/Helpers/Env.php';
require_once __DIR__ . '/../app/Helpers/Logger.php';
require_once __DIR__ . '/../app/Helpers/Database.php';
require_once __DIR__ . '/../app/Helpers/Security.php';
require_once __DIR__ . '/../app/Helpers/Session.php';
require_once __DIR__ . '/../app/Helpers/View.php';
require_once __DIR__ . '/../app/Helpers/Icon.php';
require_once __DIR__ . '/../app/Helpers/Component.php';
require_once __DIR__ . '/../app/Controllers/BaseController.php';
require_once __DIR__ . '/../app/Controllers/ReportController.php';
require_once __DIR__ . '/../app/Controllers/SettingsController.php';
require_once __DIR__ . '/../app/Services/WebhookService.php';

use App\Helpers\Env;
use App\Helpers\Database;
use App\Helpers\Icon;
use App\Helpers\Component;
use App\Helpers\View;
use App\Services\WebhookService;

class ReportsSettingsTest
{
    private int $passed = 0;
    private int $failed = 0;

    public function runAll(): void
    {
        echo "=====================================================\n";
        echo " RUNNING VALIMA BIRIYANI REPORTS & SETTINGS TESTS\n";
        echo "=====================================================\n\n";

        Env::load(__DIR__ . '/../.env');
        Database::init(require __DIR__ . '/../config/database.php');

        $this->testIconRendering();
        $this->testComponentRendering();
        $this->testReportMetricsCalculation();
        $this->testBestSellersQuery();
        $this->testChannelBreakdown();
        $this->testWebhookServiceAudit();
        $this->testSettingsAndIntegrationsView();

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

    private function testIconRendering(): void
    {
        $icon = Icon::render('shopping-bag', 'w-6 h-6');
        $this->assert('Icon Renders SVG Element', str_contains($icon, '<svg') && str_contains($icon, '</svg>'));
        $this->assert('Icon Injects Class Attribute', str_contains($icon, 'class="w-6 h-6"'));

        $barChart = Icon::render('bar-chart');
        $this->assert('Bar Chart Icon Renders', str_contains($barChart, '<line x1="12"'));

        $settings = Icon::render('settings');
        $this->assert('Settings Icon Renders', str_contains($settings, '<path d="M12.22'));
    }

    private function testComponentRendering(): void
    {
        $badge = Component::render('badge', ['text' => 'Delivered', 'variant' => 'success']);
        $this->assert('Badge Component Renders Text', str_contains($badge, 'Delivered'));
        $this->assert('Badge Component Renders Success Style', str_contains($badge, 'badge-green'));

        $statCard = Component::render('stat_card', [
            'title' => 'Total Orders',
            'value' => '48',
            'change' => '+12%',
            'changeType' => 'positive',
        ]);
        $this->assert('Stat Card Renders Title', str_contains($statCard, 'Total Orders'));
        $this->assert('Stat Card Renders Value', str_contains($statCard, '48'));
    }

    private function testReportMetricsCalculation(): void
    {
        $grossSales = Database::fetchValue("SELECT COALESCE(SUM(total_amount), 0.0) FROM orders WHERE status != 'cancelled'");
        $this->assert('Gross Sales Query Executes', is_numeric($grossSales));

        $tax = Database::fetchValue("SELECT COALESCE(SUM(tax_amount), 0.0) FROM orders WHERE status != 'cancelled'");
        $this->assert('GST Tax Query Executes', is_numeric($tax));

        $deliveryFees = Database::fetchValue("SELECT COALESCE(SUM(delivery_fee), 0.0) FROM orders WHERE status != 'cancelled'");
        $this->assert('Delivery Fees Query Executes', is_numeric($deliveryFees));
    }

    private function testBestSellersQuery(): void
    {
        $bestSellers = Database::fetchAll("
            SELECT item_name, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue
            FROM order_items
            GROUP BY item_name
            ORDER BY total_qty DESC
            LIMIT 10
        ");
        $this->assert('Best Sellers Query Returns Array', is_array($bestSellers));
    }

    private function testChannelBreakdown(): void
    {
        $channels = Database::fetchAll("
            SELECT channel, COUNT(*) as count, SUM(total_amount) as total
            FROM orders
            WHERE status != 'cancelled'
            GROUP BY channel
            ORDER BY total DESC
        ");
        $this->assert('Channels Breakdown Returns Array', is_array($channels));
    }

    private function testWebhookServiceAudit(): void
    {
        $dispatchResult = WebhookService::dispatch('order.test', [
            'test_key' => 'test_value',
            'timestamp' => time(),
        ]);
        $this->assert('Webhook Service Returns Event ID', !empty($dispatchResult['event_id']));

        $logged = Database::fetchOne("
            SELECT * FROM webhook_events WHERE event_id = :eid
        ", [':eid' => $dispatchResult['event_id']]);

        $this->assert('Webhook Event Recorded in Database Table', !empty($logged) && $logged['event_type'] === 'order.test');
    }

    private function testSettingsAndIntegrationsView(): void
    {
        $html = View::render('admin.settings.index', [
            'title' => 'Settings Test',
            'currentPage' => 'settings',
            'currentUser' => ['name' => 'Owner Admin', 'role' => 'owner'],
            'integrations' => [
                'razorpay' => ['status' => 'test_mode', 'label' => 'Test Mode', 'badge' => 'warning'],
                'whatsapp' => ['status' => 'free_active', 'label' => 'Free wa.me Links (Active)', 'badge' => 'success'],
                'make' => ['status' => 'not_configured', 'label' => 'Not Configured', 'badge' => 'neutral'],
                'mail' => ['status' => 'not_configured', 'label' => 'Local / Disabled', 'badge' => 'neutral'],
            ],
            'config' => [
                'store_name' => 'Valima Biriyani',
                'store_tagline' => 'Biriyani Specialist',
                'currency' => 'INR (₹)',
                'tax_rate' => '5% GST',
                'delivery_fee' => '₹30.00 (Free over ₹500)',
                'phone' => '+91 98765 43210',
                'address' => 'No. 12, 2nd Street, Anna Nagar, Chennai - 600040',
            ]
        ], 'admin');

        $this->assert('Settings View Contains Valima Biriyani Title', str_contains($html, 'Valima Biriyani'));
        $this->assert('Settings View Contains Razorpay Card', str_contains($html, 'Razorpay Payment Gateway'));
        $this->assert('Settings View Contains WhatsApp Integration Card', str_contains($html, 'WhatsApp Notifications'));
        $this->assert('Settings View Contains Make.com Card', str_contains($html, 'Make.com Webhooks'));
        $this->assert('Settings View Contains Integration Flow', str_contains($html, 'Automated Integration Flow'));
    }
}

// Execute if run standalone
$test = new ReportsSettingsTest();
$test->runAll();
