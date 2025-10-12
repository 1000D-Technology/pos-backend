<?php

namespace App\Http\Controllers\Api;

use App\DTO\ProductDTO;
use App\Http\Controllers\Controller;
// Using inline validation to match supplier-style; FormRequest files are present but not used here
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

    public function store(Request $request)
    {
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
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('type') === 'NON_STOCKED' && !is_null($value)) {
                        $fail('MRP must be null for NON_STOCKED products.');
                    }
                },
            ],
            'locked_price' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('type') === 'NON_STOCKED' && !is_null($value)) {
                        $fail('Locked price must be null for NON_STOCKED products.');
                    }
                },
            ],
            'cabin_number' => 'nullable|string|max:100',
            'img' => 'nullable|url',
            'color' => 'nullable|string|max:50',
            'barcode' => 'nullable|string|max:255|unique:products,barcode',
        ]);

        $dto = ProductDTO::fromArray($validated);
        $product = Product::create($dto->toArray());

        return new ProductResource($product->load(['category', 'unit', 'supplier']));
    }

    public function show($id)
    {
        $product = Product::with(['category', 'unit', 'supplier'])->findOrFail($id);
        return new ProductResource($product);
    }

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
                function ($attribute, $value, $fail) use ($request) {
                    if (($request->has('type') && $request->input('type') === 'NON_STOCKED') && !is_null($value)) {
                        $fail('MRP must be null for NON_STOCKED products.');
                    }
                },
            ],
            'locked_price' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    if (($request->has('type') && $request->input('type') === 'NON_STOCKED') && !is_null($value)) {
                        $fail('Locked price must be null for NON_STOCKED products.');
                    }
                },
            ],
            'cabin_number' => 'nullable|string|max:100',
            'img' => 'nullable|url',
            'color' => 'nullable|string|max:50',
            'barcode' => "nullable|string|max:255|unique:products,barcode,{$id}",
        ]);

        $dto = ProductDTO::fromArray($validated);
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
