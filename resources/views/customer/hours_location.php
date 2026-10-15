<style>
    .info-wrap {
        max-width: 850px;
        margin: 3rem auto;
        padding: 0 1.5rem;
    }
    .info-card {
        background: #fff;
        border-radius: var(--radius-md);
        padding: 2.2rem;
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        margin-bottom: 2rem;
    }
    .hours-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }
    .hours-table td {
        padding: 12px 0;
        border-bottom: 1px dashed var(--border);
        font-size: 14.5px;
    }
    .hours-table tr:last-child td {
        border-bottom: none;
    }
</style>

<div class="info-wrap">
    <div style="text-align:center; margin-bottom:2.5rem;">
        <h1 style="font-family:var(--font-display); font-size:36px; margin-bottom:8px;">Hours & Dining Location</h1>
        <p style="color:var(--text-2); font-size:15px;">Visit us for dine-in lunch, evening specials, and late-night biriyani cravings.</p>
    </div>

    <div class="info-card">
        <h2 style="font-family:var(--font-heading); font-size:20px; color:var(--text);">Weekly Service Schedule</h2>
        <table class="hours-table">
            <tr>
                <td><strong>Monday – Thursday</strong></td>
                <td style="text-align:right; color:var(--text-2);">11:00 AM – 11:00 PM</td>
            </tr>
            <tr>
                <td><strong>Friday (Special Jummah Dum)</strong></td>
                <td style="text-align:right; color:var(--primary); font-weight:600;">11:30 AM – 11:30 PM</td>
            </tr>
            <tr>
                <td><strong>Saturday & Sunday (Weekend Feast)</strong></td>
                <td style="text-align:right; color:var(--primary); font-weight:600;">11:00 AM – 11:45 PM</td>
            </tr>
        </table>
    </div>

    <div class="info-card">
        <h2 style="font-family:var(--font-heading); font-size:20px; color:var(--text); margin-bottom:12px;">Restaurant Address</h2>
        <p style="color:var(--text-2); font-size:15px; line-height:1.6; margin-bottom:18px;">
            <?= \App\Helpers\Security::e($app['restaurant']['address']) ?>
        </p>
        <div style="background:#fdfaf7; border:1px solid var(--border); border-radius:10px; padding:20px; text-align:center;">
            <p style="font-size:14px; color:var(--text-2); margin-bottom:12px;">🗺️ Open in navigation apps for driving directions:</p>
            <a href="https://maps.google.com/?q=<?= urlencode($app['restaurant']['address']) ?>" target="_blank" class="btn btn-primary" style="display:inline-flex;">Open in Google Maps</a>
        </div>
    </div>
</div>
