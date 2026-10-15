<div class="container py-6" x-data="menuApp()" x-init="initCart()">

    <!-- ── LOCATION BAR (Matches Mockup Screen 2) ── -->
    <div class="card p-3 mb-6 flex items-center justify-between shadow-soft" style="background: #FFFFFF;">
        <div class="flex items-center gap-3">
            <div style="color: var(--primary); background: #FFFBEB; padding: 8px; border-radius: 50%;">
                <?= \App\Helpers\Icon::render('map-pin', 'w-5 h-5') ?>
            </div>
            <div>
                <div style="font-size: 0.725rem; font-weight: 700; color: var(--muted-foreground); text-transform: uppercase;">Deliver to</div>
                <div class="font-bold text-sm text-foreground" x-text="deliveryLocation">Anna Nagar, Chennai 600040</div>
            </div>
        </div>
        <button type="button" class="btn btn-outline btn-sm" @click="changeLocation()">
            Change
        </button>
    </div>

    <!-- ── SPECIAL MENU BANNER (Matches Mockup Screen 2) ── -->
    <div class="mb-8 rounded-xl overflow-hidden relative shadow-card" style="background: #14110C; border: 1px solid var(--surface-dark-border);">
        <div class="grid md:grid-cols-2 items-center p-6 md:p-8 relative z-10" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
            <div>
                <span class="badge badge-gold mb-2">Chef's Signature</span>
                <h1 class="font-serif text-white text-2xl md:text-3xl font-bold mb-2">
                    Our Special Menu
                </h1>
                <p style="color: #c5bba8; font-size: 0.95rem; margin-bottom: 1rem;">
                    Freshly prepared dum batches | Authentic wood-fired spices
                </p>
                <div class="flex items-center gap-2">
                    <span style="background: rgba(34, 197, 94, 0.2); color: #86efac; padding: 4px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 700;">
                        ● Kitchen Open Now
                    </span>
                </div>
            </div>

            <div class="flex justify-end mt-4 md:mt-0">
                <img src="/assets/images/valima-mockup.jpg" alt="Special Menu"
                     style="height: 140px; border-radius: 12px; object-fit: cover; object-position: 70% 8%; border: 2px solid rgba(245, 184, 0, 0.3);">
            </div>
        </div>
    </div>

    <!-- ── CATEGORY PILL CHIPS & SEARCH (Matches Mockup Screen 2) ── -->
    <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-6">
        <!-- Categories horizontal scroll -->
        <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto pb-2" style="scrollbar-width: none;">
            <button type="button"
                    class="btn btn-sm"
                    :class="selectedCategory === 'all' ? 'btn-primary' : 'btn-outline'"
                    @click="selectedCategory = 'all'">
                All
            </button>
            <?php foreach ($categories as $cat): ?>
                <button type="button"
                        class="btn btn-sm"
                        :class="selectedCategory === <?= (int)$cat['id'] ?> ? 'btn-primary' : 'btn-outline'"
                        @click="selectedCategory = <?= (int)$cat['id'] ?>">
                    <?= htmlspecialchars($cat['name']) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Search bar -->
        <div class="relative w-full md:w-72">
            <input type="text"
                   x-model="searchQuery"
                   placeholder="Search biryani, starters..."
                   class="input"
                   style="padding-left: 2.5rem; border-radius: 999px;">
            <div style="position: absolute; left: 12px; top: 12px; color: var(--muted-foreground);">
                <?= \App\Helpers\Icon::render('search', 'w-4 h-4') ?>
            </div>
        </div>
    </div>

    <!-- ── POPULAR ITEMS GRID ── -->
    <div class="mb-4">
        <h2 class="font-serif font-bold text-xl text-foreground mb-4">
            Popular Items
        </h2>
    </div>

    <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
        <?php foreach ($items as $item): ?>
            <?php
                $itemJson = htmlspecialchars(json_encode([
                    'id' => (int)$item['id'],
                    'name' => $item['name'],
                    'category_id' => (int)$item['category_id'],
                    'price' => (float)$item['base_price'],
                    'promo_price' => !empty($item['promo_price']) ? (float)$item['promo_price'] : null,
                    'is_veg' => (bool)$item['is_veg'],
                    'description' => $item['description'] ?? '',
                ]), ENT_QUOTES, 'UTF-8');
            ?>
            <div class="card card-hover flex flex-col justify-between"
                 x-show="matchesFilter(<?= (int)$item['category_id'] ?>, <?= json_encode(strtolower($item['name'])) ?>)">
                <div>
                    <!-- Image with Rating & Prep Time -->
                    <div style="height: 170px; position: relative; overflow: hidden; background: #222;">
                        <img src="/assets/images/valima-mockup.jpg" alt="<?= htmlspecialchars($item['name']) ?>"
                             style="width: 100%; height: 100%; object-fit: cover; object-position: <?= (int)$item['id'] % 2 === 0 ? '25% 10%' : '70% 8%' ?>;">
                        
                        <div style="position: absolute; top: 10px; left: 10px; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); padding: 3px 8px; border-radius: 999px; font-size: 0.75rem; font-weight: 700; color: #FFF; display: flex; align-items: center; gap: 4px;">
                            <span style="color: #F59E0B;">★</span> 4.8 (2.3k)
                        </div>

                        <div style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); padding: 3px 8px; border-radius: 999px; font-size: 0.75rem; font-weight: 600; color: #FFF; display: flex; align-items: center; gap: 4px;">
                            <?= \App\Helpers\Icon::render('clock', 'w-3 h-3 text-primary') ?> <?= (int)($item['prep_time_minutes'] ?? 20) ?> mins
                        </div>
                    </div>

                    <div class="p-4">
                        <div class="flex items-center justify-between mb-1">
                            <h3 class="font-serif font-bold text-base text-foreground"><?= htmlspecialchars($item['name']) ?></h3>
                            <?php if ($item['is_veg']): ?>
                                <span style="width: 16px; height: 16px; border: 1.5px solid #16A34A; display: inline-flex; align-items: center; justify-content: center; border-radius: 3px;" title="Pure Veg">
                                    <span style="width: 8px; height: 8px; background: #16A34A; border-radius: 50%;"></span>
                                </span>
                            <?php else: ?>
                                <span style="width: 16px; height: 16px; border: 1.5px solid #DC2626; display: inline-flex; align-items: center; justify-content: center; border-radius: 3px;" title="Non-Veg">
                                    <span style="width: 8px; height: 8px; background: #DC2626; border-radius: 50%;"></span>
                                </span>
                            <?php endif; ?>
                        </div>

                        <p style="font-size: 0.825rem; color: var(--muted-foreground); line-height: 1.4; margin-bottom: 0.5rem;">
                            <?= htmlspecialchars($item['description'] ?? 'Authentic Valima recipe with heritage spices and pure ghee.') ?>
                        </p>
                    </div>
                </div>

                <div class="p-4 pt-0 flex items-center justify-between">
                    <div>
                        <div style="font-size: 0.65rem; color: var(--muted-foreground); text-transform: uppercase; font-weight: 700;">Price</div>
                        <div style="font-size: 1.3rem; font-weight: 800; color: var(--foreground); line-height: 1;">
                            ₹<?= number_format((float)$item['base_price'], 2) ?>
                        </div>
                    </div>

                    <button type="button"
                            class="btn btn-primary btn-icon"
                            @click='openItemSheet(<?= $itemJson ?>)'
                            title="Customize & Add">
                        <?= \App\Helpers\Icon::render('plus', 'w-5 h-5') ?>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- ── ITEM CUSTOMIZATION BOTTOM SHEET / MODAL (Mockup Screen 3) ── -->
    <div x-show="sheetOpen" x-cloak style="display: none;">
        <div class="sheet-backdrop" @click="sheetOpen = false"></div>
        <div class="sheet-drawer modal-desktop p-6">
            <div class="flex justify-between items-start mb-4 pb-3 border-b border-border">
                <div>
                    <h3 class="font-serif text-xl font-bold text-foreground" x-text="activeItem ? activeItem.name : ''"></h3>
                    <div class="flex items-center gap-3 mt-1 text-xs text-muted-foreground">
                        <span class="text-amber-500 font-bold">★ 4.8 (2.3k reviews)</span>
                        <span>⏱️ 20–25 mins</span>
                    </div>
                </div>
                <button type="button" class="btn-icon btn-outline btn-sm" @click="sheetOpen = false">✕</button>
            </div>

            <!-- Variant Selector -->
            <div class="mb-5">
                <div class="form-label mb-2">Choose Variant</div>
                <div class="flex flex-col gap-2">
                    <label class="radio-card" :class="selectedVariant === 'regular' ? 'active' : ''">
                        <div class="flex items-center gap-3">
                            <input type="radio" name="variant" value="regular" x-model="selectedVariant">
                            <div>
                                <div class="font-bold text-sm">Regular</div>
                                <div class="text-xs text-muted-foreground">Portion for 1 Person</div>
                            </div>
                        </div>
                        <span class="font-bold text-foreground" x-text="'₹' + (activeItem ? activeItem.price : 0)"></span>
                    </label>

                    <label class="radio-card" :class="selectedVariant === 'family' ? 'active' : ''">
                        <div class="flex items-center gap-3">
                            <input type="radio" name="variant" value="family" x-model="selectedVariant">
                            <div>
                                <div class="font-bold text-sm">Family Pack</div>
                                <div class="text-xs text-muted-foreground">Serves 3–4 with extra meat</div>
                            </div>
                        </div>
                        <span class="font-bold text-foreground" x-text="'₹' + (activeItem ? activeItem.price + 140 : 0)"></span>
                    </label>

                    <label class="radio-card" :class="selectedVariant === 'jumbo' ? 'active' : ''">
                        <div class="flex items-center gap-3">
                            <input type="radio" name="variant" value="jumbo" x-model="selectedVariant">
                            <div>
                                <div class="font-bold text-sm">Jumbo Bucket</div>
                                <div class="text-xs text-muted-foreground">Serves 5–6 with 4 eggs & raita</div>
                            </div>
                        </div>
                        <span class="font-bold text-foreground" x-text="'₹' + (activeItem ? activeItem.price + 270 : 0)"></span>
                    </label>
                </div>
            </div>

            <!-- Add Extras Checkboxes -->
            <div class="mb-5">
                <div class="form-label mb-2">Add Extras</div>
                <div class="flex flex-col gap-2">
                    <label class="flex items-center justify-between p-3 border border-border rounded-md bg-white cursor-pointer hover:bg-muted transition">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" value="extra_chicken" x-model="selectedExtras">
                            <span class="text-sm font-semibold">Extra Chicken (100g)</span>
                        </div>
                        <span class="text-sm font-bold text-foreground">+₹60</span>
                    </label>

                    <label class="flex items-center justify-between p-3 border border-border rounded-md bg-white cursor-pointer hover:bg-muted transition">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" value="boiled_egg" x-model="selectedExtras">
                            <span class="text-sm font-semibold">Boiled Egg</span>
                        </div>
                        <span class="text-sm font-bold text-foreground">+₹20</span>
                    </label>

                    <label class="flex items-center justify-between p-3 border border-border rounded-md bg-white cursor-pointer hover:bg-muted transition">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" value="special_raita" x-model="selectedExtras">
                            <span class="text-sm font-semibold">Special Onion Raita</span>
                        </div>
                        <span class="text-sm font-bold text-foreground">+₹25</span>
                    </label>
                </div>
            </div>

            <!-- Special Instructions -->
            <div class="mb-5">
                <label class="form-label">Special Cooking Instructions</label>
                <textarea x-model="specialInstructions" placeholder="e.g. Less spicy, extra onions, mild gravy..." class="textarea"></textarea>
            </div>

            <!-- Bottom Add to Cart Bar -->
            <div class="flex items-center justify-between pt-4 border-t border-border">
                <div class="qty-stepper">
                    <button type="button" class="qty-btn" @click="itemQty = Math.max(1, itemQty - 1)">-</button>
                    <span class="qty-val" x-text="itemQty"></span>
                    <button type="button" class="qty-btn" @click="itemQty = itemQty + 1">+</button>
                </div>

                <button type="button" class="btn btn-primary" style="flex: 1; margin-left: 1rem;" @click="addToCartFromSheet()">
                    <span>Add to Cart • </span>
                    <span x-text="'₹' + calculateItemSheetTotal()"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ── CART DRAWER / SHEET (Mockup Screen 4) ── -->
    <div x-show="cartDrawerOpen" x-cloak style="display: none;">
        <div class="sheet-backdrop" @click="cartDrawerOpen = false"></div>
        <div class="sheet-drawer modal-desktop p-6">
            <div class="flex justify-between items-center mb-4 pb-3 border-b border-border">
                <div class="flex items-center gap-2">
                    <h3 class="font-serif text-xl font-bold text-foreground">My Cart</h3>
                    <span class="badge badge-gold" x-text="cartTotalItems() + ' items'"></span>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" class="text-xs text-red-600 font-bold hover:underline" @click="clearCart()">Clear All</button>
                    <button type="button" class="btn-icon btn-outline btn-sm" @click="cartDrawerOpen = false">✕</button>
                </div>
            </div>

            <!-- Cart Line Items -->
            <div style="max-height: 280px; overflow-y: auto; margin-bottom: 1.5rem;">
                <template x-for="(item, key) in cart" :key="key">
                    <div class="flex items-center justify-between py-3 border-b border-border">
                        <div class="flex-1">
                            <div class="font-bold text-sm text-foreground" x-text="item.name"></div>
                            <div class="text-xs text-muted-foreground" x-text="'Variant: ' + item.variant"></div>
                            <div class="text-xs font-semibold text-primary mt-1" x-text="'₹' + item.unitPrice + ' each'"></div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="qty-stepper">
                                <button type="button" class="qty-btn" @click="updateCartQty(key, -1)">-</button>
                                <span class="qty-val" x-text="item.qty"></span>
                                <button type="button" class="qty-btn" @click="updateCartQty(key, 1)">+</button>
                            </div>
                            <span class="font-bold text-sm w-16 text-right" x-text="'₹' + (item.unitPrice * item.qty).toFixed(2)"></span>
                        </div>
                    </div>
                </template>
                <div x-show="cartTotalItems() === 0" class="text-center py-8 text-muted-foreground">
                    Your cart is empty. Add delicious biryani from the menu!
                </div>
            </div>

            <!-- Price Breakdown -->
            <div class="p-4 rounded-lg bg-muted mb-4 text-sm" style="background: #F8F4EB;">
                <div class="flex justify-between mb-2">
                    <span class="text-muted-foreground">Subtotal</span>
                    <span class="font-bold" x-text="'₹' + cartSubtotal().toFixed(2)"></span>
                </div>
                <div class="flex justify-between mb-2 text-green-700">
                    <span>Discount</span>
                    <span class="font-bold">-₹0.00</span>
                </div>
                <div class="flex justify-between mb-2">
                    <span class="text-muted-foreground">Tax (5% GST)</span>
                    <span class="font-bold" x-text="'₹' + (cartSubtotal() * 0.05).toFixed(2)"></span>
                </div>
                <div class="flex justify-between mb-2">
                    <span class="text-muted-foreground">Delivery Fee</span>
                    <span class="font-bold">₹40.00</span>
                </div>
                <div class="flex justify-between pt-2 border-t border-border font-bold text-base text-foreground">
                    <span>Total Amount</span>
                    <span class="text-primary font-extrabold" x-text="'₹' + (cartSubtotal() * 1.05 + 40).toFixed(2)"></span>
                </div>
            </div>

            <!-- Proceed to Checkout Button -->
            <a href="/checkout" class="btn btn-primary w-full text-center" :class="cartTotalItems() === 0 ? 'opacity-50 pointer-events-none' : ''">
                Proceed to Checkout &rarr;
            </a>
        </div>
    </div>

    <!-- ── STICKY MINI CART BAR (Mobile & Desktop) ── -->
    <div x-show="cartTotalItems() > 0"
         class="fixed bottom-16 md:bottom-6 left-4 right-4 max-w-lg mx-auto z-30 card p-4 flex items-center justify-between shadow-floating"
         style="background: #14110C; border: 2px solid var(--primary); color: #FFFFFF; border-radius: var(--radius-xl);">
        <div>
            <div class="text-xs text-primary font-bold uppercase tracking-wider">Valima Cart</div>
            <div class="font-extrabold text-base">
                <span x-text="cartTotalItems() + ' items'"></span> • <span class="text-primary" x-text="'₹' + (cartSubtotal() * 1.05 + 40).toFixed(2)"></span>
            </div>
        </div>

        <button type="button" class="btn btn-primary btn-sm" @click="cartDrawerOpen = true">
            View Cart &rarr;
        </button>
    </div>
