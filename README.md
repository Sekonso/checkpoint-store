# Checkpoint Store

A demo e-commerce website for a gaming gear store. It combines a company profile, a product storefront with shopping cart and checkout, and a blog with admin-managed content.

## Project Summary

Checkpoint Store is a full-stack demo application built with **Laravel + Inertia + React**. It showcases three areas:

- **Company profile** — about page, FAQ, and general store information.
- **E-commerce** — product catalog, product detail pages, shopping cart, and checkout that creates transaction records (with invoice numbers, stock management, and cancellation flow).
- **Blog** — articles written and published by store admins, with featured images and tags.

The app distinguishes between regular users (`customer`) and store staff (`admin`). Admins access a dedicated dashboard to manage products and articles.

## Features

- Storefront product catalog with categories and image galleries
- Product search and detail pages (slug-based URLs)
- Blog with articles, tags, and featured images
- User accounts: sign up, sign in, sign out, and profile management (phone & address)
- Shopping cart: add, update quantity, remove items, and clear
- Checkout that creates a transaction with a unique invoice number, decrements product stock, and clears the cart
- Transaction listing and cancellation (cancellation restores stock)
- Admin dashboard (at `/admin/dashboard`) for managing products (CRUD + display toggling) and articles (CRUD + publish/archive)

## Tech Stack

| Layer | Technology |
| --- | --- |
| Backend | Laravel 13, PHP 8.3 |
| Frontend | React 19 + TypeScript, Inertia v3 |
| Styling | Tailwind CSS 4, shadcn/ui (Base UI) |
| Tooling | Vite, TanStack Table, Tiptap, Leaflet |
| Database | PostgreSQL |
| Testing | Pest |

## Requirements

- PHP 8.3+
- Composer
- Node.js (with npm)
- PostgreSQL

## Installation

### Quick setup

The project ships a one-command setup script:

```bash
composer setup
```

This installs dependencies, creates `.env` from `.env.example` (if missing), generates an app key, runs migrations, installs npm packages, and builds the frontend assets.

After it finishes, still run `php artisan storage:link` (the store uploads product and article images into `storage/app/public`).

### Manual setup

```bash
# 1. Install PHP dependencies
composer install

# 2. Create environment file and app key
copy .env.example .env
php artisan key:generate

# 3. Configure your database in .env
#    DB_CONNECTION=pgsql
#    DB_HOST=127.0.0.1
#    DB_PORT=5432
#    DB_DATABASE=project
#    DB_USERNAME=<your user>
#    DB_PASSWORD=<your password>

# 4. Run migrations and seed sample data
php artisan migrate --seed

# 5. Link the public storage directory for image uploads
php artisan storage:link

# 6. Install and build frontend assets
npm install
npm run build
```

## Running the App

Start everything (Laravel dev server, queue worker, and Vite) with a single command:

```bash
composer dev
```

Alternatively, run each process manually in separate terminals:

```bash
php artisan serve
php artisan queue:listen --tries=1
npm run dev
```

The app is then available at `http://localhost:8000`.

## Database

Migrations define the following domain tables:

- `users`, `carts` / `cart_items`
- `products`, `product_categories`, `product_images`
- `articles`, `article_tags`, `article_tag_relations`
- `transactions`, `transaction_items`

Running `php artisan migrate --seed` populates the database with sample users (including an admin account), articles, and products so the site is usable immediately.

## Project Structure

```
app/
├── Http/
│   ├── Controllers/          # public, user, and Admin/ controllers
│   ├── Middleware/           # AuthCheck, AdminCheck, HandleInertiaRequests
│   └── Requests/             # FormRequest validation classes
├── Models/
├── Policies/
database/
├── migrations/
└── seeders/
resources/
└── js/
    ├── pages/                # Inertia pages (Home, Store, Blog, Cart, Transaction, User, Admin, Auth)
    ├── components/
    ├── layouts/
    └── types/                # shared TypeScript model types
routes/
├── web.php                   # public + authenticated-user routes
├── admin.php                 # admin-only routes (AdminCheckMiddleware)
├── auth.php                  # sign up / sign in / sign out
└── console.php
```

## Routes Overview

| Area | Routes |
| --- | --- |
| Public | `/`, `/about`, `/faq`, `/blog`, `/blog/{article}`, `/store`, `/store/{product}`, `/sign-up`, `/sign-in` |
| Authenticated user | `/my-mine`, `PUT /user`, `/cart`, `/cart/items/{id}`, `/transactions` |
| Admin | `/admin/dashboard`, `/admin/articles`, `/admin/products` |

Admin routes require an account with the `admin` role (`AdminCheckMiddleware`).

## Configuration

All application settings live in `.env`. Notable values:

- `DB_*` — PostgreSQL connection details
- `APP_URL` — the base URL used for the site

The `.env.example` file also reserves variables for the upcoming payment gateway integration:

```dotenv
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false
```