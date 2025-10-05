<?php

namespace App\Http\Controllers;

use App\DTO\ApiResponse;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * @OA\Tag(
 *     name="Units",
 *     description="API endpoints for managing units"
 * )
 * @OA\Parameter(
 *     parameter="search_query",
 *     name="search",
 *     in="query",
 *     description="Search term for filtering units by name or symbol (case-insensitive, partial match). Excludes soft-deleted units.",
 *     @OA\Schema(type="string")
 * )
 */
class UnitController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/units",
     *     summary="List all active units or search units by name or symbol",
     *     tags={"Units"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Optional search term for filtering units by name or symbol. Excludes soft-deleted units.",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of units (filtered by search term if provided)",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="symbol", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="datetime"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        // SoftDeletes trait automatically excludes soft-deleted records from query()
        $query = Unit::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $searchLower = strtolower($search);
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . $searchLower . '%'])
                  ->orWhereRaw('LOWER(symbol) LIKE ?', ['%' . $searchLower . '%']);
            });
        }
        
        $units = $query->orderBy('name')->get();
        return response()->json(
            ApiResponse::success('Units retrieved successfully', $units)->toArray()
        );
    }


    /**
     * @OA\Post(
     *     path="/api/units",
     *     summary="Create a new unit",
     *     tags={"Units"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "symbol"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="symbol", type="string", maxLength=255)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Unit created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="symbol", type="string"),
     *             @OA\Property(property="created_at", type="string", format="datetime"),
     *             @OA\Property(property="updated_at", type="string", format="datetime")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('units')->where(function ($query) {
                    $query->whereNull('deleted_at'); // Only check against non-deleted units
                }),
            ],
            'symbol' => [
                'required',
                'string',
                'max:255',
                Rule::unique('units')->where(function ($query) {
                    $query->whereNull('deleted_at'); // Only check against non-deleted units
                }),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(
                ApiResponse::error('Validation failed', $validator->errors()->toArray())->toArray(),
                422
            );
        }

        $unit = Unit::create($request->all());
        return response()->json(
            ApiResponse::success('Unit created successfully', $unit)->toArray(),
            201
        );
    }


    /**
     * @OA\Get(
     *     path="/api/units/{id}",
     *     summary="Get a specific active unit by ID",
     *     tags={"Units"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the unit",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Unit details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="symbol", type="string"),
     *             @OA\Property(property="created_at", type="string", format="datetime"),
     *             @OA\Property(property="updated_at", type="string", format="datetime")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Unit not found"
     *     )
     * )
     */
    public function show($id)
    {
        $unit = Unit::find($id); // find() automatically excludes soft-deleted records

        if (!$unit) {
            return response()->json(
                ApiResponse::error('Unit not found')->toArray(),
                404
            );
        }

        return response()->json(
            ApiResponse::success('Unit retrieved successfully', $unit)->toArray()
        );
    }


    /**
     * @OA\Put(
     *     path="/api/units/{id}",
     *     summary="Update a unit",
     *     tags={"Units"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the unit",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "symbol"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="symbol", type="string", maxLength=255)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Unit updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="symbol", type="string"),
     *             @OA\Property(property="created_at", type="string", format="datetime"),
     *             @OA\Property(property="updated_at", type="string", format="datetime")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Unit not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $unit = Unit::find($id); // find() automatically excludes soft-deleted records

        if (!$unit) {
            return response()->json(
                ApiResponse::error('Unit not found')->toArray(),
                404
            );
        }

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('units')->ignore($unit->id)->where(function ($query) {
                    $query->whereNull('deleted_at'); // Only check against non-deleted units
                }),
            ],
            'symbol' => [
                'required',
                'string',
                'max:255',
                Rule::unique('units')->ignore($unit->id)->where(function ($query) {
                    $query->whereNull('deleted_at'); // Only check against non-deleted units
                }),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(
                ApiResponse::error('Validation failed', $validator->errors()->toArray())->toArray(),
                422
            );
        }

        $unit->update($request->all());
        return response()->json(
            ApiResponse::success('Unit updated successfully', $unit)->toArray()
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/units/{id}",
     *     summary="Soft delete a unit",
     *     tags={"Units"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the unit to soft delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Unit soft deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Unit not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        $unit = Unit::find($id); // find() automatically excludes soft-deleted records

        if (!$unit) {
            return response()->json(
                ApiResponse::error('Unit not found')->toArray(),
                404
            );
        }

        $unit->delete(); // This will soft delete the unit
        return response()->json(
            ApiResponse::success('Unit soft deleted successfully')->toArray()
        );
    }

    /**
     * @OA\Get(
     *     path="/api/units/search",
     *     summary="Search units by name or symbol",
     *     tags={"Units"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Search term for filtering units",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of items per page",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful search results"
     *     )
     * )
     */
   
}
