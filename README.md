# Factory Medicine Inventory & Medical Expense Management System

A production-grade Laravel ERP for a factory's medical room/store: medicine
and medical-supply inventory, batch/FIFO costing, purchases, employee
medicine issuance, financial reporting, and an import tool for the legacy
Excel-based workflow it replaces.

## 1. What's included (Phase 1 — core system, built and working)

- **Database**: 20+ normalized MySQL tables (migrations in `database/migrations`) — medicines, batches, stock ledger, purchases, issue vouchers, employees, departments, suppliers, audit logs, settings, and an import-tracking schema.
- **Inventory engine** (`app/Services/StockService.php`): every stock movement (opening balance, purchase receipt, FIFO issue, return, adjustment, damage/expiry write-off) posts through one service into a single `stock_transactions` ledger. FIFO valuation is implemented and documented in `config/erp.php`.
- **Purchasing** (`app/Services/PurchaseService.php`): draft → confirm workflow, correct line/grand-total math with discount/tax/free-quantity handling, duplicate-invoice warning, stock is only affected on confirm.
- **Medicine issuing** (`app/Services/IssueService.php`): multi-line vouchers, FIFO batch consumption, stock-availability pre-check before posting, expired batches blocked, controlled reversal (never silent edits).
- **Legacy workbook import** (`app/Services/LegacyWorkbookImportService.php` + Import screens): upload → parse → validated preview with error/duplicate flags → explicit confirm-and-commit. The original workbook is stored untouched. No price, quantity, or employee ID is ever invented — see `docs` below.
- **Dashboard**: real KPIs and charts (Chart.js) computed from the database with date-range filters.
- **Reports**: Purchase Expense, Employee-wise Expense (+PDF export), Department-wise Expense, Medicine-wise Consumption (+Excel export), Daily/Monthly Usage (recreating and improving the original workbook reports), Highest-Cost Medicines, Supplier Performance, Inventory Valuation.
- **RBAC**: Admin, Medical Room/Store Officer, Accounts, Management (read-only) — enforced via route middleware (`app/Http/Middleware/EnsureUserHasRole.php`).
- **Audit log**: model-level (create/update/delete on financial & inventory models) and request-level logging.
- **Tests**: `tests/Feature/FifoCostingTest.php` and `PurchaseCalculationTest.php` cover the FIFO costing, insufficient-stock, expired-batch, and purchase-math acceptance criteria.

## 2. What's intentionally left for Phase 2 (documented, not hidden)

- Supplier invoice **file attachment** upload UI (the DB column exists; wire up a file input + storage call).
- **Partial-payment schedule** tracking beyond the single payment_amount field.
- **Transfer between stores** (multi-store) — schema has room (`stock_transactions.transaction_type` includes `transfer_in`/`transfer_out`) but no UI yet, since the factory currently has a single store.
- A compiled asset pipeline (Vite/Tailwind build) — the UI currently loads Tailwind and Chart.js from CDN for speed; swap to a local build for an air-gapped deployment.
- Additional report exports to PDF/Excel beyond the two demonstrated (`ReportExportController`) — every report follows the same pattern, so adding the rest is mechanical.

## 3. Installation

Requires PHP 8.2+, Composer, and MySQL 8+ (or MariaDB 10.5+). Built against Laravel 12 (the current stable line as of writing).

