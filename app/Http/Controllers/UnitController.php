<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
     *     summary="Get all units",
     *     tags={"Units"},
     *     @OA\Response(
     *         response=200,
     *         description="List of all units",
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
        $units = Unit::all();
        return response()->json($units);
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
            'name' => 'required|string|max:255|unique:units,name',
            'symbol' => 'required|string|max:255|unique:units,symbol',
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
     *     summary="Get a specific unit by ID",
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
        $unit = Unit::find($id);

        if (!$unit) {
            return response()->json(['message' => 'Unit not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:units,name,' . $id,
            'symbol' => 'required|string|max:255|unique:units,symbol,' . $id,
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
     *     summary="Delete a unit",
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
     *         description="Unit deleted successfully",
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
        $unit = Unit::find($id);

        if (!$unit) {
            return response()->json(['message' => 'Unit not found'], 404);
        }

        $unit->delete();
        return response()->json(['message' => 'Unit deleted successfully']);
    }

    
    /**
     * @OA\Get(
     *     path="/api/units/search",
     *     summary="Search units by name or symbol",
     *     tags={"Units"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=true,
     *         description="Search term for unit name or symbol",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of matching units",
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
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Search query not provided"
     *     )
     * )
     */
    public function search(Request $request)
    {
        $query = $request->input('name'); // Using 'name' as a general search term as per requirement

        if (!$query) {
            return response()->json(['message' => 'Please provide a search query (e.g., ?name=kg)'], 400);
        }

        $units = Unit::where('name', 'like', '%' . $query . '%')
                     ->orWhere('symbol', 'like', '%' . $query . '%')
                     ->get();

        return response()->json($units);
    }
}