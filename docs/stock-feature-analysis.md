# Stock Feature Analysis

This document summarizes the Stock feature implementation (controllers, models, requests), permission enforcement, and how dummy/test data is added for manual testing (Postman/Swagger) in this project. It's generated from a codebase review on branch `PB-12-Product-Controller`.

## Overview

- Controller: `app/Http/Controllers/Api/StockController.php`
- Models: `app/Models/Stock.php`, `app/Models/Grn.php`, `app/Models/Product.php`
- Request validation: `app/Http/Requests/UpdateStockRequest.php`
- Permission middleware: `app/Http/Middleware/CheckPermission.php` (aliased as `permission` in `bootstrap/app.php`)
- Seeders: `database/seeders/PermissionSeeder.php`, `database/seeders/UserSeeder.php`

## Endpoints

All stock routes are registered in `routes/api.php` and are protected by `auth:sanctum` and specific permission middleware.

- GET /api/stocks  (permission: `stocks.view`)
  - Query params: `per_page` (default 15), `low_stock` (boolean) to filter items where `qty <= qty_limit_alert`.
  - Response: paginated list of `Stock` with `product` relation included.

- GET /api/stocks/search  (permission: `stocks.view`)
  - Alias for index used for search-like purposes.

- POST /api/stocks  (permission: `stocks.create`)
  - Body validation (in `StockController@store`):
    - `product_id` (required, exists:products,id)
    - `qty` (required, numeric, min:0)
    - Price fields: `max_retail_price`, `cost_price`, etc. (nullable numeric)
    - Dates: `manufacture_date`, `expire_date` (nullable dates)
    - `qty_limit_alert` (nullable integer)
  - Behavior: Creates a `Stock` record, then creates a `Grn` record inside the same DB transaction (so both succeed or both roll back).
  - Response: 201 with created stock on success, 422 on validation error, 500 on other errors.

- GET /api/stocks/{id}  (permission: `stocks.view`)
  - Returns the `Stock` with `product` relation or 404 if not found.

- PUT /api/stocks/{id}  (permission: `stocks.update`)
  - Uses `UpdateStockRequest` which allows partial updates for `qty`, price fields, `expire_date`, and `qty_limit_alert`.
  - Behavior: Updates the stock and returns the fresh model.

## Data Shape

Stock model (`app/Models/Stock.php`) fillable fields include (but not limited to):
- `product_id`, `qty`, `manufacture_date`, `expire_date`, `max_retail_price`, `cost_price`,
- `cost_percentage`, `cost_code`, `profit_percentage`, `profit`, `discount_percentage`, `discount`,
- `whole_sale_price`, `locked_price`, `qty_limit_alert`.

Casts:
- `expire_date`, `manufacture_date` => date
- Numeric fields cast to decimal with precision.

Grn model (`app/Models/Grn.php`):
- `product_id`, `stock_id`, `qty` (only `created_at` is stored; `updated_at` has been removed)

Product model (`app/Models/Product.php`) minimal shape included for `product` relation in stock responses.

## Permission System

- Permissions stored in `permissions` table via `PermissionSeeder`.
- Seeded stock permissions (in `PermissionSeeder.php`):
  - `stocks.create`
  - `stocks.view`
  - `stocks.update`
  - `stocks.search`
- Middleware: `CheckPermission` (aliased as `permission`) resolves the permission slug passed on middleware and checks the current authenticated user's `hasPermissionTo($slug)`.
- `User::hasPermissionTo()` caches permission slugs per user (cache TTL currently set to 10 seconds for dev).
- `UserSeeder` creates default users: `admin@example.com` (all permissions), `cashier@example.com` (subset), `guest@example.com` (no permissions).

## How dummy/test data is added

- There are no `Product` or `Stock` factories in `database/factories/` at the time of review.
- Default users and permissions are added using `DatabaseSeeder` which calls `PermissionSeeder` and `UserSeeder`.
- README and `docs/api-docs-product-stock.md` instructs running:
  - `php artisan migrate:fresh --seed` (drops and recreates DB then runs seeders)
  - `php artisan db:seed --class=PermissionSeeder` (if re-seeding permissions only)
- The project doesn't include a Postman collection file in the repository. The docs suggest using Postman or Swagger UI to manually test endpoints after seeding and running the app.

Typical manual testing steps (Postman/Swagger):
1. Ensure DB is migrated and seeded: `php artisan migrate:fresh --seed`.
2. Authenticate to get Sanctum token using `POST /api/login` with seeded user credentials (`admin@example.com` / `password`).
3. Use the token in the `Authorization: Bearer <token>` header for subsequent requests.
4. Create a product first (POST /api/products) with `products.create` permission.
5. Create stock (POST /api/stocks) with `stocks.create` permission. The endpoint will create a `Grn` record automatically.

## Error Handling

- `store()` wraps DB changes in a transaction and returns 500 with error message if an exception occurs.
- Validation errors return structured responses using `App\DTO\ApiResponse` with 422 codes (both in controller and `UpdateStockRequest`).

## Notes & Recommendations

- Consider adding factories for `Product` and `Stock` to allow automated tests to seed sample inventory easily.
- Increase permission cache TTL for production (currently 10 seconds) or move to cache invalidation hooks when permissions change.
- Add Postman collection to `docs/` or the repo root for quick manual testing by team members.
- Add response resources (`StockResource`) usage in the controller for consistent response formatting (currently the controller returns raw models inside `ApiResponse`).

## Files Reviewed (quick list)
- app/Http/Controllers/Api/StockController.php
- app/Models/Stock.php
- app/Models/Grn.php
- app/Models/Product.php
- app/Http/Requests/UpdateStockRequest.php
- app/Http/Middleware/CheckPermission.php
- database/seeders/PermissionSeeder.php
- database/seeders/UserSeeder.php
- routes/api.php
- docs/api-docs-product-stock.md

---

Generated by repository analysis on 2025-10-15.
