# InvenTrack — Inventory Management System

A responsive inventory management system built with **PHP**, **MySQL**, **Tailwind v4**, and vanilla **JavaScript**. Authentication is handled via **JWT** stored in `httpOnly` cookies.

---

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Screenshots](#screenshots)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Configuration](#configuration)
- [Database Setup](#database-setup)
- [Running the Application](#running-the-application)
- [Default Credentials](#default-credentials)
- [Project Structure](#project-structure)
- [License](#license)

---

## Features

- **JWT Authentication** — Stateless auth via `httpOnly` cookies; auto-redirect on expiry
- **Role-based Access Control** — Admin and Staff roles with permission enforcement
- **Dashboard** — Live KPI cards (stock, sales, purchases), 6 charts visualisations
- **Product Management** — Add/edit/delete products with image upload, category & supplier linkage, CSV bulk import
- **Category Management** — Full CRUD with product-count guard on deletion
- **Supplier Management** — Full CRUD with CSV bulk import, product-link guard on deletion
- **Transaction Ledger** — Record sales & purchases; stock levels adjust automatically on create/edit/delete
- **Staff Accounts** — Admin-only; create, edit, activate/deactivate staff accounts
- **Profile Page** — Update personal info and change password; JWT refreshed on save
- **Toast Notifications** — Contextual success/error/warning/info toasts
- **CSV Export** — Export products and transactions to CSV
- **Chart Export** — Export charts to image
- **Reporting API** — JSON reports (overview, stock, sales, purchases, top products, low stock, category value, supplier spend, monthly trend)
- **CSV Download API** — Server-side CSV downloads for products, suppliers, transactions (with optional date range), categories, and low stock

---

## Tech Stack

| Layer           | Technology                                   |
| --------------- | -------------------------------------------- |
| Backend         | PHP 8.1+                                     |
| Database        | MySQL 8.0+                                   |
| Authentication  | `firebase/php-jwt` v7, httpOnly cookie       |
| Configuration   | `vlucas/phpdotenv` v5                        |
| Frontend        | Tailwind v4 (`@tailwindcss/cli`), Vanilla JS |
| Tables          | DataTables v2.3.7 (CDN, requires jQuery 3.7) |
| Charts          | Chart.js (CDN, dashboard only)               |
| Icons & Fonts   | Font Awesome 6, Inter (Google Fonts)         |
| Package Manager | Composer (PHP), npm (CSS build)              |

---

## Screenshots

| Page                   | Desktop                                                                                                                                     | Mobile                                                                                                                                                          |
| ---------------------- | ------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Login                  | ![Web Login Page](assets/images/screenshots/login-page-desktop.png)                                                                         | ![Mobile Login Page](assets/images/screenshots/login-page-mobile.png)                                                                                           |
| Dashboard (Admin View) | ![Web Dashboard Page](assets/images/screenshots/dashboard-desktop.png)                                                                      | ![Mobile Dashboard Page 1](assets/images/screenshots/dashboard-mobile1.png) ![Mobile Dashboard Page 2](assets/images/screenshots/dashboard-mobile2.png)         |
| Products               | ![Web Product Page](assets/images/screenshots/product-desktop.png)                                                                          | ![Mobile Product Page](assets/images/screenshots/product-mobile.png)                                                                                            |
| Transactions           | ![Web Transaction Page](assets/images/screenshots/transaction-desktop.png)                                                                  | ![Mobile Transaction Page 1](assets/images/screenshots/transaction-mobile1.png) ![Mobile Transaction Page 2](assets/images/screenshots/transaction-mobile2.png) |
| Suppliers              | ![Web Supplier Page](assets/images/screenshots/supplier-desktop.png)                                                                        | ![Mobile Supplier Page](assets/images/screenshots/supplier-mobile.png)                                                                                          |
| Categories             | ![Web Category Page](assets/images/screenshots/category-desktop.png)                                                                        | ![Mobile Category Page](assets/images/screenshots/category-mobile.png)                                                                                          |
| Staff Accounts         | ![Web Staff Account Page](assets/images/screenshots/staff-acc-desktop.png)                                                                  | ![Mobile Staff Account Page](assets/images/screenshots/staff-acc-mobile.png)                                                                                    |
| Profile                | ![Web Profile Page 1](assets/images/screenshots/profile-desktop1.png) ![Web Profile Page 2](assets/images/screenshots/profile-desktop2.png) | ![Mobile Profile Page](assets/images/screenshots/profile-mobile.png)                                                                                            |

| Component                      | Screenshot                                                                            |
| ------------------------------ | ------------------------------------------------------------------------------------- |
| Dashboard (Staff View)         | ![Dashboard - Staff](assets/images/screenshots/dashboard-staff.png)                   |
| Charts                         | ![Charts](assets/images/screenshots/charts.png)                                       |
| Add Product Modal              | ![Add Product Modal](assets/images/screenshots/add-product.png)                       |
| Edit Product Modal             | ![Edit Product Modal](assets/images/screenshots/edit-product.png)                     |
| Add Category Modal             | ![Add Category Modal](assets/images/screenshots/add-category.png)                     |
| Edit Supplier Modal            | ![Edit Supplier Modal](assets/images/screenshots/edit-supplier.png)                   |
| Add Transaction Modal          | ![Add Transaction Modal](assets/images/screenshots/add-transaction.png)               |
| Edit Transaction Modal         | ![Edit Transaction Modal](assets/images/screenshots/edit-transaction.png)             |
| Add Staff Account Modal        | ![Add Staff Account Modal](assets/images/screenshots/add-staff.png)                   |
| Edit Staff Account Modal       | ![Edit Staff Account Modal](assets/images/screenshots/edit-staff.png)                 |
| Deactivate Staff Account Modal | ![Deactivate Staff Account Modal](assets/images/screenshots/deactivate-modal.png) |
| Bulk Import Modal              | ![Bulk Import Modal](assets/images/screenshots/bulk-import.png)                   |

---

## Prerequisites

- PHP 8.1 or higher (with `mysqli`, `fileinfo`, `json` extensions enabled)
- MySQL 8.0 or higher
- Composer
- Node.js 20+ and npm (for Tailwind CSS compilation)
- A local web server: [XAMPP](https://www.apachefriends.org/), [Laragon](https://laragon.org/), or PHP's built-in server

---

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/your-org/inventory-mgmt-sys.git
cd inventory-mgmt-sys
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install Node dependencies and build CSS

```bash
npm install
npm run build
```

> The build script compiles `assets/css/input.css` → `assets/css/tailwind.css` (minified) using `@tailwindcss/cli`.
>
> During development, run `npm run dev` instead to rebuild the CSS automatically whenever you edit `input.css` or the PHP/JS files.

---

## Configuration

### Environment file

Copy the example and fill in your values:

```bash
cp .env.example .env
```

Edit `.env`:

```env
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=
DB_NAME=inventory_db
JWT_SECRET=your_random_secret_here_min_32_chars
```

> **Important:** `JWT_SECRET` must be at least 32 characters — `firebase/php-jwt` rejects shorter keys and login will fail. Generate one with:
>
> ```bash
> php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
> ```

---

## Database Setup

### 1. Create the database

```sql
CREATE DATABASE inventory_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Import the schema

```bash
mysql -u root -p inventory_db < schema.sql
```

This also creates the default admin account (see [Default Credentials](#default-credentials)).

### 3. (Optional) Load sample data

```bash
mysql -u root -p inventory_db < seed.sql
```

The seed includes 5 users, 7 categories, 5 suppliers, 20 products, and ~58 transactions spread across Jan–Apr 2026.

---

## Running the Application

### Option A — PHP built-in server (development)

```bash
php -S localhost:8000
```

Open `http://localhost:8000` in your browser.

### Option B — XAMPP / Laragon

1. Place the project folder in your web root (e.g. `htdocs/inventory-mgmt-sys`)
2. Start Apache and MySQL
3. Open `http://localhost/inventory-mgmt-sys`

### Option C — Virtual host (recommended for development)

Configure a virtual host pointing to the project root, e.g. `http://ims.local`.

---

## Default Credentials

Created by `schema.sql`:

| Role  | Username | Password   |
| ----- | -------- | ---------- |
| Admin | `admin`  | `password` |

Additional accounts created by `seed.sql` (all share the same password):

| Role  | Username     | Designation         |
| ----- | ------------ | ------------------- |
| Admin | `mary.chen`  | Operations Manager  |
| Staff | `alice.tan`  | Warehouse Staff     |
| Staff | `bob.lim`    | Inventory Clerk     |
| Staff | `carol.ng`   | Procurement Officer |
| Staff | `david.wong` | Sales Executive     |

> **Change all default passwords immediately in a production environment.**

---

## Project Structure

```
inventory-mgmt-sys/
├── assets/
│   ├── css/
│   │   ├── input.css            # Tailwind source (edit this)
│   │   ├── tailwind.css         # Compiled output (do not edit)
│   │   └── main.css             # Custom overrides (DataTables, sidebar, toast)
│   ├── js/
│   │   ├── api.js               # HTTP client wrapper (fetch + JWT cookie)
│   │   ├── app.js               # Global utilities (IMS namespace, DataTables init)
│   │   ├── toast.js             # Toast notification system
│   │   └── pages/               # Per-page JS modules
│   │       ├── dashboard.js
│   │       ├── products.js
│   │       ├── categories.js
│   │       ├── suppliers.js
│   │       ├── transactions.js
│   │       ├── users.js
│   │       └── profile.js
│   ├── images/
│   └── templates/               # CSV templates for bulk import
│       ├── products_template.csv
│       └── suppliers_template.csv
├── db/                          # PHP REST API endpoints
│   ├── mysql_conn.php           # MySQL connection (reads .env)
│   ├── login_db.php
│   ├── dashboard_db.php
│   ├── product_db.php
│   ├── category_db.php
│   ├── supplier_db.php
│   ├── transaction_db.php
│   ├── transaction_type_db.php
│   ├── user_db.php
│   ├── import_db.php            # CSV bulk import (products, suppliers)
│   ├── export_db.php            # CSV downloads
│   └── report_db.php            # Reporting API
├── includes/
│   ├── header.php               # HTML <head> — CSS/JS imports
│   └── navbar.php               # Sidebar navigation
├── lib/
│   ├── jwt_helper.php           # JWT encode/decode/cookie helpers
│   ├── CSVHelper.php            # CSV parsing and output
│   ├── Formatter.php            # Value, date, and record formatting
│   ├── ImageHelper.php          # Product image storage
│   ├── PaginationHelper.php     # Pagination, search, and sorting
│   ├── ResponseHelper.php       # JSON response helpers
│   ├── StockHelper.php          # Stock status and adjustments
│   └── Validator.php            # Input validation
├── uploads/                     # Product images (gitignored)
├── vendor/                      # Composer packages (gitignored)
├── node_modules/                # npm packages (gitignored)
├── index.php                    # Redirects to dashboard or login
├── dashboard.php
├── products.php
├── categories.php
├── suppliers.php
├── transactions.php
├── users.php
├── profile.php
├── login.php
├── logout.php
├── schema.sql                   # Database schema
├── seed.sql                     # Sample data
├── composer.json
├── package.json
├── .env.example                 # Environment template
└── .env                         # Environment secrets (gitignored)
```

---

## License

This project is licensed under the [MIT License](LICENSE).
