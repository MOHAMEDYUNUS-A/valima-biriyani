# VALIMA BIRIYANI — MASTER IMPLEMENTATION CHECKLIST

Last Updated: Phase 1 Inspection & Architecture Plan Complete

---

## Status Legend
- ✅ **COMPLETED** — Implemented, verified, and tested.
- ⏳ **PENDING** — Ready for development in the current/upcoming phase.
- 🚫 **BLOCKED** — Dependent on real hardware or third-party paid/approved credentials (documented fallback provided).

---

## Phase Breakdown

### Phase 1: Inspection & Security Assessment
- [x] Inspect existing project codebase (`db.php`, `index.php`, `menu.php`, `place_order.php`, `track.php`, `update_status.php`, `get_latest_order.php`, `kitchen.php`, `dashboard.php`).
- [x] Identify critical vulnerabilities (plaintext credentials, SQLi, XSS, lack of auth, CSRF, client pricing trust, race conditions).
- [x] Install & verify local execution environment (PHP 8.2.33 CLI + PDO + MySQLnd + SQLite + Composer).
- [x] Initialize Git version control.
- [x] Produce comprehensive Phase 1 Inspection & Architecture Report.
- [x] Generate initial `CHECKLIST.md` and `README.md`.

### Phase 2: Architecture Foundation & Core Framework
- [x] Establish directory structure (`app/`, `config/`, `database/`, `public/`, `resources/views/`, `routes/`, `storage/`, `tests/`, `legacy/`).
- [x] Safe environment configuration (`.env`, `.env.example`, environment loader with secure fallbacks).
- [x] Secure HTTP Routing engine (RESTful methods, URL parameters, CSRF protection, error handler).
- [x] PDO Database wrapper with transaction management, prepared statements, and query logging.
- [x] Base MVC components: BaseController, View, Request, Response, Session, Security, Logger.
- [x] Error & Exception Handling (safe production error pages, debug logs in `storage/logs/`).
- [x] Modern, responsive master layout templates preserving existing warm orange/gold restaurant aesthetics (`main.php`, `admin.php`).
- [x] 18 Automated Foundation Unit/Integration Tests executed and passing cleanly (0 failures).
- [x] Test web server verification on `/` (200 OK, 10.4KB) and `/health` (JSON operational).

### Phase 3: Database Migrations & Legacy Data Migration
- [x] Migration runner engine (`database/MigrationRunner.php`, `database/Migration.php`, CLI `bin/migrate.php`).
- [x] Schema migrations:
  - `001_create_core_auth_tables.php`: `users`, `roles`, `permissions`, `user_roles`, `audit_logs`, `rate_limits`.
  - `002_create_dining_and_menu_tables.php`: `restaurant_tables`, `menu_categories`, `menu_items`, `menu_item_variants`, `menu_addons`, `menu_item_addons`.
  - `003_create_customers_and_loyalty_tables.php`: `customers`, `customer_addresses`, `customer_consents`, `loyalty_settings`, `loyalty_transactions`.
  - `004_create_orders_and_billing_tables.php`: `orders`, `order_items`, `order_item_addons`, `order_status_events`, `payments`, `refunds`, `idempotency_keys`.
  - `005_create_kitchen_delivery_and_system_tables.php`: `kitchen_tickets`, `print_jobs`, `delivery_bookings`, `notification_logs`, `webhook_events`, `restaurant_settings`.
- [x] Seeders: Roles (owner, manager, cashier, kitchen, delivery), Admin User (`admin@valimabiriyani.com` / `Admin@Valima2026`), Tables 1–8, Sample Menu Items with exact existing prices (Biriyani 190/280, Fried Rice 160/200, Noodles 160/200), Add-ons, and Settings.
- [x] Legacy Data Migration Engine (`database/LegacyDataMigrator.php`) with safe non-destructive backups (`_backup_legacy_*`), status translation, and total recalculation.
- [x] 47 Automated Database Migration Tests executed and passing cleanly (0 failures).
- [x] Local database initialized and verified.

