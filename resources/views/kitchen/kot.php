<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KOT #<?= htmlspecialchars($order['order_number']) ?> — Valima Biriyani</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            background: #f1f5f9;
            color: #000;
            display: flex;
            justify-content: center;
            padding: 20px;
        }

        .kot-ticket {
            width: 80mm;
            background: #fff;
            padding: 12px 14px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }

        .title {
            font-size: 18px;
            font-weight: 900;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .subtitle {
            font-size: 13px;
            font-weight: bold;
            margin-top: 2px;
        }

        .reprint-banner {
            border: 2px solid #000;
            text-align: center;
            font-size: 14px;
            font-weight: 900;
            padding: 3px;
            margin-top: 6px;
            letter-spacing: 1px;
        }

        .meta-grid {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .table-banner {
            background: #000;
            color: #fff;
            text-align: center;
            padding: 6px;
            font-size: 20px;
            font-weight: 900;
            margin: 8px 0;
            border-radius: 4px;
            letter-spacing: 1px;
        }

        .items-header {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            font-weight: 900;
            border-top: 2px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 4px 0;
            margin-top: 6px;
        }

        .items-list {
            margin: 6px 0;
        }

        .item-row {
            display: flex;
            align-items: flex-start;
            padding: 6px 0;
            border-bottom: 1px dotted #ccc;
        }

        .item-qty {
            width: 40px;
            font-size: 20px;
            font-weight: 900;
            flex-shrink: 0;
        }

        .item-desc {
            flex-grow: 1;
        }

        .item-name {
            font-size: 16px;
            font-weight: 900;
            line-height: 1.2;
        }

        .item-notes {
            font-size: 12px;
            font-style: italic;
            font-weight: bold;
            margin-top: 2px;
        }

        .special-box {
            border: 2px solid #000;
            padding: 6px;
            margin-top: 10px;
            font-size: 13px;
            font-weight: 900;
        }

        .footer {
            border-top: 2px dashed #000;
            margin-top: 12px;
            padding-top: 8px;
            text-align: center;
            font-size: 11px;
            font-weight: bold;
        }

        .print-btn-bar {
            text-align: center;
            margin-bottom: 15px;
        }

        .btn-print {
            background: #d4a359;
            color: #000;
            border: none;
            padding: 8px 16px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .kot-ticket {
                width: 100%;
                box-shadow: none;
                padding: 0;
            }
            .print-btn-bar {
                display: none;
            }
            @page {
                size: 80mm auto;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <div>
        <div class="print-btn-bar">
            <button class="btn-print" onclick="window.print()">🖨️ Print KOT Ticket</button>
        </div>

        <div class="kot-ticket">
            <div class="header">
                <div class="title">VALIMA BIRIYANI</div>
                <div class="subtitle">KITCHEN ORDER TICKET (KOT)</div>
                <?php if ($isReprint): ?>
                    <div class="reprint-banner">*** REPRINT #<?= (int)$reprintCount ?> ***</div>
                <?php endif; ?>
            </div>

            <div class="meta-grid">
                <span>ORDER: #<?= htmlspecialchars($order['order_number']) ?></span>
                <span>KOT: <?= htmlspecialchars($order['ticket_number'] ?? 'KOT-00000') ?></span>
            </div>

            <div class="meta-grid">
                <span>DATE: <?= date('d/m/Y') ?></span>
                <span>TIME: <?= date('h:i A', strtotime($order['created_at'])) ?></span>
            </div>

            <!-- Big Fulfillment / Table Banner -->
            <div class="table-banner">
                <?php if (($order['order_type'] ?? '') === 'dine_in'): ?>
                    TABLE <?= (int)$order['table_id'] ?>
                <?php elseif (($order['order_type'] ?? '') === 'delivery'): ?>
                    🛵 DELIVERY
                <?php else: ?>
                    🛍️ TAKEAWAY
                <?php endif; ?>
            </div>

            <div class="items-header">
                <span>QTY &nbsp; ITEM</span>
            </div>

            <div class="items-list">
                <?php foreach ($items as $item): ?>
                    <div class="item-row">
                        <div class="item-qty"><?= (int)$item['quantity'] ?>x</div>
                        <div class="item-desc">
                            <div class="item-name"><?= htmlspecialchars($item['item_name']) ?></div>
                            <?php if (!empty($item['variant_name'])): ?>
                                <div class="item-notes">(<?= htmlspecialchars($item['variant_name']) ?>)</div>
                            <?php endif; ?>
                            <?php if (!empty($item['notes'])): ?>
                                <div class="item-notes">** <?= htmlspecialchars($item['notes']) ?> **</div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($order['special_instructions'])): ?>
                <div class="special-box">
                    INSTRUCTIONS: <?= htmlspecialchars($order['special_instructions']) ?>
                </div>
            <?php endif; ?>

            <div class="footer">
                PRINTED: <?= date('d-m-Y H:i:s') ?><br>
                *** KITCHEN COPY ONLY ***
            </div>
        </div>
    </div>

    <script>
        // Auto trigger print dialogue in browser
        window.addEventListener('load', () => {
            // Uncomment or trigger if user requested auto-print
        });
    </script>
</body>
</html>
