# Valima Biriyani — Hostinger Shared Hosting Deployment Guide

This guide provides step-by-step instructions for deploying **Valima Biriyani** to **Hostinger Shared Hosting** (Single, Premium, Business, or Cloud Hosting) using the built-in **Web-Based Installer Wizard**.

---

## 📋 System Prerequisites

| Requirement | Supported on Hostinger | Recommended Settings |
| :--- | :--- | :--- |
| **PHP Version** | PHP 8.2 or PHP 8.3 | In hPanel &rarr; **Advanced &rarr; PHP Configuration** &rarr; Select **PHP 8.2** or **8.3** |
| **PHP Extensions** | Standard on Hostinger | `pdo_mysql`, `curl`, `mbstring`, `openssl`, `fileinfo` (enabled by default) |
| **Database** | MySQL 8.0+ / MariaDB 10.4+ | Created via hPanel &rarr; **Databases &rarr; MySQL Databases** |
| **Web Server** | LiteSpeed / Apache with `mod_rewrite` | Handled automatically by the included `.htaccess` files |
| **SSL Certificate** | Free Let's Encrypt SSL | In hPanel &rarr; **Security &rarr; SSL** &rarr; Install Free SSL |

---

## 🚀 Step 1: Upload Project Files to Hostinger

You can upload your files using either the **Hostinger File Manager** (recommended for simplicity) or **Git Deployment**.

### Method A: Hostinger File Manager (Quickest)

1. **Compress the Project Files**:
   - On your computer, select all project files and folders (`app/`, `config/`, `database/`, `public/`, `resources/`, `routes/`, `storage/`, `.htaccess`, `.env.example`, etc.).
   - Create a ZIP archive (e.g., `valima-biriyani.zip`).
   > [!NOTE]
   > Make sure the hidden files (specifically `.htaccess` in root and in `public/`) are included in your archive.

2. **Upload to Hostinger**:
   - Log in to your **Hostinger hPanel**.
   - Navigate to **Websites &rarr; Manage** &rarr; **Files &rarr; File Manager**.
   - Click on `public_html/`.
   - Click the **Upload** icon (top right) and select `valima-biriyani.zip`.

3. **Extract Files**:
   - Right-click `valima-biriyani.zip` inside `public_html/` and click **Extract**.
   - **Important**: Ensure your files are placed either directly in `public_html/` (for domain root access `https://f5coders.com/install`) or in a subfolder (e.g. `public_html/hotel/` or `public_html/hotel/hotel/`).
   - If Hostinger extracted files into a nested folder like `public_html/hotel/hotel/`:
     - **Option 1 (Subfolder access)**: Leave it as is! Valima Biriyani's router automatically detects subfolders, so `https://f5coders.com/hotel/hotel/install` works immediately.
     - **Option 2 (Domain root access)**: In File Manager, select all files inside the subfolder, click **Move**, and set destination to `public_html/`. Then visit `https://f5coders.com/install`.
   - Delete the ZIP file after extraction.

---

## 🗄️ Step 2: Create MySQL Database in Hostinger hPanel

1. In your Hostinger hPanel, go to **Databases &rarr; MySQL Databases**.
2. Fill in the database creation form:
   - **MySQL Database Name**: e.g., `valima` (Hostinger will prefix this, e.g., `u123456789_valima`).
   - **MySQL Username**: e.g., `admin` (Hostinger will prefix this, e.g., `u123456789_admin`).
   - **Password**: Enter or generate a strong password.
3. Click **Create**.
4. **Copy down the exact values**:
   - **Database Name**: `u123456789_valima`
   - **Database User**: `u123456789_admin`
   - **Database Password**: *Your chosen password*
   - **Database Host**: `localhost` (Hostinger shared hosting always uses `localhost`)
   - **Database Port**: `3306`

---

## ⚙️ Step 3: Run the 1-Click Web Setup Wizard

Valima Biriyani includes a browser-based setup wizard that runs the database migrations, configures your environment, and creates your administrator account.

1. Open your web browser and navigate to:
   ```text
   https://yourdomain.com/install
   ```

2. **Review System Prerequisites**:
   - The setup wizard will automatically verify that PHP 8.2+, `pdo_mysql`, `curl`, and file permissions are ready.
   - You should see green checkmarks `✓` for all checks.

3. **Fill in the Installation Form**:
   - **Database Host**: `localhost`
   - **Database Port**: `3306`
   - **Database Name**: `u123456789_valima` (your full Hostinger database name)
   - **Database Username**: `u123456789_admin` (your full Hostinger database user)
   - **Database Password**: *Your database password*
   - **Restaurant Name**: `Valima Biriyani`
   - **Restaurant Phone**: `+91 98765 43210`
   - **Admin Name**: `Admin Owner`
   - **Admin Email**: `admin@yourdomain.com`
   - **Admin Password**: Enter a secure password (minimum 8 characters).
   - **Seed Authentic Menu Data**: Keep checked to automatically import authentic Biriyani items, variants (Regular/Family/Jumbo), extras, and dining tables.

4. **Complete Installation**:
   - Click **"Test Database, Run Migrations & Complete Installation"**.
   - The system will:
     1. Test the MySQL connection.
     2. Create all tables (`users`, `orders`, `menu_items`, `restaurant_tables`, `kitchen_tickets`, etc.).
     3. Populate default roles and seed the authentic menu catalog.
     4. Create the Super Administrator account with bcrypt password hashing.
     5. Write the `.env` configuration file with production settings.
     6. Generate `storage/installed.lock` to permanently disable `/install` for security.

