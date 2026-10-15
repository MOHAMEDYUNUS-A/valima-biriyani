<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Valima Biriyani — Design System & Component Styleguide</title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <script src="/assets/js/alpine.min.js" defer></script>
</head>
<body class="bg-background text-foreground" x-data="{ modalOpen: false, qty: 2, variant: 'regular' }">

    <!-- HEADER -->
    <header class="bg-dark text-white px-6 py-4 flex items-center justify-between border-b border-dark">
        <div class="flex items-center gap-3">
            <img src="/assets/images/valima-logo.jpg" alt="Valima Biriyani" style="height: 42px; border-radius: 6px;">
            <div>
                <h1 class="text-white font-serif" style="font-size: 1.25rem; font-weight: 800; line-height: 1;">VALIMA BIRIYANI</h1>
                <p style="font-size: 0.75rem; color: var(--primary); letter-spacing: 1.5px; text-transform: uppercase;">UI/UX Design System</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="/" class="btn btn-outline btn-sm text-white" style="border-color: #333;">Home</a>
            <a href="/menu" class="btn btn-primary btn-sm">Menu Page &rarr;</a>
        </div>
    </header>

    <main class="container py-8">
        <div class="mb-8">
            <h2 class="font-serif" style="font-size: 2.25rem; font-weight: 800; color: var(--foreground); margin-bottom: 0.5rem;">
                Design Tokens & Component Library
            </h2>
            <p style="color: var(--muted-foreground); font-size: 1.05rem;">
                Official shadcn-style component library matching the approved Valima Biriyani mockup (Cream #FBF6EC, Gold #F5B800, Dark #14110C, Copper #B8651B).
            </p>
        </div>

        <!-- 1. COLOR TOKENS -->
        <section class="card p-6 mb-8">
            <h3 class="font-serif text-lg font-bold mb-4">1. Design Color Swatches</h3>
            <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 1rem;">
                <div class="p-4 rounded-md shadow-soft" style="background: #FBF6EC; border: 1px solid #E8DFCB;">
                    <div style="font-size: 0.8rem; font-weight: 700;">Cream #FBF6EC</div>
                    <div style="font-size: 0.7rem; color: #666;">Background</div>
                </div>
                <div class="p-4 rounded-md text-white shadow-soft" style="background: #14110C;">
                    <div style="font-size: 0.8rem; font-weight: 700;">Dark #14110C</div>
                    <div style="font-size: 0.7rem; color: #AAA;">Header/Surface</div>
                </div>
                <div class="p-4 rounded-md shadow-soft" style="background: #F5B800; color: #14110C;">
                    <div style="font-size: 0.8rem; font-weight: 800;">Gold #F5B800</div>
                    <div style="font-size: 0.7rem; color: #333;">Primary CTA</div>
                </div>
                <div class="p-4 rounded-md text-white shadow-soft" style="background: #B8651B;">
                    <div style="font-size: 0.8rem; font-weight: 700;">Copper #B8651B</div>
                    <div style="font-size: 0.7rem; color: #DDD;">Food Accents</div>
                </div>
                <div class="p-4 rounded-md shadow-soft" style="background: #F1EADB; border: 1px solid #E8DFCB;">
                    <div style="font-size: 0.8rem; font-weight: 700;">Muted #F1EADB</div>
                    <div style="font-size: 0.7rem; color: #6F6554;">Pills/Borders</div>
                </div>
                <div class="p-4 rounded-md text-white shadow-soft" style="background: #16A34A;">
                    <div style="font-size: 0.8rem; font-weight: 700;">Green #16A34A</div>
                    <div style="font-size: 0.7rem; color: #DDD;">Success/Veg</div>
                </div>
            </div>
        </section>

        <!-- 2. BUTTONS -->
        <section class="card p-6 mb-8">
            <h3 class="font-serif text-lg font-bold mb-4">2. Buttons & States</h3>
            <div class="flex flex-wrap items-center gap-3 mb-4">
                <?= \App\Helpers\Component::render('button', ['text' => 'Order Online', 'variant' => 'primary', 'icon' => 'shopping-bag']) ?>
                <?= \App\Helpers\Component::render('button', ['text' => 'Dark Secondary', 'variant' => 'secondary']) ?>
                <?= \App\Helpers\Component::render('button', ['text' => 'Outline Button', 'variant' => 'outline']) ?>
                <?= \App\Helpers\Component::render('button', ['text' => 'View Menu', 'variant' => 'outline-gold', 'icon' => 'utensils']) ?>
                <?= \App\Helpers\Component::render('button', ['text' => 'Cancel Order', 'variant' => 'destructive']) ?>
                <?= \App\Helpers\Component::render('button', ['text' => 'Small Pill', 'variant' => 'primary', 'size' => 'sm']) ?>
                <?= \App\Helpers\Component::render('button', ['text' => 'Large Hero CTA', 'variant' => 'primary', 'size' => 'lg', 'icon' => 'sparkles']) ?>
            </div>
        </section>

        <!-- 3. STATUS BADGES -->
        <section class="card p-6 mb-8">
            <h3 class="font-serif text-lg font-bold mb-4">3. Status Badges</h3>
            <div class="flex flex-wrap items-center gap-3">
                <?= \App\Helpers\Component::render('badge', ['text' => 'Placed', 'variant' => 'gold', 'icon' => 'clock']) ?>
                <?= \App\Helpers\Component::render('badge', ['text' => 'Confirmed', 'variant' => 'blue', 'icon' => 'check']) ?>
                <?= \App\Helpers\Component::render('badge', ['text' => 'Preparing', 'variant' => 'gold', 'icon' => 'flame']) ?>
                <?= \App\Helpers\Component::render('badge', ['text' => 'Ready', 'variant' => 'green', 'icon' => 'check']) ?>
                <?= \App\Helpers\Component::render('badge', ['text' => 'Out for Delivery', 'variant' => 'gold', 'icon' => 'truck']) ?>
                <?= \App\Helpers\Component::render('badge', ['text' => 'Delivered', 'variant' => 'green', 'icon' => 'shield-check']) ?>
                <?= \App\Helpers\Component::render('badge', ['text' => 'Cancelled', 'variant' => 'red', 'icon' => 'x']) ?>
            </div>
        </section>

        <!-- 4. STAT CARDS & SVG CHARTS -->
        <section class="mb-8">
            <h3 class="font-serif text-lg font-bold mb-4">4. Dashboard Metrics & Pure SVG Charts</h3>
            <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                <?= \App\Helpers\Component::render('stat_card', ['label' => 'Total Orders', 'value' => '48', 'trend' => '+12%', 'color' => 'gold']) ?>
                <?= \App\Helpers\Component::render('stat_card', ['label' => 'Total Sales', 'value' => '₹24,560', 'trend' => '+18%', 'color' => 'green']) ?>
                <?= \App\Helpers\Component::render('stat_card', ['label' => 'Pending Orders', 'value' => '6', 'color' => 'gold']) ?>
                <?= \App\Helpers\Component::render('stat_card', ['label' => 'Cancelled Orders', 'value' => '2', 'color' => 'red']) ?>
            </div>

            <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
                <?= \App\Helpers\Component::render('svg_donut_chart', ['total' => 48]) ?>
                <?= \App\Helpers\Component::render('svg_bar_chart') ?>
            </div>
        </section>

        <!-- 5. INTERACTIVE REACTIVE DEMO (Alpine.js) -->
        <section class="card p-6 mb-8">
            <h3 class="font-serif text-lg font-bold mb-4">5. Interactive Bottom Sheet Modal & Quantity Stepper</h3>
            <div class="flex items-center gap-4">
                <button type="button" class="btn btn-primary" @click="modalOpen = true">
                    Open Item Detail Bottom Sheet &rarr;
                </button>

                <div class="qty-stepper">
                    <button type="button" class="qty-btn" @click="qty = Math.max(1, qty - 1)">-</button>
                    <span class="qty-val" x-text="qty"></span>
                    <button type="button" class="qty-btn" @click="qty = qty + 1">+</button>
                </div>
            </div>

            <!-- Bottom Sheet Modal Markup -->
            <div x-show="modalOpen" x-cloak style="display: none;">
                <div class="sheet-backdrop" @click="modalOpen = false"></div>
                <div class="sheet-drawer modal-desktop p-6">
                    <div class="flex justify-between items-center mb-4 pb-3 border-b border-border">
                        <h4 class="font-serif text-xl font-bold">Chicken Biryani Customization</h4>
                        <button type="button" class="btn-icon btn-outline btn-sm" @click="modalOpen = false">✕</button>
                    </div>

                    <div class="mb-4">
                        <div class="form-label">Choose Variant</div>
                        <div class="flex flex-col gap-2">
                            <label class="radio-card" :class="variant === 'regular' ? 'active' : ''">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="demo_variant" value="regular" x-model="variant">
                                    <span class="font-bold">Regular (1 Person)</span>
                                </div>
                                <span class="font-bold text-primary">₹180</span>
                            </label>

                            <label class="radio-card" :class="variant === 'family' ? 'active' : ''">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="demo_variant" value="family" x-model="variant">
                                    <span class="font-bold">Family Pack (3-4 Persons)</span>
                                </div>
                                <span class="font-bold text-primary">₹320</span>
                            </label>

                            <label class="radio-card" :class="variant === 'jumbo' ? 'active' : ''">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="demo_variant" value="jumbo" x-model="variant">
                                    <span class="font-bold">Jumbo Bucket (5-6 Persons)</span>
                                </div>
                                <span class="font-bold text-primary">₹450</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-border">
                        <div class="qty-stepper">
                            <button type="button" class="qty-btn" @click="qty = Math.max(1, qty - 1)">-</button>
                            <span class="qty-val" x-text="qty"></span>
                            <button type="button" class="qty-btn" @click="qty = qty + 1">+</button>
                        </div>

                        <button type="button" class="btn btn-primary" @click="modalOpen = false; alert('Added ' + qty + 'x ' + variant + ' Chicken Biryani to cart!')">
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