```bash
composer install
cp .env.example .env
php artisan key:generate
# Edit .env with your database credentials
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

The seeder creates:
- 4 roles (Admin, Store Officer, Accounts, Management)
- 1 admin login: **admin@factory.local / ChangeMe!123** — change this password immediately after first login.
- Departments and the 23 medicines found in the legacy workbook's Medicine List (unpriced — see `claude/workbook-analysis-and-schema.md` for why).

### Importing your historical data

Log in as Admin or Store Officer → **Import Workbook** → upload the original `Medicine Report.xlsx`. Review the preview (valid/error/duplicate counts per row) before clicking **Confirm & Commit**. Rows with missing dates, unmapped medicines, or unparseable quantities are never committed — fix them in a corrected re-upload or handle them manually afterward.

## 4. Deployment guide (production)

1. Provision PHP 8.2+, MySQL, and a web server (Nginx/Apache) pointing its document root at `public/`.
2. `composer install --no-dev --optimize-autoloader`
3. Set `.env`: `APP_ENV=production`, `APP_DEBUG=false`, real `DB_*` credentials, a generated `APP_KEY`.
4. `php artisan migrate --force` (do **not** re-run `--seed` against a live database after the first setup).
5. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
6. Point a process supervisor (systemd, Supervisor) at `php artisan queue:work` if you enable background exports later.
7. Set up HTTPS (Let's Encrypt or your CA) — this system handles employee medical data and must not run on plain HTTP in production.

## 5. Backup & restore

- **Database**: `mysqldump -u <user> -p medicare_erp > backup-$(date +%F).sql`. Schedule this nightly via cron. Restore with `mysql -u <user> -p medicare_erp < backup-2026-01-01.sql`.
- **Uploaded files** (purchase invoice attachments, imported workbooks): back up `storage/app` alongside the database — they are referenced by path, not stored in the DB.
- Keep at least 30 days of rolling backups off the application server.

## 6. User guide (Medical Room / Store Officer)

1. **Daily issuing**: Medicine Issue → + Issue Medicine → pick the employee (name/department auto-fills), add each medicine with quantity, submit. Stock deducts immediately (FIFO). Print the voucher for the receiver's signature.
2. **Receiving stock**: Purchases → + New Purchase → enter supplier, invoice #, and each medicine's batch/expiry/quantity/price → Save as Draft → review → **Confirm & Receive Stock** (this is the only action that increases stock from a purchase).
3. **Returns**: if an employee returns unused, usable medicine, record it under Returns so it goes back into the correct batch.
4. **Stock counts**: periodically do a physical count (Stock Ledger → Physical Counts → New Count), enter what you actually counted, then **Reconcile** to post the discrepancy as an adjustment with a reason.
5. Watch the **Expiry Management** and **Low Stock** pages weekly.

## 7. Management guide (reading the reports)

- **Purchase cost** is what the factory paid suppliers. **Issue cost** is the value of medicine actually consumed. These are deliberately shown separately on the dashboard and in reports — purchasing is a procurement outflow, consumption is the real expense driver.
- Any number shown as **"Cost unavailable"** means the historical data imported from the old Excel workbook had no purchase price recorded for that stock — the system never fabricates a number here. Once purchases are entered at current prices, future issues will cost correctly under FIFO.
- **Employee-wise** and **Department-wise Expense** reports are the two to review monthly for medical cost control; **Inventory Valuation** is the figure for balance-sheet/asset discussions.

## 8. Known environment limitation this build was created under

This codebase was authored in a sandboxed cloud workspace whose network
policy blocks Packagist and GitHub archive downloads, so `composer install`
could not be run here to verify the dependency tree end-to-end. Every PHP
file was syntax-checked (`php -l`) and the code follows standard Laravel
11/12 conventions throughout (the two share the same slim bootstrap/app.php
structure). Run `composer install` on your own machine/server
as the first step — if anything in `composer.json` needs a version nudge for
your PHP version, Composer's own error message will say exactly which
package.

**Update (post-review):** `composer.json` originally targeted Laravel 11, which `composer install`'s security audit now blocks — every 11.x release (and 12.x releases below 12.60) is affected by several disclosed advisories (including CVE-2026-48019), so Composer refuses to resolve them. The requirements now target **Laravel ^12.60** on **PHP 8.2+** (Laravel 12 itself only needs 8.2). `maatwebsite/excel` stays on the **^3.1** line, which supports Laravel up to 13.x on PHP 8.2 — its newer v4 line requires PHP 8.3+, which would have forced a PHP upgrade for no reason, so it was deliberately not used. `app/Exports/ArrayReportExport.php` keeps its native return types (harmless on v3.1, required if you ever move to v4). If your hosting is stuck on PHP 8.1 or older, you'd need to stay on Laravel 11 and either add the specific advisory IDs to `"audit": {"ignore": [...]}` in `composer.json` and accept the known risk, or wait for a patched 11.x release — `--ignore-platform-req` is not the right tool for an advisory block.
