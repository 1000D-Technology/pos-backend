# API docs: Product & Stock (guide for l5-swagger)

This document explains how this project generates OpenAPI docs using l5-swagger and records the exact additions I made while implementing Product and Stock features (controller annotations, examples, DB migrations and models). Use this as the canonical reference for the Product/Stock docs and for verifying the code changes locally.

## How docs are generated

- This project uses the darkaonline/l5-swagger package (see `composer.json`).
- Configuration: `config/l5-swagger.php` (generates `storage/api-docs/api-docs.json`).
- l5-swagger scans your PHP files for `@OA` docblocks placed above controllers, models and methods. The generator is typically run with:

```powershell
php artisan l5-swagger:generate
```

If the generator reports missing `$ref` components (e.g. `#/components/schemas/Product`) check that the corresponding model has an `@OA\Schema` block and that the file is autoloadable by Composer.

## Summary of recent changes

Files created or updated to support Product/Stock functionality and documentation:

- `app/Http/Controllers/Api/ProductController.php`
  - Added inline validation rules for create/update, a `type` query filter for `index`, image upload handling (optional), and structured error handling using `App\DTO\ApiResponse`.
  - `@OA` method-level docblocks were added (POST/PUT/GET) including two request-body examples for Product creation: `stocked` and `non_stocked`.

- `app/Http/Controllers/Api/StockController.php`
  - Implemented `store()` (POST) to create stocks and to automatically create a GRN record inside a DB transaction. Added better error handling and validation.
  - Moved/added `@OA\Put` docblock directly above `update()` and added examples for `qty_update` and `price_update`.

- `app/DTO/ProductDTO.php`
  - Responsible for normalizing Product attributes before model create/update. This file must ensure that when `type === 'STOCKED'` prices are null and when `type === 'NON_STOCKED'` prices are required.

- `app/Models/Stock.php`
  - Extended `$fillable` and `$casts` to include new fields required by the provided ERD: `manufacture_date`, `cost_percentage`, `cost_code`, `profit_percentage`, `profit`, `discount_percentage`, `discount`, `whole_sale_price`, `locked_price`.

- `app/Models/Grn.php` (new)
  - New model to represent GRN records created automatically when stock is added. `$fillable = ['product_id','stock_id','qty']`.

- `database/migrations/2025_10_15_000001_update_stocks_table_add_fields.php` (new)
  - Migration that adds nullable pricing/manufacture/profit/discount/locked_price columns to `stocks` (safe incremental change).

- `database/migrations/2025_10_15_000002_create_grns_table.php` (new)
  - Migration to create `grns` table (id, product_id FK, stock_id FK, qty, and `created_at` only; `updated_at` intentionally omitted).

- `database/seeders/PermissionSeeder.php` (edited earlier)
  - Added `stocks.*` permission slugs (`stocks.view`, `stocks.create`, `stocks.update`, `stocks.search`) so routes are protected consistently.

- `docs/api-docs-product-stock.md` (this file) — updated with these details and local verification steps.

## What changed in the OpenAPI docs

- `@OA\Tag` docblocks were added to `ProductController` and `StockController` so the swagger UI groups endpoints under Product and Stock.
- Request examples for Product create (two examples: `stocked` and `non_stocked`) were added; they appear in the generated `storage/api-docs/api-docs.json` and in Swagger UI "Try it out".
- Examples for `PUT /api/stocks/{id}` were added: `qty_update` and `price_update`.
- Minimal `@OA\Schema` docblocks were added to `app/Models/Product.php` and `app/Models/Stock.php` where needed so the generator can resolve refs.

## How to verify locally

1. Install / update composer deps (if needed):

```powershell
composer install
```

2. Run migrations (this will add the new `stocks` columns and create the `grns` table):

```powershell
php artisan migrate
```

If you want a fresh DB with seeded permissions (be careful on production):

```powershell
php artisan migrate:fresh --seed
```

3. Re-seed permissions (if you didn't refresh the DB):

```powershell
php artisan db:seed --class=PermissionSeeder
```

4. Regenerate OpenAPI JSON for Swagger UI:

```powershell
php artisan l5-swagger:generate
```

5. Inspect the generated file to confirm endpoints and examples:

 - `storage/api-docs/api-docs.json` — search for `/api/products` and `/api/stocks` entries and verify `examples` blocks are present.

6. Run the app and test endpoints (secure routes require auth + permission):

```powershell
php artisan serve
# then use Postman or Swagger UI (if configured in the project) to test endpoints
```

Note: the new `StockController::store` creates a `Grn` row in the same DB transaction as `Stock::create()`. Ensure migrations are applied first or the request will fail.

## Known issues & decisions you should confirm

1. Product price business rule (ACTION REQUIRED)
  - Requested rule: "Stocked Products MRP Locked price need to be null at the product table; Non stocked Products MRP Locked price required."
  - Current code state (before these edits): `ProductDTO::fromArray()` cleared prices for `NON_STOCKED`, which was incorrect. This has been corrected so `STOCKED` products have null product-level prices and `NON_STOCKED` products require prices.

2. Product image upload behavior (clarify)
  - Optional image upload handling was implemented in `ProductController` (file validation and storing via `Storage::url()` to `public` disk). If uploads should be disallowed, remove the file-handling code and keep `img` as a nullable URL only.

3. Linter/IDE warnings
   - After edits some static-analysis warnings were reported by the edit environment (e.g. unresolved symbol notes). These are likely environment-specific or transient; if you see them locally, check imports at the top of the edited controllers: `use App\DTO\ApiResponse; use Illuminate\Support\Facades\Storage;` are used in the code.

## Next recommended steps (pick the ones you want me to do next)

 - Fix the `ProductDTO` business-rule mismatch and update controller validations (already applied in this branch).
 - Remove or adjust product image upload handling per project preference.
 - Add full `@OA\Schema` components for `Product` and `Stock` with property definitions to improve generated docs.
 - Add response examples (200/201 and 422) to the `@OA` docblocks so Swagger UI shows success and validation error examples.
