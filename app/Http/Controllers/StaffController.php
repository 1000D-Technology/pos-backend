<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; // Import Rule for unique validation

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // By default, SoftDeletes trait excludes soft-deleted records from `all()`
        $staff = Staff::orderBy('role_name')->get();
        return response()->json($staff);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_name' => [
                'required',
                'string',
                'max:255',
                // Ensure role_name is unique among active (non-deleted) staff members
                Rule::unique('staff')->where(function ($query) {
                    $query->whereNull('deleted_at');
                }),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $staff = Staff::create($request->all());
        return response()->json($staff, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // find() automatically excludes soft-deleted records by default with SoftDeletes trait
        $staff = Staff::find($id);

        if (!$staff) {
            return response()->json(['message' => 'Staff member not found'], 404);
        }

        return response()->json($staff);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // find() automatically excludes soft-deleted records by default with SoftDeletes trait
        $staff = Staff::find($id);

        if (!$staff) {
            return response()->json(['message' => 'Staff member not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'role_name' => [
                'required',
                'string',
                'max:255',
                // Ensure role_name is unique among active staff members, ignoring the current staff member
                Rule::unique('staff')->ignore($staff->id)->where(function ($query) {
                    $query->whereNull('deleted_at');
                }),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $staff->update($request->all());
        return response()->json($staff);
    }

    /**
     * Remove the specified resource from storage. (Soft Delete)
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // find() automatically excludes soft-deleted records by default with SoftDeletes trait
        $staff = Staff::find($id);

        if (!$staff) {
            return response()->json(['message' => 'Staff member not found'], 404);
        }

        $staff->delete(); // This will soft delete the staff member
        return response()->json(['message' => 'Staff member soft deleted successfully']);
    }
}