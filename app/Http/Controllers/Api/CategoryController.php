<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * Get all categories
     *
     * Retrieve a list of all active (non-deleted) categories in the system.
     *
     * @OA\Get(
     *     path="/api/categories",
     *     summary="Get all active categories",
     *     description="Retrieve a list of all active categories (excludes soft deleted categories). Supports sorting and pagination.",
     *     operationId="getCategories",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         required=false,
     *         description="Field to sort by (id, name, created_at, updated_at)",
     *         @OA\Schema(
     *             type="string",
     *             enum={"id", "name", "created_at", "updated_at"},
     *             default="created_at"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort_order",
     *         in="query",
     *         required=false,
     *         description="Sort order (asc or desc)",
     *         @OA\Schema(
     *             type="string",
     *             enum={"asc", "desc"},
     *             default="desc"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of items per page (1-100). Omit for no pagination.",
     *         @OA\Schema(
     *             type="integer",
     *             minimum=1,
     *             maximum=100
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Category")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 description="Pagination metadata (only present when per_page is specified)",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=67)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Internal server error"
     *             )
     *         )
     *     )
     * )
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Validate query parameters
        $validated = $request->validate([
            'sort_by' => 'nullable|string|in:id,name,created_at,updated_at',
            'sort_order' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100'
        ]);

        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortOrder = $validated['sort_order'] ?? 'desc';
        $perPage = $validated['per_page'] ?? null;

        $query = Category::orderBy($sortBy, $sortOrder);

        // Apply pagination if per_page is specified
        if ($perPage) {
            $categories = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $categories->items(),
                'meta' => [
                    'current_page' => $categories->currentPage(),
                    'last_page' => $categories->lastPage(),
                    'per_page' => $categories->perPage(),
                    'total' => $categories->total(),
                ]
            ], 200);
        }

        // No pagination - return all results
        $categories = $query->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     path="/api/categories",
     *     summary="Create a new category",
     *     description="Store a newly created category in the database. Requires authentication and categories.manage permission.",
     *     operationId="storeCategory",
     *     tags={"Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Category data",
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 maxLength=255,
     *                 description="Category name",
     *                 example="Electronics"
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 nullable=true,
     *                 description="Category description",
     *                 example="Electronic devices and accessories"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Created category data"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not have the required permission.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 description="Validation errors"
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string'
        ]);

        $category = Category::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => $category
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *     path="/api/categories/{id}",
     *     summary="Get a specific category by ID",
     *     description="Retrieve a single category using its unique identifier",
     *     operationId="getCategoryById",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Category ID",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Category object"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Category not found")
     *         )
     *     )
     * )
     */
    public function show(string $id): JsonResponse
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $category
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * Update an existing category
     * 
     * @OA\Put(
     *     path="/api/categories/{id}",
     *     summary="Update a category",
     *     description="Update an existing category by ID. Requires authentication and categories.manage permission.",
     *     operationId="updateCategory",
     *     tags={"Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Category ID",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Category data to update",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 maxLength=255,
     *                 description="Category name"
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 nullable=true,
     *                 description="Category description"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Updated category object"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not have the required permission.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Category not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 description="Validation errors"
     *             )
     *         )
     *     )
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:categories,name,' . $id,
            'description' => 'nullable|string'
        ]);

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => $category->fresh()
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * Delete a category
     * 
     * @OA\Delete(
     *     path="/api/categories/{id}",
     *     tags={"Categories"},
     *     summary="Delete a category (Soft Delete)",
     *     description="Soft delete a category by ID. The category is marked as deleted but not physically removed from the database. Requires authentication and categories.manage permission.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Category ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category soft deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not have the required permission.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Category not found")
     *         )
     *     )
     * )
     * 
     * @param string $id The category ID to delete
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        // Soft delete using Laravel's delete method
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ], 200);
    }

    /**
     * Restore a soft deleted category
     * 
     * @OA\Post(
     *     path="/api/categories/{id}/restore",
     *     tags={"Categories"},
     *     summary="Restore a soft deleted category",
     *     description="Restore a category that was previously soft deleted. Requires authentication and categories.manage permission.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Category ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category restored successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category restored successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Category"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not have the required permission.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found or not deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Category not found or not deleted")
     *         )
     *     )
     * )
     * 
     * @param string $id The category ID to restore
     * @return JsonResponse
     */
    public function restore(string $id): JsonResponse
    {
        $category = Category::onlyTrashed()->find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found or not deleted'
            ], 404);
        }

        // Restore using Laravel's restore method
        $category->restore();

        return response()->json([
            'success' => true,
            'message' => 'Category restored successfully',
            'data' => $category->fresh()
        ], 200);
    }

    /**
     * Get all deleted categories
     * 
     * @OA\Get(
     *     path="/api/categories/deleted",
     *     tags={"Categories"},
     *     summary="Get all soft deleted categories",
     *     description="Retrieve a list of all soft deleted categories. Requires authentication and categories.manage permission.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of items per page (1-100)",
     *         @OA\Schema(
     *             type="integer",
     *             minimum=1,
     *             maximum=100,
     *             default=15
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Deleted categories retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Category")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=3),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=42)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not have the required permission.")
     *         )
     *     )
     * )
     * 
     * @return JsonResponse
     */
    public function deleted(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1|max:100'
        ]);

        $perPage = $validated['per_page'] ?? 15;

        $categories = Category::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $categories->items(),
            'meta' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
            ]
        ], 200);
    }

    /**
     * Bulk delete categories
     * 
     * @OA\Post(
     *     path="/api/categories/bulk-delete",
     *     tags={"Categories"},
     *     summary="Bulk delete categories",
     *     description="Soft delete multiple categories at once. Requires authentication and categories.manage permission.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Array of category IDs to delete",
     *         @OA\JsonContent(
     *             required={"ids"},
     *             @OA\Property(
     *                 property="ids",
     *                 type="array",
     *                 description="Array of category IDs",
     *                 @OA\Items(type="integer"),
     *                 example={1, 2, 3}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categories bulk deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="3 categories deleted successfully"),
     *             @OA\Property(
     *                 property="deleted_count",
     *                 type="integer",
     *                 example=3
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not have the required permission.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid.")
     *         )
     *     )
     * )
     * 
     * @return JsonResponse
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer'
        ]);

        $ids = $validated['ids'];

        // Check if categories exist
        $existingIds = Category::whereIn('id', $ids)
            ->pluck('id')
            ->toArray();

        if (empty($existingIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid active categories found to delete'
            ], 404);
        }

        // Soft delete all categories with these IDs using Laravel's delete method
        $deletedCount = Category::whereIn('id', $existingIds)->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount} categories deleted successfully",
            'deleted_count' => $deletedCount
        ], 200);
    }

    /**
     * Bulk restore categories
     * 
     * @OA\Post(
     *     path="/api/categories/bulk-restore",
     *     tags={"Categories"},
     *     summary="Bulk restore categories",
     *     description="Restore multiple soft deleted categories at once. Requires authentication and categories.manage permission.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Array of category IDs to restore",
     *         @OA\JsonContent(
     *             required={"ids"},
     *             @OA\Property(
     *                 property="ids",
     *                 type="array",
     *                 description="Array of category IDs",
     *                 @OA\Items(type="integer"),
     *                 example={1, 2, 3}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categories bulk restored successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="3 categories restored successfully"),
     *             @OA\Property(
     *                 property="restored_count",
     *                 type="integer",
     *                 example=3
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You do not have the required permission.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid.")
     *         )
     *     )
     * )
     * 
     * @return JsonResponse
     */
    public function bulkRestore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer'
        ]);

        $ids = $validated['ids'];

        // Check if deleted categories exist
        $existingIds = Category::onlyTrashed()
            ->whereIn('id', $ids)
            ->pluck('id')
            ->toArray();

        if (empty($existingIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid deleted categories found to restore'
            ], 404);
        }

        // Restore all categories with these IDs using Laravel's restore method
        $restoredCount = Category::onlyTrashed()
            ->whereIn('id', $existingIds)
            ->restore();

        return response()->json([
            'success' => true,
            'message' => "{$restoredCount} categories restored successfully",
            'restored_count' => $restoredCount
        ], 200);
    }


    /**
     * Search categories by name or description.
     */
    /**
     * Search categories
     * 
     * @OA\Get(
     *     path="/api/categories/search",
     *     summary="Search active categories by query term",
     *     description="Search for active categories using a query string with pagination support. Excludes soft deleted categories.",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         required=true,
     *         description="Search term to filter categories (case-insensitive partial match on name and description)",
     *         @OA\Schema(
     *             type="string",
     *             minLength=1,
     *             example="electronics"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of items per page (1-100)",
     *         @OA\Schema(
     *             type="integer",
     *             minimum=1,
     *             maximum=100,
     *             default=15,
     *             example=20
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categories found successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Electronics"),
     *                     @OA\Property(property="description", type="string", example="Electronic devices and accessories"),
     *                     @OA\Property(property="product_count", type="integer", example=25)
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=67)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="query",
     *                     type="array",
     *                     @OA\Items(type="string", example="The query field is required.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => 'required|string|min:1',
            'per_page' => 'nullable|integer|min:1|max:100'
        ]);

        $searchTerm = $validated['query'];
        $perPage = $validated['per_page'] ?? 15;

        // Search logic moved from model scope to controller
        $categories = Category::where(function ($query) use ($searchTerm) {
            $query->where('name', 'like', "%{$searchTerm}%")
                ->orWhere('description', 'like', "%{$searchTerm}%");
        })
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $categories->items(),
            'meta' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
            ]
        ], 200);
    }
}
