<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-serif font-bold text-foreground">Integrations & Settings</h1>
            <p class="text-sm text-muted-foreground">Manage payment gateways, third-party webhook automation, WhatsApp templates, and store settings.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                System Operational
            </span>
        </div>
    </div>

    <!-- Integrations Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Razorpay Gateway Card -->
        <div class="card p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-[#02042B] flex items-center justify-center p-2 text-white font-bold text-xs tracking-wider">
                            RZP
                        </div>
                        <div>
                            <h3 class="font-bold text-foreground">Razorpay Payment Gateway</h3>
                            <p class="text-xs text-muted-foreground">UPI, Credit/Debit Cards, NetBanking & Wallets</p>
                        </div>
                    </div>
                    <?= \App\Helpers\Component::render('badge', [
                        'text' => $integrations['razorpay']['label'],
                        'variant' => $integrations['razorpay']['badge']
                    ]) ?>
                </div>
                <p class="text-xs text-muted-foreground mb-4">
                    Authorizes online customer payments with HMAC-SHA256 signature verification. Safe fallback to Cash/COD when keys are unconfigured.
                </p>
            </div>
            <div class="pt-4 border-t border-border flex items-center justify-between text-xs text-muted-foreground">
                <span>Webhook Endpoint:</span>
                <code class="bg-muted px-2 py-0.5 rounded text-[11px] font-mono">/api/webhooks/razorpay</code>
            </div>
        </div>

        <!-- WhatsApp Messaging Card -->
        <div class="card p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-[#25D366] text-white flex items-center justify-center">
                            <?= \App\Helpers\Icon::render('phone', 'w-5 h-5') ?>
                        </div>
                        <div>
                            <h3 class="font-bold text-foreground">WhatsApp Notifications</h3>
                            <p class="text-xs text-muted-foreground">Order updates & delivery alerts</p>
                        </div>
                    </div>
                    <?= \App\Helpers\Component::render('badge', [
                        'text' => $integrations['whatsapp']['label'],
                        'variant' => $integrations['whatsapp']['badge']
                    ]) ?>
                </div>
                <p class="text-xs text-muted-foreground mb-4">
                    Utilizes free <code class="text-xs bg-muted px-1.5 py-0.5 rounded font-mono">https://wa.me/</code> pre-filled links for 100% free messaging, with optional Meta Cloud API adapter support.
                </p>
            </div>
            <div class="pt-4 border-t border-border flex items-center justify-between text-xs text-muted-foreground">
                <span>Cost:</span>
                <span class="font-semibold text-emerald-700">₹0.00 / Free Tier Active</span>
            </div>
        </div>

        <!-- Make.com Automation Card -->
        <div class="card p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-[#6d28d9] text-white flex items-center justify-center font-bold text-xs">
                            M
                        </div>
                        <div>
                            <h3 class="font-bold text-foreground">Make.com Webhooks</h3>
                            <p class="text-xs text-muted-foreground">Workflow automation & CRM triggers</p>
                        </div>
                    </div>
                    <?= \App\Helpers\Component::render('badge', [
                        'text' => $integrations['make']['label'],
                        'variant' => $integrations['make']['badge']
                    ]) ?>
                </div>
                <p class="text-xs text-muted-foreground mb-4">
                    Dispatches asynchronous event payloads on <code class="bg-muted px-1 py-0.5 rounded text-[11px] font-mono">order.confirmed</code> and <code class="bg-muted px-1 py-0.5 rounded text-[11px] font-mono">order.delivered</code> with SHA-256 HMAC signature verification.
                </p>
            </div>
            <div class="pt-4 border-t border-border flex items-center justify-between text-xs text-muted-foreground">
                <span>Trigger Event:</span>
                <span class="font-mono text-xs">POST &lt;Webhook_URL&gt;</span>
            </div>
        </div>

        <!-- Email & SMTP Card -->
        <div class="card p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-[#0284c7] text-white flex items-center justify-center">
                            <?= \App\Helpers\Icon::render('mail', 'w-5 h-5') ?>
                        </div>
                        <div>
                            <h3 class="font-bold text-foreground">Email / SMTP Invoicing</h3>
                            <p class="text-xs text-muted-foreground">Customer receipt delivery</p>
                        </div>
                    </div>
                    <?= \App\Helpers\Component::render('badge', [
                        'text' => $integrations['mail']['label'],
                        'variant' => $integrations['mail']['badge']
                    ]) ?>
                </div>
                <p class="text-xs text-muted-foreground mb-4">
                    Sends automated PDF/HTML tax invoices upon order completion. Configurable via `.env` SMTP credentials.
                </p>
            </div>
            <div class="pt-4 border-t border-border flex items-center justify-between text-xs text-muted-foreground">
                <span>Status:</span>
                <span class="font-medium"><?= \App\Helpers\Security::e($integrations['mail']['label']) ?></span>
            </div>
        </div>
    </div>

    <!-- Integration Flow Diagram (Mockup Screen 13) -->
    <div class="card p-6 bg-amber-50/40 border border-amber-200">
        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-amber-200">
            <?= \App\Helpers\Icon::render('sparkles', 'w-5 h-5 text-copper') ?>
            <h3 class="font-bold text-foreground">Automated Integration Flow</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div class="flex items-start gap-3">
                    <div class="w-7 h-7 rounded-full bg-primary text-foreground font-bold text-xs flex items-center justify-center flex-shrink-0 mt-0.5">
                        1
                    </div>
                    <div>
                        <h4 class="font-semibold text-foreground text-sm">Order Confirmed</h4>
                        <ul class="mt-1 text-xs text-muted-foreground list-disc list-inside space-y-1">
                            <li>Make.com Webhook dispatched</li>
                            <li>WhatsApp Order Confirmation via Click-to-Chat / API</li>
                            <li>KDS Ticket pushed to kitchen display</li>
                        </ul>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="w-7 h-7 rounded-full bg-emerald-500 text-white font-bold text-xs flex items-center justify-center flex-shrink-0 mt-0.5">
                        2
                    </div>
                    <div>
                        <h4 class="font-semibold text-foreground text-sm">Order Delivered</h4>
                        <ul class="mt-1 text-xs text-muted-foreground list-disc list-inside space-y-1">
                            <li>Make.com Webhook for analytics and CRM sync</li>
                            <li>Feedback link sent via WhatsApp</li>
                            <li>10 Valima Royalty Points credited to customer balance</li>
                            <li>Invoice Email (SMTP)</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="bg-card p-4 rounded-xl border border-border">
                <h4 class="text-xs font-bold text-muted-foreground uppercase tracking-wider mb-2">Key System Features</h4>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="flex items-center gap-1.5 text-foreground font-medium">
                        <span class="text-emerald-600 font-bold">✓</span> Location Detection
                    </div>
                    <div class="flex items-center gap-1.5 text-foreground font-medium">
                        <span class="text-emerald-600 font-bold">✓</span> Razorpay Payment
                    </div>
                    <div class="flex items-center gap-1.5 text-foreground font-medium">
                        <span class="text-emerald-600 font-bold">✓</span> Real-time Order Tracking
                    </div>
                    <div class="flex items-center gap-1.5 text-foreground font-medium">
                        <span class="text-emerald-600 font-bold">✓</span> WhatsApp Notifications
                    </div>
                    <div class="flex items-center gap-1.5 text-foreground font-medium">
                        <span class="text-emerald-600 font-bold">✓</span> Feedback Collection
                    </div>
                    <div class="flex items-center gap-1.5 text-foreground font-medium">
                        <span class="text-emerald-600 font-bold">✓</span> Email Invoice
                    </div>
                    <div class="flex items-center gap-1.5 text-foreground font-medium">
                        <span class="text-emerald-600 font-bold">✓</span> Secure & Scalable
                    </div>
                    <div class="flex items-center gap-1.5 text-foreground font-medium">
                        <span class="text-emerald-600 font-bold">✓</span> Loyalty Points Engine
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Store Profile Configuration -->
    <div class="card p-6">
        <h3 class="font-bold text-foreground mb-4 pb-2 border-b border-border">Restaurant Profile & Business Rules</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
            <div>
                <span class="text-xs text-muted-foreground block">Store Name</span>
                <span class="font-semibold text-foreground"><?= \App\Helpers\Security::e($config['store_name']) ?></span>
            </div>
            <div>
                <span class="text-xs text-muted-foreground block">Tagline</span>
                <span class="font-semibold text-foreground"><?= \App\Helpers\Security::e($config['store_tagline']) ?></span>
            </div>
            <div>
                <span class="text-xs text-muted-foreground block">Currency</span>
                <span class="font-semibold text-foreground"><?= \App\Helpers\Security::e($config['currency']) ?></span>
            </div>
            <div>
                <span class="text-xs text-muted-foreground block">Tax Policy</span>
                <span class="font-semibold text-foreground"><?= \App\Helpers\Security::e($config['tax_rate']) ?></span>
            </div>
            <div>
                <span class="text-xs text-muted-foreground block">Delivery Fee</span>
                <span class="font-semibold text-foreground"><?= \App\Helpers\Security::e($config['delivery_fee']) ?></span>
            </div>
            <div>
                <span class="text-xs text-muted-foreground block">Support Phone</span>
                <span class="font-semibold text-foreground"><?= \App\Helpers\Security::e($config['phone']) ?></span>
            </div>
            <div class="sm:col-span-2 lg:col-span-3">
                <span class="text-xs text-muted-foreground block">Store Address</span>
                <span class="font-semibold text-foreground"><?= \App\Helpers\Security::e($config['address']) ?></span>
            </div>
        </div>
    </div>
</div>
