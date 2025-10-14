# API docs: Product & Stock (guide for l5-swagger)

This document explains how this project generates OpenAPI docs using l5-swagger and provides minimal example annotations for Product and Stock endpoints. Use this as a starting point to expand documentation across the API.

## How docs are generated

- This project uses the darkaonline/l5-swagger package (see `composer.json`).
- Configuration: `config/l5-swagger.php` (generates `storage/api-docs/api-docs.json`).
- The project already includes many `@OA` annotations in controllers and models (examples: `app/Http/Controllers/Api/BankController.php`, `app/Models/Supplier.php`, etc.). l5-swagger scans PHP files for these docblocks to build the OpenAPI JSON.
- A route in `routes/api.php` calls `Artisan::call('l5-swagger:generate')` in some flows; otherwise run the generator manually locally:
  - php artisan l5-swagger:generate

## Minimal annotation examples

- Add an `@OA\Tag` docblock at the top of controllers (already added to ProductController and StockController).
- Add `@OA\Get`, `@OA\Post`, `@OA\Put`, `@OA\Delete` blocks above methods to describe endpoints, parameters, request bodies and responses.

Example (concise) for ProductController::store (place above the method):

/*
 * @OA\Post(
 *     path="/api/products",
 *     tags={"Product"},
 *     summary="Create a product",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/Product"),
 *         examples={
 *             "stocked"={"summary":"STOCKED product","value":{"name":"Sample Product","type":"STOCKED","category_id":1,"unit_id":1,"supplier_id":null,"mrp":100.00,"locked_price":90.00,"barcode":"123456"}},
 *             "non_stocked"={"summary":"NON_STOCKED product","value":{"name":"Service Product","type":"NON_STOCKED","category_id":2,"unit_id":1,"supplier_id":null,"mrp":null,"locked_price":null,"barcode":null}}
 *         }
 *     ),
 *     @OA\Response(response=201, description="Product created"),
 *     @OA\Response(response=422, description="Validation Error")
 * )
 */

Notes:
- The Product API supports two primary product types: `STOCKED` and `NON_STOCKED`.
- Use the `stocked` example in Swagger UI when creating physical inventory items that have `mrp`/`locked_price` values.
- Use the `non_stocked` example for services or non-inventory items — `mrp` and `locked_price` should be null for this type.

Stock update examples:
- The `PUT /api/stocks/{id}` endpoint supports partial updates (only send the fields you want to change).
- Examples added to the docs: `qty_update` (change only `qty`) and `price_update` (update `max_retail_price` and `cost_price`).
- In Swagger UI choose the example from the Request Body area to populate the example JSON before trying out the request.

Example (concise) for StockController::index (above method):

/*
 * @OA\Get(
 *     path="/api/stocks",
 *     tags={"Stock"},
 *     summary="List stocks",
 *     @OA\Parameter(name="low_stock", in="query", @OA\Schema(type="boolean")),
 *     @OA\Response(response=200, description="OK")
 * )
 */

## Next steps / recommended additions

- Add full `@OA\Schema` components for `Product` and `Stock` models (if not already present) similar to `app/Models/Supplier.php`.
- Annotate each controller method with request/response schemas.
- Run `php artisan l5-swagger:generate` and review `storage/api-docs/api-docs.json`.
- If you run into missing endpoints in docs, ensure the files with annotations are autoloaded by composer (PSR-4) and are scanned by l5-swagger; check `config/l5-swagger.php` 'scan' settings.

## Verification (local)

1. Ensure composer dependencies are installed.
2. Run migrations/seeds if needed.
3. Run the generator: `php artisan l5-swagger:generate` and open the generated `storage/api-docs/api-docs.json` or visit the swagger UI route.

## Small example changes made by the agent

- Added controller-level `@OA\Tag` blocks to `ProductController.php` and `StockController.php` to group endpoints in the swagger UI.
- Created this doc `docs/api-docs-product-stock.md` with examples and recommended next steps.


---
If you'd like, I can now add specific method-level `@OA` annotations for the Product and Stock controller methods (store, index, show, update) following the project's existing style. Tell me whether you prefer detailed schemas (components/schemas) or concise refs to the model schemas already present in `app/Models`.
