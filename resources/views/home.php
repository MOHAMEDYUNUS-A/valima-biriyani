<!-- ── HERO SECTION (Matches Mockup Screen 1) ── -->
<section class="bg-dark text-white py-12 md:py-20 relative overflow-hidden">
    <div class="container grid md:grid-cols-2 items-center gap-8 md:gap-12 relative z-10" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));">
        <!-- Hero Text -->
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full mb-4" style="background: rgba(245, 184, 0, 0.15); border: 1px solid rgba(245, 184, 0, 0.3);">
                <span style="color: var(--primary);">★</span>
                <span style="font-size: 0.775rem; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 1px;">Pure Dum Biriyani Tradition</span>
            </div>

            <h1 class="font-serif text-white font-extrabold mb-4" style="font-size: clamp(2.4rem, 5vw, 3.8rem); line-height: 1.15;">
                Authentic <span style="color: var(--primary);">Biryani</span>, Rich in Tradition
            </h1>

            <p style="color: #c5bba8; font-size: 1.1rem; line-height: 1.6; max-width: 500px; margin-bottom: 2rem;">
                Experience the royal taste of freshly prepared biryani made with premium long-grain basmati, succulent meats, and authentic heritage spices.
            </p>

            <div class="flex flex-wrap items-center gap-4">
                <a href="<?= $basePath ?? '' ?>/menu" class="btn btn-primary btn-lg shadow-card">
                    <?= \App\Helpers\Icon::render('shopping-bag', 'w-5 h-5') ?>
                    <span>Order Online</span>
                </a>

                <a href="#special-menu" class="btn btn-outline-gold btn-lg">
                    <span>View Menu</span>
                </a>
            </div>
        </div>

        <!-- Hero Copper Bowl Biriyani Imagery -->
        <div class="flex justify-center relative">
            <div style="position: relative; max-width: 440px; width: 100%;">
                <!-- Soft Glow behind the handi -->
                <div style="position: absolute; inset: 10%; background: radial-gradient(circle, rgba(245, 184, 0, 0.35) 0%, transparent 70%); filter: blur(30px); border-radius: 50%; z-index: 1;"></div>
                
                <img src="<?= $basePath ?? '' ?>/assets/images/valima-mockup.jpg" alt="Authentic Valima Biriyani in Traditional Copper Handi"
                     style="position: relative; z-index: 2; width: 100%; height: auto; max-height: 380px; object-fit: cover; object-position: 23% 10%; border-radius: 20px; box-shadow: 0 20px 50px rgba(0,0,0,0.6); border: 2px solid rgba(245, 184, 0, 0.3);">

                <!-- Floating Rating Pill -->
                <div style="position: absolute; bottom: -12px; right: 10px; z-index: 3; background: #FFFFFF; color: #14110C; padding: 8px 16px; border-radius: 999px; box-shadow: var(--shadow-floating); display: flex; align-items: center; gap: 8px; border: 1px solid #E8DFCB;">
                    <span style="color: #F59E0B; font-size: 1rem;">★</span>
                    <span style="font-weight: 800; font-size: 0.95rem;">4.9</span>
                    <span style="color: #777; font-size: 0.8rem;">(5,000+ happy foodies)</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── TRUST STRIP (Matches Mockup Screen 1) ── -->
<section style="background: #FFFFFF; border-bottom: 1px solid var(--border); padding: 2rem 0;">
    <div class="container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem;">
        <div class="flex flex-col items-center text-center">
            <div class="trust-icon-circle">
                <?= \App\Helpers\Icon::render('sparkles', 'w-6 h-6') ?>
            </div>
            <h4 class="font-bold text-sm text-foreground">Fresh Ingredients Always</h4>
            <p style="font-size: 0.775rem; color: var(--muted-foreground); margin-top: 2px;">Farm-fresh tender meats & whole aromatic spices</p>
        </div>

        <div class="flex flex-col items-center text-center">
            <div class="trust-icon-circle">
                <?= \App\Helpers\Icon::render('shield-check', 'w-6 h-6') ?>
            </div>
            <h4 class="font-bold text-sm text-foreground">Hygienic & Safe Cooking</h4>
            <p style="font-size: 0.775rem; color: var(--muted-foreground); margin-top: 2px;">Strictly sanitized kitchen & sealed tamper-proof packaging</p>
        </div>

        <div class="flex flex-col items-center text-center">
            <div class="trust-icon-circle">
                <?= \App\Helpers\Icon::render('truck', 'w-6 h-6') ?>
            </div>
            <h4 class="font-bold text-sm text-foreground">Fast & Reliable Delivery</h4>
            <p style="font-size: 0.775rem; color: var(--muted-foreground); margin-top: 2px;">Hot dum biriyani delivered quickly to your doorstep</p>
        </div>

        <div class="flex flex-col items-center text-center">
            <div class="trust-icon-circle">
                <?= \App\Helpers\Icon::render('award', 'w-6 h-6') ?>
            </div>
            <h4 class="font-bold text-sm text-foreground">Best Biryani in Town</h4>
            <p style="font-size: 0.775rem; color: var(--muted-foreground); margin-top: 2px;">Celebrated dum recipe loved by generations</p>
        </div>
    </div>