### Phase 4: Authentication, Authorization & Security Hardening
- [x] Staff authentication (`password_hash` / `password_verify`, Bcrypt).
- [x] Session security (`Session::regenerate()` on login, HttpOnly, SameSite=Lax, session timeout).
- [x] Role-Based Access Control (RBAC): Owner, Manager, Cashier/POS, Kitchen, Delivery.
- [x] Role-protect routes (`/admin`, `/pos`, `/kitchen`, `/delivery`) via `AuthMiddleware` and `RoleMiddleware`.
- [x] Persistent Rate limiting on login attempts via `rate_limits` table (IP + email tracking).
- [x] CSRF middleware on all state-changing endpoints (POST/PUT/DELETE) with token verification.
- [x] Comprehensive Audit Logger (`Logger::audit()`) writing to daily audit logs and database table.
- [x] Responsive, branded staff login view (`resources/views/auth/login.php`).
- [x] 13 Automated Auth & Security Tests executed and passing cleanly (0 failures).
- [x] HTTP Verification: `/login` renders 200 with CSRF; `/admin` returns 302 redirecting unauthenticated requests.

### Phase 5: Public Website & Menu Management
- [x] Public restaurant storefront: Home, About, Menu, Contact, Hours & Location, FAQ.
- [x] Policy pages: Privacy Policy, Terms of Service, Refund & Cancellation Policy, Delivery Policy.
- [x] Dynamic Menu Management in Admin:
  - List all menu categories and items with thumbnails.
  - CRUD Menu Items with validation, base price, promo price, prep time, veg/non-veg tags.
  - Secure image upload (MIME validation, extension check, 3MB limit, non-executable storage in `public/uploads/menu/`).
  - Real-time Availability toggle (instantly toggle In Stock / Out of Stock).
- [x] Customer Menu browsing reading directly from MySQL/SQLite database (replaces legacy JS hardcoded array).
- [x] Public JSON APIs: `GET /api/menu`, `GET /api/menu/categories`.
- [x] 17 Automated Menu & Storefront Tests executed and passing cleanly (0 failures).
- [x] 95 Total automated tests passing across Phases 1 through 5.

### Phase 6: Customer Ordering & Secure Checkout
- [ ] Dine-in table allocation with database row locking (`FOR UPDATE`) to eliminate race conditions.
- [ ] Multi-channel order type support: Dine-in, Takeaway/Pickup, Home Delivery.
- [ ] Customer phone & name normalization (+91 standard, unverified guest tracking).
- [ ] Server-side cart validation and recalculation (server prices only, zero client price trust).
- [ ] Idempotency key validation to prevent double-click duplicate orders.
- [ ] Order number generation (`VAL-YYYYMMDD-XXXX`).
- [ ] Cryptographic random tracking token generation (`random_bytes(32)`).
- [ ] Status event timeline initialization (`Order Taken`).
- [ ] Cash / UPI at Counter payment recording.

### Phase 7: Central Admin Orders, POS & Browser Receipts
- [ ] Central Orders Management view (Live search, channel filter, status filter, date filter, pagination).
- [ ] Fast Point-of-Sale (POS) interface:
  - Instant item search & quick category tabs.
  - Live bill builder with server recalculation.
  - Customer lookup & guest order support.
  - Payment collection (Cash, Card, UPI).
  - Role-gated discount approval.
- [ ] Printable Invoice & Receipt (Browser CSS print template, thermal 58mm & 80mm support).
- [ ] Order cancellation & refund handling with permission checks and audit logs.

### Phase 8: Kitchen Display System (KDS) & KOT Queue
- [ ] Modern 3D/Isometric Table Overview preserving existing aesthetic.
- [ ] Active orders grid with elapsed timers & late-order color alerts.
- [ ] Status transition state machine (New → Accepted → Preparing → Ready → Served).
- [ ] PII redaction (customer phone & address hidden from kitchen display).
- [ ] Real-time AJAX polling (3–5s) against lightweight delta JSON endpoints (zero full-page reloads).
- [ ] Audio alert for new orders with user-gesture autoplay toggle ("Enable Sound").
- [ ] Kitchen Order Ticket (KOT) print queue:
  - Browser print template (58mm/80mm thermal).
  - Reprint marking ("REPRINT" watermark) & duplicate prevention.
  - 🚫 *Physical automated silent printing via local print agent marked BLOCKED until physical hardware connection.*

