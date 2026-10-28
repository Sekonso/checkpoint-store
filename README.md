# Checkpoint Store

A demo e-commerce website for a gaming gear store. It combines a company profile, a product storefront with shopping cart and checkout, and a blog with admin-managed content.

## Table of Contents

- [Key Features](#key-features)
- [Tech Stack](#tech-stack)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configurations](#configurations)
- [Running the App](#running-the-app)
- [Route Overview](#route-overview)
- [Database](#database)
- [Tools](#tools)
- [Attribution](#attribution)

## Key Features

- Company profile pages, including About and FAQ.
- User registration, sign-in, and profile management.
- Product catalog, product details, shopping cart, checkout, and transaction tracking.
- Midtrans Snap payment sessions, payment status synchronization, and webhook notifications.
- Admin management for products, articles, and transaction shipping.
- Blog articles with tags and featured images.

## Tech Stack

| Layer | Technology |
| --- | --- |
| Backend | Laravel 13, PHP 8.3+ |
| Frontend | React 19, TypeScript, Inertia v3 |
| Styling | Tailwind CSS 4, shadcn/ui (Base UI) |
| Tooling and UI | Vite, TanStack Table, Tiptap, Leaflet |
| Payments | Midtrans PHP SDK and Snap |
| Database | PostgreSQL |
| Testing | Pest |

## Requirements

- PHP 8.3 or later and Composer
- Node.js with npm
- PostgreSQL
- Midtrans sandbox account and API keys to exercise online payments

## Installation

### Manual setup

1. Install PHP dependencies:

   ```bash
   composer install
   ```

2. Create the environment file and application key:

   ```bash
   copy .env.example .env
   php artisan key:generate
   ```

3. Set `PostgreSQL` and `Midtrans` configuration in `.env` (see [Configurations](#configurations)).
4. Apply migrations and seed demo content:

   ```bash
   php artisan migrate --seed
   ```

5. Link public storage:

   ```bash
   php artisan storage:link
   ```

6. Install and build frontend assets:

   ```bash
   npm install
   npm run build
   ```

### Midtrans sandbox setup

1. Create or use a Midtrans sandbox account and copy its **sandbox** Server Key and Client Key into `.env` as `MIDTRANS_SERVER_KEY` and `MIDTRANS_CLIENT_KEY`.
2. Keep `MIDTRANS_IS_PRODUCTION=false` while developing with sandbox credentials.
3. In the Midtrans sandbox dashboard, configure the payment notification URL to `https://<your-public-app-host>/webhook/midtrans/notification`. For local development, expose the app through a public HTTPS tunnel and use that tunnel URL; Midtrans cannot call `localhost` directly.
4. Use Midtrans sandbox payment methods and test accounts when checking payment flows. Do not use production credentials for local testing.

The webhook route is public so Midtrans can reach it. The application validates notifications using the configured Server Key.

## Configurations

Copy `.env.example` to `.env` and adjust values for the local environment. Keep `.env` private and never commit real credentials.

| Variables | Purpose |
| --- | --- |
| `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_DEBUG`, `APP_URL` | Application identity, encryption key, debug mode, and public base URL. Set `APP_URL` to the address used by the browser and payment webhook. |
| `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Database connection. The project uses PostgreSQL (`pgsql`). |
| `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, `MIDTRANS_IS_PRODUCTION` | Midtrans API credentials and environment selection. Use sandbox keys with `false`; use production keys and `true` only for a production deployment. |

When changing environment values after configuration has been cached, clear the cached configuration with `php artisan config:clear`.

## Running the App

Start the Laravel development server, queue worker, and Vite together:

```bash
composer dev
```

Or start each process in a separate terminal:

```bash
php artisan serve
php artisan queue:listen --tries=1
npm run dev
```

The Laravel server is available at `http://localhost:8000` by default. Leave the Vite and queue processes running during development.

## Route Overview

| Area | Routes | Access |
| --- | --- | --- |
| Public pages | `GET /`, `/about`, `/faq`, `/blog`, `/blog/{article:slug}`, `/store`, `/store/{product:slug}` | Public |
| Authentication | `GET /sign-up`, `/sign-in`; `POST /sign-up`, `/sign-in`, `/sign-out` | Public; sign-out ends the current session |
| Profile | `GET /my-mine`, `PUT /user` | Signed-in user |
| Cart | `GET /cart`; `POST /cart`; `PATCH /cart/items/{cartItem}`; `DELETE /cart/items/{cartItem}`, `/cart` | Signed-in user |
| Transactions | `GET /transactions`, `/transactions/{transaction:invoice_number}`; `POST /transactions`, `/transactions/{transaction:invoice_number}/cancel`, `/transactions/{transaction:invoice_number}/payments`, `/transactions/{transaction:invoice_number}/payments/reset`, `/transactions/{transaction:invoice_number}/payments/sync` | Signed-in user; payment actions are rate-limited |
| Admin dashboard and content | `/admin/dashboard`, `/admin/articles/*`, `/admin/products/*` | Admin role required |
| Admin shipping | `/admin/transactions`, `/admin/transactions/{transaction}/edit`, `PATCH /admin/transactions/{transaction}/shipping`, `DELETE /admin/transactions/{transaction}` | Admin role required |
| Payment webhook | `POST /webhook/midtrans/notification` | Public gateway callback; notification signature is verified by the app |

Route definitions are in `routes/web.php`, `routes/auth.php`, and `routes/admin.php`. Admin routes use `AdminCheckMiddleware`; signed-in routes use `AuthCheckMiddleware`.

## Database

The application uses PostgreSQL by default. Migrations create the following main tables:

- Accounts and framework support: `users`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`
- Catalog: `product_categories`, `products`, `product_images`
- Shopping cart: `carts`, `cart_items`
- Blog: `articles`, `article_tags`, `article_tag_relations`
- Orders and payments: `transactions`, `transaction_items`, `payments`

Create or update the schema with `php artisan migrate`. Add sample content with `php artisan db:seed`; run both with `php artisan migrate --seed` on a fresh database. The seeders add sample users, articles/tags, product categories, and products.

The demo admin account created by the seeder is `admin@checkpoint.store` with password `00000000`. This is a local demo credential only; change or remove it before exposing a seeded database beyond a trusted development environment.

## Tools

- `composer setup` — install dependencies, prepare `.env`, generate the app key, migrate, and build assets.
- `composer dev` — run Laravel, the queue worker, and Vite together.
- `php artisan ...` — Laravel commands such as `migrate`, `db:seed`, `route:list`, and `storage:link`.
- `npm run dev` — run the Vite development server.
- `npm run build` — compile production frontend assets.
- `php artisan test` — run the Pest test suite.

## Miscellaneous

- Checkpoint Store project author metadata identifies **Adriansyah (Sekonso)**; the project profile links to [Sekonso on GitHub](https://github.com/Sekonso).
- Product and article image credits depend on the assets uploaded or added to the database; add source and creator credits when using externally sourced media.
