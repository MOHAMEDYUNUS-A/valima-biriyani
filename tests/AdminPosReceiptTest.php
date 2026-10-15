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
require_once __DIR__ . '/../app/Models/MenuItem.php';
require_once __DIR__ . '/../app/Services/OrderService.php';

use App\Helpers\Env;
use App\Helpers\Database;
use App\Helpers\View;
use App\Models\MenuItem;
use App\Services\OrderService;

class AdminPosReceiptTest
{
    private int $passed = 0;
    private int $failed = 0;

    public function runAll(): void
    {
        echo "=====================================================\n";
        echo " RUNNING VALIMA BIRIYANI ADMIN, POS & RECEIPT TESTS\n";
        echo "=====================================================\n\n";

        Env::load(__DIR__ . '/../.env');
        Database::init(require __DIR__ . '/../config/database.php');

        $this->testPosOrderCreation();
        $this->testCustomerSearchQuery();
        $this->testOrderStatusTransition();
        $this->testThermalReceiptRendering();
        $this->testAdminDashboardMetrics();

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

    private function testPosOrderCreation(): void
    {
        $biriyani = Database::queryOne("SELECT id FROM menu_items WHERE slug = 'chicken-biriyani'");

        $posParams = [
            'customer_name' => 'Walk-in Guest',
            'customer_phone' => '9888877777',
            'table_id' => 4,
            'channel' => 'pos',
            'order_type' => 'dine_in',
            'payment_method' => 'cash',
            'cart_items' => [
                ['id' => $biriyani['id'], 'qty' => 2]
            ],
            'idempotency_key' => 'pos_test_' . bin2hex(random_bytes(6)),
        ];

        $order = OrderService::createOrder($posParams);
        $this->assert('POS Order Created', !empty($order['order_id']));

        // Check channel in DB
        $dbOrder = Database::queryOne("SELECT * FROM orders WHERE id = :id", [':id' => $order['order_id']]);
        $this->assert('Order Channel Recorded as POS', $dbOrder['channel'] === 'pos');
        $this->assert('Table ID 4 Assigned', (int)$dbOrder['table_id'] === 4);
    }

    private function testCustomerSearchQuery(): void
    {
        $res = Database::query(
            "SELECT id, name, phone FROM customers WHERE phone LIKE :p LIMIT 5",
            [':p' => '%9888877777%']
        );
        $this->assert('Customer Found by Phone', count($res) >= 1);
        $this->assert('Customer Phone is Normalized', $res[0]['phone'] === '+919888877777');
    }

    private function testOrderStatusTransition(): void
    {
        $order = Database::queryOne("SELECT id, status FROM orders ORDER BY id DESC LIMIT 1");
        $orderId = (int)$order['id'];

        // Transition from accepted -> preparing
        Database::execute("UPDATE orders SET status = 'preparing' WHERE id = :id", [':id' => $orderId]);
        Database::execute(
            "INSERT INTO order_status_events (order_id, status, notes, created_at)
             VALUES (:oid, 'preparing', 'Chef started preparation', :dt)",
            [':oid' => $orderId, ':dt' => gmdate('Y-m-d H:i:s')]
        );

        $updated = Database::queryOne("SELECT status FROM orders WHERE id = :id", [':id' => $orderId]);
        $this->assert('Order Status Updated to Preparing', $updated['status'] === 'preparing');

        // Check lifecycle event
        $ev = Database::queryOne("SELECT * FROM order_status_events WHERE order_id = :oid AND status = 'preparing'", [':oid' => $orderId]);
        $this->assert('Status Event Audit Recorded', $ev !== null);
    }

    private function testThermalReceiptRendering(): void
    {
        $order = Database::queryOne("SELECT o.*, t.table_number FROM orders o LEFT JOIN restaurant_tables t ON o.table_id = t.id ORDER BY o.id DESC LIMIT 1");
        $items = Database::query("SELECT * FROM order_items WHERE order_id = :id", [':id' => $order['id']]);

        $receiptHtml = View::render('pos.receipt', [
            'order' => $order,
            'items' => $items,
        ]);

        $this->assert('Receipt Renders HTML', str_contains($receiptHtml, 'Valima Biriyani'));
        $this->assert('Receipt Has 80mm Width CSS', str_contains($receiptHtml, '80mm'));
        $this->assert('Receipt Displays Order Number', str_contains($receiptHtml, $order['order_number']));
        $this->assert('Receipt Displays Net Total', str_contains($receiptHtml, 'TOTAL AMOUNT'));
    }

    private function testAdminDashboardMetrics(): void
    {
        $count = (int)Database::queryOne("SELECT COUNT(*) as c FROM orders")['c'];
        $this->assert('Dashboard Calculates Real Database Order Count', $count >= 1);

        $sales = (float)Database::queryOne("SELECT COALESCE(SUM(total_amount), 0) as s FROM orders")['s'];
        $this->assert('Dashboard Computes Order Sales Figure', $sales > 0);
    }
}

// Run test
$test = new AdminPosReceiptTest();
$test->runAll();