### Phase 9: Live Tracking & Manual Delivery Management
- [ ] Secure public tracking route (`/track/{token}`) with zero sequential ID exposure.
- [ ] Mobile-first tracking page preserving existing warm theme & live progress bar.
- [ ] Honest ETA calculation based on real status and item prep times.
- [ ] Seamless "Add More Items" retaining table session and customer context.
- [ ] Manual Delivery / Rapido logistics module:
  - Staff entry for delivery partner booking reference, fee, tracking URL.
  - Status lifecycle (Awaiting Booking → Booked → Picked Up → In Transit → Delivered / Failed).
  - Separate reporting of delivery fees from food revenue.

### Phase 10: CRM & Loyalty Rewards Engine
- [ ] Customer Relationship Management (CRM):
  - Unique customer identifier (`VAL-C000001`).
  - Privacy safeguards (strictly authenticated access, no public phone lookups).
  - Order history, lifetime spend, average order value, last visit date.
  - Customer marketing consent flags.
- [ ] Loyalty Points System:
  - Configurable points rule (default: 10 points per completed order).
  - Immutable ledger table (`earn`, `redeem`, `reverse`, `adjust`).
  - Idempotent award on order completion.
  - Transactional redemption with row locking (`SELECT balance ... FOR UPDATE`).
  - Automatic reversal on order cancellation or refund.

### Phase 11: Business Intelligence & Reports
- [ ] Date-range analytics dashboard (Today, Yesterday, Last 7 Days, Month-to-date, Custom range).
- [ ] Financial metrics breakdown: Gross Sales, Item Discounts, Taxes, Delivery Fees, Refunds, Net Sales, Cash Collected.
- [ ] Channel performance (Dine-in, Website, POS, Phone, WhatsApp, Swiggy, Zomato).
- [ ] Best sellers & inventory volume reports.
- [ ] Kitchen operational metrics (average preparation time, delayed orders).
- [ ] Secure CSV export for accounting and audits (role-protected).

### Phase 12: Optional Third-Party Adapters
- [ ] Free WhatsApp click-to-chat generator (`https://wa.me/...` with prefilled order & tracking message).
- [ ] 🚫 *WhatsApp Cloud API adapter (structured behind `WHATSAPP_API_ENABLED=false` until Meta credentials provided).*
- [ ] Razorpay payment gateway integration (server-side order creation, checkout modal, signature verification, webhook handler, idempotency). Configurable test mode switch (`RAZORPAY_ENABLED=false`).
- [ ] 🚫 *Make.com outgoing webhook dispatcher (structured behind `MAKE_WEBHOOK_ENABLED=false` until webhook URL provided).*

### Phase 13: Automated Testing & Security Verification
- [ ] Automated PHP test suite:
  - Auth & RBAC access control tests.
  - Server-side pricing enforcement test.
  - Double-click order prevention (idempotency) test.
  - Table allocation concurrency locking test.
  - Status transition state machine validation test.
  - Secure random tracking token validation test.
  - Loyalty point ledger idempotency & concurrent redemption test.
  - Cancellation/refund permission test.
- [ ] Security audit (SQL injection, XSS escaping, CSRF enforcement, upload restrictions, `.env` file shielding).
- [ ] Database backup & restore script test.

### Phase 14: Deployment Documentation & Runbook
- [ ] XAMPP Windows setup guide (step-by-step commands, vhost, Apache config, MariaDB setup).
- [ ] Hostinger Shared / VPS deployment guide (`.htaccess` docroot shielding, database migration, file permissions, SSL).
- [ ] Maintenance runbook: backup routines, password rotation, log rotation, rollback procedure.
