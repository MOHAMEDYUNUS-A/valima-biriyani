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
- [x] Dine-in table allocation with transactional locking (`OrderService::allocateTable()`) to eliminate race conditions.
- [x] Multi-channel order type support: Dine-in (with table), Takeaway/Pickup, Home Delivery.
- [x] Customer phone & name normalization (+91 standard, unverified guest tracking).
- [x] Server-side cart validation and recalculation (server prices only, zero client price trust).
- [x] Idempotency key validation to prevent double-click duplicate orders.
- [x] Order number generation (`VAL-YYYYMMDD-XXXX`).
- [x] Cryptographic random tracking token generation (`random_bytes(16)` -> 32 hex chars).
- [x] Status event timeline initialization (`order_status_events`).
- [x] Automatic kitchen ticket generation (`kitchen_tickets`).
- [x] Cash / Counter UPI payment recording.
- [x] 15 Automated Order & Checkout Tests executed and passing cleanly (0 failures).
- [x] 110 Total automated tests passing across Phases 1 through 6.

### Phase 7: Central Admin Orders, POS & Browser Receipts
- [x] Central Orders Management view (Live search, channel filter, status filter, date filter, pagination).
- [x] Order detail inspection (`/admin/orders/{id}`) with item snapshots, lifecycle events timeline, and status updates.
- [x] Fast Point-of-Sale (POS) interface (`/pos`):
  - Instant item search & quick category tabs.
  - Live bill builder with server recalculation.
  - Customer lookup & guest order support (+91 normalization).
  - Payment collection (Cash, UPI / QR at counter).
  - Immediate KOT dispatch to kitchen.
- [x] Printable Invoice & Receipt (`/pos/receipt/{id}` with browser CSS print template, thermal 58mm & 80mm support).
- [x] Operational Dashboard analytics (`/admin`) displaying live sales, kitchen queue, and orders from database.
- [x] 13 Automated Admin, POS & Receipt Tests executed and passing cleanly (0 failures).
- [x] 123 Total automated tests passing across Phases 1 through 7.

### Phase 8: Kitchen Display System (KDS) & KOT Queue
- [x] Modern 3D/Isometric Table Overview preserving restaurant aesthetic.
- [x] Active orders grid with elapsed timers & late-order color alerts.
- [x] Status transition state machine (New → Accepted → Preparing → Ready → Served).
- [x] PII redaction (customer phone & address hidden from kitchen display).
- [x] Real-time AJAX polling (3–5s) against lightweight delta JSON endpoints (zero full-page reloads).
- [x] Audio alert for new orders with user-gesture autoplay toggle ("Enable Sound").
- [x] Kitchen Order Ticket (KOT) print queue:
  - Browser print template (58mm/80mm thermal).
  - Reprint marking ("REPRINT" watermark) & duplicate prevention.
  - 🚫 *Physical automated silent printing via local print agent marked BLOCKED until physical hardware connection.*
- [x] 24 Automated Kitchen & KDS Tests executed and passing.

### Phase 9: Live Tracking & Manual Delivery Management
- [x] Secure public tracking route (`/track/{token}`) with zero sequential ID exposure.
- [x] Mobile-first tracking page preserving premium black+gold theme & live progress bar.
- [x] Honest ETA calculation based on real status and item prep times.
- [x] Seamless "Add More Items" retaining table session and customer context.
- [x] Manual Delivery / Rapido logistics module:
  - Staff entry for delivery partner booking reference, fee, tracking URL.
  - Status lifecycle (Awaiting Booking → Booked → Picked Up → In Transit → Delivered / Failed).
  - Separate reporting of delivery fees from food revenue.
- [x] 22 Automated Tracking & Delivery Tests executed and passing.

### Phase 10: CRM & Loyalty Rewards Engine
- [x] Customer Relationship Management (CRM):
  - Unique customer identifier (`VAL-C000001`).
  - Privacy safeguards (strictly authenticated access, no public phone lookups).
  - Order history, lifetime spend, average order value, last visit date.
  - Customer marketing consent flags.
- [x] Loyalty Points System:
  - Configurable points rule (default: 10 points per completed order).
  - Immutable ledger table (`earn`, `redeem`, `reverse`, `adjust`).
  - Idempotent award on order completion.
  - Transactional redemption with balance verification.
  - Automatic reversal on order cancellation or refund.
- [x] 28 Automated CRM & Loyalty Tests executed and passing.

### Phase 11: Business Intelligence & Reports
- [x] Operational analytics dashboard (`/admin/reports`).
- [x] Financial metrics breakdown: Gross Sales, Collected Revenue, Pending Payments, GST (CGST+SGST), Delivery Fees.
- [x] Channel performance (Website, POS, Phone, WhatsApp, Swiggy, Zomato).
- [x] Best sellers table (Top 10 dishes by volume & revenue).
- [x] Secure CSV export (`/admin/reports/export.csv`) for accounting and audits (role-protected).

### Phase 12: Optional Third-Party Adapters
- [x] Free WhatsApp click-to-chat generator (`https://wa.me/...` with prefilled order & tracking message).
- [x] WhatsApp Cloud API adapter (structured behind `WHATSAPP_USE_CLOUD_API=false` until Meta credentials provided).
- [x] Razorpay payment gateway integration (HMAC-SHA256 signature verification, webhook handler, idempotency).
- [x] Make.com outgoing webhook dispatcher (structured behind `MAKE_WEBHOOK_ENABLED=false` until webhook URL provided) with audit log table.

### Phase 13: UI/UX Design System & Automated Verification
- [x] **UI-1**: Design tokens defined (`#FBF6EC` cream, `#14110C` dark, `#F5B800` gold, `#B8651B` copper), standalone CSS (`public/assets/css/app.css`), Google Fonts (Playfair Display + Inter), Lucide SVG icon system (`Icon::render`).
- [x] **UI-2**: Reusable PHP component library (`resources/views/components/`) and interactive `/styleguide` dev view.
- [x] **UI-3**: Customer storefront flow (Home, Menu with variant bottom-sheet, Cart drawer, 3-step Checkout, Order confirmation banner, Real-time tracking).
- [x] **UI-4**: Admin dashboard with SVG Donut and Bar charts, Orders list, Order details, CRM view, Reports, Settings & Integrations.
- [x] **UI-5**: POS billing terminal and Kitchen KDS display with thermal KOT.
- [x] **UI-6**: 10 Automated PHP test suites, 217 passing tests, 0 failures.

### Phase 14: Deployment Documentation & Runbook
- [ ] XAMPP Windows setup guide (step-by-step commands, vhost, Apache config, MariaDB setup).
- [ ] Hostinger Shared / VPS deployment guide (`.htaccess` docroot shielding, database migration, file permissions, SSL).
- [ ] Maintenance runbook: backup routines, password rotation, log rotation, rollback procedure.

