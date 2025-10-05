<?php

namespace App\Http\Controllers\Api;

use App\DTO\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{

    /**
     * Display a listing of the resource.
     */

    /**
      * @OA\Get(
      *     path="/api/suppliers",
      *     tags={"Suppliers"},
      *     summary="Get paginated list of suppliers",
      *     @OA\Response(
      *         response=200,
      *         description="Successful operation",
      *         @OA\JsonContent(
      *             @OA\Property(property="success", type="boolean", example=true),
      *             @OA\Property(property="message", type="string", example="Suppliers list retrieved successfully."),
      *             @OA\Property(
      *                 property="data",
      *                 type="object",
      *                 @OA\Property(property="current_page", type="integer", example=1),
      *                 @OA\Property(
      *                     property="data",
      *                     type="array",
      *                     @OA\Items(ref="#/components/schemas/Supplier")
      *                 ),
      *                 @OA\Property(property="first_page_url", type="string"),
      *                 @OA\Property(property="from", type="integer"),
      *                 @OA\Property(property="last_page", type="integer"),
      *                 @OA\Property(property="last_page_url", type="string"),
      *                 @OA\Property(property="links", type="array", @OA\Items(type="object")),
      *                 @OA\Property(property="next_page_url", type="string", nullable=true),
      *                 @OA\Property(property="path", type="string"),
      *                 @OA\Property(property="per_page", type="integer"),
      *                 @OA\Property(property="prev_page_url", type="string", nullable=true),
      *                 @OA\Property(property="to", type="integer"),
      *                 @OA\Property(property="total", type="integer")
      *             )
      *         )
      *     )
      * )
      */
    public function index()
    {
        $suppliers = Supplier::select('id', 'name', 'email', 'phone', 'address', 'company')->paginate(15);
        return response()->json(
            ApiResponse::success("Suppliers list retrieved successfully.", $suppliers)->toArray(),200
        );
    }

    // Search suppliers by name, email, phone, or company using a single search term (OR logic)
    /**
     * @OA\Get(
     * path="/api/suppliers/search",
     * tags={"Suppliers"},
     * summary="Search suppliers by name, email, phone, or company",
     * @OA\Parameter(
     * name="search_term",
     * in="query",
     * required=true,
     * description="The term to search for across name, email, phone, and company fields.",
     * @OA\Schema(type="string", minLength=1)
     * ),
     * @OA\Response(
     * response=200,
     * description="Suppliers search results retrieved successfully.",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Suppliers search results retrieved successfully."),
     * @OA\Property(property="data", type="object",
     * @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Supplier"))
     * )
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="No suppliers found matching the search criteria."
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation failed (e.g., 'search_term' is missing)."
     * ),
     * )
     */
    public function search(Request $request)
    {
        $request->validate(['search_term'=>'required|string|max:100|min:1']);

        $searchTerm = $request->input('search_term');
        $like = '%'.$searchTerm.'%';
        $query = Supplier::select('id', 'name', 'email', 'phone', 'address', 'company')->where(function ($q) use ($like) {
            $q->where('name', 'LIKE', $like)
              ->orWhere('email', 'LIKE', $like)
              ->orWhere('phone', 'LIKE', $like)
              ->orWhere('company', 'LIKE', $like);
        });
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
    /**
     * @OA\Post(
     * path="/api/suppliers",
     * tags={"Suppliers"},
     * summary="Create a new supplier",
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"name", "email", "company"},
     * @OA\Property(property="name", type="string", example="New Supplier Co.", maxLength=100),
     * @OA\Property(property="email", type="string", format="email", example="supplier@example.com", maxLength=100),
     * @OA\Property(property="phone", type="string", example="0771234567", maxLength=20, nullable=true),
     * @OA\Property(property="address", type="string", example="123 Main St, City", maxLength=255, nullable=true),
     * @OA\Property(property="company", type="string", example="Acme Products", maxLength=100),
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Supplier created successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Supplier created successfully"),
     * @OA\Property(property="data", ref="#/components/schemas/Supplier")
     * )
     * ),
     * @OA\Response(
     * response=443,
     * description="Validation failed"
     * ),
     * @OA\Response(
     * response=500,
     * description="Server error"
     * ),
     * )
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
    /**
     * @OA\Get(
     * path="/api/suppliers/{id}",
     * tags={"Suppliers"},
     * summary="Retrieve a specific supplier by ID",
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="ID of the supplier to retrieve",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Supplier retrieved successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Supplier retrieved successfully"),
     * @OA\Property(property="data", ref="#/components/schemas/Supplier")
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Supplier not found"
     * )
     * )
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
    /**
     * @OA\Put(
     * path="/api/suppliers/{id}",
     * tags={"Suppliers"},
     * summary="Update an existing supplier",
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="ID of the supplier to update",
     * @OA\Schema(type="integer")
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="name", type="string", example="Updated Supplier Name", maxLength=100, nullable=true),
     * @OA\Property(property="email", type="string", format="email", example="new_email@example.com", maxLength=100, nullable=true),
     * @OA\Property(property="phone", type="string", example="0779998887", maxLength=20, nullable=true),
     * @OA\Property(property="address", type="string", example="456 New Road, Town", maxLength=255, nullable=true),
     * @OA\Property(property="company", type="string", example="New Products Inc.", maxLength=100, nullable=true),
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Supplier updated successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Supplier updated successfully"),
     * @OA\Property(property="data", ref="#/components/schemas/Supplier")
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Supplier not found"
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation failed"
     * ),
     * @OA\Response(
     * response=500,
     * description="Server error"
     * ),
     * )
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
    /**
     * @OA\Delete(
     * path="/api/suppliers/{id}",
     * tags={"Suppliers"},
     * summary="Delete a supplier (Soft Delete)",
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="ID of the supplier to delete",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Supplier deleted successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Supplier deleted successfully")
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Supplier not found"
     * ),
     * @OA\Response(
     * response=500,
     * description="Server error"
     * ),
     * )
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