</section>

<!-- ── POPULAR ITEMS (Matches Mockup Screen 2) ── -->
<section id="special-menu" class="py-12 md:py-16">
    <div class="container">
        <div class="flex items-end justify-between mb-8">
            <div>
                <span class="badge badge-gold mb-2">Our Special Menu</span>
                <h2 class="font-serif font-extrabold text-foreground" style="font-size: clamp(1.8rem, 3vw, 2.4rem);">
                    Popular Dishes
                </h2>
                <p style="color: var(--muted-foreground); font-size: 0.95rem; margin-top: 4px;">Freshly prepared batches hot from the dum pot</p>
            </div>

            <a href="<?= $basePath ?? '' ?>/menu" class="btn btn-outline btn-sm hidden sm:inline-flex">
                View All Items &rarr;
            </a>
        </div>

        <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
            <!-- Chicken Biriyani Card -->
            <div class="card card-hover flex flex-col justify-between">
                <div>
                    <div style="height: 180px; position: relative; overflow: hidden; background: #222;">
                        <img src="<?= $basePath ?? '' ?>/assets/images/valima-mockup.jpg" alt="Chicken Biryani"
                             style="width: 100%; height: 100%; object-fit: cover; object-position: 70% 8%;">
                        <div style="position: absolute; top: 12px; left: 12px; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); padding: 3px 8px; border-radius: 999px; font-size: 0.75rem; font-weight: 700; color: #FFF; display: flex; align-items: center; gap: 4px;">
                            <span style="color: #F59E0B;">★</span> 4.8 (2.3k)
                        </div>
                        <div style="position: absolute; top: 12px; right: 12px; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); padding: 3px 8px; border-radius: 999px; font-size: 0.75rem; font-weight: 600; color: #FFF; display: flex; align-items: center; gap: 4px;">
                            <?= \App\Helpers\Icon::render('clock', 'w-3 h-3 text-primary') ?> 20–25 mins
                        </div>
                    </div>

                    <div class="p-5">
                        <div class="flex items-center justify-between mb-1">
                            <h3 class="font-serif font-bold text-lg text-foreground">Chicken Biryani</h3>
                            <span style="width: 16px; height: 16px; border: 1.5px solid #DC2626; display: inline-flex; align-items: center; justify-content: center; border-radius: 3px;" title="Non-Veg">
                                <span style="width: 8px; height: 8px; background: #DC2626; border-radius: 50%;"></span>
                            </span>
                        </div>
                        <p style="font-size: 0.85rem; color: var(--muted-foreground); line-height: 1.5; margin-bottom: 1rem;">
                            Aromatic basmati rice cooked with tender chicken pieces, exotic spices, boiled egg and rich mint gravy.
                        </p>
                    </div>
                </div>

                <div class="p-5 pt-0 flex items-center justify-between">
                    <div>
                        <div style="font-size: 0.7rem; color: var(--muted-foreground); text-transform: uppercase; font-weight: 700;">Starts At</div>
                        <div style="font-size: 1.4rem; font-weight: 800; color: var(--foreground); line-height: 1;">₹190</div>
                    </div>
                    <a href="<?= $basePath ?? '' ?>/menu" class="btn btn-primary btn-icon" title="Choose Variant & Add">
                        <?= \App\Helpers\Icon::render('plus', 'w-5 h-5') ?>
                    </a>
                </div>
            </div>

            <!-- Mutton Biriyani Card -->
            <div class="card card-hover flex flex-col justify-between">
                <div>
                    <div style="height: 180px; position: relative; overflow: hidden; background: #222;">
                        <img src="<?= $basePath ?? '' ?>/assets/images/valima-mockup.jpg" alt="Mutton Biryani"
                             style="width: 100%; height: 100%; object-fit: cover; object-position: 25% 10%;">
                        <div style="position: absolute; top: 12px; left: 12px; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); padding: 3px 8px; border-radius: 999px; font-size: 0.75rem; font-weight: 700; color: #FFF; display: flex; align-items: center; gap: 4px;">
                            <span style="color: #F59E0B;">★</span> 4.9 (1.8k)
                        </div>
                        <div style="position: absolute; top: 12px; right: 12px; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); padding: 3px 8px; border-radius: 999px; font-size: 0.75rem; font-weight: 600; color: #FFF; display: flex; align-items: center; gap: 4px;">
                            <?= \App\Helpers\Icon::render('clock', 'w-3 h-3 text-primary') ?> 25–30 mins
                        </div>
                    </div>

                    <div class="p-5">
                        <div class="flex items-center justify-between mb-1">
                            <h3 class="font-serif font-bold text-lg text-foreground">Mutton Biryani</h3>
                            <span style="width: 16px; height: 16px; border: 1.5px solid #DC2626; display: inline-flex; align-items: center; justify-content: center; border-radius: 3px;" title="Non-Veg">
                                <span style="width: 8px; height: 8px; background: #DC2626; border-radius: 50%;"></span>
                            </span>
                        </div>
                        <p style="font-size: 0.85rem; color: var(--muted-foreground); line-height: 1.5; margin-bottom: 1rem;">
                            Slow-cooked juicy tender mutton pieces marinated in traditional herbs, layered over long fragrant basmati.
                        </p>
                    </div>
                </div>

                <div class="p-5 pt-0 flex items-center justify-between">
                    <div>
                        <div style="font-size: 0.7rem; color: var(--muted-foreground); text-transform: uppercase; font-weight: 700;">Starts At</div>
                        <div style="font-size: 1.4rem; font-weight: 800; color: var(--foreground); line-height: 1;">₹280</div>
                    </div>
                    <a href="<?= $basePath ?? '' ?>/menu" class="btn btn-primary btn-icon" title="Choose Variant & Add">
                        <?= \App\Helpers\Icon::render('plus', 'w-5 h-5') ?>
                    </a>
                </div>
            </div>

            <!-- Veg Fried Rice Card -->
            <div class="card card-hover flex flex-col justify-between">
                <div>
                    <div style="height: 180px; position: relative; overflow: hidden; background: #222;">
                        <img src="<?= $basePath ?? '' ?>/assets/images/valima-mockup.jpg" alt="Veg Fried Rice"
                             style="width: 100%; height: 100%; object-fit: cover; object-position: 70% 8%;">
                        <div style="position: absolute; top: 12px; left: 12px; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); padding: 3px 8px; border-radius: 999px; font-size: 0.75rem; font-weight: 700; color: #FFF; display: flex; align-items: center; gap: 4px;">
                            <span style="color: #F59E0B;">★</span> 4.7 (950)
                        </div>
                        <div style="position: absolute; top: 12px; right: 12px; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); padding: 3px 8px; border-radius: 999px; font-size: 0.75rem; font-weight: 600; color: #FFF; display: flex; align-items: center; gap: 4px;">
                            <?= \App\Helpers\Icon::render('clock', 'w-3 h-3 text-primary') ?> 15–20 mins
                        </div>
                    </div>

                    <div class="p-5">
                        <div class="flex items-center justify-between mb-1">
                            <h3 class="font-serif font-bold text-lg text-foreground">Veg Fried Rice</h3>
                            <span style="width: 16px; height: 16px; border: 1.5px solid #16A34A; display: inline-flex; align-items: center; justify-content: center; border-radius: 3px;" title="Pure Veg">
                                <span style="width: 8px; height: 8px; background: #16A34A; border-radius: 50%;"></span>
                            </span>
                        </div>
                        <p style="font-size: 0.85rem; color: var(--muted-foreground); line-height: 1.5; margin-bottom: 1rem;">
                            Wok-tossed basmati rice with crunchy fresh garden vegetables, spring onions, and oriental seasoning.
                        </p>
                    </div>
                </div>

                <div class="p-5 pt-0 flex items-center justify-between">
                    <div>
                        <div style="font-size: 0.7rem; color: var(--muted-foreground); text-transform: uppercase; font-weight: 700;">Starts At</div>
                        <div style="font-size: 1.4rem; font-weight: 800; color: var(--foreground); line-height: 1;">₹160</div>
                    </div>
                    <a href="<?= $basePath ?? '' ?>/menu" class="btn btn-primary btn-icon" title="Choose Variant & Add">
                        <?= \App\Helpers\Icon::render('plus', 'w-5 h-5') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
