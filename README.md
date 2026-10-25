# Tahu Reverse

Laravel 12 ordering website for Tahu Reverse. Customers place guest orders and pay with a manually verified static QRIS image once the official merchant image is configured. Admin pages are available only beneath the configurable `ADMIN_PATH`.

## Requirements

- PHP 8.2+
- Composer
- Node.js and npm
- MySQL 8+ or compatible MariaDB

## Local setup

1. Install dependencies:

   ```powershell
   composer install
   npm install
   ```

2. Create `.env` and the Laravel application key:

   ```powershell
   Copy-Item .env.example .env
   php artisan key:generate
   ```

3. Configure the MySQL connection in `.env`. Set a strong `ADMIN_EMAIL`, `ADMIN_PASSWORD`, and private `ADMIN_PATH` before seeding.

4. Apply migrations and create the initial admin, product, and site settings:

   ```powershell
   php artisan migrate --seed
   ```

   Existing installations should run `php artisan migrate --force` during deployment. These migrations add payment history and order fulfillment fields without dropping existing order, customer, product, or user records. The old order `status`, `snap_token`, and `payment_type` columns remain in place for historical compatibility; the active flow uses the payment and fulfillment records instead.

5. Build frontend assets and make uploaded files available:

   ```powershell
   npm run build
   php artisan storage:link
   ```

6. Start the application:

   ```powershell
   php artisan serve
   ```

   Open `http://127.0.0.1:8000`. Admin login is at `http://127.0.0.1:8000/<ADMIN_PATH>/login`. The seeder refuses to create an admin unless both admin credentials are configured. Do not commit `.env`.

The initial product price and stock are placeholders. Review them in the admin panel before accepting orders. WhatsApp and Instagram contact settings can also be configured there.

## Static QRIS setup

No payment-provider credentials are needed. Static QRIS is not ready to accept payments until the business supplies and configures its own official merchant QRIS image.

1. Obtain the genuine QRIS image from the business's official merchant account.
2. Sign in to the admin panel and open **Pengaturan**.
3. Upload the official JPG, PNG, or WebP image (maximum 4 MB). The application stores it on the Laravel public storage disk.
4. Confirm that the image is visible on the public payment page and that its merchant identity is correct.

When no image is configured or the stored file is unavailable, the payment page displays a setup warning, no placeholder QR code, and tells customers not to pay using another code. Customers can still create an order, but must contact the business rather than pay on that page.

A static QRIS code may not encode an order-specific amount. Customers must enter the exact total shown on their payment page if their payment app asks for an amount, check the merchant name, and retain their transaction reference. Payment remains pending until an authorized admin matches the actual incoming transaction and amount in the official merchant application. Clicking a button or providing a screenshot does not verify payment.

## Payment and order operations

- Checkout calculates the price from the active product in the database and creates the order, payment, and initial audit event in one database transaction.
- Each payment page uses a random secret URL token; only its SHA-256 hash is stored. Do not share the URL publicly.
- Admins review pending QRIS orders under **Pesanan**. Verify only after checking the official merchant account and matching the amount and transaction. Record the merchant transaction reference in the verification form.
- Rejected claims require a reason. Payment events record status changes, the acting admin, timestamp, and available reference/reason.
- A verified payment reduces stock once and moves its order into processing. Admins can then mark it ready or completed. Paid orders cannot be canceled through this workflow because refunds are not implemented.
- The dashboard counts revenue only from payment records marked paid.
- Do not delete payment/order history to resolve outstanding payments. Before upgrading an installation with an older online-payment integration, reconcile any unsettled transactions with the merchant first.

The QRIS image path is stored in the existing database-backed `settings` table as `qris_image_path`. No QRIS merchant ID or payment secret is hardcoded or required.

## Production checklist

- [ ] Serve the site over HTTPS; set `APP_URL` to the public HTTPS origin.
- [ ] Set `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_ENCRYPT=true`, and `SESSION_SECURE_COOKIE=true`.
- [ ] Set `LOG_LEVEL=error` (not `debug`) to avoid leaking stack traces.
- [ ] Generate a fresh `APP_KEY` with `php artisan key:generate`; never reuse a key across environments.
- [ ] Configure production MySQL with a dedicated, non-root user and a strong password; bind MySQL to `127.0.0.1` only.
- [ ] Set a unique, hard-to-guess `ADMIN_PATH` (no default fallback is provided).
- [ ] Set strong `ADMIN_EMAIL` and `ADMIN_PASSWORD` (minimum 12 characters, not a common password). The seeder will refuse to run otherwise.
- [ ] Back up the database and persistent `storage/app/public` files.
- [ ] Apply migrations with `php artisan migrate --force`; do not reset or wipe the database.
- [ ] Build assets with `npm ci && npm run build`.
- [ ] Run `php artisan storage:link` and ensure the web process can write to persistent storage.
- [ ] Schedule `php artisan orders:expire-pending` (e.g., every 5 minutes via cron/scheduler) so pending orders expire and release reserved stock.
- [ ] Upload and verify the official merchant QRIS image before inviting customers to pay.
- [ ] Test guest checkout, the private payment page, admin authorization, manual verification, rejection, inventory updates, order expiration, and backups.

## Tests

Run the automated test suite with:

```powershell
php artisan test
```
