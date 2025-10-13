<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Salary;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Salary Management",
 *     description="API endpoints for managing employee salary slips"
 * )
 */
class SalaryController extends Controller
{
    /**
     * Display a listing of salary slips.
     *
     * @OA\Get(
     *     path="/api/salaries",
     *     operationId="getSalaries",
     *     tags={"Salary Management"},
     *     summary="Get list of salary slips",
     *     description="Returns paginated list of salary slips with optional filters",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Filter by user ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="salary_month",
     *         in="query",
     *         description="Filter by salary month (Y-m format)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-10")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="user_id", type="integer", example=1),
     *                         @OA\Property(property="salary_month", type="string", example="2025-10"),
     *                         @OA\Property(property="basic_salary", type="number", format="float", example=50000.00),
     *                         @OA\Property(property="allowances", type="number", format="float", example=5000.00),
     *                         @OA\Property(property="deductions", type="number", format="float", example=2000.00),
     *                         @OA\Property(property="total_salary", type="number", format="float", example=53000.00),
     *                         @OA\Property(property="notes", type="string", example="Monthly salary"),
     *                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time"),
     *                         @OA\Property(
     *                             property="user",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="John Doe"),
     *                             @OA\Property(property="email", type="string", example="john@example.com")
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=100)
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
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="This action is unauthorized.")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Salary::with('user:id,name,email');

        // Filter by user_id if provided
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by salary_month if provided
        if ($request->has('salary_month')) {
            $query->where('salary_month', $request->salary_month);
        }

        $salaries = $query->orderBy('salary_month', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $salaries,
        ], 200);
    }

    /**
     * Store a newly created salary slip.
     *
     * @OA\Post(
     *     path="/api/salaries",
     *     operationId="storeSalary",
     *     tags={"Salary Management"},
     *     summary="Create a new salary slip",
     *     description="Generate a new salary slip for an employee",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id","salary_month","basic_salary"},
     *             @OA\Property(property="user_id", type="integer", example=1, description="Employee user ID"),
     *             @OA\Property(property="salary_month", type="string", format="date", example="2025-10", description="Salary month in Y-m format"),
     *             @OA\Property(property="basic_salary", type="number", format="float", example=50000.00, description="Basic salary amount"),
     *             @OA\Property(property="allowances", type="number", format="float", example=5000.00, description="Additional allowances"),
     *             @OA\Property(property="deductions", type="number", format="float", example=2000.00, description="Deductions from salary"),
     *             @OA\Property(property="notes", type="string", example="Monthly salary for October", description="Additional notes")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Salary slip created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Salary slip generated successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="salary_month", type="string", example="2025-10"),
     *                 @OA\Property(property="basic_salary", type="number", format="float", example=50000.00),
     *                 @OA\Property(property="allowances", type="number", format="float", example=5000.00),
     *                 @OA\Property(property="deductions", type="number", format="float", example=2000.00),
     *                 @OA\Property(property="total_salary", type="number", format="float", example=53000.00),
     *                 @OA\Property(property="notes", type="string", example="Monthly salary"),
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john@example.com")
     *                 )
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
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="This action is unauthorized.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Salary slip already exists",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Salary slip already exists for this user and month.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'salary_month' => 'required|date_format:Y-m',
            'basic_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if salary already exists for this user and month
        $existingSalary = Salary::where('user_id', $request->user_id)
            ->where('salary_month', $request->salary_month)
            ->first();

        if ($existingSalary) {
            return response()->json([
                'status' => 'error',
                'message' => 'Salary slip already exists for this user and month.',
            ], 409);
        }

        // Calculate total salary
        $basicSalary = $request->basic_salary;
        $allowances = $request->allowances ?? 0;
        $deductions = $request->deductions ?? 0;
        $totalSalary = $basicSalary + $allowances - $deductions;

        $salary = Salary::create([
            'user_id' => $request->user_id,
            'salary_month' => $request->salary_month,
            'basic_salary' => $basicSalary,
            'allowances' => $allowances,
            'deductions' => $deductions,
            'total_salary' => $totalSalary,
            'notes' => $request->notes,
        ]);

        $salary->load('user:id,name,email');

        return response()->json([
            'status' => 'success',
            'message' => 'Salary slip generated successfully.',
            'data' => $salary,
        ], 201);
    }

    /**
     * Display the specified salary slip.
     *
     * @OA\Get(
     *     path="/api/salaries/{id}",
     *     operationId="getSalaryById",
     *     tags={"Salary Management"},
     *     summary="Get salary slip by ID",
     *     description="Returns a single salary slip",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Salary slip ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="salary_month", type="string", example="2025-10"),
     *                 @OA\Property(property="basic_salary", type="number", format="float", example=50000.00),
     *                 @OA\Property(property="allowances", type="number", format="float", example=5000.00),
     *                 @OA\Property(property="deductions", type="number", format="float", example=2000.00),
     *                 @OA\Property(property="total_salary", type="number", format="float", example=53000.00),
     *                 @OA\Property(property="notes", type="string", example="Monthly salary"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john@example.com")
     *                 )
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
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="This action is unauthorized.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Salary slip not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Salary slip not found.")
     *         )
     *     )
     * )
     */
    public function show(string $id): JsonResponse
    {
        $salary = Salary::with(['user:id,name,email'])
            ->find($id);

        if (!$salary) {
            return response()->json([
                'status' => 'error',
                'message' => 'Salary slip not found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $salary,
        ], 200);
    }
}
