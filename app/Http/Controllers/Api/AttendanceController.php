<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use App\DTO\ApiResponse;
use App\DTO\AttendanceDTO;

/**
 * @OA\Tag(
 *     name="Attendances",
 *     description="API Endpoints for managing Attendance records"
 * )
 */
class AttendanceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/attendances",
     *     tags={"Attendances"},
     *     summary="List attendance records",
     *     @OA\Parameter(name="user_id", in="query", @OA\Schema(type="integer"), description="Filter by user id"),
     *     @OA\Parameter(name="attendance_date", in="query", @OA\Schema(type="string", format="date"), description="Filter by exact attendance_date (YYYY-MM-DD)"),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer"), description="Pagination size"),
     *     @OA\Response(
     *         response=200,
     *         description="Attendance list retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/AttendanceDTO")),
     *             @OA\Property(property="meta", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'nullable|integer|exists:users,id',
            'attendance_date' => 'nullable|date',
            'per_page' => 'nullable|integer|min:1|max:100'
        ]);

    $query = Attendance::with('user')->orderBy('attendance_date', 'desc');

        if (!empty($validated['user_id'])) {
            $query->where('user_id', $validated['user_id']);
        }

        if (!empty($validated['attendance_date'])) {
            $query->whereDate('attendance_date', $validated['attendance_date']);
        }

        $perPage = $validated['per_page'] ?? null;

        if ($perPage) {
            $results = $query->paginate($perPage);

            $data = array_map(function($a) {
                return [
                    'id' => $a->id,
                    'user' => $a->user ? [
                        'id' => $a->user->id,
                        'name' => $a->user->name,
                        'email' => $a->user->email,
                        'nic' => $a->user->nic ?? null,
                        'basic_salary' => $a->user->basic_salary ?? null,
                        'contact_no' => $a->user->contact_no ?? null,
                        'address' => $a->user->address ?? null,
                    ] : null,
                    'attendance_date' => optional($a->attendance_date)->format('Y-m-d') ?? null,
                    'status' => $a->status,
                    'total_hours' => $a->total_hours,
                    'note' => $a->note,
                    'created_at' => optional($a->created_at)->toDateTimeString(),
                    'updated_at' => optional($a->updated_at)->toDateTimeString(),
                ];
            }, $results->items());

            return response()->json(ApiResponse::success('Attendance list retrieved', [
                'data' => array_map(fn($a) => (new AttendanceDTO($a))->toArray(), $results->items()),
                'meta' => [
                    'current_page' => $results->currentPage(),
                    'last_page' => $results->lastPage(),
                    'per_page' => $results->perPage(),
                    'total' => $results->total(),
                ]
            ])->toArray(), 200);
        }

        $results = $query->get();

        $data = array_map(function($a) {
            return (new AttendanceDTO($a))->toArray();
        }, $results->all());

        return response()->json(ApiResponse::success('Attendance list retrieved', $data)->toArray(), 200);
    }

    /**
     * @OA\Post(
     *     path="/api/attendances",
     *     tags={"Attendances"},
     *     summary="Create a new attendance record",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id","attendance_date","status"},
     *             @OA\Property(property="user_id", type="integer", example=2),
     *             @OA\Property(property="attendance_date", type="string", format="date", example="2025-10-06"),
     *             @OA\Property(property="status", type="string", example="Present"),
     *             @OA\Property(property="total_hours", type="number", format="float", example=8.00),
     *             @OA\Property(property="note", type="string", example="Manual entry by admin")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Attendance created", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/AttendanceDTO"))),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function store(StoreAttendanceRequest $request): JsonResponse
    {
        $data = $request->only(['user_id', 'attendance_date', 'status', 'total_hours', 'note']);

        try {
            $attendance = Attendance::create($data);

            $dto = new AttendanceDTO($attendance);
            return response()->json(ApiResponse::success('Attendance created successfully', $dto->toArray())->toArray(), 201);

        } catch (QueryException $e) {
            // Handle duplicate unique constraint and other DB errors
            $sqlState = $e->errorInfo[0] ?? null; // SQLSTATE
            $errorCode = $e->errorInfo[1] ?? null; // driver error code (MySQL etc)

            // MySQL duplicate entry: SQLSTATE 23000 and error code 1062
            if ($sqlState === '23000' || $errorCode === 1062 || stripos($e->getMessage(), 'duplicate') !== false || stripos($e->getMessage(), 'unique') !== false) {
                return response()->json(ApiResponse::error('An attendance record for this user on the specified date already exists.')->toArray(), 409);
            }

            return response()->json(ApiResponse::error('Database error while creating attendance', [$e->getMessage()])->toArray(), 500);
        } catch (ValidationException $e) {
            return response()->json(ApiResponse::error('Validation Error', $e->errors())->toArray(), 422);
        } catch (\Exception $e) {
            return response()->json(ApiResponse::error('Error creating attendance', [$e->getMessage()])->toArray(), 500);
        }
    }

    /**
     * Show attendance records for a given user id.
     * The route parameter {id} is treated as user_id.
     * Optional query param: month=YYYY-MM to filter by month.
     *
     * @OA\Get(
     *     path="/api/attendances/{id}",
     *     tags={"Attendances"},
     *     summary="Get attendance records for a user",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"), description="User ID"),
     *     @OA\Parameter(name="month", in="query", @OA\Schema(type="string"), description="Filter by month (YYYY-MM)"),
     *     @OA\Response(response=200, description="Attendances retrieved", @OA\JsonContent(@OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/AttendanceDTO"))))
     * )
     */
    public function show(Request $request, string $id): JsonResponse
    {
        // Validate inputs
        $validated = $request->validate([
            'month' => ['nullable', 'regex:/^\d{4}-\d{2}$/'] // YYYY-MM
        ]);

        $query = Attendance::with('user')->where('user_id', $id)->orderBy('attendance_date', 'desc');

        if (! empty($validated['month'])) {
            [$year, $month] = explode('-', $validated['month']);
            $query->whereYear('attendance_date', $year)->whereMonth('attendance_date', $month);
        }

        $results = $query->get();

        if ($results->isEmpty()) {
            return response()->json(ApiResponse::success('No attendances found', [])->toArray(), 200);
        }

        $data = array_map(fn($a) => (new AttendanceDTO($a))->toArray(), $results->all());

        return response()->json(ApiResponse::success('Attendances retrieved', $data)->toArray(), 200);
    }

    /**
     * @OA\Put(
     *     path="/api/attendances/{id}",
     *     tags={"Attendances"},
     *     summary="Update an attendance record",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"), description="Attendance ID"),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="integer", example=2),
     *             @OA\Property(property="attendance_date", type="string", format="date", example="2025-10-06"),
     *             @OA\Property(property="status", type="string", example="Present"),
     *             @OA\Property(property="total_hours", type="number", format="float", example=8.00),
     *             @OA\Property(property="note", type="string", example="Adjusted note")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Attendance updated", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/AttendanceDTO"))),
     *     @OA\Response(response=404, description="Attendance not found"),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function update(UpdateAttendanceRequest $request, string $id): JsonResponse
    {
        $attendance = Attendance::find($id);

        if (!$attendance) {
            return response()->json(ApiResponse::error('Attendance not found')->toArray(), 404);
        }
        $attendance->fill($request->only(['user_id', 'attendance_date', 'status', 'total_hours', 'note']));

        try {
            $attendance->save();

            $dto = new AttendanceDTO($attendance);
            return response()->json(ApiResponse::success('Attendance updated successfully', $dto->toArray())->toArray(), 200);

        } catch (QueryException $e) {
            $sqlState = $e->errorInfo[0] ?? null;
            $errorCode = $e->errorInfo[1] ?? null;

            if ($sqlState === '23000' || $errorCode === 1062 || stripos($e->getMessage(), 'duplicate') !== false || stripos($e->getMessage(), 'unique') !== false) {
                return response()->json([
                    'success' => false,
                    'message' => 'An attendance record for this user on the specified date already exists.'
                ], 409);
            }

            return response()->json(['success' => false, 'message' => 'Database error while updating attendance', 'error' => $e->getMessage()], 500);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation Error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error updating attendance', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/attendances/{id}",
     *     tags={"Attendances"},
     *     summary="Soft delete an attendance record",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer"), description="Attendance ID"),
     *     @OA\Response(response=200, description="Attendance deleted successfully"),
     *     @OA\Response(response=404, description="Attendance not found")
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        $attendance = Attendance::find($id);

        if (! $attendance) {
            return response()->json(['success' => false, 'message' => 'Attendance not found'], 404);
        }

        try {
            // Soft delete
            $attendance->delete();
            return response()->json(ApiResponse::success('Attendance soft-deleted successfully')->toArray(), 200);
        } catch (QueryException $e) {
            return response()->json(['success' => false, 'message' => 'Database error while deleting attendance', 'error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting attendance', 'error' => $e->getMessage()], 500);
        }
    }
}
