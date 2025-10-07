<?php

namespace App\Http\Controllers;

use App\DTO\ApiResponse;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * @OA\Tag(
 *     name="Staff Roles",
 *     description="API endpoints for managing staff roles"
 * )
 */
class StaffController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/staff-roles",
     *     summary="List all active staff roles",
     *     tags={"Staff Roles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of active staff roles",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Staff roles retrieved successfully"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="role_name", type="string"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        // The SoftDeletes trait automatically excludes soft-deleted records.
        $staff = Staff::orderBy('role_name')->get();
        return response()->json(
            ApiResponse::success('Staff roles retrieved successfully', $staff)->toArray()
        );
    }

    /**
     * @OA\Post(
     *     path="/api/staff-roles",
     *     summary="Create a new staff role",
     *     tags={"Staff Roles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"role_name"},
     *             @OA\Property(property="role_name", type="string", maxLength=255, example="Manager")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Staff role created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Staff role created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="role_name", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_name' => [
                'required',
                'string',
                'max:255',
                // Ensure role_name is unique among active (non-deleted) staff roles
                Rule::unique('staff')->where(function ($query) {
                    $query->whereNull('deleted_at');
                }),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(
                ApiResponse::error('Validation failed', $validator->errors()->toArray())->toArray(),
                422
            );
        }

        $staff = Staff::create($request->all());
        return response()->json(
            ApiResponse::success('Staff role created successfully', $staff)->toArray(),
            201
        );
    }

    /**
     * @OA\Get(
     *     path="/api/staff-roles/{id}",
     *     summary="Get a specific staff role by ID",
     *     tags={"Staff Roles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the staff role",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Staff role details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Staff role retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="role_name", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Staff role not found"
     *     )
     * )
     */
    public function show($id)
    {
        // find() automatically excludes soft-deleted records.
        $staff = Staff::find($id);

        if (!$staff) {
            return response()->json(
                ApiResponse::error('Staff role not found')->toArray(),
                404
            );
        }

        return response()->json(
            ApiResponse::success('Staff role retrieved successfully', $staff)->toArray()
        );
    }

    /**
     * @OA\Put(
     *     path="/api/staff-roles/{id}",
     *     summary="Update a staff role",
     *     tags={"Staff Roles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the staff role",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"role_name"},
     *             @OA\Property(property="role_name", type="string", maxLength=255, example="Senior Manager")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Staff role updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Staff role updated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="role_name", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Staff role not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        // find() automatically excludes soft-deleted records.
        $staff = Staff::find($id);

        if (!$staff) {
            return response()->json(
                ApiResponse::error('Staff role not found')->toArray(),
                404
            );
        }

        $validator = Validator::make($request->all(), [
            'role_name' => [
                'required',
                'string',
                'max:255',
                // Ensure role_name is unique among active roles, ignoring the current one.
                Rule::unique('staff')->ignore($staff->id)->where(function ($query) {
                    $query->whereNull('deleted_at');
                }),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(
                ApiResponse::error('Validation failed', $validator->errors()->toArray())->toArray(),
                422
            );
        }

        $staff->update($request->all());
        return response()->json(
            ApiResponse::success('Staff role updated successfully', $staff)->toArray()
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/staff-roles/{id}",
     *     summary="Soft delete a staff role",
     *     tags={"Staff Roles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the staff role to soft delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Staff role soft deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Staff role soft deleted successfully"),
     *             @OA\Property(property="data", type="object", example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Staff role not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        // find() automatically excludes soft-deleted records.
        $staff = Staff::find($id);

        if (!$staff) {
            return response()->json(
                ApiResponse::error('Staff role not found')->toArray(),
                404
            );
        }

        $staff->delete(); // This performs a soft delete.
        return response()->json(
            ApiResponse::success('Staff role soft deleted successfully')->toArray()
        );
    }
}