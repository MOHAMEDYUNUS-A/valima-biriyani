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
require_once __DIR__ . '/../app/Services/LoyaltyService.php';
require_once __DIR__ . '/../app/Controllers/BaseController.php';
require_once __DIR__ . '/../app/Controllers/CrmController.php';

use App\Helpers\Env;
use App\Helpers\Database;
use App\Helpers\Session;
use App\Helpers\View;
use App\Services\OrderService;
use App\Services\LoyaltyService;

class CrmLoyaltyTest
{
    private int $passed = 0;
    private int $failed = 0;

    public function runAll(): void
    {
        echo "=====================================================\n";
        echo " RUNNING VALIMA BIRIYANI CRM & LOYALTY TESTS\n";
        echo "=====================================================\n\n";

        Env::load(__DIR__ . '/../.env');
        Database::init(require __DIR__ . '/../config/database.php');
        Session::start();

        $this->testCustomerProfileCreation();
        $this->testLoyaltyAwardOnCompletedOrder();
        $this->testLoyaltyAwardIdempotency();
        $this->testIneligibleStatusDoesNotAward();
        $this->testRedemptionThresholdValidation();
        $this->testSuccessfulRedemption();
        $this->testLoyaltyReversalOnRefund();
        $this->testAdminCrmViewsRender();

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

    private int $testCustomerId = 0;
    private int $testOrderId = 0;
    private string $testPhone = '';

    private function testCustomerProfileCreation(): void
    {
        $this->testPhone = '9' . str_pad((string)rand(100000000, 999999999), 9, '0');
        $biriyani = Database::fetchOne("SELECT id FROM menu_items WHERE slug = 'chicken-biriyani'");

        $order = OrderService::createOrder([
            'customer_name' => 'Biriyani Loyalist',
            'customer_phone' => $this->testPhone,
            'order_type' => 'dine_in',
            'table_id' => 5,
            'channel' => 'website',
            'cart_items' => [['id' => $biriyani['id'], 'qty' => 2]],
            'idempotency_key' => 'crm_test_' . bin2hex(random_bytes(6)),
        ]);

        $this->testOrderId = (int)$order['order_id'];

        $customer = Database::fetchOne("SELECT * FROM customers WHERE phone = :p", [':p' => '+91' . $this->testPhone]);
        $this->assert('Customer Profile Created Automatically', !empty($customer));
        $this->assert('Customer Code Has Prefix VAL-C', str_starts_with($customer['customer_code'], 'VAL-C'));
        $this->assert('Customer Phone is Stored Normalized', $customer['phone'] === '+91' . $this->testPhone);

        $this->testCustomerId = (int)$customer['id'];
    }

    private function testLoyaltyAwardOnCompletedOrder(): void
    {
        // Advance order to completed
        Database::execute("UPDATE orders SET status = 'completed' WHERE id = :id", [':id' => $this->testOrderId]);

        $result = LoyaltyService::awardOrderPoints($this->testOrderId);
        $this->assert('Loyalty Award Function Executed', $result !== null);
        $this->assert('Award Status is True', $result['awarded'] === true);
        $this->assert('10 Points Awarded for Eligible Order', $result['points'] === 10);

        // Verify balance in customer record
        $customer = Database::fetchOne("SELECT loyalty_points, lifetime_spend, total_orders FROM customers WHERE id = :cid", [':cid' => $this->testCustomerId]);
        $this->assert('Customer Balance Increased to 10 Points', (int)$customer['loyalty_points'] === 10);
        $this->assert('Lifetime Spend Updated with Order Total', (float)$customer['lifetime_spend'] > 0);
        $this->assert('Total Orders Incremented to 1', (int)$customer['total_orders'] === 1);

        // Verify transaction logged in immutable ledger
        $tx = Database::fetchOne("
            SELECT * FROM loyalty_transactions WHERE customer_id = :cid AND order_id = :oid AND type = 'earn'
        ", [':cid' => $this->testCustomerId, ':oid' => $this->testOrderId]);
        $this->assert('Loyalty Transaction Recorded in Immutable Ledger', $tx !== null);
        $this->assert('Ledger Stamped balance_after = 10', (int)$tx['balance_after'] === 10);
    }

    private function testLoyaltyAwardIdempotency(): void
    {
        // Try awarding points a second time for the exact same order
        $secondAttempt = LoyaltyService::awardOrderPoints($this->testOrderId);

        $this->assert('Second Award Attempt Blocked (idempotent)', $secondAttempt['awarded'] === false);
        $this->assert('Reason is Already awarded', $secondAttempt['reason'] === 'Already awarded');

        // Verify customer points did not double
        $customer = Database::fetchOne("SELECT loyalty_points FROM customers WHERE id = :cid", [':cid' => $this->testCustomerId]);
        $this->assert('Customer Balance Remains Exactly 10 (No Duplicate Points)', (int)$customer['loyalty_points'] === 10);
    }

    private function testIneligibleStatusDoesNotAward(): void
    {
        // Create an uncompleted order in 'pending' status
        $biriyani = Database::fetchOne("SELECT id FROM menu_items WHERE slug = 'chicken-biriyani'");
        $pendingOrder = OrderService::createOrder([
            'customer_name' => 'Pending Guest',
            'customer_phone' => '9444433333',
            'order_type' => 'takeaway',
            'channel' => 'website',
            'cart_items' => [['id' => $biriyani['id'], 'qty' => 1]],
            'idempotency_key' => 'pending_award_' . bin2hex(random_bytes(6)),
        ]);

        Database::execute("UPDATE orders SET status = 'pending' WHERE id = :id", [':id' => $pendingOrder['order_id']]);

        $res = LoyaltyService::awardOrderPoints((int)$pendingOrder['order_id']);
        $this->assert('Pending Order Rebuffed from Earning Points', $res === null);
    }

    private function testRedemptionThresholdValidation(): void
    {
        // Customer only has 10 points; minimum required is 50
        $threwException = false;
        try {
            LoyaltyService::redeemPoints($this->testCustomerId, 20);
        } catch (\RuntimeException $e) {
            $threwException = true;
        }

        $this->assert('Redemption Rejected Below Minimum Threshold (50 pts)', $threwException);
    }

    private function testSuccessfulRedemption(): void
    {
        // Grant bonus points so customer has 80 points
        Database::execute("UPDATE customers SET loyalty_points = 80 WHERE id = :cid", [':cid' => $this->testCustomerId]);

        $redemption = LoyaltyService::redeemPoints($this->testCustomerId, 50, $this->testOrderId);
        $this->assert('Redemption Succeeded for 50 Points', $redemption['success'] === true);
        $this->assert('Discount Value is ₹50.00', (float)$redemption['discount_value'] === 50.00);
        $this->assert('Customer Balance After is 30 Points', (int)$redemption['balance_after'] === 30);

        // Check redemption entry in ledger
        $tx = Database::fetchOne("
            SELECT * FROM loyalty_transactions WHERE customer_id = :cid AND type = 'redeem' ORDER BY id DESC LIMIT 1
        ", [':cid' => $this->testCustomerId]);
        $this->assert('Redeem Ledger Entry Recorded with Negative Points (-50)', (int)$tx['points'] === -50);
    }

    private function testLoyaltyReversalOnRefund(): void
    {
        // Simulate cancelling order #testOrderId
        $reversed = LoyaltyService::reverseOrderPoints($this->testOrderId, 'Customer requested refund');
        $this->assert('Loyalty Points Reversal Succeeded', $reversed === true);

        // Check ledger
        $revTx = Database::fetchOne("
            SELECT * FROM loyalty_transactions WHERE order_id = :oid AND type = 'reverse'
        ", [':oid' => $this->testOrderId]);
        $this->assert('Reverse Transaction Logged in Ledger', $revTx !== null);
        $this->assert('Points Deducted on Reversal (-10)', (int)$revTx['points'] === -10);

        // Test idempotency of reversal (should not reverse twice)
        $secondRev = LoyaltyService::reverseOrderPoints($this->testOrderId);
        $this->assert('Duplicate Reversal Prevented', $secondRev === false);
    }

    private function testAdminCrmViewsRender(): void
    {
        $customers = Database::fetchAll("SELECT * FROM customers ORDER BY id DESC LIMIT 5");
        $htmlList = View::render('admin.crm.index', [
            'customers' => $customers,
            'search' => '',
            'stats' => ['total_customers' => 10, 'loyalty_issued' => 250, 'total_revenue' => 4500.0],
        ], 'admin');

        $this->assert('Admin CRM Index View Contains Customer CRM Header', str_contains($htmlList, 'Customer CRM &amp; Loyalty'));
        $this->assert('Admin CRM Index Shows Customer Code Column', str_contains($htmlList, 'VAL-C'));

        $customer = $customers[0];
        $htmlShow = View::render('admin.crm.show', [
            'customer' => $customer,
            'orders' => [],
            'ledger' => [],
        ], 'admin');

        $this->assert('Admin CRM Show View Displays Customer Summary', str_contains($htmlShow, 'Customer Summary'));
        $this->assert('Admin CRM Show View Includes Points Adjustment Form', str_contains($htmlShow, 'Adjust Loyalty Points'));
    }
}

// Run test suite
(new CrmLoyaltyTest())->runAll();
