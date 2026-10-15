<style>
    .checkout-wrap {
        max-width: 950px;
        margin: 2.5rem auto;
        padding: 0 1.2rem;
    }
    .checkout-grid {
        display: grid;
        grid-template-columns: 1.4fr 1fr;
        gap: 30px;
        align-items: flex-start;
    }
    .box-card {
        background: #fff;
        border-radius: var(--radius-md);
        padding: 2rem;
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        margin-bottom: 20px;
    }
    .box-title {
        font-family: var(--font-heading);
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 1.2rem;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border);
        color: var(--text);
    }
    .cart-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px dashed var(--border);
        font-size: 14px;
    }
    .cart-row:last-child {
        border-bottom: none;
    }
    .summary-line {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        font-size: 14px;
        color: var(--text-2);
    }
    .summary-total {
        display: flex;
        justify-content: space-between;
        padding: 14px 0 0;
        margin-top: 10px;
        border-top: 2px solid var(--border);
        font-family: var(--font-heading);
        font-size: 20px;
        font-weight: 700;
        color: var(--primary-dark);
    }
    .type-selector {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-bottom: 1.5rem;
    }
    .type-btn {
        padding: 12px;
        border: 1.5px solid var(--border);
        border-radius: 8px;
        background: #fff;
        text-align: center;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s;
    }
    .type-btn.active {
        border-color: var(--primary);
        background: var(--primary-light);
        color: var(--primary-dark);
    }
    @media (max-width: 768px) {
        .checkout-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="checkout-wrap">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-family:var(--font-display); font-size:32px;">Confirm Your Order</h1>
        <p style="color:var(--text-2); font-size:14px;">Review your items and confirm fulfillment details.</p>
    </div>

    <form id="checkoutForm" action="/orders" method="POST">
        <?= $csrf_field ?>
        <input type="hidden" name="idempotency_key" value="<?= \App\Helpers\Security::randomToken(16) ?>">
        <input type="hidden" name="order_type" id="orderTypeInput" value="<?= $tableId ? 'dine_in' : 'pickup' ?>">
        <input type="hidden" name="table_id" value="<?= (int)($tableId ?? 0) ?>">
        <input type="hidden" name="cart_json" id="cartJsonInput">

        <div class="checkout-grid">
            <!-- Left: Customer Details & Fulfillment -->
            <div>
                <div class="box-card">
                    <h2 class="box-title">1. Fulfillment Method</h2>
                    <div class="type-selector">
                        <div class="type-btn <?= $tableId ? 'active' : '' ?>" onclick="selectOrderType('dine_in', this)">
                            🍽️ Dine-in <?= $tableId ? "(T#{$tableId})" : '' ?>
                        </div>
                        <div class="type-btn <?= !$tableId ? 'active' : '' ?>" onclick="selectOrderType('pickup', this)">
                            🛍️ Takeaway / Pickup
                        </div>
                        <div class="type-btn" onclick="selectOrderType('delivery', this)">
                            🛵 Home Delivery
                        </div>
                    </div>

                    <div id="deliveryFields" style="display: none; padding-top: 10px; border-top: 1px dashed var(--border);">
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label style="display:block; font-size:12px; font-weight:700; color:var(--text-2); margin-bottom:5px;">Delivery Address *</label>
                            <input type="text" name="address[line1]" id="addrLine1" class="form-input" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px;" placeholder="Flat / House No., Street, Area">
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                            <div class="form-group">
                                <label style="display:block; font-size:12px; font-weight:700; color:var(--text-2); margin-bottom:5px;">Landmark</label>
                                <input type="text" name="address[landmark]" class="form-input" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px;" placeholder="Near Park, School...">
                            </div>
                            <div class="form-group">
                                <label style="display:block; font-size:12px; font-weight:700; color:var(--text-2); margin-bottom:5px;">Pincode</label>
                                <input type="text" name="address[postal_code]" class="form-input" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px;" placeholder="600045">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box-card">
                    <h2 class="box-title">2. Your Details</h2>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:1rem;">
                        <div class="form-group">
                            <label style="display:block; font-size:12px; font-weight:700; color:var(--text-2); margin-bottom:5px;">Name *</label>
                            <input type="text" name="name" class="form-input" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px;" value="<?= \App\Helpers\Security::e($customerName) ?>" placeholder="Rajesh Kumar" required>
                        </div>
                        <div class="form-group">
                            <label style="display:block; font-size:12px; font-weight:700; color:var(--text-2); margin-bottom:5px;">Mobile Number *</label>
                            <input type="tel" name="phone" class="form-input" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px;" value="<?= \App\Helpers\Security::e($customerPhone) ?>" placeholder="9876543210" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label style="display:block; font-size:12px; font-weight:700; color:var(--text-2); margin-bottom:5px;">Cooking or Delivery Instructions</label>
                        <textarea name="instructions" rows="2" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:8px; font-family:inherit; font-size:13.5px;" placeholder="Less spicy, extra salna, contactless delivery..."></textarea>
                    </div>
                </div>

                <div class="box-card">
                    <h2 class="box-title">3. Payment Method</h2>
                    <div style="display:flex; flex-direction:column; gap:10px;">
                        <label style="display:flex; align-items:center; gap:10px; padding:12px; border:1px solid var(--border); border-radius:8px; cursor:pointer;">
                            <input type="radio" name="payment_method" value="cash" checked>
                            <div>
                                <strong style="font-size:14px;">Cash at Counter / Cash on Delivery</strong>
                                <div style="font-size:12px; color:var(--text-2);">Pay directly at the restaurant counter or upon delivery.</div>
                            </div>
                        </label>
                        <label style="display:flex; align-items:center; gap:10px; padding:12px; border:1px solid var(--border); border-radius:8px; cursor:pointer;">
                            <input type="radio" name="payment_method" value="counter_upi">
                            <div>
                                <strong style="font-size:14px;">UPI / QR Code Scan</strong>
                                <div style="font-size:12px; color:var(--text-2);">GPay, PhonePe, Paytm QR code scan at counter.</div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Right: Live Cart Breakdown Calculated by Server -->
            <div>
                <div class="box-card" style="position: sticky; top: 90px;">
                    <h2 class="box-title">Order Summary</h2>
                    <div id="cartItemsList">
                        <p style="color:var(--text-2); font-size:13px; text-align:center; padding:1rem 0;">Loading cart...</p>
                    </div>

                    <div style="margin-top: 15px; padding-top: 12px; border-top: 1px solid var(--border);">
                        <div class="summary-line">
                            <span>Subtotal:</span>
                            <span id="subtotalVal">₹0.00</span>
                        </div>
                        <div class="summary-line">
                            <span>GST Tax (5%):</span>
                            <span id="taxVal">₹0.00</span>
                        </div>
                        <div class="summary-line" id="deliveryFeeLine" style="display:none;">
                            <span>Delivery Fee:</span>
                            <span id="deliveryFeeVal">₹40.00</span>
                        </div>
                        <div class="summary-total">
                            <span>Total Payable:</span>
                            <span id="totalVal">₹0.00</span>
                        </div>
                    </div>

                    <button type="submit" id="btnPlaceOrder" class="btn btn-primary" style="width:100%; justify-content:center; padding:14px; margin-top:20px; font-size:15px;">
                        Confirm & Place Order
                    </button>
                    <div style="text-align:center; margin-top:10px; font-size:12px; color:var(--text-2);">
                        🔒 Prices verified and calculated by server.
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
let currentCart = JSON.parse(localStorage.getItem('valima_cart') || '{}');
let currentOrderType = '<?= $tableId ? 'dine_in' : 'pickup' ?>';

function selectOrderType(type, el) {
    currentOrderType = type;
    document.getElementById('orderTypeInput').value = type;
    document.querySelectorAll('.type-btn').forEach(b => b.classList.remove('active'));
    el.classList.add('active');

    const deliveryBox = document.getElementById('deliveryFields');
    const deliveryFeeLine = document.getElementById('deliveryFeeLine');
    if (type === 'delivery') {
        deliveryBox.style.display = 'block';
        deliveryFeeLine.style.display = 'flex';
        document.getElementById('addrLine1').required = true;
    } else {
        deliveryBox.style.display = 'none';
        deliveryFeeLine.style.display = 'none';
        document.getElementById('addrLine1').required = false;
    }

    recalculateWithServer();
}

function recalculateWithServer() {
    let itemsArr = Object.values(currentCart);
    if (itemsArr.length === 0) {
        document.getElementById('cartItemsList').innerHTML = '<p style="color:#d63031; text-align:center;">Your cart is empty. <a href="/menu">Browse Menu</a></p>';
        document.getElementById('btnPlaceOrder').disabled = true;
        return;
    }

    fetch('/api/orders/calculate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cart: itemsArr, order_type: currentOrderType })
    })
    .then(r => r.json())
    .then(res => {
        if (!res.success) {
            alert(res.message);
            return;
        }
        let data = res.data;
        let html = '';
        data.items.forEach(it => {
            html += `<div class="cart-row">
                <div>
                    <strong>${it.item_name}</strong>
                    <div style="font-size:12px; color:var(--text-2);">Qty: ${it.quantity} × ₹${it.unit_price.toFixed(2)}</div>
                </div>
                <div style="font-weight:600;">₹${it.subtotal.toFixed(2)}</div>
            </div>`;
        });
        document.getElementById('cartItemsList').innerHTML = html;
        document.getElementById('subtotalVal').innerText = '₹' + data.subtotal.toFixed(2);
        document.getElementById('taxVal').innerText = '₹' + data.tax_amount.toFixed(2);
        document.getElementById('totalVal').innerText = '₹' + data.total_amount.toFixed(2);
    })
    .catch(err => console.error('Calculation error:', err));
}

document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    let itemsArr = Object.values(currentCart);
    if (itemsArr.length === 0) {
        e.preventDefault();
        alert('Your cart is empty! Please add items from the menu first.');
        return false;
    }

    // Set cart items in form
    let hiddenContainer = document.createElement('div');
    itemsArr.forEach((it, idx) => {
        let inputId = document.createElement('input');
        inputId.type = 'hidden';
        inputId.name = `cart[${idx}][id]`;
        inputId.value = it.id;
        hiddenContainer.appendChild(inputId);

        let inputQty = document.createElement('input');
        inputQty.type = 'hidden';
        inputQty.name = `cart[${idx}][qty]`;
        inputQty.value = it.qty;
        hiddenContainer.appendChild(inputQty);
    });
    this.appendChild(hiddenContainer);

    // Clear cart in local storage after submission starts
    localStorage.removeItem('valima_cart');
});

// Initial calculate on load
recalculateWithServer();
</script>
