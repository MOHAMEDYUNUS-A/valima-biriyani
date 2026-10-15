<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Database;
use App\Helpers\Request;
use App\Helpers\Response;
use App\Helpers\Session;
use App\Helpers\Logger;

class CrmController extends BaseController
{
    /**
     * Customer CRM list view with search and metrics.
     */
    public function index(Request $request): void
    {
        $this->requireRoles(['owner', 'manager']);

        $search = trim((string)$request->input('q', ''));
        $sql = "SELECT * FROM customers WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (name LIKE :q1 OR phone LIKE :q2 OR customer_code LIKE :q3)";
            $params[':q1'] = "%{$search}%";
            $params[':q2'] = "%{$search}%";
            $params[':q3'] = "%{$search}%";
        }

        $sql .= " ORDER BY id DESC LIMIT 100";
        $customers = Database::fetchAll($sql, $params);

        // Overall CRM stats
        $totalCustomers = (int)Database::fetchValue("SELECT COUNT(*) FROM customers");
        $totalLoyaltyIssued = (int)Database::fetchValue("SELECT COALESCE(SUM(points), 0) FROM loyalty_transactions WHERE type = 'earn'");
        $totalLifetimeRevenue = (float)Database::fetchValue("SELECT COALESCE(SUM(lifetime_spend), 0.0) FROM customers");

        $this->render('admin.crm.index', [
            'title' => 'Customer CRM & Loyalty',
            'currentPage' => 'crm',
            'customers' => $customers,
            'search' => $search,
            'stats' => [
                'total_customers' => $totalCustomers,
                'loyalty_issued' => $totalLoyaltyIssued,
                'total_revenue' => $totalLifetimeRevenue,
            ],
        ], 'admin');
    }

    /**
     * Customer detail view: order history, addresses, loyalty ledger.
     */
    public function show(Request $request, string $id): void
    {
        $this->requireRoles(['owner', 'manager']);

        $cid = (int)$id;
        $customer = Database::fetchOne("SELECT * FROM customers WHERE id = :id", [':id' => $cid]);
        if (!$customer) {
            Session::flash('error', 'Customer record not found.');
            $this->redirect('/admin/crm');
            return;
        }

        // Order history
        $orders = Database::fetchAll("
            SELECT id, order_number, order_type, channel, status, total_amount, created_at
            FROM orders
            WHERE customer_id = :cid
            ORDER BY id DESC
        ", [':cid' => $cid]);

        // Loyalty ledger
        $ledger = Database::fetchAll("
            SELECT id, order_id, type, points, balance_after, description, created_at
            FROM loyalty_transactions
            WHERE customer_id = :cid
            ORDER BY id DESC
        ", [':cid' => $cid]);

        $this->render('admin.crm.show', [
            'title' => "Customer Profile — {$customer['name']}",
            'currentPage' => 'crm',
            'customer' => $customer,
            'orders' => $orders,
            'ledger' => $ledger,
        ], 'admin');
    }

    /**
     * Manual authorized staff points adjustment.
     */
    public function adjustPoints(Request $request, string $id): void
    {
        $this->requireRoles(['owner', 'manager']);

        $cid = (int)$id;
        $customer = Database::fetchOne("SELECT id, name, loyalty_points FROM customers WHERE id = :id", [':id' => $cid]);
        if (!$customer) {
            $this->error('Customer not found.', 404);
            return;
        }

        $points = (int)$request->input('points', 0);
        $reason = trim((string)$request->input('reason', 'Administrative adjustment'));

        if ($points === 0) {
            Session::flash('error', 'Adjustment points cannot be zero.');
            $this->redirect("/admin/crm/{$cid}");
            return;
        }

        $now = gmdate('Y-m-d H:i:s');
        $user = Session::user();

        Database::transaction(function ($pdo) use ($cid, $points, $reason, $customer, $user, $now) {
            $newBalance = max(0, (int)$customer['loyalty_points'] + $points);

            Database::execute("
                UPDATE customers
                SET loyalty_points = :bal, updated_at = :dt
                WHERE id = :cid
            ", [
                ':bal' => $newBalance,
                ':dt' => $now,
                ':cid' => $cid,
            ]);

            Database::execute("
                INSERT INTO loyalty_transactions (customer_id, order_id, type, points, balance_after, description, created_at)
                VALUES (:cid, NULL, 'adjust', :pts, :bal, :desc, :dt)
            ", [
                ':cid' => $cid,
                ':pts' => $points,
                ':bal' => $newBalance,
                ':desc' => "Staff adjustment by {$user['name']}: {$reason}",
                ':dt' => $now,
            ]);
        });

        Logger::info("Loyalty adjusted for customer {$customer['name']} by {$points} points", [
            'customer_id' => $cid,
            'points' => $points,
            'reason' => $reason,
        ]);

        Session::flash('success', "Loyalty points adjusted successfully. New balance: {$customer['loyalty_points']} pts.");
        $this->redirect("/admin/crm/{$cid}");
    }
}
