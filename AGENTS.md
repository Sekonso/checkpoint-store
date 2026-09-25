# Project

Checkpoint store is demo website a gaming gear store. The site contain profile information about the company, e-commerce for customer to buy our product and blog to share new related to our store's theme.

 a Laravel 13 + Inertia/React storefront with an admin dashboard (storefront, blog, cart, Midtrans transactions, product/article management).

## General rule

- Language: English (US)
- Never read any files from node modules and vendor. Refer to the official documentation first, then consult if documentation or any public information are not available. Only then you can read external libraries

## Tech stack

- Backend: Laravel 13, PHP 8.3
- Frontend: React 19 + TypeScript, Inertia v3, Vite, Tailwind CSS 4, shadcn/ui (Base UI), TanStack Table, Tiptap, Leaflet
- Tooling: Pest (tests), Laravel Pint, Prettier
- Data: PostgreSQL

## Coding conventions

### Backend (PHP)

- Routes split by purpose: `routes/web.php` (public + auth user), `routes/admin.php` (AdminCheckMiddleware), `routes/auth.php`.
- Validation lives in FormRequest classes (`app/Http/Requests`); define per-attribute rule arrays.
- Render methods return `Inertia::render(...)`. All non-render methods wrap logic in `try/catch (\Throwable)`: `report($e)`; rethrow in `local`/`development`; otherwise respond via flash toast or validation errors.
- Multi-step writes use `DB::transaction`; clean up side effects (e.g. stored files) on failure.
- Enforce ownership/role with `abort_unless(...)` / Policies; keep ownership checks in controllers.
- Models: explicit `$table`, `$fillable`, `$casts`, typed relations with docblock return types; use attribute accessors/mutators for derived fields (e.g. slugs).

### Frontend (TS/React)

- Pages in `resources/js/pages/` are PascalCase (`Store/index.tsx`); components in `components/` are kebab-case (`search-bar.tsx`).
- Shared model types in `resources/js/types/models.ts`; use `cn()` from `@/lib/utils` for class merging; Inertia page props are camelCase.
- Reuse existing components (shadcn/ui, tiptap, sidebar, data-table) before introducing new abstractions.

## Guard rails

- Confirm before any consequential action: migrations / schema changes, deleting files or data, installing packages, editing `.env`/config, or anything affecting payments, data, architecture, or user-visible behavior.
- Never read, log, or expose secrets from `.env` or other sensitive files.
- Never run git operations.
- Keep changes narrowly scoped to the request — no unrelated refactors, formatting churn, or dependency upgrades.
- Never reset, discard, or overwrite user work. Inspect existing routes, controllers, migrations, and tests before editing; don't guess behavior.
- Prefer reversible operations and small, reviewable patches.

## Working style

- Restate understanding and scope briefly before implementing; ask one focused question when a choice matters, else assume the smallest reasonable option.
- Follow existing patterns over new abstractions.
- Do not use subagents unless asked.
- Explain changes in plain language and note limitations or follow-up work.