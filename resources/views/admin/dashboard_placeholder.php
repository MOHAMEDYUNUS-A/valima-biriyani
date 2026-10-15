<div class="page-header">
    <div>
        <h1>Operational Dashboard</h1>
        <div class="sub">Welcome back, <?= \App\Helpers\Security::e($currentUser['name'] ?? 'Administrator') ?> · Role: <strong><?= strtoupper(\App\Helpers\Security::e($currentUser['role'] ?? 'owner')) ?></strong></div>
    </div>
    <div style="display:flex; gap:10px;">
        <a href="/pos" class="btn btn-primary">🖥️ Open POS Terminal</a>
        <a href="/kitchen" class="btn btn-secondary" target="_blank">👨‍🍳 Kitchen Screen</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>System Status & Security Summary</h3>
    </div>
    <p style="color:var(--text2); line-height:1.7;">
        Authentication and Role-Based Access Control (RBAC) are active and enforcing session security.
    </p>
    <div style="margin-top:15px; display:flex; gap:10px; flex-wrap:wrap;">
        <span class="badge badge-success">Session Protected</span>
        <span class="badge badge-info">CSRF Enforced</span>
        <span class="badge badge-warn">Rate Limiting Active</span>
    </div>
</div>
