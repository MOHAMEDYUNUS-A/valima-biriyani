<div class="container py-8 max-w-4xl" x-data="checkoutApp()" x-init="loadCheckoutCart()">

    <!-- ── SECURE CHECKOUT HEADER (Matches Mockup Screen 5) ── -->
    <div class="flex items-center justify-between pb-4 mb-6 border-b border-border">
        <div class="flex items-center gap-3">
            <a href="/menu" class="btn btn-outline btn-sm" style="border-radius: 999px;">
                &larr; Back to Menu
            </a>
            <h1 class="font-serif text-2xl font-bold text-foreground">Secure Checkout</h1>
        </div>
        <div class="flex items-center gap-2 text-xs font-bold text-green-700 bg-green-50 px-3 py-1 rounded-full border border-green-200">
            <?= \App\Helpers\Icon::render('shield-check', 'w-4 h-4 text-green-600') ?>
            <span>256-Bit SSL Encrypted</span>
        </div>
    </div>

    <!-- ── 3-STEP PROGRESS STEPPER ── -->
    <div class="stepper-header max-w-md mx-auto mb-8">
        <div class="stepper-line"></div>
        <div class="stepper-line-fill" :style="'width: ' + (step === 1 ? '0%' : (step === 2 ? '50%' : '100%'))"></div>

        <div class="stepper-step" :class="step >= 1 ? (step === 1 ? 'active' : 'completed') : ''">
            <div class="stepper-dot" x-text="step > 1 ? '✓' : '1'"></div>
            <div class="text-xs font-bold" :class="step >= 1 ? 'text-foreground' : 'text-muted-foreground'">Address</div>
        </div>

        <div class="stepper-step" :class="step >= 2 ? (step === 2 ? 'active' : 'completed') : ''">
            <div class="stepper-dot" x-text="step > 2 ? '✓' : '2'"></div>
            <div class="text-xs font-bold" :class="step >= 2 ? 'text-foreground' : 'text-muted-foreground'">Payment</div>
        </div>

        <div class="stepper-step" :class="step === 3 ? 'active' : ''">
            <div class="stepper-dot">3</div>
            <div class="text-xs font-bold" :class="step === 3 ? 'text-foreground' : 'text-muted-foreground'">Complete</div>
        </div>
    </div>

    <div class="grid md:grid-cols-3 gap-8" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
        <!-- ── LEFT 2 COLUMNS: STEP FORMS ── -->
        <div style="grid-column: span 2;">
            <!-- ── STEP 1: ADDRESS & CUSTOMER DETAILS (Mockup Screen 5) ── -->
            <div x-show="step === 1">
                <!-- Fulfilment Selector -->
                <div class="card p-5 mb-6">
                    <h3 class="font-serif font-bold text-base mb-3">Fulfilment Method</h3>
                    <div class="grid grid-cols-3 gap-3" style="display: grid; grid-template-columns: repeat(3, 1fr);">
                        <button type="button"
                                class="btn text-center flex flex-col py-3"
                                :class="fulfillmentType === 'delivery' ? 'btn-primary' : 'btn-outline'"
                                @click="fulfillmentType = 'delivery'; recalculateServerTotals()">
                            <span class="text-lg">🛵</span>
                            <span class="text-xs font-bold mt-1">Delivery</span>
                        </button>
                        <button type="button"
                                class="btn text-center flex flex-col py-3"
                                :class="fulfillmentType === 'pickup' ? 'btn-primary' : 'btn-outline'"
                                @click="fulfillmentType = 'pickup'; recalculateServerTotals()">
                            <span class="text-lg">🛍️</span>
                            <span class="text-xs font-bold mt-1">Takeaway</span>
                        </button>
                        <button type="button"
                                class="btn text-center flex flex-col py-3"
                                :class="fulfillmentType === 'dine_in' ? 'btn-primary' : 'btn-outline'"
                                @click="fulfillmentType = 'dine_in'; recalculateServerTotals()">
                            <span class="text-lg">🪑</span>
                            <span class="text-xs font-bold mt-1">Dine-In Table</span>
                        </button>
                    </div>
                </div>

                <!-- Delivery Address Card with Map Preview (Mockup Screen 5) -->
                <div class="card p-5 mb-6" x-show="fulfillmentType === 'delivery'">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-serif font-bold text-base flex items-center gap-2">
                            <?= \App\Helpers\Icon::render('map-pin', 'w-4 h-4 text-primary') ?>
                            <span>Delivery Address</span>
                        </h3>
                        <button type="button" class="btn btn-outline btn-sm" @click="useCurrentLocation()">
                            📍 Use My Location
                        </button>
                    </div>

                    <div class="p-3 bg-muted rounded-md mb-3" style="background: #F8F4EB; border: 1px dashed var(--border);">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="font-bold text-sm text-foreground flex items-center gap-1">
                                    <span>Home / Office</span>
                                    <span class="badge badge-gold" style="font-size: 10px;">Primary</span>
                                </div>
                                <div class="text-xs text-muted-foreground mt-1" x-text="deliveryAddress.address || 'No. 12, 2nd Street, Anna Nagar, Chennai - 600040'"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Complete Street Address *</label>
                        <input type="text" x-model="deliveryAddress.address" placeholder="Flat / House no, Street, Area" class="input" required>
                    </div>

                    <div class="grid grid-cols-2 gap-3" style="display: grid; grid-template-columns: 1fr 1fr;">
                        <div>
                            <label class="form-label">Landmark</label>
                            <input type="text" x-model="deliveryAddress.landmark" placeholder="Near Big Mosque / Metro" class="input">
                        </div>
                        <div>
                            <label class="form-label">Postal Code</label>
                            <input type="text" x-model="deliveryAddress.pincode" placeholder="600040" class="input">
                        </div>
                    </div>
                </div>

                <!-- Dine-In Table Picker -->
                <div class="card p-5 mb-6" x-show="fulfillmentType === 'dine_in'">
                    <h3 class="font-serif font-bold text-base mb-3 flex items-center gap-2">
                        <?= \App\Helpers\Icon::render('sparkles', 'w-4 h-4 text-primary') ?>
                        <span>Dining Table Selection (1–8)</span>
                    </h3>
                    <div class="grid grid-cols-4 gap-3" style="display: grid; grid-template-columns: repeat(4, 1fr);">
                        <template x-for="t in 8" :key="t">
                            <button type="button"
                                    class="p-3 border rounded-md font-bold text-center transition"
                                    :class="selectedTable === t ? 'bg-primary text-black border-primary' : 'bg-white text-foreground border-border hover:bg-muted'"
                                    @click="selectedTable = t"
                                    x-text="'Table ' + t">
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Customer Details Form (Mockup Screen 5) -->
                <div class="card p-5 mb-6">
                    <h3 class="font-serif font-bold text-base mb-3">Customer Details</h3>
                    
                    <div class="mb-4">
                        <label class="form-label">Full Name *</label>
                        <input type="text" x-model="customerName" placeholder="e.g. Rahul Kumar" class="input" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Mobile Number (without OTP) *</label>
                        <div class="relative">
                            <input type="tel" x-model="customerPhone" placeholder="+91 98765 43210" class="input" required>
                        </div>
                        <div class="form-helper">We will send live WhatsApp tracking updates to this mobile number.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Email Address (Optional)</label>
                        <input type="email" x-model="customerEmail" placeholder="rahul@gmail.com" class="input">
                    </div>

                    <div>
                        <label class="form-label">Delivery Instructions (Optional)</label>
                        <textarea x-model="deliveryInstructions" placeholder="e.g. Ring the bell, leave packet at door..." class="textarea"></textarea>
                    </div>
                </div>

                <button type="button" class="btn btn-primary btn-lg w-full" @click="goToPaymentStep()">
                    Continue to Payment &rarr;
                </button>
            </div>

            <!-- ── STEP 2: PAYMENT METHOD (Mockup Screen 6) ── -->
            <div x-show="step === 2" x-cloak style="display: none;">
                <!-- Total Amount Card -->
                <div class="card p-5 mb-6" style="background: #14110C; color: #FFFFFF; border: 1px solid var(--surface-dark-border);">
                    <div class="flex items-center justify-between">
                        <div>
                            <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--primary); font-weight: 700; letter-spacing: 1px;">
                                Total Amount Payable
                            </div>
                            <div class="font-serif font-extrabold text-3xl mt-1 text-white" x-text="'₹' + totals.total_amount.toFixed(2)"></div>
                        </div>
                        <button type="button" class="btn btn-outline btn-sm text-white" style="border-color: #444;" @click="step = 1">
                            Edit Details
                        </button>
                    </div>
                </div>

                <!-- Choose Payment Method (Mockup Screen 6) -->
                <div class="card p-5 mb-6">
                    <h3 class="font-serif font-bold text-base mb-4">Choose Payment Method</h3>

                    <div class="flex flex-col gap-3">
                        <!-- Razorpay Card -->
                        <label class="radio-card" :class="paymentMethod === 'razorpay' ? 'active' : ''">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="pay_method" value="razorpay" x-model="paymentMethod">
                                <div>
                                    <div class="font-bold text-base flex items-center gap-2">
                                        <span>Razorpay</span>
                                        <span class="badge badge-gold" style="font-size: 10px;">Instant</span>
                                    </div>
                                    <div class="text-xs text-muted-foreground mt-1">UPI (GPay, PhonePe, Paytm), Credit/Debit Cards, Netbanking</div>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-primary">⚡ Recommended</span>
                        </label>

                        <!-- Cash Payment Card -->
                        <label class="radio-card" :class="paymentMethod === 'cash' ? 'active' : ''">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="pay_method" value="cash" x-model="paymentMethod">
                                <div>
                                    <div class="font-bold text-base">Cash on Delivery / Counter</div>
                                    <div class="text-xs text-muted-foreground mt-1">Pay with cash or UPI QR code at table/counter upon delivery</div>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-muted-foreground">💵 Cash</span>
                        </label>
                    </div>
                </div>

                <!-- 100% Secure Payments Badge -->
                <div class="card p-4 mb-6 flex items-center justify-center gap-2 bg-green-50 border-green-200 text-green-800">
                    <?= \App\Helpers\Icon::render('shield-check', 'w-5 h-5 text-green-600') ?>
                    <span class="font-bold text-sm">100% Secure Payments Verified</span>
                </div>

                <!-- Submit Button -->
                <button type="button"
                        class="btn btn-primary btn-lg w-full mb-3"
                        :disabled="isSubmitting"
                        @click="submitOrder()">
                    <span x-show="!isSubmitting" x-text="paymentMethod === 'razorpay' ? 'Pay with Razorpay • ₹' + totals.total_amount.toFixed(2) : 'Confirm Order • ₹' + totals.total_amount.toFixed(2)"></span>
                    <span x-show="isSubmitting">Processing Order...</span>
                </button>

                <div class="text-center text-xs text-muted-foreground">
                    By placing this order you agree to Valima Biriyani's Terms & Conditions and Refund Policy.
                </div>
            </div>
        </div>

        <!-- ── RIGHT COLUMN: ORDER SUMMARY SIDEBAR ── -->
        <div>
            <div class="card p-5 sticky top-20 shadow-soft">
                <h3 class="font-serif font-bold text-base mb-3 pb-2 border-b border-border">Order Summary</h3>

                <!-- Cart Items List -->
                <div class="mb-4 max-h-60 overflow-y-auto" style="scrollbar-width: thin;">
                    <template x-for="(item, key) in cart" :key="key">
                        <div class="flex items-start justify-between py-2 border-b border-border text-sm">
                            <div>
                                <span class="font-bold" x-text="item.qty + 'x'"></span>
                                <span class="font-semibold text-foreground" x-text="item.name"></span>
                                <div class="text-xs text-muted-foreground" x-text="item.variant"></div>
                            </div>
                            <span class="font-bold text-foreground" x-text="'₹' + (item.unitPrice * item.qty).toFixed(2)"></span>
                        </div>
                    </template>
                </div>

                <!-- Price Totals -->
                <div class="space-y-2 text-sm pt-2">
                    <div class="flex justify-between text-muted-foreground">
                        <span>Subtotal</span>
                        <span class="font-bold text-foreground" x-text="'₹' + totals.subtotal.toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between text-muted-foreground">
                        <span>GST (5%)</span>
                        <span class="font-bold text-foreground" x-text="'₹' + totals.tax_amount.toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between text-muted-foreground">
                        <span>Delivery Fee</span>
                        <span class="font-bold text-foreground" x-text="'₹' + totals.delivery_fee.toFixed(2)"></span>
                    </div>

                    <div class="flex justify-between pt-3 border-t-2 border-border font-bold text-lg text-foreground">
                        <span>Total</span>
                        <span class="text-primary font-extrabold" x-text="'₹' + totals.total_amount.toFixed(2)"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── CHECKOUT APP SCRIPT (Alpine.js) ── -->