</div>

<!-- ── MENU JAVASCRIPT STATE (Alpine.js) ── -->
<script>
function menuApp() {
    return {
        selectedCategory: 'all',
        searchQuery: '',
        deliveryLocation: 'Anna Nagar, Chennai 600040',
        sheetOpen: false,
        cartDrawerOpen: false,
        activeItem: null,
        selectedVariant: 'regular',
        selectedExtras: [],
        specialInstructions: '',
        itemQty: 1,
        cart: {},

        initCart() {
            try {
                const stored = localStorage.getItem('valima_cart');
                if (stored) {
                    this.cart = JSON.parse(stored);
                }
            } catch (e) {
                this.cart = {};
            }
        },

        saveCart() {
            localStorage.setItem('valima_cart', JSON.stringify(this.cart));
            if (typeof updateGlobalCartBadge === 'function') {
                updateGlobalCartBadge();
            }
        },

        matchesFilter(catId, itemName) {
            const matchesCat = (this.selectedCategory === 'all' || this.selectedCategory === catId);
            const matchesSearch = !this.searchQuery || itemName.includes(this.searchQuery.toLowerCase());
            return matchesCat && matchesSearch;
        },

        changeLocation() {
            const loc = prompt('Enter your delivery area/pincode:', this.deliveryLocation);
            if (loc) {
                this.deliveryLocation = loc;
            }
        },

        openItemSheet(item) {
            this.activeItem = item;
            this.selectedVariant = 'regular';
            this.selectedExtras = [];
            this.specialInstructions = '';
            this.itemQty = 1;
            this.sheetOpen = true;
        },

        calculateItemSheetTotal() {
            if (!this.activeItem) return '0.00';
            let unit = this.activeItem.price;
            if (this.selectedVariant === 'family') unit += 140;
            if (this.selectedVariant === 'jumbo') unit += 270;

            if (this.selectedExtras.includes('extra_chicken')) unit += 60;
            if (this.selectedExtras.includes('boiled_egg')) unit += 20;
            if (this.selectedExtras.includes('special_raita')) unit += 25;

            return (unit * this.itemQty).toFixed(2);
        },

        addToCartFromSheet() {
            if (!this.activeItem) return;

            let unit = this.activeItem.price;
            if (this.selectedVariant === 'family') unit += 140;
            if (this.selectedVariant === 'jumbo') unit += 270;

            if (this.selectedExtras.includes('extra_chicken')) unit += 60;
            if (this.selectedExtras.includes('boiled_egg')) unit += 20;
            if (this.selectedExtras.includes('special_raita')) unit += 25;

            const cartKey = `${this.activeItem.id}_${this.selectedVariant}_${this.selectedExtras.sort().join('-')}`;

            if (this.cart[cartKey]) {
                this.cart[cartKey].qty += this.itemQty;
            } else {
                this.cart[cartKey] = {
                    id: this.activeItem.id,
                    name: this.activeItem.name,
                    variant: this.selectedVariant,
                    extras: [...this.selectedExtras],
                    notes: this.specialInstructions,
                    unitPrice: unit,
                    qty: this.itemQty
                };
            }

            this.saveCart();
            this.sheetOpen = false;
            this.cartDrawerOpen = true;
        },

        updateCartQty(key, delta) {
            if (!this.cart[key]) return;
            this.cart[key].qty += delta;
            if (this.cart[key].qty <= 0) {
                delete this.cart[key];
            }
            this.saveCart();
        },

        clearCart() {
            if (confirm('Are you sure you want to clear your cart?')) {
                this.cart = {};
                this.saveCart();
            }
        },

        cartTotalItems() {
            return Object.values(this.cart).reduce((sum, item) => sum + item.qty, 0);
        },

        cartSubtotal() {
            return Object.values(this.cart).reduce((sum, item) => sum + (item.unitPrice * item.qty), 0);
        }
    };
}
</script>
