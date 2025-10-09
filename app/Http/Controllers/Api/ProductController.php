<?php

namespace App\Http\Controllers\Api;

use App\DTO\ProductDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
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

        $perPage = (int) $request->query('per_page', 15);
        $products = $query->paginate($perPage)->appends($request->query());

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request)
    {
        $dto = ProductDTO::fromArray($request->validated());
        $product = Product::create($dto->toArray());

        return new ProductResource($product->load(['category', 'unit', 'supplier']));
    }

    public function show($id)
    {
        $product = Product::with(['category', 'unit', 'supplier'])->findOrFail($id);
        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);
        $dto = ProductDTO::fromArray($request->validated());
        $product->update($dto->toArray());

        return new ProductResource($product->fresh(['category', 'unit', 'supplier']));
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(['status' => 'success', 'message' => 'Product deleted.']);
    }
}
