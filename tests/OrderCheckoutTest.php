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
require_once __DIR__ . '/../app/Models/MenuItem.php';
require_once __DIR__ . '/../app/Services/OrderService.php';

use App\Helpers\Env;
use App\Helpers\Database;
use App\Models\MenuItem;
use App\Services\OrderService;

class OrderCheckoutTest
{
    private int $passed = 0;
    private int $failed = 0;

    public function runAll(): void
    {
        echo "=====================================================\n";
        echo " RUNNING VALIMA BIRIYANI ORDER & CHECKOUT TESTS\n";
        echo "=====================================================\n\n";

        Env::load(__DIR__ . '/../.env');
        Database::init(require __DIR__ . '/../config/database.php');

        $this->testTransactionalTableAllocation();
        $this->testServerSidePriceRecalculation();
        $this->testOutOfStockItemRejection();
        $this->testOrderCreationAndIdempotency();

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

    private function testTransactionalTableAllocation(): void
    {
        // 1. Initial allocation gets table 1
        $t1 = OrderService::allocateTable(1);
        $this->assert('Table 1 Allocated when Empty', $t1 === 1);

        // 2. Simulate table 1 being occupied by an active order
        $trackingToken = \App\Helpers\Security::generateTrackingToken();
        Database::execute("
            INSERT INTO orders (order_number, tracking_token, customer_name, customer_phone, table_id, status, subtotal, total_amount)
            VALUES ('TEST-OCC-1', '{$trackingToken}', 'Busy Customer', '+919999999991', 1, 'preparing', 200, 210)
        ");

        // 3. Requesting table 1 now auto-allocates next available table (table 2)
        $t2 = OrderService::allocateTable(1);
        $this->assert('Table 1 Occupied -> Table 2 Auto-Allocated', $t2 === 2);

        // Clean up test order
        Database::execute("DELETE FROM orders WHERE order_number = 'TEST-OCC-1'");
    }

    private function testServerSidePriceRecalculation(): void
    {
        // Look up Chicken Biriyani (base price 190.00)
        $cb = Database::queryOne("SELECT id, base_price FROM menu_items WHERE slug = 'chicken-biriyani'");
        $this->assert('Chicken Biriyani Found for Calculation', $cb !== null);

        // Client submits manipulated price of 5.00 instead of 190.00
        $clientCart = [
            ['id' => $cb['id'], 'qty' => 2, 'price' => 5.00] // Manipulated client price
        ];

        // Server recalculation
        $calc = OrderService::calculateCart($clientCart, 'dine_in');

        // Subtotal must be 2 * 190.00 = 380.00, ignoring the 5.00
        $this->assert('Server Recomputes Subtotal Using DB Price (380.00)', (float)$calc['subtotal'] === 380.00);
        $this->assert('Server Adds 5% Tax (19.00)', (float)$calc['tax_amount'] === 19.00);
        $this->assert('Server Computes Final Total (399.00)', (float)$calc['total_amount'] === 399.00);

        // For delivery order, ₹40 delivery fee added
        $delivCalc = OrderService::calculateCart($clientCart, 'delivery');
        $this->assert('Delivery Order Adds ₹40 Delivery Fee', (float)$delivCalc['delivery_fee'] === 40.00);
        $this->assert('Delivery Total Includes Fee (439.00)', (float)$delivCalc['total_amount'] === 439.00);
    }

    private function testOutOfStockItemRejection(): void
    {
        $item = Database::queryOne("SELECT id FROM menu_items WHERE slug = 'veg-noodles'");
        $itemId = (int)$item['id'];

        // Mark out of stock
        Database::execute("UPDATE menu_items SET is_available = 0 WHERE id = :id", [':id' => $itemId]);

        $rejected = false;
        try {
            OrderService::calculateCart([['id' => $itemId, 'qty' => 1]]);
        } catch (\RuntimeException $e) {
            $rejected = str_contains($e->getMessage(), 'out of stock');
        }

        $this->assert('Out of Stock Item Rejected at Checkout', $rejected);

        // Restore availability
        Database::execute("UPDATE menu_items SET is_available = 1 WHERE id = :id", [':id' => $itemId]);
    }

    private function testOrderCreationAndIdempotency(): void
    {
        $cb = Database::queryOne("SELECT id FROM menu_items WHERE slug = 'chicken-biriyani'");
        $idempKey = 'idemp_test_' . bin2hex(random_bytes(8));

        $orderParams = [
            'customer_name' => 'Karthik Raja',
            'customer_phone' => '9840123456',
            'table_id' => 3,
            'channel' => 'website',
            'order_type' => 'dine_in',
            'payment_method' => 'cash',
            'cart_items' => [
                ['id' => $cb['id'], 'qty' => 1]
            ],
            'idempotency_key' => $idempKey,
        ];

        // First attempt creates order
        $res1 = OrderService::createOrder($orderParams);
        $this->assert('Order 1 Created', !empty($res1['order_number']));
        $this->assert('Order Has Secure 32-Char Tracking Token', strlen($res1['tracking_token']) === 32);

        // Check customer normalized phone in DB
        $cust = Database::queryOne("SELECT phone FROM customers WHERE id = (SELECT customer_id FROM orders WHERE id = :oid)", [':oid' => $res1['order_id']]);
        $this->assert('Customer Phone Normalized to E.164 (+91)', $cust['phone'] === '+919840123456');

        // Check KOT ticket generated
        $kot = Database::queryOne("SELECT ticket_number FROM kitchen_tickets WHERE order_id = :oid", [':oid' => $res1['order_id']]);
        $this->assert('Kitchen Ticket Created Automatically', $kot !== null);

        // Second attempt with SAME idempotency key (simulating double click)
        $res2 = OrderService::createOrder($orderParams);
        $this->assert('Idempotent Call Returns Same Order Number', $res1['order_number'] === $res2['order_number']);

        // Verify only 1 order exists in the DB for this idempotency key
        $count = (int)Database::queryOne("SELECT COUNT(*) as c FROM orders WHERE idempotency_key = :k", [':k' => $idempKey])['c'];
        $this->assert('Database Contains Exactly 1 Order (No Duplicates)', $count === 1);
    }
}

// Run test
$test = new OrderCheckoutTest();
$test->runAll();
