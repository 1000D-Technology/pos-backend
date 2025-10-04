<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; // Make sure to add this line

/**
 * @OA\Tag(
 *     name="Units",
 *     description="API endpoints for managing units"
 * )
 */
class UnitController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/units",
     *     summary="Get all active units",
     *     tags={"Units"},
     *     @OA\Response(
     *         response=200,
     *         description="List of all active units",
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
    public function index()
    {
        // With SoftDeletes trait, `all()` automatically excludes soft-deleted records.
        $units = Unit::all();
        return response()->json($units);
    }

    /**
     * @OA\Post(
     *     path="/api/units",
     *     summary="Create a new unit",
     *     tags={"Units"},
     *     security={{"bearerAuth":{}}}, // Assuming 'permission:unit.manage' might be tied to authentication
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "symbol"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="Kilogram"),
     *             @OA\Property(property="symbol", type="string", maxLength=255, example="kg")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Unit created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Kilogram"),
     *             @OA\Property(property="symbol", type="string", example="kg"),
     *             @OA\Property(property="created_at", type="string", format="datetime", example="2023-10-27T10:00:00.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="datetime", example="2023-10-27T10:00:00.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object", example={"name": {"The name has already been taken."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - if bearerAuth is active"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - if permission:unit.manage is active"
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
                    return $query->whereNull('deleted_at'); // Only check against non-deleted units
                }),
            ],
            'symbol' => [
                'required',
                'string',
                'max:255',
                Rule::unique('units')->where(function ($query) {
                    return $query->whereNull('deleted_at'); // Only check against non-deleted units
                }),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $unit = Unit::create($request->all());
        return response()->json($unit, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/units/{id}",
     *     summary="Get a specific active unit by ID",
     *     tags={"Units"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the unit",
     *         @OA\Schema(type="integer", format="int64", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Unit details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Kilogram"),
     *             @OA\Property(property="symbol", type="string", example="kg"),
     *             @OA\Property(property="created_at", type="string", format="datetime", example="2023-10-27T10:00:00.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="datetime", example="2023-10-27T10:00:00.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Unit not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unit not found")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        // With SoftDeletes trait, `find()` automatically excludes soft-deleted records by default.
        $unit = Unit::find($id);

        if (!$unit) {
            return response()->json(['message' => 'Unit not found'], 404);
        }

        return response()->json($unit);
    }

    /**
     * @OA\Put(
     *     path="/api/units/{id}",
     *     summary="Update a unit",
     *     tags={"Units"},
     *     security={{"bearerAuth":{}}}, // Assuming 'permission:unit.manage' might be tied to authentication
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the unit",
     *         @OA\Schema(type="integer", format="int64", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "symbol"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="Kilogram"),
     *             @OA\Property(property="symbol", type="string", maxLength=255, example="Kg")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Unit updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Kilogram"),
     *             @OA\Property(property="symbol", type="string", example="Kg"),
     *             @OA\Property(property="created_at", type="string", format="datetime", example="2023-10-27T10:00:00.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="datetime", example="2023-10-27T10:00:00.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Unit not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unit not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object", example={"symbol": {"The symbol has already been taken."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - if bearerAuth is active"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - if permission:unit.manage is active"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        // With SoftDeletes trait, `find()` automatically excludes soft-deleted records by default.
        $unit = Unit::find($id);

        if (!$unit) {
            return response()->json(['message' => 'Unit not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('units')->ignore($unit->id)->where(function ($query) {
                    return $query->whereNull('deleted_at'); // Only check against non-deleted units
                }),
            ],
            'symbol' => [
                'required',
                'string',
                'max:255',
                Rule::unique('units')->ignore($unit->id)->where(function ($query) {
                    return $query->whereNull('deleted_at'); // Only check against non-deleted units
                }),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $unit->update($request->all());
        return response()->json($unit);
    }

    /**
     * @OA\Delete(
     *     path="/api/units/{id}",
     *     summary="Soft delete a unit",
     *     tags={"Units"},
     *     security={{"bearerAuth":{}}}, // Assuming 'permission:unit.manage' might be tied to authentication
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the unit to soft delete",
     *         @OA\Schema(type="integer", format="int64", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Unit soft deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unit soft deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Unit not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unit not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - if bearerAuth is active"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - if permission:unit.manage is active"
     *     )
     * )
     */
    public function destroy($id)
    {
        // With SoftDeletes trait, `find()` automatically excludes soft-deleted records by default.
        $unit = Unit::find($id);

        if (!$unit) {
            return response()->json(['message' => 'Unit not found'], 404);
        }

        $unit->delete(); // This performs a soft delete
        return response()->json(['message' => 'Unit soft deleted successfully']);
    }

    /**
     * @OA\Get(
     *     path="/api/units/search",
     *     summary="Search active units by name or symbol (case-insensitive, partial match)",
     *     tags={"Units"},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=true,
     *         description="Search term for unit name or symbol",
     *         @OA\Schema(type="string", example="kg")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of matching active units",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Kilogram"),
     *                 @OA\Property(property="symbol", type="string", example="kg"),
     *                 @OA\Property(property="created_at", type="string", format="datetime", example="2023-10-27T10:00:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime", example="2023-10-27T10:00:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Search query not provided",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Please provide a search query (e.g., ?name=kg)")
     *         )
     *     )
     * )
     */
    public function search(Request $request)
    {
        $searchTerm = $request->input('name'); // Assuming Postman sends 'name' in query parameters

        if (!$searchTerm) {
            return response()->json(['message' => 'Please provide a search query (e.g., ?name=kg)'], 400);
        }

        $units = Unit::where(function ($query) use ($searchTerm) {
            // For PostgreSQL, use ILIKE for case-insensitive search
            // For MySQL, use LIKE and ensure database collation is case-insensitive,
            // or use whereRaw("LOWER(name) LIKE ?", ['%' . strtolower($searchTerm) . '%'])
            $query->where('name', 'ILIKE', '%' . $searchTerm . '%')
                  ->orWhere('symbol', 'ILIKE', '%' . $searchTerm . '%');
        })
        ->get(); // SoftDeletes trait automatically excludes soft-deleted records from `get()`

        return response()->json($units);
    }
}