<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Database;
use App\Helpers\Request;
use App\Helpers\Response;
use App\Helpers\Session;

class ReportController extends BaseController
{
    /**
     * Reports and operational analytics view.
     */
    public function index(Request $request): void
    {
        $this->requireRoles(['owner', 'manager']);

        // 1. Overall Sales Financial Breakdown
        $grossSales = (float)Database::fetchValue("SELECT COALESCE(SUM(total_amount), 0.0) FROM orders WHERE status != 'cancelled'");
        $collectedRevenue = (float)Database::fetchValue("SELECT COALESCE(SUM(total_amount), 0.0) FROM orders WHERE payment_status = 'paid' AND status != 'cancelled'");
        $pendingRevenue = (float)Database::fetchValue("SELECT COALESCE(SUM(total_amount), 0.0) FROM orders WHERE payment_status = 'pending' AND status != 'cancelled'");
        $totalTax = (float)Database::fetchValue("SELECT COALESCE(SUM(tax_amount), 0.0) FROM orders WHERE status != 'cancelled'");
        $totalDeliveryFees = (float)Database::fetchValue("SELECT COALESCE(SUM(delivery_fee), 0.0) FROM orders WHERE status != 'cancelled'");

        // 2. Best-Selling Items
        $bestSellers = Database::fetchAll("
            SELECT item_name, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue
            FROM order_items
            GROUP BY item_name
            ORDER BY total_qty DESC
            LIMIT 10
        ");

        // 3. Channel breakdown
        $channels = Database::fetchAll("
            SELECT channel, COUNT(*) as count, SUM(total_amount) as total
            FROM orders
            WHERE status != 'cancelled'
            GROUP BY channel
            ORDER BY total DESC
        ");

        $this->render('admin.reports.index', [
            'title' => 'Sales & Operational Reports',
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
    }

    /**
     * Export sales data to CSV.
     */
    public function exportCsv(Request $request): void
    {
        $this->requireRoles(['owner', 'manager']);

        $orders = Database::fetchAll("
            SELECT order_number, order_type, channel, status, total_amount, payment_method, payment_status, created_at
            FROM orders
            ORDER BY id DESC
        ");

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=valima_orders_export_' . date('Y-m-d') . '.csv');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Order Number', 'Type', 'Channel', 'Status', 'Total (INR)', 'Payment Method', 'Payment Status', 'Date']);

        foreach ($orders as $row) {
            fputcsv($out, [
                $row['order_number'],
                $row['order_type'],
                $row['channel'],
                $row['status'],
                number_format((float)$row['total_amount'], 2),
                $row['payment_method'],
                $row['payment_status'],
                $row['created_at'],
            ]);
        }

        fclose($out);
        exit;
    }
}
