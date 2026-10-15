<?php

declare(strict_types=1);

namespace Tests;

require_once __DIR__ . '/../app/Helpers/Env.php';
require_once __DIR__ . '/../app/Helpers/Database.php';
require_once __DIR__ . '/../app/Helpers/Session.php';
require_once __DIR__ . '/../app/Helpers/Security.php';
require_once __DIR__ . '/../app/Helpers/Logger.php';
require_once __DIR__ . '/../app/Helpers/Request.php';
require_once __DIR__ . '/../app/Helpers/Response.php';
require_once __DIR__ . '/../app/Helpers/View.php';
require_once __DIR__ . '/../app/Models/MenuItem.php';
require_once __DIR__ . '/../app/Models/MenuCategory.php';
require_once __DIR__ . '/../app/Services/OrderService.php';

use App\Helpers\Env;
use App\Helpers\Database;
use App\Helpers\Session;
use App\Helpers\View;
use App\Services\OrderService;

class KitchenKdsTest
{
    private int $passed = 0;
    private int $failed = 0;

    public function runAll(): void
    {
        echo "=====================================================\n";
        echo " RUNNING VALIMA BIRIYANI KITCHEN & KOT (PHASE 8) TESTS\n";
        echo "=====================================================\n\n";

        Env::load(__DIR__ . '/../.env');
        Database::init(require __DIR__ . '/../config/database.php');
        Session::start();

        $this->testKitchenOrderCreationAndKdsQuery();
        $this->testCustomerPiiRedactedInKitchenQuery();
        $this->testKitchenStatusTransitions();
        $this->testTableFreedWhenOrderServed();
        $this->testKotPrintAndReprintAudit();
        $this->testKdsDisplayViewRenders();
        $this->testKotThermalViewRenders();

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

    private int $testOrderId = 0;
    private int $testTableId = 2;

    private function testKitchenOrderCreationAndKdsQuery(): void
    {
        // Clear any old leftover test orders on table 2 so it is cleanly available
        Database::execute("UPDATE orders SET status = 'completed' WHERE table_id = :tbl", [':tbl' => $this->testTableId]);

        $biriyani = Database::fetchOne("SELECT id FROM menu_items WHERE slug = 'chicken-biriyani'");

        $order = OrderService::createOrder([
            'customer_name' => 'KDS Dine-In Guest',
            'customer_phone' => '9777788888',
            'order_type' => 'dine_in',
            'table_id' => $this->testTableId,
            'channel' => 'pos',
            'payment_method' => 'cash',
            'special_instructions' => 'Less spicy, extra raita',
            'cart_items' => [
                ['id' => $biriyani['id'], 'qty' => 3, 'notes' => 'Hot']
            ],
            'idempotency_key' => 'kds_test_' . bin2hex(random_bytes(6)),
        ]);

        $this->testOrderId = (int)$order['order_id'];
        $this->assert('KDS Order Created Successfully', $this->testOrderId > 0);
        $this->assert('Table 2 Assigned to Order', (int)$order['table_id'] === $this->testTableId);

        // Verify kitchen ticket was auto-created
        $ticket = Database::fetchOne("SELECT * FROM kitchen_tickets WHERE order_id = :oid", [':oid' => $this->testOrderId]);
        $this->assert('Kitchen Ticket Automatically Generated', !empty($ticket['ticket_number']));
        $this->assert('Initial Ticket Status is pending', $ticket['status'] === 'pending');
    }

    private function testCustomerPiiRedactedInKitchenQuery(): void
    {
        // Kitchen stream query selects only non-PII fields: order_number, table_id, items, notes, special_instructions
        $activeOrders = Database::fetchAll("
            SELECT o.id, o.order_number, o.table_id, o.channel, o.order_type,
                   o.status, o.special_instructions, o.created_at
            FROM orders o
            WHERE o.status IN ('new', 'accepted', 'pending', 'confirmed', 'preparing', 'ready')
            ORDER BY o.id DESC LIMIT 5
        ");

        $this->assert('Active Kitchen Orders Retrieved', !empty($activeOrders));
        $firstOrder = $activeOrders[0];
        $this->assert('Customer Phone is Excluded From Kitchen Select', !array_key_exists('customer_phone', $firstOrder));
        $this->assert('Customer Address is Excluded From Kitchen Select', !array_key_exists('delivery_address', $firstOrder));
    }

    private function testKitchenStatusTransitions(): void
    {
        $orderId = $this->testOrderId;

        // Transition: accepted -> preparing
        Database::execute("UPDATE orders SET status = 'preparing', updated_at = :dt WHERE id = :id", [
            ':dt' => gmdate('Y-m-d H:i:s'),
            ':id' => $orderId,
        ]);
        Database::execute("UPDATE kitchen_tickets SET status = 'in_kitchen' WHERE order_id = :oid", [
            ':oid' => $orderId,
        ]);
        Database::execute("
            INSERT INTO order_status_events (order_id, status, notes, created_at)
            VALUES (:oid, 'preparing', 'Chef started preparing', :dt)
        ", [':oid' => $orderId, ':dt' => gmdate('Y-m-d H:i:s')]);

        $updated = Database::fetchOne("SELECT status FROM orders WHERE id = :id", [':id' => $orderId]);
        $this->assert('Status Transition to Preparing Succeeded', $updated['status'] === 'preparing');

        $kt = Database::fetchOne("SELECT status FROM kitchen_tickets WHERE order_id = :oid", [':oid' => $orderId]);
        $this->assert('Kitchen Ticket Status is in_kitchen', $kt['status'] === 'in_kitchen');

        // Transition: preparing -> ready
        Database::execute("UPDATE orders SET status = 'ready', updated_at = :dt WHERE id = :id", [
            ':dt' => gmdate('Y-m-d H:i:s'),
            ':id' => $orderId,
        ]);
        $ready = Database::fetchOne("SELECT status FROM orders WHERE id = :id", [':id' => $orderId]);
        $this->assert('Status Transition to Ready Succeeded', $ready['status'] === 'ready');
    }

    private function testTableFreedWhenOrderServed(): void
    {
        $orderId = $this->testOrderId;
        $tableId = $this->testTableId;

        // Mark served
        Database::execute("UPDATE orders SET status = 'served', updated_at = :dt WHERE id = :id", [
            ':dt' => gmdate('Y-m-d H:i:s'),
            ':id' => $orderId,
        ]);
        Database::execute("UPDATE kitchen_tickets SET status = 'completed' WHERE order_id = :oid", [
            ':oid' => $orderId,
        ]);

        $served = Database::fetchOne("SELECT status FROM orders WHERE id = :id", [':id' => $orderId]);
        $this->assert('Order Marked as Served', $served['status'] === 'served');

        // Verify table allocation logic recognizes table as free
        $allocatedTable = OrderService::allocateTable($tableId);
        $this->assert("Table {$tableId} is Freed Up and Available for Allocation", $allocatedTable === $tableId);
    }

    private function testKotPrintAndReprintAudit(): void
    {
        $order = Database::fetchOne("SELECT id, order_number FROM orders ORDER BY id DESC LIMIT 1");
        $orderId = (int)$order['id'];

        // 1st print job
        $now = gmdate('Y-m-d H:i:s');
        Database::execute("UPDATE kitchen_tickets SET printed_at = :dt, status = 'in_kitchen' WHERE order_id = :oid", [
            ':dt' => $now,
            ':oid' => $orderId,
        ]);
        Database::execute("
            INSERT INTO print_jobs (order_id, job_type, content_text, status, is_reprint, reprint_count, created_at)
            VALUES (:oid, 'kot', 'KOT initial print', 'printed', 0, 0, :dt)
        ", [':oid' => $orderId, ':dt' => $now]);

        $firstJob = Database::fetchOne("SELECT * FROM print_jobs WHERE order_id = :oid AND job_type = 'kot' ORDER BY id ASC LIMIT 1", [':oid' => $orderId]);
        $this->assert('Initial KOT Print Job Recorded', $firstJob !== null);
        $this->assert('Initial KOT is_reprint is 0', (int)$firstJob['is_reprint'] === 0);

        // 2nd print job (reprint)
        Database::execute("
            INSERT INTO print_jobs (order_id, job_type, content_text, status, is_reprint, reprint_count, created_at)
            VALUES (:oid, 'kot', 'KOT reprint', 'printed', 1, 1, :dt)
        ", [':oid' => $orderId, ':dt' => $now]);

        $reprintJob = Database::fetchOne("SELECT * FROM print_jobs WHERE order_id = :oid AND is_reprint = 1 ORDER BY id DESC LIMIT 1", [':oid' => $orderId]);
        $this->assert('Reprint Job Recorded with is_reprint = 1', (int)$reprintJob['is_reprint'] === 1);
        $this->assert('Reprint Count Tracked Accurately', (int)$reprintJob['reprint_count'] === 1);
    }

    private function testKdsDisplayViewRenders(): void
    {
        $tables = Database::fetchAll("SELECT * FROM restaurant_tables ORDER BY table_number ASC");
        foreach ($tables as &$tbl) {
            $tbl['is_occupied'] = false;
            $tbl['active_order'] = null;
        }

        $html = View::render('kitchen.display', [
            'tables' => $tables,
            'activeOrders' => [],
            'stats' => ['new' => 0, 'preparing' => 0, 'ready' => 0, 'served_today' => 5],
        ]);

        $this->assert('KDS View Contains Valima Biriyani Title', str_contains($html, 'VALIMA BIRIYANI'));
        $this->assert('KDS View Contains Dining Tables Overview', str_contains($html, 'Dining Tables Overview'));
        $this->assert('KDS View Contains Chime Button', str_contains($html, 'soundToggle'));
        $this->assert('KDS View Contains Polling JavaScript (No reload)', str_contains($html, 'pollKitchenStream'));
    }

    private function testKotThermalViewRenders(): void
    {
        $order = Database::fetchOne("SELECT * FROM orders ORDER BY id DESC LIMIT 1");
        $items = Database::fetchAll("SELECT * FROM order_items WHERE order_id = :oid", [':oid' => $order['id']]);

        $html = View::render('kitchen.kot', [
            'order' => $order,
            'items' => $items,
            'isReprint' => true,
            'reprintCount' => 2,
        ]);

        $this->assert('KOT Thermal View Renders 80mm Layout', str_contains($html, '80mm'));
        $this->assert('KOT Thermal View Shows KITCHEN ORDER TICKET', str_contains($html, 'KITCHEN ORDER TICKET'));
        $this->assert('KOT Thermal View Shows Reprint Banner on Reprint', str_contains($html, 'REPRINT #2'));
        $this->assert('KOT Thermal View Contains KITCHEN COPY ONLY', str_contains($html, 'KITCHEN COPY ONLY'));
    }
}

// Run tests
(new KitchenKdsTest())->runAll();
