<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Request;
use App\Helpers\Response;
use App\Helpers\Session;
use App\Helpers\Security;
use App\Services\OrderService;
use App\Models\MenuItem;
use Throwable;

class OrderController extends BaseController
{
    public function dineIn(Request $request): void
    {
        $requestedTable = (int)($request->input('id') ?? Session::get('table_id') ?? 1);
        $allocatedTable = OrderService::allocateTable($requestedTable);

        $isFullyOccupied = ($allocatedTable === null);
        $tableId = $allocatedTable ?? $requestedTable;

        if (!$isFullyOccupied) {
            Session::set('table_id', $tableId);
        }

        $this->render('customer.dine_in', [
            'title' => 'Dine-In Table Experience',
            'tableId' => $tableId,
            'isFullyOccupied' => $isFullyOccupied,
        ], 'main');
    }

    public function startDineIn(Request $request): void
    {
        $name = Security::sanitizeString((string)$request->input('name', ''));
        $phone = Security::normalizePhone((string)$request->input('phone', ''));
        $tableId = (int)$request->input('table_id', Session::get('table_id', 1));

        if (empty($name) || !Security::isValidIndianPhone($phone)) {
            Session::flash('error', 'Please enter a valid name and 10-digit Indian mobile number.');
            $this->redirect('/dine-in');
        }

        Session::set('customer_name', $name);
        Session::set('customer_phone', $phone);
        Session::set('table_id', $tableId);

        $this->redirect('/menu');
    }

    public function checkout(Request $request): void
    {
        $tableId = Session::get('table_id');
        $customerName = Session::get('customer_name', '');
        $customerPhone = Session::get('customer_phone', '');

        $this->render('customer.checkout', [
            'title' => 'Checkout & Confirm Order',
            'tableId' => $tableId,
            'customerName' => $customerName,
            'customerPhone' => $customerPhone,
        ], 'main');
    }

    public function store(Request $request): void
    {
        try {
            $rawPayload = $request->json() ?? $request->all();

            $orderParams = [
                'customer_name' => $rawPayload['name'] ?? Session::get('customer_name', 'Guest'),
                'customer_phone' => $rawPayload['phone'] ?? Session::get('customer_phone', ''),
                'table_id' => $rawPayload['table_id'] ?? Session::get('table_id'),
                'channel' => 'website',
                'order_type' => $rawPayload['order_type'] ?? (Session::get('table_id') ? 'dine_in' : 'pickup'),
                'payment_method' => $rawPayload['payment_method'] ?? 'cash',
                'special_instructions' => $rawPayload['instructions'] ?? null,
                'cart_items' => $rawPayload['cart'] ?? [],
                'delivery_address' => $rawPayload['address'] ?? null,
                'idempotency_key' => $rawPayload['idempotency_key'] ?? bin2hex(random_bytes(16)),
            ];

            $order = OrderService::createOrder($orderParams);

            if ($request->isJson()) {
                $this->success($order, 'Order created successfully.');
            }

            Session::flash('success', "Order placed successfully! Order #{$order['order_number']}");
            $this->redirect("/track/{$order['tracking_token']}");
        } catch (Throwable $e) {
            if ($request->isJson()) {
                $this->error($e->getMessage(), 422);
            }

            Session::flash('error', $e->getMessage());
            $this->redirect('/checkout');
        }
    }

    public function calculate(Request $request): void
    {
        try {
            $raw = $request->json() ?? [];
            $cartItems = $raw['cart'] ?? [];
            $orderType = $raw['order_type'] ?? 'dine_in';

            $calculated = OrderService::calculateCart($cartItems, $orderType);
            $this->success($calculated, 'Cart calculated.');
        } catch (Throwable $e) {
            $this->error($e->getMessage(), 422);
        }
    }
}