5. Click **"Log In to Admin Dashboard"** to sign in to your new installation!

---

## 🛡️ Step 4: Security & Permissions Verification

### Document Root & Directory Protection
Valima Biriyani includes dual-layer `.htaccess` protection:

1. **Root `.htaccess`** (`public_html/.htaccess`):
   - Automatically rewrites all incoming visitor requests into the `public/` directory.
   - Forbids direct access to sensitive directories (`app/`, `config/`, `database/`, `storage/`, `tests/`) and files (`.env`, `.git`).
   - If a visitor tries to open `https://yourdomain.com/.env`, Hostinger will return **403 Forbidden**.

2. **Public `.htaccess`** (`public_html/public/.htaccess`):
   - Disables directory browsing (`Options -Indexes`).
   - Sets security headers:
     - `X-Content-Type-Options: nosniff`
     - `X-Frame-Options: SAMEORIGIN`
     - `X-XSS-Protection: 1; mode=block`
     - `Referrer-Policy: strict-origin-when-cross-origin`

### File Permissions in Hostinger File Manager
Set recommended permissions in hPanel:
- All Folders: `755`
- All Files: `644`
- `storage/` directory and its subdirectories: `775` (writable for logs and uploads)
- `.env` file: `600` or `640`

---

## 🌐 Step 5: Verify Your Application Pages & Workflows

Once installed, verify the key pages and workflows:

| Page / Feature | URL | Description |
| :--- | :--- | :--- |
| **Customer Storefront** | `https://yourdomain.com/` | Hero banner, real logo, 4-icon trust strip, popular dishes |
| **Customer Menu** | `https://yourdomain.com/menu` | Interactive catalog with variant bottom-sheet (Regular, Family Pack, Jumbo) |
| **Online Checkout** | `https://yourdomain.com/checkout` | 3-step checkout with GPS location detection and Razorpay/Cash choices |
| **Order Tracking** | `https://yourdomain.com/track/{token}` | Vertical order progress timeline with polling updates and Rapido tracking |
| **Staff Login** | `https://yourdomain.com/login` | Secure staff authentication with session preservation |
| **Admin Dashboard** | `https://yourdomain.com/admin` | Live sales KPIs, SVG Donut chart, and recent orders table |
| **Central Orders** | `https://yourdomain.com/admin/orders` | Filter by status, payment method, and sales channel |
| **Menu Management** | `https://yourdomain.com/admin/menu` | Add/edit dishes, manage prices, and toggle live availability |
| **Customer CRM & Loyalty** | `https://yourdomain.com/admin/crm` | Customer profiles (`VAL-CXXXXXX`), spend history, and 10-point loyalty ledger |
| **Sales Reports** | `https://yourdomain.com/admin/reports` | Gross vs net sales, GST breakdown, top dishes, and CSV export (`/export.csv`) |
| **Settings & Integrations** | `https://yourdomain.com/admin/settings` | Razorpay, WhatsApp, and Make.com webhook status cards |
| **Kitchen KDS** | `https://yourdomain.com/kitchen` | Kitchen display system with 3D table overview and sound chimes |
| **POS Billing Terminal** | `https://yourdomain.com/pos` | Quick walk-in billing and 80mm thermal receipt printing |

---

## 🔧 Maintenance & Troubleshooting

### How to Re-Run the Installer
If you ever need to reset or re-run the web installation:
1. Open the Hostinger File Manager.
2. Navigate to `storage/`.
3. Delete the file `installed.lock`.
4. Navigate back to `https://yourdomain.com/install`.

### How to Take Database Backups in Hostinger
1. In hPanel, go to **Databases &rarr; phpMyAdmin** &rarr; Click **Enter phpMyAdmin** next to your database.
2. Select your database from the left sidebar.
3. Click the **Export** tab at the top.
4. Choose **Quick** export method and click **Export**. A `.sql` backup file will download to your computer.

### Optional Integrations Configuration in `.env`
You can configure optional integrations at any time by editing `.env` in the Hostinger File Manager:

- **Razorpay (Online Payments)**:
  ```ini
  RAZORPAY_ENABLED=true
  RAZORPAY_KEY_ID=rzp_live_xxxxxxxxxxxx
  RAZORPAY_KEY_SECRET=your_razorpay_secret
  RAZORPAY_WEBHOOK_SECRET=your_webhook_secret
  ```

- **WhatsApp (Free Click-to-Chat)**:
  Valima Biriyani uses free `https://wa.me/` links by default (no paid API required). Just verify your phone number:
  ```ini
  RESTAURANT_WHATSAPP="+919876543210"
  ```

- **Make.com (Outgoing Webhooks)**:
  ```ini
  MAKE_WEBHOOK_ENABLED=true
  MAKE_WEBHOOK_URL=https://hook.eu1.make.com/xxxxxxxxxxxxxxxx
  MAKE_WEBHOOK_SECRET=your_shared_secret
  ```

---

## 📞 Support & Contacts
For assistance with domain DNS pointing or SSL certificates, consult the Hostinger Help Center or your hPanel live support.
