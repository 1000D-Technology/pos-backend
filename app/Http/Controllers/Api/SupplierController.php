<?php

namespace App\Http\Controllers\Api;

use App\DTO\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Supplier;
use http\Client\Response;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $suppliers = Supplier::select('id', 'name', 'email', 'phone', 'address', 'company')->paginate(15);
        return response()->json(
            ApiResponse::success("Suppliers list retrieved successfully.", $suppliers)->toArray(),200
        );
    }

    // Search suppliers by name
    public function search(Request $request)
    {
        $request->validate(['name'=>'required|string|max:100|min:1']);

        $name = $request->input('name');

        $query = Supplier::select('id', 'name', 'email', 'phone', 'address', 'company')->where('name','LIKE', $name.'%');
        $suppliers = $query->paginate(10);

        if($suppliers->isEmpty()){
            return response()->json(
                ApiResponse::error("No suppliers found matching the search criteria.", [])->toArray(),404
            );
        }

        return response()->json(
            ApiResponse::success("Suppliers search results retrieved successfully.", $suppliers)->toArray(),200
        );

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:100',
                'email' => 'required|string|email|max:100|unique:suppliers',
                'phone' => 'nullable|string|max:20|unique:suppliers|regex:/^0\d{9}$/',
                'address' => 'nullable|string|max:255',
                'company' => 'required|string|max:100',
            ]);

            $supplier = Supplier::create($validatedData);

            return response()->json(
                ApiResponse::success(
                  'Supplier created successfully', $supplier,
                ),
                201
            );

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(
                ApiResponse::error('Validation failed', $e->errors())->toArray(),443
            );
        } catch (\Exception $e) {
            return  response()->json(
                ApiResponse::error("Error creating supplier", [$e->getMessage()])->toArray(),500
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $supplier = Supplier::find($id);
        if(!$supplier){
            return response()->json(ApiResponse::error('Supplier not found', ['Supplier not found with ID: '.$id]),404);
        }
        return response()->json(ApiResponse::success('Supplier retrieved successfully', $supplier),200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $supplier = Supplier::find($id);
        if(!$supplier){
            return response()->json(ApiResponse::error('Supplier not found', ['Supplier not found with ID: '.$id]),404);
        }

        try {
            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:100',
                'email' => 'sometimes|required|string|email|max:100|unique:suppliers,email,'.$supplier->id,
                'phone' => 'sometimes|nullable|string|max:20|unique:suppliers,phone,'.$supplier->id.'|regex:/^0\d{9}$/',
                'address' => 'sometimes|nullable|string|max:255',
                'company' => 'sometimes|required|string|max:100',
            ]);

            $supplier->update($validatedData);

            return response()->json(
                ApiResponse::success(
                    'Supplier updated successfully', $supplier,
                ),
                200
            );

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(
                ApiResponse::error('Validation failed', $e->errors())->toArray(),443
            );
        } catch (\Exception $e) {
            return  response()->json(
                ApiResponse::error("Error updating supplier", [$e->getMessage()])->toArray(),500
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $supplier = Supplier::find($id);

            if (!$supplier) {
                return response()->json(
                    ApiResponse::error('Supplier not found', ['Supplier not found with ID: ' . $id])->toArray(),
                    404
                );
            }

            $supplier->delete();

            return response()->json(
                ApiResponse::success('Supplier deleted successfully')->toArray(),
                200
            );

        } catch (\Exception $e) {
            return response()->json(
                ApiResponse::error('Error deleting supplier', [$e->getMessage()])->toArray(),
                500
            );
        }
    }
}
