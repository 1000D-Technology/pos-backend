<?php

namespace App\Http\Controllers\Api;

use App\DTO\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateStockRequest;
use App\Http\Resources\StockResource;
use App\Models\Stock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    /**
     * @OA\Tag(
     *     name="Stock",
     *     description="Stock management endpoints"
     * )
     */
    public function index(Request $request)
    {
        $query = Stock::with('product');

        // low stock filter
        if ($request->boolean('low_stock')) {
            $query->whereColumn('qty', '<=', 'qty_limit_alert');
        }

        $perPage = (int) $request->query('per_page', 15);
        $stocks = $query->paginate($perPage)->appends($request->query());

        return response()->json(ApiResponse::success('Stock list retrieved successfully.', $stocks)->toArray(), 200);
    }

    /**
     * @OA\Get(
     *     path="/api/stocks",
     *     tags={"Stock"},
     *     summary="List stocks",
     *     @OA\Parameter(name="low_stock", in="query", @OA\Schema(type="boolean")),
     *     @OA\Response(response=200, description="OK")
     * )
     */

    public function store(Request $request)
    {
        // inline validation to allow API-based creation
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|numeric|min:0',
            'max_retail_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'expire_date' => 'nullable|date',
            'qty_limit_alert' => 'nullable|integer|min:0',
        ]);

        try {
            $stock = Stock::create($validated);
            return response()->json(ApiResponse::success('Stock created successfully', $stock)->toArray(), 201);
        } catch (\Exception $e) {
            return response()->json(ApiResponse::error('Error creating stock', [$e->getMessage()])->toArray(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/stocks",
     *     tags={"Stock"},
     *     summary="Create stock",
     *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/Stock")),
     *     @OA\Response(response=201, description="Created"),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */

    public function show($id)
    {
        $stock = Stock::with('product')->find($id);
        if (!$stock) {
            return response()->json(ApiResponse::error('Stock not found', ['Stock not found with ID: '.$id])->toArray(), 404);
        }
        return response()->json(ApiResponse::success('Stock retrieved successfully', $stock)->toArray(), 200);
    }

    /**
     * @OA\Get(
     *     path="/api/stocks/{id}",
     *     tags={"Stock"},
     *     summary="Get stock",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */

    /**
     * @OA\Put(
     *     path="/api/stocks/{id}",
     *     tags={"Stock"},
     *     summary="Update stock",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/Stock"),
     *             @OA\Examples(
     *                 example="qty_update",
     *                 summary="Update quantity only",
     *                 value={"qty":25}
     *             ),
     *             @OA\Examples(
     *                 example="price_update",
     *                 summary="Update prices",
     *                 value={"max_retail_price":120.00,"cost_price":80.00}
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function update(UpdateStockRequest $request, $id)
    {
        $stock = Stock::find($id);
        if (!$stock) {
            return response()->json(ApiResponse::error('Stock not found', ['Stock not found with ID: '.$id])->toArray(), 404);
        }

        try {
            $stock->update($request->validated());
            return response()->json(ApiResponse::success('Stock updated successfully', $stock->fresh())->toArray(), 200);
        } catch (\Exception $e) {
            return response()->json(ApiResponse::error('Error updating stock', [$e->getMessage()])->toArray(), 500);
        }
    }
}
