# Daraz Affiliate Platform - Project Memory & Rules

## System Architecture & Stack
- **Architecture**: Nano Modular Monolith built with PHP 8.3, PDO MySQL, BCMath financial precision, and CSRF protection.
- **Production Server**: Hostinger cPanel / hPanel (`178.16.128.229:65002`, user `u866412713`).
- **Live Domain**: https://darazaffilate.com
- **Web Root**: `/home/u866412713/domains/darazaffilate.com/public_html` (symlinked to `/home/u866412713/domains/darazaffilate.com/project/public`).
- **GitHub Repository**: `https://github.com/comdaraz/comdaraz.git` (branch `main`).

## User Authentication & Registration Rules
1. **Instant User Onboarding**: Newly registered accounts are auto-verified (`is_verified = 1`) and logged in immediately upon registration.
2. **Password Validation**: Password minimum length is 6 characters (`strlen($password) >= 6`).
3. **Admin Controls**: Admin user management supports full CRUD, instant balance Add/Deduct via BCMath atomic transactions, OTP generation, and password resets.
4. **Deposit Proof Uploads**: Payment recharges include payment proof image uploads stored in `public/uploads/recharge_proofs/` and viewable via Admin Console (`/admin/recharges`).

## Database Rules
- Production database: `u866412713_yes` on Hostinger MySQL.
- Strict BCMath financial calculations (`format_money()`, `money_add()`, `money_sub()`) must be used for all wallet balances.
- Never duplicate routes, controller methods, or SQL parameters. Always use unique SQL parameter names in PDO queries.
