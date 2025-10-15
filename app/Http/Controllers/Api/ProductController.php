<?php

namespace App\Http\Controllers\Api;

use App\DTO\ProductDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\DTO\ApiResponse;

class ProductController extends Controller
{
    /**
     * @OA\Tag(
     *     name="Product",
     *     description="Product management endpoints"
     * )
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'unit', 'supplier']);

    // Filters
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->query('name') . '%');
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }
        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->query('unit_id'));
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->query('supplier_id'));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }

        $perPage = (int) $request->query('per_page', 15);
        $products = $query->paginate($perPage)->appends($request->query());

        return ProductResource::collection($products);
    }

    /**
     * @OA\Get(
     *     path="/api/products",
     *     tags={"Product"},
     *     summary="List products",
     *     @OA\Parameter(name="name", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="category_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK")
     * )
     */

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:STOCKED,NON_STOCKED',
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'mrp' => [
                'nullable',
                'numeric',
                'min:0',
                'required_if:type,NON_STOCKED',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('type') === 'STOCKED' && !is_null($value)) {
                        $fail('MRP must be null for STOCKED products.');
                    }
                },
            ],
            'locked_price' => [
                'nullable',
                'numeric',
                'min:0',
                'required_if:type,NON_STOCKED',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('type') === 'STOCKED' && !is_null($value)) {
                        $fail('Locked price must be null for STOCKED products.');
                    }
                },
            ],
            'cabin_number' => 'nullable|string|max:100',
            'img' => 'nullable|image|max:2048',
            'color' => 'nullable|string|max:50',
            'barcode' => 'nullable|string|max:255|unique:products,barcode',
        ]);
            if ($request->hasFile('img')) {
                $path = $request->file('img')->store('products', 'public');
                $validated['img'] = Storage::url($path);
            }

            $dto = ProductDTO::fromArray($validated);
            $product = Product::create($dto->toArray());

            return new ProductResource($product->load(['category', 'unit', 'supplier']));
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json(ApiResponse::error('Validation failed', $ve->errors())->toArray(), 422);
        } catch (\Exception $e) {
            return response()->json(ApiResponse::error('Error creating product', [$e->getMessage()])->toArray(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     tags={"Product"},
     *     summary="Create a product",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="type", type="string", enum={"STOCKED","NON_STOCKED"}),
     *                 @OA\Property(property="category_id", type="integer"),
     *                 @OA\Property(property="unit_id", type="integer"),
     *                 @OA\Property(property="supplier_id", type="integer", nullable=true),
     *                 @OA\Property(property="mrp", type="number", format="float", nullable=true),
     *                 @OA\Property(property="locked_price", type="number", format="float", nullable=true),
     *                 @OA\Property(property="cabin_number", type="string", nullable=true),
     *                 @OA\Property(property="img", type="string", format="binary", nullable=true),
     *                 @OA\Property(property="color", type="string", nullable=true),
     *                 @OA\Property(property="barcode", type="string", nullable=true)
     *             ),
     *             @OA\Examples(
     *                 example="stocked",
     *                 summary="STOCKED product",
     *                 value={"name":"Sample Product","type":"STOCKED","category_id":1,"unit_id":1,"supplier_id":null,"mrp":null,"locked_price":null,"barcode":"123456"}
     *             ),
     *             @OA\Examples(
     *                 example="non_stocked",
     *                 summary="NON_STOCKED product",
     *                 value={"name":"Service Product","type":"NON_STOCKED","category_id":2,"unit_id":1,"supplier_id":null,"mrp":100.00,"locked_price":90.00,"barcode":null}
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Created"),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */

    public function show($id)
    {
        $product = Product::with(['category', 'unit', 'supplier'])->findOrFail($id);
        return new ProductResource($product);
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     tags={"Product"},
     *     summary="Get product",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="Not Found")
     * )
     */

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:STOCKED,NON_STOCKED',
            'category_id' => 'sometimes|required|exists:categories,id',
            'unit_id' => 'sometimes|required|exists:units,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'mrp' => [
                'nullable',
                'numeric',
                'min:0',
                'required_if:type,NON_STOCKED',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->has('type') && $request->input('type') === 'STOCKED' && !is_null($value)) {
                        $fail('MRP must be null for STOCKED products.');
                    }
                },
            ],
            'locked_price' => [
                'nullable',
                'numeric',
                'min:0',
                'required_if:type,NON_STOCKED',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->has('type') && $request->input('type') === 'STOCKED' && !is_null($value)) {
                        $fail('Locked price must be null for STOCKED products.');
                    }
                },
            ],
            'cabin_number' => 'nullable|string|max:100',
            'img' => 'nullable|image|max:2048',
            'color' => 'nullable|string|max:50',
            'barcode' => "nullable|string|max:255|unique:products,barcode,{$id}",
        ]);
        try {
            // handle image upload
            if ($request->hasFile('img')) {
                $path = $request->file('img')->store('products', 'public');
                $validated['img'] = Storage::url($path);
            }

            if (array_key_exists('type', $validated) && $validated['type'] === 'STOCKED') {
                $validated['mrp'] = null;
                $validated['locked_price'] = null;
            }

            $dto = ProductDTO::fromArray($validated, true);
            $product->update($dto->toArray());

            return new ProductResource($product->fresh(['category', 'unit', 'supplier']));
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json(ApiResponse::error('Validation failed', $ve->errors())->toArray(), 422);
        } catch (\Exception $e) {
            return response()->json(ApiResponse::error('Error updating product', [$e->getMessage()])->toArray(), 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/products/{id}",
     *     tags={"Product"},
     *     summary="Update a product",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="type", type="string", enum={"STOCKED","NON_STOCKED"}),
     *                 @OA\Property(property="category_id", type="integer"),
     *                 @OA\Property(property="unit_id", type="integer"),
     *                 @OA\Property(property="supplier_id", type="integer", nullable=true),
     *                 @OA\Property(property="mrp", type="number", format="float", nullable=true),
     *                 @OA\Property(property="locked_price", type="number", format="float", nullable=true),
     *                 @OA\Property(property="cabin_number", type="string", nullable=true),
     *                 @OA\Property(property="img", type="string", format="binary", nullable=true),
     *                 @OA\Property(property="color", type="string", nullable=true),
     *                 @OA\Property(property="barcode", type="string", nullable=true)
     *             ),
     *             @OA\Examples(
     *                 example="stocked",
     *                 summary="STOCKED product",
     *                 value={"name":"Sample Product","type":"STOCKED","category_id":1,"unit_id":1,"supplier_id":null,"mrp":null,"locked_price":null}
     *             ),
     *             @OA\Examples(
     *                 example="non_stocked",
     *                 summary="NON_STOCKED product",
     *                 value={"name":"Service Product","type":"NON_STOCKED","category_id":2,"unit_id":1,"supplier_id":null,"mrp":100.00,"locked_price":90.00}
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(['status' => 'success', 'message' => 'Product deleted.']);
    }
}