<script>
function checkoutApp() {
    return {
        step: 1,
        fulfillmentType: 'delivery',
        selectedTable: 1,
        customerName: '<?= htmlspecialchars(App\Helpers\Session::get('customer_name') ?? '') ?>',
        customerPhone: '<?= htmlspecialchars(App\Helpers\Session::get('customer_phone') ?? '') ?>',
        customerEmail: '',
        deliveryInstructions: '',
        deliveryAddress: {
            address: '',
            landmark: '',
            pincode: '600040'
        },
        paymentMethod: 'razorpay',
        cart: {},
        totals: {
            subtotal: 0,
            tax_amount: 0,
            delivery_fee: 40,
            total_amount: 0
        },
        isSubmitting: false,
        csrfToken: '<?= \App\Helpers\Security::generateCsrfToken() ?>',

        loadCheckoutCart() {
            try {
                const raw = localStorage.getItem('valima_cart');
                if (raw) {
                    this.cart = JSON.parse(raw);
                }
            } catch (e) {
                this.cart = {};
            }

            if (Object.keys(this.cart).length === 0) {
                alert('Your cart is empty. Please select dishes from the menu first.');
                window.location.href = '/menu';
                return;
            }

            this.recalculateServerTotals();
        },

        async recalculateServerTotals() {
            const items = Object.values(this.cart).map(it => ({
                id: it.id,
                qty: it.qty,
                notes: it.notes || it.variant || ''
            }));

            try {
                const base = window.APP_BASE_URL || '';
                const res = await fetch(`${base}/api/orders/calculate`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        order_type: this.fulfillmentType,
                        cart_items: items
                    })
                });
                const data = await res.json();
                if (data.success) {
                    this.totals.subtotal = data.data.subtotal;
                    this.totals.tax_amount = data.data.tax_amount;
                    this.totals.delivery_fee = data.data.delivery_fee;
                    this.totals.total_amount = data.data.total_amount;
                }
            } catch (err) {
                console.warn('Server total calculation error:', err);
            }
        },

        useCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        this.deliveryAddress.address = `GPS [${pos.coords.latitude.toFixed(4)}, ${pos.coords.longitude.toFixed(4)}] Anna Nagar, Chennai`;
                        alert('Location captured successfully!');
                    },
                    (err) => {
                        alert('Could not access GPS location. Please type your address manually.');
                    }
                );
            } else {
                alert('Geolocation is not supported by your browser.');
            }
        },

        goToPaymentStep() {
            if (!this.customerName.trim()) {
                alert('Please enter your full name.');
                return;
            }
            if (!this.customerPhone.trim() || this.customerPhone.replace(/\D/g, '').length < 10) {
                alert('Please enter a valid 10-digit mobile number.');
                return;
            }
            if (this.fulfillmentType === 'delivery' && !this.deliveryAddress.address.trim()) {
                alert('Please enter your delivery address.');
                return;
            }

            this.step = 2;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        async submitOrder() {
            this.isSubmitting = true;

            const items = Object.values(this.cart).map(it => ({
                id: it.id,
                qty: it.qty,
                notes: it.notes || it.variant || ''
            }));

            const payload = {
                customer_name: this.customerName,
                customer_phone: this.customerPhone,
                order_type: this.fulfillmentType,
                table_id: this.fulfillmentType === 'dine_in' ? this.selectedTable : null,
                payment_method: this.paymentMethod,
                delivery_address: this.fulfillmentType === 'delivery' ? this.deliveryAddress : null,
                special_instructions: this.deliveryInstructions,
                cart_items: items,
                _csrf_token: this.csrfToken,
                idempotency_key: 'chk_' + Date.now() + '_' + Math.random().toString(36).substring(2, 9)
            };

            try {
                const base = window.APP_BASE_URL || '';
                const res = await fetch(`${base}/api/orders`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();

                if (data.success) {
                    // Clear cart
                    localStorage.removeItem('valima_cart');
                    if (typeof updateGlobalCartBadge === 'function') {
                        updateGlobalCartBadge();
                    }

                    // Redirect to tracking page
                    window.location.href = `${base}/track/${data.data.tracking_token}`;
                } else {
                    alert(data.message || data.error || 'Failed to place order.');
                    this.isSubmitting = false;
                }
            } catch (err) {
                console.error('Order creation error:', err);
                alert('Network error while placing your order. Please try again.');
                this.isSubmitting = false;
            }
        }
    };
}
</script>
