<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?= \App\Helpers\Security::e($order['order_number']) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            color: #000;
            background: #fff;
            padding: 10px;
            width: 80mm;
            margin: 0 auto;
        }

        .receipt-header {
            text-align: center;
            padding-bottom: 8px;
            border-bottom: 1px dashed #000;
            margin-bottom: 8px;
        }
        .brand-name {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .brand-sub {
            font-size: 11px;
            margin-top: 2px;
        }
        .meta-line {
            display: flex;
            justify-content: space-between;
            font-size: 11.5px;
            margin: 3px 0;
        }
        .receipt-items {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
        }
        .receipt-items th {
            text-align: left;
            border-bottom: 1px dashed #000;
            padding: 4px 0;
            font-size: 11.5px;
        }
        .receipt-items td {
            padding: 4px 0;
            vertical-align: top;
            font-size: 12px;
        }
        .receipt-totals {
            border-top: 1px dashed #000;
            padding-top: 6px;
            margin-top: 4px;
        }
        .total-line {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin: 2px 0;
        }
        .final-total {
            font-size: 15px;
            font-weight: bold;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 6px 0;
            margin: 6px 0;
        }
        .receipt-footer {
            text-align: center;
            font-size: 11px;
            margin-top: 12px;
            padding-top: 8px;
            border-top: 1px dashed #000;
        }

        /* ── Thermal Printer Media Queries ── */
        @media print {
            body {
                width: 100%;
                padding: 0;
                margin: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 15px; text-align: center;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #000; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-family: sans-serif; font-weight: bold;">
            🖨️ Print Receipt
        </button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #ccc; border: none; border-radius: 4px; cursor: pointer; font-family: sans-serif; margin-left: 6px;">
            Close
        </button>
    </div>

    <div class="receipt-header">
        <div class="brand-name"><?= \App\Helpers\Security::e($app['restaurant']['name'] ?? 'VALIMA BIRIYANI') ?></div>
        <div class="brand-sub"><?= \App\Helpers\Security::e($app['restaurant']['tagline'] ?? 'Traditional Dum Biriyani') ?></div>
        <div class="brand-sub"><?= \App\Helpers\Security::e($app['restaurant']['address'] ?? '') ?></div>
        <div class="brand-sub">Phone: <?= \App\Helpers\Security::e($app['restaurant']['phone'] ?? '') ?></div>
    </div>

    <div class="meta-line">
        <span>Order: <?= \App\Helpers\Security::e($order['order_number']) ?></span>
        <span>Date: <?= date('d/m/Y', strtotime($order['created_at'])) ?></span>
    </div>
    <div class="meta-line">
        <span>Type: <?= strtoupper(\App\Helpers\Security::e($order['order_type'])) ?><?= $order['table_id'] ? " (T#{$order['table_number']})" : "" ?></span>
        <span>Time: <?= date('h:i A', strtotime($order['created_at'])) ?></span>
    </div>
    <div class="meta-line">
        <span>Cust: <?= \App\Helpers\Security::e($order['customer_name']) ?></span>
        <span>Ph: <?= \App\Helpers\Security::e($order['customer_phone']) ?></span>
    </div>

    <table class="receipt-items">
        <thead>
            <tr>
                <th>Item</th>
                <th style="text-align: center;">Qty</th>
                <th style="text-align: right;">Price</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $it): ?>
                <tr>
                    <td><?= \App\Helpers\Security::e($it['item_name']) ?></td>
                    <td style="text-align: center;"><?= (int)$it['quantity'] ?></td>
                    <td style="text-align: right;"><?= number_format((float)$it['unit_price'], 2) ?></td>
                    <td style="text-align: right;"><?= number_format((float)$it['subtotal'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="receipt-totals">
        <div class="total-line">
            <span>Subtotal:</span>
            <span>₹<?= number_format((float)$order['subtotal'], 2) ?></span>
        </div>
        <div class="total-line">
            <span>GST Tax (5%):</span>
            <span>₹<?= number_format((float)$order['tax_amount'], 2) ?></span>
        </div>
        <?php if ((float)$order['delivery_fee'] > 0): ?>
            <div class="total-line">
                <span>Delivery Charge:</span>
                <span>₹<?= number_format((float)$order['delivery_fee'], 2) ?></span>
            </div>
        <?php endif; ?>
        <div class="total-line final-total">
            <span>TOTAL AMOUNT:</span>
            <span>₹<?= number_format((float)$order['total_amount'], 2) ?></span>
        </div>
        <div class="total-line">
            <span>Payment Mode:</span>
            <span><?= strtoupper(\App\Helpers\Security::e($order['payment_method'])) ?> (<?= strtoupper(\App\Helpers\Security::e($order['payment_status'])) ?>)</span>
        </div>
    </div>

    <div class="receipt-footer">
        <div>Thank you for dining with Valima Biriyani!</div>
        <div>Please visit again! 🍲</div>
    </div>

    <script>
        // Auto open print dialog when opened
        window.onload = function() {
            setTimeout(function() { window.print(); }, 400);
        };
    </script>
</body>
</html>
