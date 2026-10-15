<style>
  .dine-wrap {
    width: 100%;
    max-width: 480px;
    margin: 3rem auto;
    padding: 0 1rem;
  }
  .top-banner {
    background: #b5460f;
    color: #fff;
    text-align: center;
    padding: 9px 20px;
    font-size: 11px;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    font-weight: 600;
    border-radius: 14px 14px 0 0;
  }
  .card-dine {
    background: #ffffff;
    border-radius: 0 0 20px 20px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
    border: 1px solid var(--border);
    border-top: none;
  }
  .card-hero {
    background: linear-gradient(145deg, #fff8f0 0%, #fff3e6 100%);
    padding: 2.2rem 2.2rem 1.6rem;
    border-bottom: 1px solid #f0e6d3;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
  }
  .kms-eyebrow {
    font-size: 10.5px;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    color: #b5460f;
    font-weight: 700;
    margin-bottom: 6px;
  }
  .hero-title-dine {
    font-family: var(--font-display);
    font-size: 26px;
    font-weight: 700;
    color: var(--text);
    line-height: 1.2;
  }
  .hero-sub-dine {
    font-size: 13px;
    color: var(--text-2);
    margin-top: 4px;
  }
  .hero-icon-dine {
    width: 52px;
    height: 52px;
    background: #fff;
    border-radius: 12px;
    border: 1.5px solid #f0ddd5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
  }
  .table-strip {
    background: #faf6f0;
    padding: 12px 20px;
    border-bottom: 1px solid #f0e6d3;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 13px;
  }
  .status-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #22c55e;
    display: inline-block;
    margin-right: 6px;
  }
  .status-dot.occupied {
    background: #ef4444;
  }
  .table-number-badge {
    background: #b5460f;
    color: #fff;
    padding: 4px 12px;
    border-radius: 20px;
    font-weight: 700;
    font-size: 12.5px;
  }
  .card-body-dine {
    padding: 2rem 2.2rem;
  }
  .form-group-dine {
    margin-bottom: 1.3rem;
  }
  .form-label-dine {
    display: block;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: var(--text-2);
    margin-bottom: 6px;
  }
  .form-input-dine {
    width: 100%;
    padding: 12px 14px;
    border: 1.5px solid var(--border);
    border-radius: 8px;
    font-size: 14.5px;
    font-family: inherit;
    outline: none;
    background: #fafbfc;
  }
  .form-input-dine:focus {
    border-color: #b5460f;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(181,70,15,0.12);
  }
  .submit-btn-dine {
    width: 100%;
    padding: 14px;
    background: #b5460f;
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.15s, transform 0.1s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 1.5rem;
    box-shadow: 0 4px 15px rgba(181,70,15,0.25);
  }
  .submit-btn-dine:hover {
    background: #96380a;
    transform: translateY(-1px);
  }
</style>

<div class="dine-wrap">
  <div class="top-banner">Table Ordering Terminal</div>
  <div class="card-dine">
    <div class="card-hero">
      <div>
        <div class="kms-eyebrow">Dine-in Experience</div>
        <h2 class="hero-title-dine">Welcome to Your Table</h2>
        <p class="hero-sub-dine">Enter your details to browse our live menu & place direct orders.</p>
      </div>
      <div class="hero-icon-dine">🍲</div>
    </div>

    <div class="table-strip">
      <div style="display:flex; align-items:center;">
        <span class="status-dot <?= $isFullyOccupied ? 'occupied' : '' ?>"></span>
        <span style="font-weight:600; color:var(--text);"><?= $isFullyOccupied ? 'Restaurant Full' : 'Table Assigned' ?></span>
      </div>
      <div class="table-number-badge">Table No. <?= (int)$tableId ?></div>
    </div>

    <div class="card-body-dine">
      <?php if ($isFullyOccupied): ?>
        <div style="text-align:center; padding:1.5rem 0;">
          <p style="color:#b5460f; font-weight:600; font-size:15px; margin-bottom:10px;">⚠️ Full Capacity</p>
          <p style="font-size:13.5px; color:var(--text-2); margin-bottom:1.5rem;">All dining tables are currently active. Please check back in a few minutes.</p>
          <button onclick="location.reload()" class="submit-btn-dine" style="background:#7a6550">Refresh Availability</button>
        </div>
      <?php else: ?>
        <form action="/dine-in" method="POST">
          <?= $csrf_field ?>
          <input type="hidden" name="table_id" value="<?= (int)$tableId ?>">

          <div class="form-group-dine">
            <label class="form-label-dine">Your Name *</label>
            <input class="form-input-dine" type="text" name="name" placeholder="e.g. Rajesh Kumar" required autofocus>
          </div>

          <div class="form-group-dine">
            <label class="form-label-dine">Mobile Number (+91) *</label>
            <input class="form-input-dine" type="tel" name="phone" placeholder="9876543210" required>
            <small style="color:var(--text-3); font-size:11px; margin-top:4px; display:block;">Used for order status updates and loyalty points.</small>
          </div>

          <button class="submit-btn-dine" type="submit">
            <span>Begin Dining Experience</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>
