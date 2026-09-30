# Daraz-Style Nano Modular Affiliate Platform (Phase 1)

A high-performance, zero-framework-overhead PHP 8.2+ e-commerce & affiliate platform built with a Nano-Modular Monolith architecture. Specially designed for high speed and seamless compatibility on shared hosting environments (cPanel / DirectAdmin).

---

## 🚀 Key Features & Highlights

- **Nano-Modular Monolith Architecture**: Isolated, self-contained micro-modules (`Auth`, `Marketplace`, `Cart`, `Order`, `Wallet`, `Affiliate`, etc.) without heavy framework performance penalties.
- **Zero Heavy Dependencies**: Built strictly using pure PHP 8.2+ PDO, native sessions, custom regex router, Tailwind CSS, and Alpine.js.
- **Atomic Ledger Wallet**: Financial mutations run inside atomic database transactions (`PDO::beginTransaction()`) with `DECIMAL(12,2)` precision and immutable transaction logging.
- **Affiliate Engine Foundation**: Link generation, click tracking (`/ref/{slug}`), order conversion linking, and real-time wallet commission updates.
- **Lightweight File Caching**: Non-sensitive category and product listings are cached with TTL on shared hosting disks to minimize SQL query overhead.
- **Security First**: `password_hash()`, `password_verify()`, HttpOnly Lax cookies, session ID regeneration, CSRF token validation, and XSS output escaping.

---

## 🛠️ Requirements

- **PHP**: 8.2 or higher (PDO extension enabled)
- **Database**: MySQL 8.0+ / MariaDB 10.4+
- **Web Server**: Apache / Nginx / PHP Built-in Server

---

## 📁 Directory Structure

```text
daraz-affiliate/
├── app/
│   ├── Core/               # Router, Database (PDO), View, Session, Cache, CSRF
│   ├── Helpers/            # XSS escaping, env, url, csrf helpers
│   └── Modules/            # Micro-Modules
│       ├── Auth/           # Registration, Login, Logout, Lockout
│       ├── User/           # Profile management
│       ├── Marketplace/    # Catalog, filters, search, pagination
│       ├── Product/        # Product detail, stock control
│       ├── Category/       # Category hierarchy
│       ├── Cart/           # Server-validated shopping cart
│       ├── Order/          # Transactional order checkout
│       ├── Affiliate/      # Referral links, click tracking
│       ├── Commission/     # Payout processing
│       ├── Wallet/         # Balance & ledger history
│       ├── Recharge/       # Deposit foundation
│       ├── Withdrawal/     # Payout request foundation
│       ├── Admin/          # Administrative controls
│       └── Notification/   # Alert messaging
│
├── config/                 # App and Database config files
├── database/               # MySQL schema and seed data (schema.sql)
├── public/                 # Document root (index.php, .htaccess, static assets)
├── routes/                 # Clean web router definitions (web.php)
├── storage/                # File cache & logs
└── views/                  # Clean layout & page templates
```

---

## 💻 Local Setup Instructions

1. **Clone or Navigate to Directory**:
   ```bash
   cd Desktop/daraz-affiliate
   ```

2. **Environment Configuration**:
   Copy `.env.example` to `.env` and fill in your MySQL credentials:
   ```bash
   cp .env.example .env
   ```

3. **Import Database Schema**:
   Create a MySQL database named `daraz_affiliate` and import `database/schema.sql`:
   ```bash
   mysql -u root -p daraz_affiliate < database/schema.sql
   ```

4. **Start Local Development Server**:
   ```bash
   php -S localhost:8000 -t public
   ```

5. **Access Application**:
   Open browser at [http://localhost:8000](http://localhost:8000).

---

## 🌐 Shared Hosting / cPanel Deployment

1. Upload all files to your cPanel hosting directory (e.g., `public_html`).
2. Point your domain or cPanel document root to the `/public` folder.
3. Import `database/schema.sql` into your cPanel MySQL Database via phpMyAdmin.
4. Update `.env` with your cPanel database user, password, and database name.
5. Ensure `storage/cache` and `storage/logs` have write permissions (`0755` or `0777`).

---

## 🔐 Security Features

- Passwords hashed with `PASSWORD_BCRYPT`.
- Session security with `HttpOnly`, `SameSite=Lax`, and `session_regenerate_id(true)` upon authentication.
- All forms protected with `CSRF::verifyOrDie()`.
- XSS prevention using HTML escaping via `e()` helper function.
- Parameterized SQL queries using PDO (zero raw string queries).
