# Valima Biriyani — Restaurant Management & Ordering Platform

Valima Biriyani is an enterprise-grade, cost-effective restaurant platform evolved from the KMS Foods codebase. Built purely on modern **PHP 8.2+**, **Vanilla JavaScript**, **HTML5/CSS3**, and **MySQL/MariaDB**, it delivers maximum performance, robust security, and zero paid SaaS dependencies.

---

## 🚀 Key Architectural Pillars

1. **Zero Paid Software Required:** Runs seamlessly on XAMPP, Apache, and standard Linux/Hostinger shared or VPS hosting.
2. **Server-Authoritative Security:** Client never dictates pricing, totals, or order statuses. All totals are computed on the server using database prices.
3. **Robust Concurrency:** Transactional row locking prevents duplicate table allocations and double-spending of loyalty points.
4. **Clean Modular MVC:** Structured with Controllers, Models, Services, Middleware, and Validators.
5. **Real-time Performance:** Low-overhead delta JSON polling for kitchen and tracking without disruptive page reloads.

---

## 📁 Directory Structure Overview

```text
valima-biriyani/
├── app/
│   ├── Controllers/         # HTTP request handlers (Public, Admin, POS, Kitchen, Api)
│   ├── Models/              # Database entities and query builders
│   ├── Services/            # Business logic (OrderService, LoyaltyService, PaymentService)
│   ├── Middleware/          # AuthMiddleware, RoleMiddleware, CsrfMiddleware, RateLimitMiddleware
│   ├── Validators/          # Input schema and business validation
│   └── Helpers/             # Formatting, sanitization, currency, security helpers
├── config/                  # App, database, payment, and notification settings
├── database/
│   ├── migrations/          # Version-controlled schema migrations
│   └── seeds/               # Initial seeders (Admin, Roles, Sample Menu)
├── public/                  # Public web document root
│   ├── index.php            # Application front controller
│   ├── assets/              # CSS, JS, audio, and branding assets
│   └── uploads/             # Secure user and menu item image uploads (PHP execution blocked)
├── resources/
│   └── views/               # PHP layout templates and views
│       ├── layouts/         # Master layouts (Customer, Admin, Kitchen)
│       ├── customer/        # Storefront, Menu, Cart, Checkout
│       ├── admin/           # Dashboard, Menu CRUD, Reports, CRM
│       ├── pos/             # Point-of-Sale billing interface
│       ├── kitchen/         # KDS display with live status updates
│       ├── tracking/        # Live order tracker with ETA
│       └── auth/            # Staff login & session screens
├── routes/                  # Web and API routing definitions
├── storage/                 # Private app storage
│   ├── logs/                # Audit, application, and payment logs
│   ├── backups/             # Database backups
│   └── private/             # Exported reports and temporary files
├── tests/                   # Automated PHPUnit / integration test suites
├── legacy/                  # Original KMS Foods files preserved safely
├── .env.example             # Template for configuration and credentials
├── CHECKLIST.md             # Phase tracking and implementation roadmap
└── README.md                # Project documentation and deployment guide
```

---

## 🛠 Local Environment Setup

1. **PHP 8.2+** with extensions enabled: `pdo`, `pdo_mysql`, `curl`, `mbstring`, `openssl`, `fileinfo`.
2. **MySQL 8.0+** or **MariaDB 10.4+**.
3. **Local Dev Server:**
   ```bash
   php -S localhost:8000 -t public
   ```

---

## 🎨 UI/UX Design System (Valima Biriyani)

The user interface matches the approved premium restaurant mockup:
- **Color Palette:**
  - Background: Cream `#FBF6EC`
  - Text & Accents: Near-Black `#14110C`
  - Primary CTA & Highlights: Royal Gold `#F5B800`
  - Food Accents: Rich Copper `#B8651B`
- **Typography:** Google Fonts `Playfair Display` (headings) + `Inter` (UI elements).
- **Icons:** Inline Lucide SVG icons via `Icon::render($name, $class)`.
- **Interactivity:** Lightweight standalone Alpine.js 3.14 (`public/assets/js/alpine.min.js`) for modal bottom-sheets, mobile drawers, and steppers.
- **Charts:** Zero-dependency pure SVG Donut chart and Channel bar charts.
- **Component Library & Styleguide:** Available at `/styleguide` featuring buttons, inputs, radio cards, status badges, and stat cards.

---

## 🧪 Automated Testing

Valima Biriyani includes 11 standalone test suites with **266 automated assertions** covering auth, RBAC, server pricing, concurrency, KDS, tracking, loyalty, reports, settings, and full end-to-end workflows:

```powershell
# Run all 11 test suites
Get-ChildItem -Path tests -Filter "*Test.php" | ForEach-Object { php $_.FullName }
```

---

## 🌐 Production Deployment (Hostinger)

For complete step-by-step instructions on deploying to Hostinger shared hosting using the 1-click web installer wizard (`/install`), see:
👉 **[HOSTINGER_HOSTING.md](file:///c:/Users/niran/Downloads/hotel/HOSTINGER_HOSTING.md)**

---

## 📋 Project Roadmap & Phases

See [CHECKLIST.md](file:///c:/Users/niran/Downloads/hotel/CHECKLIST.md) for full phase status and deliverables.


