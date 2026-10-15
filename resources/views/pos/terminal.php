<style>
    .pos-container {
        display: grid;
        grid-template-columns: 1.8fr 1.2fr;
        gap: 20px;
        height: calc(100vh - 120px);
    }
    .pos-items-area {
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .pos-cat-bar {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding-bottom: 10px;
        margin-bottom: 15px;
    }
    .pos-cat-btn {
        padding: 8px 16px;
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
    }
    .pos-cat-btn.active {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
    }
    .pos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 12px;
        overflow-y: auto;
        padding-right: 6px;
        flex: 1;
    }
    .pos-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 14px;
        cursor: pointer;
        transition: transform 0.1s, border-color 0.1s;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .pos-card:hover {
        border-color: var(--primary);
        transform: translateY(-2px);
    }
    .pos-card-name {
        font-weight: 600;
        font-size: 14px;
        color: var(--text);
        margin-bottom: 6px;
    }
    .pos-card-price {
        font-weight: 700;
        color: var(--primary-dark);
        font-size: 16px;
    }
    .bill-panel {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: var(--shadow);
    }
    .bill-header {
        padding: 16px 20px;
        background: #fafbfc;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .bill-items-wrap {
        flex: 1;
        overflow-y: auto;
        padding: 15px 20px;
    }
    .bill-item-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px dashed var(--border);
        font-size: 13.5px;
    }
    .bill-qty-controls {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .bill-qty-btn {
        width: 24px;
        height: 24px;
        border: 1px solid var(--border);
        background: #f0f2f7;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
    }
    .bill-footer {
        padding: 18px 20px;
        background: #fafbfc;
        border-top: 1px solid var(--border);
    }
    .bill-total-row {
        display: flex;
        justify-content: space-between;
        font-size: 18px;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 15px;
    }
</style>

<div class="pos-container">
    <!-- Left: Menu Items Catalog -->
    <div class="pos-items-area">
        <div style="display:flex; gap:10px; margin-bottom:12px;">
            <input type="text" id="posSearch" placeholder="🔍 Quick search items (e.g. Biriyani, Noodles)..." class="form-input" style="flex:1; padding:9px 12px; font-size:14px; border:1.5px solid var(--border); border-radius:8px;" onkeyup="filterPosItems()">
        </div>

        <div class="pos-cat-bar">
            <button class="pos-cat-btn active" onclick="filterCategory('all', this)">All Items</button>
            <?php foreach ($categories as $cat): ?>
                <button class="pos-cat-btn" onclick="filterCategory('<?= $cat['slug'] ?>', this)"><?= \App\Helpers\Security::e($cat['name']) ?></button>
            <?php endforeach; ?>
        </div>

        <div class="pos-grid" id="posGrid">
            <?php foreach ($items as $item): ?>
                <div class="pos-card" data-cat="<?= $item['category_slug'] ?>" data-name="<?= strtolower($item['name']) ?>" onclick="posAddItem(<?= $item['id'] ?>, '<?= addslashes($item['name']) ?>', <?= (float)$item['base_price'] ?>)">
                    <div>
                        <div class="pos-card-name"><?= \App\Helpers\Security::e($item['name']) ?></div>
                        <div style="font-size:11px; color:var(--text2);"><?= $item['is_veg'] ? '🌱 Veg' : '🍗 Non-Veg' ?></div>
                    </div>
                    <div class="pos-card-price">₹<?= number_format((float)$item['base_price'], 2) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Right: Bill Builder & Cashier Checkout -->
    <div class="bill-panel">
        <div class="bill-header">
            <h3 style="font-size:16px; margin:0;">Current Bill</h3>
            <button class="btn btn-sm btn-secondary" onclick="clearBill()">Clear</button>
        </div>

        <div style="padding:12px 20px; border-bottom:1px solid var(--border); background:#fff;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; color:var(--text2);">Order Type</label>
                    <select id="posOrderType" style="width:100%; padding:7px; border:1px solid var(--border); border-radius:6px;" onchange="toggleTableSelect()">
                        <option value="dine_in">Dine-In</option>
                        <option value="pickup">Takeaway / Counter</option>
                    </select>
                </div>
                <div id="posTableWrap">
                    <label style="display:block; font-size:11px; font-weight:700; color:var(--text2);">Table No.</label>
                    <select id="posTableId" style="width:100%; padding:7px; border:1px solid var(--border); border-radius:6px;">
                        <?php foreach ($tables as $tbl): ?>
                            <option value="<?= $tbl['id'] ?>">Table #<?= $tbl['table_number'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1.2fr; gap:10px;">
                <input type="text" id="posCustName" placeholder="Customer Name" value="Walk-in Customer" style="padding:7px; border:1px solid var(--border); border-radius:6px; font-size:13px;">
                <input type="tel" id="posCustPhone" placeholder="Mobile (+91)" value="9999999999" style="padding:7px; border:1px solid var(--border); border-radius:6px; font-size:13px;">
            </div>
        </div>

        <!-- Bill Items List -->
        <div class="bill-items-wrap" id="posBillItems">
            <div style="text-align:center; padding:3rem 0; color:var(--text2);">
                Select items from the catalog to build the bill.
            </div>
        </div>

        <!-- Bill Summary & Payment Settlement -->
        <div class="bill-footer">
            <div style="display:flex; justify-content:space-between; font-size:13px; color:var(--text2); margin-bottom:4px;">
                <span>Subtotal:</span>
                <span id="posSubtotal">₹0.00</span>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:13px; color:var(--text2); margin-bottom:10px;">
                <span>GST Tax (5%):</span>
                <span id="posTax">₹0.00</span>
            </div>
            <div class="bill-total-row">
                <span>Net Total:</span>
                <span id="posNetTotal">₹0.00</span>
            </div>

            <div style="display:flex; gap:10px; margin-bottom:14px;">
                <label style="display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
                    <input type="radio" name="posPayment" value="cash" checked> Cash
                </label>
                <label style="display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
                    <input type="radio" name="posPayment" value="counter_upi"> UPI / QR Scan
                </label>
            </div>

            <button class="btn btn-primary" id="btnSettleBill" style="width:100%; justify-content:center; padding:12px; font-size:15px;" onclick="settlePosOrder()">
                💰 Settle & Print Receipt
            </button>
        </div>
    </div>
</div>

<script>
let posBill = {};

function posAddItem(id, name, price) {
    if (!posBill[id]) {
        posBill[id] = { id: id, name: name, price: price, qty: 1 };
    } else {
        posBill[id].qty++;
    }
    renderPosBill();
}

function posModQty(id, delta) {
    if (posBill[id]) {
        posBill[id].qty += delta;
        if (posBill[id].qty <= 0) {
            delete posBill[id];
        }
    }
    renderPosBill();
}

function clearBill() {
    posBill = {};
    renderPosBill();
}

function renderPosBill() {
    let items = Object.values(posBill);
    let container = document.getElementById('posBillItems');

    if (items.length === 0) {
        container.innerHTML = '<div style="text-align:center; padding:3rem 0; color:var(--text2);">Select items from catalog.</div>';
        document.getElementById('posSubtotal').innerText = '₹0.00';
        document.getElementById('posTax').innerText = '₹0.00';
        document.getElementById('posNetTotal').innerText = '₹0.00';
        return;
    }

    let subtotal = 0;
    let html = '';
    items.forEach(it => {
        let lineTotal = it.price * it.qty;
        subtotal += lineTotal;
        html += `
        <div class="bill-item-row">
            <div>
                <strong>${it.name}</strong>
                <div style="font-size:11px; color:var(--text2);">₹${it.price.toFixed(2)} each</div>
            </div>
            <div style="display:flex; align-items:center; gap:12px;">
                <div class="bill-qty-controls">
                    <button class="bill-qty-btn" onclick="posModQty(${it.id}, -1)">−</button>
                    <b>${it.qty}</b>
                    <button class="bill-qty-btn" onclick="posModQty(${it.id}, 1)">+</button>
                </div>
                <div style="font-weight:600; min-width:60px; text-align:right;">₹${lineTotal.toFixed(2)}</div>
            </div>
        </div>`;
    });

    container.innerHTML = html;
    let tax = subtotal * 0.05;
    let net = subtotal + tax;

    document.getElementById('posSubtotal').innerText = '₹' + subtotal.toFixed(2);
    document.getElementById('posTax').innerText = '₹' + tax.toFixed(2);
    document.getElementById('posNetTotal').innerText = '₹' + net.toFixed(2);
}

function toggleTableSelect() {
    let type = document.getElementById('posOrderType').value;
    document.getElementById('posTableWrap').style.display = (type === 'dine_in') ? 'block' : 'none';
}

function filterCategory(slug, el) {
    document.querySelectorAll('.pos-cat-btn').forEach(b => b.classList.remove('active'));
    el.classList.add('active');

    let cards = document.querySelectorAll('.pos-card');
    cards.forEach(c => {
        if (slug === 'all' || c.getAttribute('data-cat') === slug) {
            c.style.display = 'flex';
        } else {
            c.style.display = 'none';
        }
    });
}

function filterPosItems() {
    let q = document.getElementById('posSearch').value.toLowerCase();
    let cards = document.querySelectorAll('.pos-card');
    cards.forEach(c => {
        let name = c.getAttribute('data-name');
        c.style.display = name.includes(q) ? 'flex' : 'none';
    });
}

function settlePosOrder() {
    let items = Object.values(posBill);
    if (items.length === 0) {
        alert('Please add items to the bill before settling.');
        return;
    }

    let payload = {
        customer_name: document.getElementById('posCustName').value || 'Walk-in Customer',
        customer_phone: document.getElementById('posCustPhone').value || '9999999999',
        order_type: document.getElementById('posOrderType').value,
        table_id: document.getElementById('posTableId').value,
        payment_method: document.querySelector('input[name="posPayment"]:checked').value,
        cart: items
    };

    fetch('/api/pos/orders', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(res => {
        if (!res.success) {
            alert('POS Error: ' + res.message);
            return;
        }

        let order = res.data;
        // Open printable receipt in popup window
        window.open('/pos/receipt/' + order.order_id, '_blank', 'width=420,height=600');
        clearBill();
    })
    .catch(err => {
        console.error(err);
        alert('Server communication error during POS billing.');
    });
}
</script>
