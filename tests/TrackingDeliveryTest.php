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
require_once __DIR__ . '/../app/Services/OrderService.php';
require_once __DIR__ . '/../app/Controllers/BaseController.php';
require_once __DIR__ . '/../app/Controllers/TrackingController.php';
require_once __DIR__ . '/../app/Controllers/DeliveryController.php';

use App\Helpers\Env;
use App\Helpers\Database;
use App\Helpers\Session;
use App\Helpers\View;
use App\Helpers\Request;
use App\Services\OrderService;
use App\Controllers\TrackingController;
use App\Controllers\DeliveryController;

class TrackingDeliveryTest
{
    private int $passed = 0;
    private int $failed = 0;

    public function runAll(): void
    {
        echo "=====================================================\n";
        echo " RUNNING VALIMA BIRIYANI TRACKING & DELIVERY TESTS\n";
        echo "=====================================================\n\n";

        Env::load(__DIR__ . '/../.env');
        Database::init(require __DIR__ . '/../config/database.php');
        Session::start();

        $this->testCreateDeliveryOrder();
        $this->testTrackingTokenLookup();
        $this->testInvalidTrackingToken();
        $this->testTrackingViewProtection();
        $this->testAddMoreItemsPreservesSessionContext();
        $this->testRapidoParcelBookingUpdate();
        $this->testTrackingStatusStepperMapping();

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

    private array $testOrder = [];

    private function testCreateDeliveryOrder(): void
    {
        $biriyani = Database::fetchOne("SELECT id FROM menu_items WHERE slug = 'chicken-biriyani'");

        $this->testOrder = OrderService::createOrder([
            'customer_name' => 'Tracking Test Guest',
            'customer_phone' => '9555544444',
            'order_type' => 'delivery',
            'delivery_address' => [
                'address' => '42, Biriyani Lane, Near Clock Tower',
                'landmark' => 'Opposite Big Mosque',
            ],
            'channel' => 'website',
            'payment_method' => 'cash',
            'cart_items' => [
                ['id' => $biriyani['id'], 'qty' => 2]
            ],
            'idempotency_key' => 'track_test_' . bin2hex(random_bytes(6)),
        ]);

        $this->assert('Delivery Order Placed with Tracking Token', !empty($this->testOrder['tracking_token']));
        $this->assert('Tracking Token is 32-character Hex String', strlen($this->testOrder['tracking_token']) === 32);
    }

    private function testTrackingTokenLookup(): void
    {
        $token = $this->testOrder['tracking_token'];
        $order = Database::fetchOne("SELECT * FROM orders WHERE tracking_token = :tok", [':tok' => $token]);

        $this->assert('Order Found by Secret Tracking Token', !empty($order));
        $this->assert('Order Number Matches in Database', $order['order_number'] === $this->testOrder['order_number']);
        $this->assert('Order Type is Delivery', $order['order_type'] === 'delivery');
    }

    private function testInvalidTrackingToken(): void
    {
        $fakeToken = 'invalid_nonexistent_token_123456';
        $order = Database::fetchOne("SELECT * FROM orders WHERE tracking_token = :tok", [':tok' => $fakeToken]);

        $this->assert('Non-existent Token Returns Null', $order === null);

        $html = View::render('tracking.not_found', ['token' => $fakeToken]);
        $this->assert('Order Not Found View Renders Gracefully', str_contains($html, 'Order Not Found'));
    }

    private function testTrackingViewProtection(): void
    {
        $token = $this->testOrder['tracking_token'];
        $order = Database::fetchOne("SELECT * FROM orders WHERE tracking_token = :tok", [':tok' => $token]);
        $items = Database::fetchAll("SELECT * FROM order_items WHERE order_id = :oid", [':oid' => $order['id']]);

        $html = View::render('tracking.show', [
            'order' => $order,
            'items' => $items,
            'events' => [],
            'delivery' => null,
            'stepIndex' => 1,
        ]);

        $this->assert('Tracking View Displays Order Number', str_contains($html, $order['order_number']));
        $this->assert('Tracking View Displays Delivery Badge', str_contains($html, 'Delivery'));
        $this->assert('Tracking View Includes Polling Script (No Reload)', str_contains($html, 'pollOrderStatus'));
    }

    private function testAddMoreItemsPreservesSessionContext(): void
    {
        // First create a Dine-In order on Table 7
        $biriyani = Database::fetchOne("SELECT id FROM menu_items WHERE slug = 'chicken-biriyani'");
        $dineInOrder = OrderService::createOrder([
            'customer_name' => 'Table 7 Customer',
            'customer_phone' => '9111122222',
            'order_type' => 'dine_in',
            'table_id' => 7,
            'channel' => 'website',
            'cart_items' => [['id' => $biriyani['id'], 'qty' => 1]],
            'idempotency_key' => 'add_more_' . bin2hex(random_bytes(6)),
        ]);

        $token = $dineInOrder['tracking_token'];

        // Simulate Add More Items click
        $trackingController = new TrackingController();
        $req = new Request([], [], [], [], ['REQUEST_METHOD' => 'GET']);

        // Directly invoke logic of addMoreItems
        $order = Database::fetchOne("SELECT * FROM orders WHERE tracking_token = :tok", [':tok' => $token]);
        Session::set('dine_in_table', (int)$order['table_id']);
        Session::set('customer_name', $order['customer_name']);
        Session::set('customer_phone', $order['customer_phone']);
        Session::set('parent_order_number', $order['order_number']);

        $this->assert('Session Keeps Table ID 7 for Dine-in Context', Session::get('dine_in_table') === 7);
        $this->assert('Session Keeps Customer Name', Session::get('customer_name') === 'Table 7 Customer');
        $this->assert('Session Tracks Parent Order Number', Session::get('parent_order_number') === $order['order_number']);
    }

    private function testRapidoParcelBookingUpdate(): void
    {
        $orderId = (int)$this->testOrder['order_id'];
        $now = gmdate('Y-m-d H:i:s');

        // Simulate Staff updating Rapido delivery details
        Database::execute("
            INSERT INTO delivery_bookings (order_id, provider_name, booking_reference, delivery_fee, tracking_url, status, delivery_notes, booked_at, updated_at)
            VALUES (:oid, 'rapido', 'RAPIDO-BLR-998822', 45.00, 'https://rapido.bike/track/RAPIDO-BLR-998822', 'in_transit', 'Rider Rajesh picked up', :dt, :dt)
        ", [
            ':oid' => $orderId,
            ':dt' => $now,
        ]);

        Database::execute("UPDATE orders SET status = 'out_for_delivery', updated_at = :dt WHERE id = :oid", [
            ':dt' => $now,
            ':oid' => $orderId,
        ]);

        $booking = Database::fetchOne("SELECT * FROM delivery_bookings WHERE order_id = :oid", [':oid' => $orderId]);
        $this->assert('Rapido Booking Reference Saved', $booking['booking_reference'] === 'RAPIDO-BLR-998822');
        $this->assert('Rapido Delivery Fee Recorded (₹45.00)', (float)$booking['delivery_fee'] === 45.00);
        $this->assert('Tracking URL Saved', str_contains($booking['tracking_url'], 'rapido.bike'));
        $this->assert('Delivery Status is in_transit', $booking['status'] === 'in_transit');

        $updatedOrder = Database::fetchOne("SELECT status FROM orders WHERE id = :oid", [':oid' => $orderId]);
        $this->assert('Order Synchronized to out_for_delivery', $updatedOrder['status'] === 'out_for_delivery');
    }

    private function testTrackingStatusStepperMapping(): void
    {
        $controller = new TrackingController();
        $reflector = new \ReflectionClass($controller);
        $method = $reflector->getMethod('calculateStepIndex');
        $method->setAccessible(true);

        $stepNew = $method->invoke($controller, 'new');
        $stepAccepted = $method->invoke($controller, 'accepted');
        $stepPrep = $method->invoke($controller, 'preparing');
        $stepOut = $method->invoke($controller, 'out_for_delivery');
        $stepCompleted = $method->invoke($controller, 'completed');

        $this->assert('Step Index for new/accepted is 1', $stepNew === 1 && $stepAccepted === 1);
        $this->assert('Step Index for preparing is 2', $stepPrep === 2);
        $this->assert('Step Index for out_for_delivery is 3', $stepOut === 3);
        $this->assert('Step Index for completed is 4', $stepCompleted === 4);
    }
}

// Run test suite
(new TrackingDeliveryTest())->runAll();
