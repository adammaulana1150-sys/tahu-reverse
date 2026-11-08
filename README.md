# Tahu Reverse

Laravel 12 catalog website for Tahu Reverse. Customers browse the product and current stock, then contact the business via WhatsApp, Instagram, or email to place an order. Admin pages are available only beneath the configurable `ADMIN_PATH`, where stock and site settings are managed.

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

   Existing installations should run `php artisan migrate --force` during deployment. These migrations add order fulfillment fields without dropping existing order, customer, product, or user records. The old order `status`, `snap_token`, and `payment_type` columns remain in place for historical compatibility.

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

## Contact and order flow

The product page shows the current stock. Customers place orders by contacting the business through WhatsApp, Instagram, or email — there is no online checkout or payment page. The Instagram link on the site opens the official account without exposing the raw URL.

Admins manage the product (name, description, price, stock, photo, and active status) under **Produk** and can set the available quantity directly. Orders and customers can be reviewed under **Pesanan** and **Pelanggan**, and site contact settings under **Pengaturan**.

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
- [ ] Review the initial product price and stock in the admin panel before accepting orders.

## Tests

Run the automated test suite with:

```powershell
php artisan test
```
