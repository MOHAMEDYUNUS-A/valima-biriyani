<style>
    .contact-page {
        max-width: 1000px;
        margin: 3rem auto;
        padding: 0 1.5rem;
    }
    .contact-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
    }
    .contact-info-card {
        background: #fff;
        border-radius: var(--radius-md);
        padding: 2.5rem;
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
    }
    .contact-item {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        margin-bottom: 1.8rem;
    }
    .contact-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        background: var(--primary-light);
        color: var(--primary-dark);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    .btn-wa-direct {
        background: #25D366;
        color: #fff;
        padding: 12px 24px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 10px;
    }
    @media (max-width: 768px) {
        .contact-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="contact-page">
    <div style="text-align:center; margin-bottom: 2.5rem;">
        <h1 style="font-family:var(--font-display); font-size:36px; margin-bottom:8px;">Get in Touch</h1>
        <p style="color:var(--text-2); font-size:15px;">We are here to answer your catering queries, order questions, and dining reservations.</p>
    </div>

    <div class="contact-grid">
        <div class="contact-info-card">
            <h2 style="font-family:var(--font-heading); font-size:20px; margin-bottom:1.5rem;">Restaurant Details</h2>

            <div class="contact-item">
                <div class="contact-icon">📍</div>
                <div>
                    <h4 style="font-size:14px; font-weight:600; margin-bottom:4px;">Address</h4>
                    <p style="color:var(--text-2); font-size:13.5px; line-height:1.5;"><?= \App\Helpers\Security::e($app['restaurant']['address']) ?></p>
                </div>
            </div>

            <div class="contact-item">
                <div class="contact-icon">📞</div>
                <div>
                    <h4 style="font-size:14px; font-weight:600; margin-bottom:4px;">Phone & Helpline</h4>
                    <p style="color:var(--text-2); font-size:13.5px;"><?= \App\Helpers\Security::e($app['restaurant']['phone']) ?></p>
                </div>
            </div>

            <div class="contact-item">
                <div class="contact-icon">💬</div>
                <div>
                    <h4 style="font-size:14px; font-weight:600; margin-bottom:4px;">WhatsApp Direct</h4>
                    <p style="color:var(--text-2); font-size:13.5px; margin-bottom:8px;">Instant support and catering inquiries.</p>
                    <a href="https://wa.me/<?= preg_replace('/\D/', '', $app['restaurant']['whatsapp']) ?>?text=Hello%20Valima%20Biriyani,%20I%20have%20an%20enquiry" class="btn-wa-direct" target="_blank">
                        <span>Chat on WhatsApp</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="contact-info-card">
            <h2 style="font-family:var(--font-heading); font-size:20px; margin-bottom:1.5rem;">Send Us a Message</h2>
            <form action="#" method="POST" onsubmit="alert('Thank you! Your message has been received.'); return false;">
                <div class="form-group" style="margin-bottom:1.2rem;">
                    <label style="display:block; font-size:12px; font-weight:600; color:var(--text-2); text-transform:uppercase; margin-bottom:6px;">Your Name</label>
                    <input type="text" style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px;" required placeholder="e.g. Imran Khan">
                </div>
                <div class="form-group" style="margin-bottom:1.2rem;">
                    <label style="display:block; font-size:12px; font-weight:600; color:var(--text-2); text-transform:uppercase; margin-bottom:6px;">Mobile Number</label>
                    <input type="tel" style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px;" required placeholder="+91 98765 43210">
                </div>
                <div class="form-group" style="margin-bottom:1.4rem;">
                    <label style="display:block; font-size:12px; font-weight:600; color:var(--text-2); text-transform:uppercase; margin-bottom:6px;">Message or Catering Request</label>
                    <textarea rows="4" style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px;" required placeholder="How can we help you?"></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;">Submit Inquiry</button>
            </form>
        </div>
    </div>
</div>
